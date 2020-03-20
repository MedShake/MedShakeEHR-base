/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
 * Bertrand Boutillier <b.boutillier@gmail.com>
 * http://www.medshake.net
 *
 * MedShakeEHR is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * MedShakeEHR is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MedShakeEHR.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Fonctions JS pour la lecture vitale / CPS et la réalisation de FSE
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

var dataVitale = [];
var reglementObjetID;
var originalModalBody;

/**
 * Obtenir les data nécessaires pour l'envoi au module tiers
 * @param  {object} el élément cliqué
 * @return void
 */
function getFseData(el) {
  originalModalBody = $('#modalFaireFse div.modal-body').html();
  reglementObjetID = el.closest('tr').attr('data-objetID');
  mode = el.attr('data-mode');
  $.ajax({
    url: urlBase + '/patient/ajax/getFseData/',
    type: 'get',
    data: {
      objetID: reglementObjetID,
      mode: mode
    },
    dataType: "json",
    success: function(data) {
      console.log(data);

      // remplir le form
      $.each(data.formFields, function(key, value) {
        $('#modalFaireFseStartForm [name="' + key + '"]').val(value);
      });

      // si mode simple
      if (mode == 'simple') {
        $('#fseRetirerSiModeSimple').html('');
        doFse(el);
        return;
      }

      // présenter les actes
      html = '<table class="table table-sm text-right">';
      html += '<thead class="thead-light"><tr> \
      <th class="text-left" colspan="2">Actes - modificateurs</th> \
      <th ></th> \
      <th >Code asso.</th> \
      <th>P.U.</th> \
      <th>%</th> \
      <th>Dep.</th> \
      </tr></thead><tbody>';
      $.each(data.actes, function(k, v) {
        if (!('qte' in v)) v.qte = '';
        if (!('modifsCCAM' in v)) v.modifsCCAM = '';
        if (!('codeAsso' in v)) v.codeAsso = '';
        if (!('codeQualif' in v)) v.codeQualif = '';
        if (!('depassement' in v)) {
          v.depassement = '';
        } else {
          v.depassement = v.depassement + ' €';
        }
        if (!('pourcents' in v)) {
          v.pourcents = '';
        } else {
          v.pourcents = v.pourcents + ' %';
        }

        html += '<tr> \
        <td class="text-left">' + (v.qte > 1 ? v.qte : '') + v.acte + '</td> \
        <td>' + v.codeQualif + '</td> \
        <td>' + v.modifsCCAM + '</td> \
        <td>' + v.codeAsso + '</td> \
        <td>' + v.base + ' €</td> \
        <td>' + v.pourcents + ' </td> \
        <td>' + v.depassement + '</td> \
        </tr>';
      });
      html += '</tbody></table>';
      $('#modalFaireFseActes').html(html);
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

/**
 * Enclencher l'action finale de réalisation de FSE
 * @param  {object} el élément cliqué
 * @return {void}
 */
function doFse(el) {
  serviceVitale = el.attr('data-vitaleservice');
  $.ajax({
    url: urlBase + '/modulesExternes/' + serviceVitale + '/faireFse.php',
    type: 'post',
    data: $("#modalFaireFseStartForm").serialize(),
    dataType: "html",
    success: function(data) {
      console.log(data);
      $('#modalFaireFseFinishForm').html(data);
      $('#' + serviceVitale + 'ActionForm').submit();
      fseWait();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

/**
 * Attente du retour des data FSE par l'API REST
 * @return {void}
 */
function fseWait() {
  $('#modalFaireFseValider').addClass('d-none');
  waitingMsg = '<div class="text-center"><div class="spinner-grow text-success" style="width: 3rem; height: 3rem;" role="status"><span class="sr-only">Attente du retour</span></div><div>Nous attendons le retour d\'informations concernant la FSE établie.<br>Ce message sera mis à jour automatiquement.</div></div>';
  $('#modalFaireFse div.modal-body').html(waitingMsg);
  getFseReturnData();
}

/**
 * Vérification récursive de la présence des data FSE de retour
 * @return {void}
 */
function getFseReturnData() {
  $.ajax({
    url: urlBase + '/patient/ajax/getFseReturnData/',
    type: 'get',
    data: {
      objetID: reglementObjetID,
    },
    dataType: "json",
    success: function(data) {
      if (data.status == 'wait') {
        setTimeout(getFseReturnData, 2000);
      } else if (data.status == 'end') {
        fseEnd(data);
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

/**
 * Actions quand le retour des data FSE a eu lieu via API
 * @param  {object} data data
 * @return {void}
 */
function fseEnd(data) {
  if (data.aPayerError || data.totalError) {
    endMsg = '<div class="text-center"> \
    <i class="fas fa-exclamation-circle text-warning fa-4x"></i> \
    <span class="d-block font-weight-bold mt-2">Il existe un différentiel entre le règlement enregistré et la FSE réalisée.<br>Éditez votre règlement pour corriger cette situation.</span> \
    <table class="table table-sm table-hover my-3"> \
      <thead class="thead-light"><tr><th></th><th>Règlement</th><th>FSE</th></tr></thead> \
      <tbody> \
      <tr><td class="font-weight-bold">Actes</td><td>' + data.actesEHR + '</td><td>' + data.actesFSE + '</td></tr> \
      <tr><td class="font-weight-bold">Montant total</td><td>' + data.totalEHR + '</td><td>' + data.totalFSE + '</td></tr> \
      <tr><td class="font-weight-bold">À régler</td><td>' + data.aPayerEHR + '</td><td>' + data.aPayerFSE + '</td></tr> \
      </tbody></table> \
    </div>';
  } else {
    endMsg = '<div class="text-center"><i class="fas fa-check-circle text-success fa-4x"></i><span class="d-block font-weight-bold mt-2">Le processus s\'est terminé correctement !</span></div>';
  }
  $('#modalFaireFseTerminer').removeClass('d-none');
  $('#modalFaireFse div.modal-body').html(endMsg);
  $('#modalFaireFseTerminer').focus();
}

/**
 * Passer d'un retour brut JSON à un tableau d'objets avec index basés sur typeName de l'EHR
 * @param  {json} data retour json brut de des data vitale
 * @return {array}      tableau d'objets
 */
function vitaleToEhrTypeName(data) {
  dataVitale = [];
  $(data.vitale.data[104]).each(function(index, dat) {

    if (mNumEtRue = dat[5].match(/([0-9a-z]+) (.*)/)) {
      streetNumber = mNumEtRue[1];
      street = mNumEtRue[2];
    } else {
      streetNumber = '';
      street = ''
    }

    if (mCpEtVille = dat[8].match(/([0-9a-z]+) (.*)/)) {
      postalCodePerso = mCpEtVille[1];
      city = mCpEtVille[2];
    } else {
      postalCodePerso = '';
      city = ''
    }

    if (dat[9].charAt(0) == '2') {
      administrativeGenderCode = 'F';
    } else if (dat[9].charAt(0) == '1') {
      administrativeGenderCode = 'M';
    } else {
      administrativeGenderCode = 'U';
    }

    if (dat[1] == dat[2] && administrativeGenderCode == 'M') {
      birthname = dat[1];
      lastname = '';
    } else if (dat[1] != '' && dat[2] != '') {
      birthname = dat[2];
      lastname = dat[1];
    } else if (dat[1] != '' && dat[2] == '') {
      birthname = dat[1];
      lastname = '';
    } else if (dat[2] != '' && dat[1] == '') {
      birthname = dat[2];
      lastname = '';
    }

    dataVitale[index] = {
      'birthname': birthname,
      'lastname': lastname,
      'firstname': dat[3],
      'birthdate': moment(dat[12], 'YYYYMMDDHHmm').format('DD/MM/YYYY'),
      'streetNumber': streetNumber,
      'street': street,
      'postalCodePerso': postalCodePerso,
      'city': city,
      'nss': dat[9] + dat[10],
      'administrativeGenderCode': administrativeGenderCode,
    };

    if (data['vitale']['correspondances'][index]) {
      dataVitale[index]['correspondances'] = data['vitale']['correspondances'][index];
    }

  });
  return dataVitale;
}

/**
 * Formater vers du HTML les data vitale pour affichage en modal
 * @param  {string} mode mode de variation du html généré
 * @return {string}      html
 */
function ehrTypeDataToHtml(mode) {
  if (!mode) mode = 'classique';
  if (dataVitale.length == 0) {
    return '<div class="alert alert-danger" role="alert"> \
      La lecture a échoué. Vérifiez la connectique de votre lecteur et réessayez. \
      </div>';
  }
  html = '<div class="list-group">';
  $(dataVitale).each(function(index, dat) {
    html += '<div data-indexVitale="' + index + '" class="list-group-item list-group-item-action flex-column align-items-start peopleVitale"> \
    <div class="d-flex w-100 justify-content-between"> \
      <h5 class="mb-1">' + ucfirst(dat.firstname) + ' ';
    if (dat.lastname != '' && dat.birthname != '') {
      html += dat.lastname + ' (' + dat.birthname + ')';
    } else {
      html += dat.birthname;
    }
    html += '</h5> \
      <small>' + dat.birthdate + '</small> \
    </div> \
    <p class="mb-1">' + dat.streetNumber + ' ' + dat.street + '<br>' + dat.postalCodePerso + ' ' + dat.city + '</p> \
    <div class="d-flex w-100 justify-content-between"> \
      <div> \
        <small>' + dat.nss;
    if (mode == "prevenirDossierExistant" && dat.correspondances) {
      html += ' - <a href="/patient/' + dat['correspondances'][0] + '/" class="text-danger goToPatientFromVitaleData" target="_blank">Dossier existant pour ce n° de sécurité sociale</a> ';
    }
    html += '</small> \
      </div> \
      <div>';

    if (mode == "classique") {
      if (dat.correspondances && dat.correspondances.length == 1) {
        html += '<a href="/patient/' + dat['correspondances'][0] + '/" class="btn btn-sm btn-danger ml-1 goToPatientFromVitaleData" title="Ouvrir le dossier"><i class="fas fa-folder-open"></i></a>';
      }

      if (!dat.correspondances) {
        html += '<form class="d-inline" action="' + urlBase + '/patient/create/" method="post">';

        $.each(dataVitale[index], function(key, value) {
          html += '<input type="hidden" name="' + key + '" value="' + value + '">';
        });
        html += '<button type="submit" class="btn btn-sm btn-warning ml-1" title="Créer le dossier"><i class="fas fa-user-plus"></i></button>';
        html += '</form>';
      }

      html += '<button type="button" data-indexVitale="' + index + '" class="btn btn-sm btn-secondary ml-1 searchPatientFromVitaleDataNss" title="Rechercher le dossier avec nom et prénom"><i class="fas fa-search"></i></button>';

    } else {
      if (dat.correspondances && dat.correspondances.length == 1) {
        html += '<a href="/patient/' + dat['correspondances'][0] + '/" class="btn btn-sm btn-danger ml-1 goToPatientFromVitaleData" title="Ouvrir le dossier"><i class="fas fa-folder-open"></i></a>';
      } else {
        html += '<button type="button" class="btn btn-sm btn-warning ml-1" title="Créer le dossier"><i class="fas fa-user-plus"></i></button>';
      }
    }
    html += '</div>\
    </div></div>';
  });
  html += '</div>';

  return html;
}
