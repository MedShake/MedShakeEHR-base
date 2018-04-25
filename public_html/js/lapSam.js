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
 * Fonctions SAM pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

/**
 * SAM dans l'ordonnance
 * @type {Array}
 */
var samsInOrdo = [];

/**
 * SAM affichés
 * @type {Array}
 */
var samsInSamsZone = [];

/**
 * SAMs à afficher comme alerte
 * @type {array}
 */
var samsToAlert = [];

/**
 * SAMs déjà vus dans cette session
 * @type {Array}
 */
var samsAlertViewed = [];

/**
 * SAMs dans le traitement en cours
 * @type {Array}
 */
var samsInTTenCours = [];

$(document).ready(function() {

  // On check les alertes SAM à la disparition de la modal de prescription
  $('#modalRecherche').on("hidden.bs.modal", function() {
    testSamsAndAlert();
  });

  // On check les alertes SAM à la disparition de la modal d'alerte
  // pour un effet récursif si plusieurs SAMs
  $('#modalLapAlerteSam').on("hidden.bs.modal", function() {
    testSamsAndAlert();
  });

  $('#modAlerteSamBloquerPourPatient').on("click", function() {
    toggleSamState($(this));
  });

});


/**
 * Tester les SAMs théoriques / affichés et mettre à jour pour la zone nouvelle ordo
 * @return {void}
 */
function testSamsAndDisplay() {
  samsToShow = [];
  console.log(samsInOrdo);
  console.log(samsInTTenCours);
  if (samsInOrdo.length < 1 && samsInTTenCours.length < 1) {
    samsInOrdo = [];
    samsInSamsZone = [];
    $('#samsZoneOrdo').html('');
    $('#samsZoneTTenCours').html('');
    console.log('refresh sams : clean all !');
    return;
  } else if (samsInOrdo.length > 0 && samsInTTenCours.length > 0) {
    samsToShow = Array.from(new Set(samsInOrdo.concat(samsInTTenCours)))
  } else if (samsInOrdo.length > 0 && samsInTTenCours.length < 1) {
    samsToShow = samsInOrdo;
  } else if (samsInOrdo.length < 1 && samsInTTenCours.length > 0) {
    samsToShow = samsInTTenCours;
  }

  if (samsToShow.length > 1) samsToShow.sort();
  if (samsInSamsZone.length > 1) samsInSamsZone.sort();

  if (!arraysEqual(samsToShow, samsInSamsZone)) {
    refreshTheSamsZone('ordo', samsToShow);
  }
}

/**
 * Tester et produire les alertes SAM
 * @return {void}
 */
function testSamsAndAlert() {
  if (samsToAlert.length > 0) {
    samID = samsToAlert[0];
    samsToAlert.splice(0, 1);
    if ($.inArray(samID, samsAlertViewed) == -1) {
      samsAlertViewed.push(samID);
      produceSamAlert(samID);
    }
  }
}

/**
 * Produire les alertes SAM
 * @param  {string} samID identifiant du SAM
 * @return {void}
 */
function produceSamAlert(samID) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapSamAlerteForNew/',
    type: 'post',
    data: {
      samID: samID,
      patientID: $('#identitePatient').attr("data-patientID"),
      analyseWithNoRestriction: analyseWithNoRestriction
    },
    dataType: "json",
    success: function(data) {
      if (data.alert == 'ok') {
        $('#modalLapAlerteSam .modal-body').html(data.html);
        $('#modAlerteSamBloquerPourPatient').attr('data-samID', samID);
        if (data.analyseWithNoRestriction == 'true') {
          $('#modAlerteSamBloquerPourPatient').hide();
        } else {
          $('#modAlerteSamBloquerPourPatient').show();
        }
        $('#modalLapAlerteSam').modal('show');
        console.log("Produire alerte SAMs : OK");
        //commentaires du SAM
        $("#modalLapAlerteSam textarea.samCommentObserv").typeWatch({
          wait: 1000,
          highlight: false,
          allowSubmit: false,
          captureLength: 1,
          callback: function(value) {
            saveSamComment($("#modalLapAlerteSam textarea.samCommentObserv"), value);
          }
        });
      }
    },
    error: function() {
      console.log("Produire alerte SAMs : PROBLEME");
    }
  });
}

