/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
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
 * Fonctions générales JS pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

var alerteGrossesseSup46EtAllaitSup3Deja = '';
var aldActivesListe = [];

$(document).ready(function() {

  $(document).mouseup(function (e) {
    if(!($(e.target).hasClass("popover-content"))){
      $(".popover").popover('hide');
      }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations pour saut entre tabs

  // Onglet général LAP
  $('#ongletLAP').on("show.bs.tab", function() {
    lapRefreshLateralPatientData();
    refreshTTenCours();
    if (alerteGrossesseSup46EtAllaitSup3Deja != 'oui') {
      setTimeout(checkGrossesseSup46EtAllaitSup3, '500');
    }
    if ($('#ordonnanceTabL').hasClass('active')) {
      voirOrdonnanceMode = 'editionOrdonnance';
    } else if ($('#tttencoursTabL').hasClass('active')) {
      voirOrdonnanceMode = 'TTenCours';
    } else if ($('#ordohistoriqueTabL').hasClass('active')) {
      voirOrdonnanceMode = 'voirOrdonnance';
    } else if ($('#tthistoriqueTabL').hasClass('active')) {
      voirOrdonnanceMode = 'voirOrdonnance';
    }

    setTimeout(getAldActivesListe, '500');
  });

  // Onglet nouvelle ordonnance
  $('#ordonnanceTabL').on("show.bs.tab", function() {
    voirOrdonnanceMode = 'editionOrdonnance';
  });

  // Onglet TT en cours
  $('#tttencoursTabL').on("show.bs.tab", function() {
    refreshTTenCours();
    voirOrdonnanceMode = 'TTenCours';
  });

  // Onglet Historique ordo
  $('#ordohistoriqueTabL').on("show.bs.tab", function() {
    getHistoriqueOrdos();
    voirOrdonnanceMode = 'voirOrdonnance';
  });

  // Onglet historique traitement
  $('#tthistoriqueTabL').on("show.bs.tab", function() {
    getHistoriqueTT(year);
    voirOrdonnanceMode = 'voirOrdonnance';
  });

  // Afficher prescriptions préétablies
  $('#prescriptionspreTabL').on("show.bs.tab", function() {
    if ($('#listePresPre').html() == '') {
      //getPresPre();
    }
    getPresPre();
    voirOrdonnanceMode = 'voirPrescriptionType';
  });


  ////////////////////////////////////////////////////////////////////////
  ///////// Observations état allaitement

  // switcher ON OFF l'état d'allaitement
  $('#tabLAP').on("click", ".allaitementStart", function() {
    setPeopleDataByTypeName(true, $('#identitePatient').attr("data-patientID"), 'allaitementActuel', '#allaitementDet', 0);
    lapRefreshLateralPatientData();
  });
  $('#tabLAP').on("click", ".allaitementStop", function() {
    setPeopleDataByTypeName(false, $('#identitePatient').attr("data-patientID"), 'allaitementActuel', '#allaitementDet', 0);
    lapRefreshLateralPatientData();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations infos medic

  $('#tabLAP').on("click", ".effetsIndesirables", function(e) {
    e.preventDefault();
    var codeSpe = $(this).attr('data-speThe');
    lapVoirEffetsIndesirables(codeSpe);
  });

});

/**
 * Rafraichier la colonne latérale du LAP
 * @return {void}
 */
function lapRefreshLateralPatientData() {
  $.ajax({
    url: urlBase + '/lap/ajax/lapPatientLateralDataRefresh/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID")
    },
    dataType: "html",
    success: function(data) {
      $('#patientLateralData').html(data);
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

/**
 * Constituer le tableau des n° d'ALD actives pour le patient
 * @return {void}
 */
function getAldActivesListe() {
  $.each($('td[data-aldnumber]'), function(index, item) {
    aldActivesListe.push($(item).attr('data-aldNumber'));
  });
  console.log(aldActivesListe);
}

/**
 * Générer les alertes grossesse > 46 sa et allaitement > 3 ans
 * @return {void}
 */
function checkGrossesseSup46EtAllaitSup3() {
  var msg = '';
  $('#patientLateralData tr.alerteMsg').each(function() {
    typeAlerte = $(this).attr('data-typeAlert');
    if ((typeAlerte == 'lapAlertPatientTermeGrossesseSup46' && lapAlertPatientTermeGrossesseSup46 == true) || (typeAlerte == 'lapAlertPatientAllaitementSup3Ans' && lapAlertPatientAllaitementSup3Ans == true) || analyseWithNoRestriction == true) {
      msg = msg + '<br>- ' + $(this).attr('data-alertemsg');
    }
  });
  if (msg.length > 10) {
    msg = "Veuillez noter les informations suivantes : " + msg;
    $('#modalLapAlerte div.modal-body').html(msg);
    $('#modAlerteImprimer, #modAlerteModifier').hide();
    $('#modAlerteFermer').show();
    $('#modalLapAlerte').modal('show');
    alerteGrossesseSup46EtAllaitSup3Deja = 'oui'
  }
}

/**
 * Voir la modal avec effets indésirables
 * @param  {int} codeSpe code spécialité
 * @return {void}
 */
function lapVoirEffetsIndesirables(codeSpe) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapVoirEffetsIndesirables/',
    type: 'post',
    data: {
      codeSpe: codeSpe
    },
    dataType: "json",
    success: function(data) {
      $('#modalLapInfosMedic h4.modal-title').html(data['titreModal']);
      $('#modalLapInfosMedic div.modal-body').html(data['html']);
      $('#modalLapInfosMedic').modal('show');
    },
    error: function() {
      alert('Problème, rechargez la page !');

      $('#modalLapAlerte').modal('show');
    }
  });
}
