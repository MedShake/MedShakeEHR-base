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

/**
 * tableau des médicaments hors ALD de l'ordo en cours
 * @type {Array}
 */
var ordoMedicsG = [];
/**
 * tableau des médicaments ALD de l'ordo en cours
 * @type {Array}
 */
var ordoMedicsALD = [];
/**
 * nom zone source de la ligne de prescription déplacée
 * @type {string}
 */
var srcTab;
/**
 * index de la ligne de prescription déplacée
 * @type {int}
 */
var srcIdx;

$(document).ready(function() {

  // Refresh des data patient au retour sur la page LAP.
  document.addEventListener("visibilitychange", function() {
    if (document.visibilityState == 'visible') {
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
  });

  //Nouvelle prescription
  $("button.nouvellePrescription").on("click", function(e) {
    modeActionModal = 'new';
    ligneCouranteIndex = '';
    zoneOrdoAction = '';
    prepareModalPrescription();
    cleanModalRecherche();
    $('#modalRecherche').modal('toggle');
  });


  // Ordonner par drag & drop l'ordonnance
  $("#conteneurPrescriptionsALD, #conteneurPrescriptionsG").sortable({
    connectWith: ".connectedOrdoZones",
  });
  $("#conteneurPrescriptionsALD, #conteneurPrescriptionsG").disableSelection();

  $(".connectedOrdoZones").on("sortstart", function(event, ui) {
    srcTab = ui.item.hasClass('ald') ? ordoMedicsALD : ordoMedicsG;
    srcIdx = ui.item.index();
  });
  $(".connectedOrdoZones").on("sortupdate", function(event, ui) {
    if (this === ui.item.parent()[0]) {
      console.log('Déplacement de ligne d\'ordonnance : START');
      landingIndex = ui.item.index();
      console.log('index de départ : ' + srcIdx);
      console.log('index d\'arrivée : ' + landingIndex);

      moveLignePrescription(
        srcTab,
        ui.item.parent('div.connectedOrdoZones').hasClass('ald') ? ordoMedicsALD : ordoMedicsG,
        srcIdx,
        landingIndex
      );


      if (ui.item.parent('div.connectedOrdoZones').hasClass('ald')) {
        ui.item.addClass('ald');
        ordoMedicsALD[landingIndex]['ligneData']['isALD'] = 'true';
      } else {
        ui.item.removeClass('ald');
        ordoMedicsG[landingIndex]['ligneData']['isALD'] = 'false';
      }

      console.log(ordoMedicsALD);
      console.log(ordoMedicsG);
      console.log('Déplacement de ligne d\'ordonnance : STOP');
      ordoLiveSave();
    }
  });

  // Editer un médicament d'une ligne de prescription
  $("#conteneurOrdonnance").on("click", 'button.editLignePrescription', function(e) {
    console.log("Editer medic unique ligne prescription : START");
    modeActionModal = 'edit';
    ligneCouranteIndex = $(this).parents('div.lignePrescription').index();
    indexMedic = '0';
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      zone = ordoMedicsALD;
    } else {
      zone = ordoMedicsG;
    }
    editPrescription(zone, ligneCouranteIndex);
    console.log("Editer medic unique ligne prescription : STOP");
  });

  // Editer un médicament d'une ligne de prescription où ils sont multiple
  $("#conteneurOrdonnance").on("click", 'button.editMedicLignePrescription', function(e) {
    console.log("Editer medic multiple ligne prescription : START");
    modeActionModal = 'edit';
    ligneCouranteIndex = $(this).parents('div.lignePrescription').index();
    indexMedic = $(this).parents('table.tablePrescripMultiMedic tr').index();
    console.log("index ligne prescription : " + ligneCouranteIndex + " index medic : " + indexMedic);
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      zone = ordoMedicsALD;
    } else {
      zone = ordoMedicsG;
    }
    editPrescription(zone, ligneCouranteIndex);
    console.log("Editer medic multiple ligne prescription : STOP");
  });

  // Détruire une ligne d'ordonnance
  $("#conteneurOrdonnance").on("click", 'button.removeLignePrescription', function(e) {
    console.log('Destruction de ligne de prescription : START');
    index = $(this).parents('div.lignePrescription').index();
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      ordoMedicsALD.splice(index, 1);
    } else {
      ordoMedicsG.splice(index, 1);
    }
    $(this).parents('div.lignePrescription').remove();

    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);
    console.log('Destruction de ligne de prescription : STOP');
    ordoLiveSave();

  });

  // Détruire un médicament dans ligne d'ordonnance
  $("#conteneurOrdonnance").on("click", 'button.removeMedicLignePrescription', function(e) {
    console.log('Destruction d\'un médic dans ligne de prescription : START');
    index = $(this).parents('div.lignePrescription').index();
    indexMedic = $(this).parents('table.tablePrescripMultiMedic tr').index();

    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      ordoMedicsALD[index]['medics'].splice(indexMedic, 1);
      construireHtmlLigneOrdonnance(ordoMedicsALD[index], 'replace', $('#conteneurPrescriptionsALD div.lignePrescription').eq(index));
    } else {
      ordoMedicsG[index]['medics'].splice(indexMedic, 1);
      construireHtmlLigneOrdonnance(ordoMedicsG[index], 'replace', $('#conteneurPrescriptionsG div.lignePrescription').eq(index));
    }

    console.log('Destruction du médic ' + indexMedic + ' ligne prescription : ' + index);
    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);
    console.log('Destruction d\'un médic dans ligne de prescription : STOP');
    ordoLiveSave();

  });

  // Détruire tout le contenu de l'ordonnance
  $('a.removeAllLignesPrescription').on("click", function(e) {
    if (confirm("Confirmez-vous la suppression de toutes les lignes de prescription ?")) {
      e.preventDefault();
      cleanOrdonnance();
      ordoLiveSave();
    }
  });

  // ajouter un médicament à une ligne de prescription
  $("#conteneurOrdonnance").on("click", "a.addToLigne", function(e) {
    console.log('Installation ajout d\'un médic dans ligne de prescription : START');
    e.preventDefault();
    modeActionModal = 'addToLigne';
    cleanModalRecherche();
    prepareModalPrescription();
    ligneCouranteIndex = $(this).parents('div.lignePrescription').index();
    if ($(this).parents('div.connectedOrdoZones').hasClass('ald')) {
      zoneOrdoAction = 'ALD';
    } else {
      zoneOrdoAction = 'G';
    }

    $('#modalRecherche').modal('show');
    console.log('Installation ajout de medic sur la ligne ' + ligneCouranteIndex + ' en zone ' + zoneOrdoAction);
    console.log('Installation ajout d\'un médic dans ligne de prescription : STOP');
  });

  // Ordo live : restaurer la version sauvegardée (undo)
  $("a.ordoLiveRestore").on("click", function(e) {
    e.preventDefault();
    ordoLiveRestore();
  });

});

