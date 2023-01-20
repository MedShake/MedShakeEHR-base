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
 * Fonctions JS autour du traitement en cours pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

/**
 * tableau des tt en cours
 * @type {Array}
 */
var TTenCours = {};



$(document).ready(function() {

  //Saisir un traitement en cours
  $("button.saisirTTenCours").on("click", function(e) {
    modeActionModal = 'saisirTTenCours';
    ligneCouranteIndex = '';
    zoneOrdoAction = '';
    prepareModalPrescription();
    cleanModalRecherche();
    $('#modalRecherche').modal('toggle');
  });

  //Sauver une ligne de TT en cours saveLigneTTenCours
  $("button.addToTTenCours").on("click", function(e) {
    catchCurrentPrescriptionData();
    saveLigneTTenCours(0);
    cleanModalRecherche();
    refreshTTenCours();
    $('#modalRecherche').modal('toggle');
  });

  // Marquer la fin effective pour une ligne de prescription à maintenant
  $('#tttencoursTab').on("click", 'a.marquerArretEffectifCeJour', function(e) {
    e.preventDefault();
    var ligneID = $(this).parents("div.lignePrescription").attr("data-ligneID");
    saveDateEffectiveArretTT(ligneID);
  });

  // Entrer une date de fin effective antérieure pour une ligne de prescription
  $('#tttencoursTab').on("click", 'a.marquerArretEffectif', function(e) {
    e.preventDefault();
    var ligneID = $(this).parents("div.lignePrescription").attr("data-ligneID");
    $('#modalDateFinEffective input[name="ligneID"]').val(ligneID);
    $('#modalDateFinEffective').modal('toggle');
  });

  // Entrer une date de fin effective pour une ligne de prescription
  $('#modalDateFinEffective button.marquerArretEffectifGo').on("click", function(e) {
    e.preventDefault();
    var ligneID = $('#modalDateFinEffective input[name="ligneID"]').val();
    date = $('#modalDateFinEffective input[name="dateEffectiveDeFin"]').val();
    saveDateEffectiveArretTT(ligneID, date);
    $('#modalDateFinEffective').modal('toggle');
  });

  $('#tttencoursTab').on("click", 'button.renouvLignePrescription', function(e) {
    var ligneIndex = $(this).parents('div.lignePrescription').index();
    //source
    if ($(this).parents('div').hasClass('traitementEnCoursChronique')) {
      var source = 'TTChroniques';
    } else {
      var source = 'TTPonctuels';
    }
    //destination
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      var zone = ordoMedicsALD;
    } else {
      var zone = ordoMedicsG;
    }

    var ligneAinjecter = TTenCours[source][ligneIndex];
    ligneAinjecter = cleanLignePrescriptionAvantRenouv(ligneAinjecter);

    console.log(ligneAinjecter);
    zone.push(ligneAinjecter);
    construireHtmlLigneOrdonnance(ligneAinjecter, 'append', '', '#conteneurOrdonnanceCourante', 'editionOrdonnance');

    // SAMS
    getDifferentsSamFromOrdo();
    testSamsAndDisplay();

    // retirer les infos allergiques potentiellement présentes
    deleteRisqueAllergique();

    // sauvegarde
    ordoLiveSave();

    //reset objets
    resetObjets();

    flashBackgroundElement($(this).parents('div.lignePrescription'));
  });

});

/**
 * Nettoyer une ligne qui va être réinjecter en renouvellement dans l'ordo courante
 * @param  {object} ligne ligne de prescription
 * @return {object}       ligne de prescrition nettoyée
 */
function cleanLignePrescriptionAvantRenouv(ligne) {

  //ajuster nouvelles dates
  ligne.ligneData.dateDebutPrise = moment(new Date()).format('DD/MM/YYYY');
  if (ligne.ligneData.dureeTotaleMachineJours > 0) {
    ligne.ligneData.dateFinPrise = moment(new Date()).add(ligne.ligneData.dureeTotaleMachineJours - 1, 'days').format('DD/MM/YYYY');
    ligne.ligneData.dateFinPriseAvecRenouv = moment(new Date()).add(ligne.ligneData.dureeTotaleMachineJoursAvecRenouv - 1, 'days').format('DD/MM/YYYY');
  } else {
    ligne.ligneData.dateFinPrise = ligne.ligneData.dateDebutPrise;
    ligne.ligneData.dateFinPriseAvecRenouv = ligne.ligneData.dateDebutPrise;
  }
  //retirer éventuels prescripteurs initiaux
  $.each(ligne.medics, function(index, l) {
    delete ligne.medics[index].prescripteurInitialTT;
    delete ligne.medics[index].risqueAllergique;
  });

  return ligne;
}