/**
 * Sauver le commentaire SAM pour le patient
 * @param  {object} source objet jquery source
 * @param  {string} value  commentaire
 * @return {void}
 */
function saveSamComment(source, value) {
  if (source.attr('data-objetID')) {
    objetID = source.attr('data-objetID');
  } else {
    objetID = '';
  }
  $.ajax({
    url: urlBase + '/lap/ajax/lapSamSaveSamComment/',
    type: 'post',
    data: {
      samID: source.attr('data-samID'),
      objetID: objetID,
      patientID: $('#identitePatient').attr("data-patientID"),
      comment: value
    },
    dataType: "json",
    success: function(data) {
      if (data.statut == 'ok') {
        $('textarea.' + source.attr('data-samID')).attr('data-objetID', data['objetID']);
        flashBackgroundElement(source);
        console.log("Sauver commentaire SAMs : OK");
        $('textarea.' + source.attr('data-samID')).val(value);
      }
    },
    error: function() {
      console.log("Sauver commentaire SAMs : PROBLEME");
    }
  });
}

function toggleSamState(source) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapSamToggleForPatient/',
    type: 'post',
    data: {
      samID: source.attr('data-samID'),
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "json",
    success: function(data) {

    },
    error: function() {
      console.log("Toogle SAM state : PROBLEME");
    }
  });
}

/**
 * Extraire les diférents SAMs de l'ordo
 * @return {void}
 */
function getDifferentsSamFromOrdo() {
  if (ordoMedicsALD.length < 1 && ordoMedicsG.length < 1) {
    samsInOrdo = [];
    return;
  }

  $.each(ordoMedicsALD, function(ligneIndex, ligneData) {
    $.each(ordoMedicsALD[ligneIndex]['medics'], function(medicIndex, medic) {
      if ($.isArray(medic['sams']) && medic['sams'].length > 0) {
        $.each(medic['sams'], function(samIndex, sam) {
          if ($.inArray(sam, samsInOrdo) == -1) samsInOrdo.push(sam);
        });
      }
    });
  });

  $.each(ordoMedicsG, function(ligneIndex, ligneData) {
    $.each(ordoMedicsG[ligneIndex]['medics'], function(medicIndex, medic) {
      if ($.isArray(medic['sams']) && medic['sams'].length > 0) {
        $.each(medic['sams'], function(samIndex, sam) {
          if ($.inArray(sam, samsInOrdo) == -1) samsInOrdo.push(sam);
        });
      }
    });
  });
}

/**
 * Afficher les SAMs de la zone.
 * @return {[type]} [description]
 */
function refreshTheSamsZone(zone, sams) {
  if (sams.length < 1) return;
  $.ajax({
    url: urlBase + '/lap/ajax/lapSamRefreshTheSamsZone/',
    type: 'post',
    data: {
      zone: zone,
      sams: sams,
      patientID: $('#identitePatient').attr("data-patientID")
    },
    dataType: "json",
    success: function(data) {
      if (data['zone'] == 'ordo') {
        $('#samsZoneOrdo').html(data['html']);
        samsInSamsZone = data['samsInSamsZone'];
        console.log("Refresh sams zone ordo : OK");
      } else if (data['zone'] == 'ttencours') {
        $('#samsZoneTTenCours').html(data['html']);
        console.log("Refresh sams zone ttencours : OK");
      }
      //commentaires du SAM
      $("#samsZoneOrdo textarea.samCommentObserv, #samsZoneTTenCours textarea.samCommentObserv").typeWatch({
        wait: 1000,
        highlight: false,
        allowSubmit: false,
        captureLength: 1,
        callback: function(value) {
          saveSamComment($(this), value);
        }
      });
    },
    error: function() {
      console.log("Refresh sams zone ordo : PROBLEME");
    }
  });
}