/**
 * Ordonnance vierge
 * @return {void}
 */
function cleanOrdonnance() {
  ordoMedicsALD = [];
  ordoMedicsG = [];
  $('#conteneurOrdonnance div.lignePrescription').remove();
}

/**
 * Déplacer une ligne de prescription dans les tableaux de medics
 * @param  {string} tabDepart    tableau de départ
 * @param  {string} tabArrivee   nom du tableau d'arrivée
 * @param  {int} indexDepart  n° de l'index de départ
 * @param  {int} indexArrivee n° de l'index d'arrivée
 * @return {void}
 */
function moveLignePrescription(tabDepart, tabArrivee, indexDepart, indexArrivee) {
  tabArrivee.splice(indexArrivee, 0, tabDepart.splice(indexDepart, 1)[0]);
}

/**
 * Sauvegarder l'ordonnance en version JSON dans l'état précédent l'action
 * @return {void}
 */
function ordoLiveSave() {
  var ordoLive = {
    ordoMedicsALD: ordoMedicsALD,
    ordoMedicsG: ordoMedicsG
  };
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoLiveSave/',
    type: 'post',
    data: {
      ordoLive: ordoLive,
    },
    dataType: "json",
    success: function() {
      console.log("Sauvegarde automatique ordonnance : OK");
    },
    error: function() {
      console.log("Sauvegarde automatique ordonnance : PROBLEME");
    }
  });
}