/**
 * Enregsitrer une date effective d'arret d'une ligne de prescription
 * @param  {int} ligneID ID de la ligne
 * @param  {string} date    date au format dd/mm/yyyy, si absente, date du moment
 * @return {void}
 */
function saveDateEffectiveArretTT(ligneID, date) {
  if (!date) date = moment(new Date()).format('DD/MM/YYYY');
  $.ajax({
    url: urlBase + '/lap/ajax/lapSaveDateEffectiveArretTT/',
    type: 'post',
    data: {
      ligneID: ligneID,
      date: date,
      patientID: $('#identitePatient').attr("data-patientID")
    },
    dataType: "json",
    success: function(data) {
      if (data['statut'] == 'ok') {
        $('#tttencoursTab div.lignePrescription[data-ligneID="' + ligneID + '"]').remove();
        console.log("Enregistrement date effective : OK");
      } else {
        console.log("Enregistrement date effective : PROBLEME");
      }
    },
    error: function() {
      alert('Problème ! Rechargez la page !');
    }
  });
}

/**
 * Enregistrer ligne de prescription traitement en cours
 */
function saveLigneTTenCours(ligneID) {
  var ligne = {
    ligneData: ligneData,
    medics: [medicData],
  }
  $.ajax({
    url: urlBase + '/lap/ajax/lapTTenCoursSaveOrUpdateLigne/',
    type: 'post',
    data: {
      ligneID: ligneID,
      patientID: $('#identitePatient').attr("data-patientID"),
      ligne: ligne
    },
    dataType: "json",
    success: function() {
      console.log("Sauvegarde ligne prescription TT en cours : OK");
    },
    error: function() {
      console.log("Sauvegarde ligne prescription TT en cours : PROBLEME");
    }
  });
}

/**
 * Obtenir le traitement en cours
 * @return {object } TT en cours
 */
function refreshTTenCours() {
  $.ajax({
    url: urlBase + '/lap/ajax/lapTTenCoursGet/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "json",
    success: function(data) {
      TTenCours = data;
      construireTTenCours();
      console.log(TTenCours);
    },
    error: function() {

    }
  });
}

/**
 * Construction HTML du traitement en cours
 * @return {void}
 */
function construireTTenCours() {
  if (!$.isEmptyObject(TTenCours.TTChroniques)) {
    $('#traitementEnCoursChronique').html('');
    $.each(TTenCours.TTChroniques, function(index, ligne) {
      $('#traitementEnCoursChronique').append(makeLigneOrdo(ligne, 'TTenCours'));

      //SAMs
      $.each(TTenCours.TTChroniques[index]['medics'], function(medicIndex, medic) {
        if ($.isArray(medic['sams'])) {
          $.each(medic['sams'], function(samIndex, sam) {
            if ($.inArray(sam, samsInTTenCours) == -1) samsInTTenCours.push(sam);
          });
        }
      });

    });
  }
  if (!$.isEmptyObject(TTenCours.TTPonctuels)) {
    $('#traitementEnCoursPonctuel').html('');
    $.each(TTenCours.TTPonctuels, function(index, ligne) {
      $('#traitementEnCoursPonctuel').append(makeLigneOrdo(ligne, 'TTenCours'));

      //SAMs
      $.each(TTenCours.TTPonctuels[index]['medics'], function(medicIndex, medic) {
        if ($.isArray(medic['sams'])) {
          $.each(medic['sams'], function(samIndex, sam) {
            if ($.inArray(sam, samsInTTenCours) == -1) samsInTTenCours.push(sam);
          });
        }
      });

    });
  }
  $(function() {
    $('[data-toggle="popover"]').popover()
  })
  refreshTheSamsZone('ttencours', samsInTTenCours);
  testSamsAndDisplay();
}
