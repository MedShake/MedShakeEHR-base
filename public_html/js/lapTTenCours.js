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

  $('#tttencoursTabL').on("show.bs.tab", function() {
    refreshTTenCours();
  });

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
    $('#modalRecherche').modal('toggle');
  });

  // Entrer une date de fin effective pour une ligne de prescription
  $('#tttencoursTab').on("click", 'a.marquerArretEffectif', function(e) {
    e.preventDefault();
    var ligneID = $(this).attr("data-ligneID");
    $('#modalDateFinEffective').modal('toggle');
  });

});

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

function construireTTenCours() {
  if (!$.isEmptyObject(TTenCours.TTChroniques)) {
    $('#traitementEnCoursChronique').html('');
    $.each(TTenCours.TTChroniques, function(index, ligne) {
      $('#traitementEnCoursChronique').append(makeLigneOrdo(ligne, 'TTenCours'));

    });
  }
  if (!$.isEmptyObject(TTenCours.TTPonctuels)) {
    $('#traitementEnCoursPonctuel').html('');
    $.each(TTenCours.TTPonctuels, function(index, ligne) {
      $('#traitementEnCoursPonctuel').append(makeLigneOrdo(ligne, 'TTenCours'));

    });
  }
}