/**
 * Restaurer la version précédente de l'ordonnance
 * @return {void}
 */
function ordoLiveRestore() {
  console.log("Restoration automatique ordonnance : START");
  cleanOrdonnance();
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoLiveRestore/',
    type: 'post',
    data: {
      ordoLive: $('#conteneurOrdonnance').html(),
    },
    dataType: "json",
    success: function(data) {
      if (data['statut'] == 'ok') {
        if (data['ordoLive']) {
          construireOrdonnance(data['ordoLive']['ordoMedicsG'], data['ordoLive']['ordoMedicsALD']);
        }
        console.log("Restoration automatique ordonnance : OK");

      } else if (data['statut'] == 'nofile') {
        alert("Aucune version antérieure trouvée");
        console.log("Restoration automatique ordonnance : NOFILE");
      }
    },
    error: function() {
      console.log("Restoration automatique ordonnance : PROBLEME");
    }
  });
}

/**
 * Construire html ordonnance
 * @param  {array} tabMedicsG   données médicaments hors ALD
 * @param  {array} tabMedicsALD données médicaments ald
 * @return {void}
 */
function construireOrdonnance(tabMedicsG, tabMedicsALD) {
  console.log('reconstruction d\'ordonnance : START');
  if (tabMedicsG) {
    ordoMedicsG = tabMedicsG;
    $.each(tabMedicsG, function(index, ligne) {
      construireHtmlLigneOrdonnance(ligne, 'append');
      console.log('reconstruction d\'ordonnance : ajout ligne G');
    });
  } else {
    ordoMedicsG = [];
  }
  if (tabMedicsALD) {
    ordoMedicsALD = tabMedicsALD;
    $.each(tabMedicsALD, function(index, ligne) {
      construireHtmlLigneOrdonnance(ligne, 'append');
      console.log('econstruction d\'ordonnance : ajout ligne ALD');
    });
  } else {
    ordoMedicsALD = [];
  }
  console.log(ordoMedicsALD);
  console.log(ordoMedicsG);
  console.log('reconstruction d\'ordonnance : STOP');
}


function makeLigneOrdo(data) {
  console.log(data);
  if (data.medics.length == 1) {
    retour = '<div class="well well-sm ui-sortable-handle lignePrescription';
    if (data.ligneData.isALD == 'true') retour += ' ald ';
    retour += ' ">';
    retour += '  <div class="row">';
    retour += '    <div class="col-md-7">';
    retour += '      <div><strong>';
    retour += '        ' + data.medics[0].nomUtileFinal + '</strong>';
    if (data.medics[0].isNPS == 'true') {
      retour += ' [non substituable';
      if (data.medics[0].motifNPS) retour += '   - ' + data.medics[0].motifNPS
      retour += ']';
    }
    if (data.ligneData.isChronique == 'true') {
      retour += '        <span class="label label-default">chronique</span>';
    }
    retour += '      </div>';
    retour += '      <div>' + data.medics[0].voieUtilisee;
    if (data.medics[0].posoFrappeeNbDelignesPosologiques > 1) {
      retour += '          -';
      retour += '          ' + data.medics[0].dureeTotaleHuman;
    }
    retour += '      </div>';
    retour += '      <div>' + data.medics[0].posoHumanComplete + '</div>';
    retour += '    </div>';

    retour += '    <div class="col-md-4">';
    if (data.medics[0].prescriptionMotif) {
      retour += '        <div class="small">Motif de prescription :<br>';
      retour += '          ' + nl2br(data.medics[0].prescriptionMotif) + '</div>';
    }
    retour += '  </div>';

    retour += '  <div class="col-md-1 text-right">';
    retour += '    <button class="btn btn-default btn-xs editLignePrescription">';
    retour += '      <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>';


    retour += '    <div class="btn-group">';
    retour += '      <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    retour += '        <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>';
    retour += '        <span class="caret"></span>';
    retour += '      </button>';
    retour += '      <ul class="dropdown-menu dropdown-menu-right">';
    retour += '        <li>';
    retour += '          <a href="#" class="addToLigne">';
    retour += '            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
    retour += '            Ajouter un médicament à cette ligne de prescription</a>';
    retour += '        </li>';

    if (data.medics[0].prescriptibleEnDC == '1') {
      retour += '          <li role="separator" class="divider"></li>';
      retour += '          <li>';
      retour += '            <a href="#" class="">';
      retour += '              <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>';
      retour += '              Convertir en DCI</a>';
      retour += '          </li>';
    }
    retour += '      </ul>';
    retour += '    </div>';

    retour += '    <button class="btn btn-default btn-xs removeLignePrescription">';
    retour += '      <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';

    retour += '  </div>';
    retour += '</div>';
    retour += '</div>';

  } else if (data.medics.length > 1) {

    retour = '<div class="well well-sm lignePrescription';
    if (data.ligneData.isALD == 'true') retour += ' ald ';
    retour += '">';
    retour += '  <div class="row" style="margin-bottom: 12px">';
    retour += '    <div class="col-md-7 gras text-capitalize">';
    retour += '      ' + data.medics[0].voieUtilisee + ' - ' + data.medics[0].dureeTotaleHuman + ' : ';
    retour += '    </div>';
    retour += '    <div class="col-md-4"></div>';
    retour += '    <div class="col-md-1 text-right">';


    retour += '      <div class="btn-group">';
    retour += '        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
    retour += '          <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>';
    retour += '          <span class="caret"></span>';
    retour += '        </button>';
    retour += '        <ul class="dropdown-menu dropdown-menu-right">';
    retour += '          <li>';
    retour += '            <a href="#" class="addToLigne">';
    retour += '              <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
    retour += '              Ajouter un médicament à cette ligne de prescription</a>';
    retour += '          </li>';

    if (data.medics[0].prescriptibleEnDC == '1') {
      retour += '            <li role="separator" class="divider"></li>';
      retour += '            <li>';
      retour += '              <a href="#" class="">';
      retour += '                <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>';
      retour += '                Convertir en DCI</a>';
      retour += '            </li>';
    }
    retour += '        </ul>';
    retour += '      </div>';
    retour += '      ';
    retour += '      <button class="btn btn-default btn-xs removeLignePrescription">';
    retour += '        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
    retour += '    </div>';
    retour += '  </div>';

    retour += '  <table class="table table-hover tablePrescripMultiMedic">';
    retour += '    <tbody>';
    var i = 1;
    $.each(data.medics, function(index, medic) {

      retour += '        <tr>';
      retour += '          <td class="col-md-6">';
      retour += '            <div>';
      retour += '              ' + i++ + ' - <strong>' + medic.nomUtileFinal + '</strong>';
      if (medic.isNPS == 'true') {
        retour += ' [non substituable';
        if (medic.motifNPS) retour += ' - ' + medic.motifNPS
        retour += ']';
      }
      if (data.ligneData.isChronique == 'true') {
        retour += '<span class = "label label-default" > chronique < /span>';
      }
      retour += '    </div>';
      retour += '    <div>' + medic.posoHumanComplete + '</div>';
      retour += '  </td>';

      retour += '  <td class="col-md-4">';
      if (medic.prescriptionMotif.length > 0) {
        retour += '<div class="small">Motif de prescription :<br>';
        retour += nl2br(medic.prescriptionMotif) + '</div>';
      }
      retour += '</td>';

      retour += '<td class="col-md-2">';

      retour += '  <button class="btn btn-default btn-xs editMedicLignePrescription">';
      retour += '    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>';

      retour += '  <button class="btn btn-default btn-xs removeMedicLignePrescription">';
      retour += '    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
      retour += '</td>';

      retour += '</tr>';
    });
    retour += '</tbody>';
    retour += '</table>';

    retour += '</div>';
    retour += '</div>';
  }

  return retour;
}
