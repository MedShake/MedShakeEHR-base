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
 * Fonctions JS pour les relations patient <-> patient et patient <-> praticien
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */


$(document).ready(function() {

  // reset recherche patient
  $('body').on("click", "#searchPeopleID, #searchPratID, #searchGroupID", function(e) {
    $(this).val('');
    $(this).attr('data-id', '');
  });

  //autocomplete pour la relation patient <-> patient
  $('body').delegate('#searchPeopleID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/people/ajax/getRelationsPatients/',
      select: function(event, ui) {
        $('#searchPeopleID').val(ui.item.label);
        $('#searchPeopleID').attr('data-id', ui.item.id);
      }
    });
  });

  //ajouter une relation patient <-> patient
  $('body').on("click", "#addRelationPatientPatients", function(e) {
    e.preventDefault();
    patient2ID = $('#searchPeopleID').attr('data-id');
    patientID = $('#identitePatient').attr("data-patientID");
    toStatus = $('#preRelationPatientPatientID').val();
    setRelation('relationPatientPatient', patientID, patient2ID, toStatus);
  });

  // reset recherche praticien
  $('body').on("click", "#searchPratID", function(e) {
    $('#searchPratID').val('');
    $('#searchPratID').attr('data-id', '');
  });

  //autocomplete pour la relation patient <-> praticien
  $('body').delegate('#searchPratID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/people/ajax/getRelationsPraticiens/',
      select: function(event, ui) {
        $('#searchPratID').val(ui.item.label);
        $('#searchPratID').attr('data-id', ui.item.id);
      }
    });
  });

  //ajouter une relation patient <-> praticien
  $('body').on("click", "#addRelationPatientPrat", function(e) {
    e.preventDefault();
    praticienID = $('#searchPratID').attr('data-id');
    patientID = $('#identitePatient').attr("data-patientID");
    toStatus = $('#preRelationPatientPratID').val();
    setRelation('relationPatientPraticien', patientID, praticienID, toStatus);
  });

  //autocomplete pour la relation praticien <-> groupes
  $('body').delegate('#searchGroupID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/people/ajax/getRelationsGroupes/',
      select: function(event, ui) {
        $('#searchGroupID').val(ui.item.label);
        $('#searchGroupID').attr('data-id', ui.item.id);
      }
    });
  });

  //ajouter une relation praticien <-> groupe
  $('body').on("click", "#addRelationPraticienGroupe", function(e) {
    e.preventDefault();
    groupeID = $('#searchGroupID').attr('data-id');
    pratID = $('#identitePatient').attr("data-patientID");
    toStatus = $('#preRelationPraticienGroupeID').val();
    setRelation('relationPraticienGroupe', pratID, groupeID, toStatus);
  });

  //retirer une relation patient <-> praticien/patient
  $('body').on("click", ".removeRelationPatient", function(e) {
    e.preventDefault();
    e.stopPropagation();
    deleteRelation($(this).attr("data-peopleID"), $(this).attr('data-withID'));
  });

  //ajax save form in modal
  $('body').on("click", "#newPro button.modal-save", function(e) {
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    ajaxModalSave(form, modal, function() {
      $(modal).modal('hide');
    });
  });

  $('body').on('click', '.voirDossier', function() {
    window.location = $(this).find('a.btn').attr('href');
  });

});


/**
 * Définir une relation
 * @param {string} relationType type de la relation
 * @param {int} peopleID     peopleID principal
 * @param {int} withID       second peopleID
 * @param {string} toStatus     statut du peopleID (extrait du menu)
 */
function setRelation(relationType, peopleID, withID, toStatus) {
  if (withID > 0) {
    $.ajax({
      url: urlBase + '/people/ajax/setRelation/',
      type: 'post',
      data: {
        peopleID: peopleID,
        withID: withID,
        toStatus: toStatus,
        relationType: relationType
      },
      dataType: "json",
      success: function(data) {
        if (data.status == 'ok') {
          if (relationType == 'relationPatientPraticien') getRelationsPatientPraticiensTab(peopleID);
          if (relationType == 'relationPatientPatient') getRelationsPatientPatientsTab(peopleID);
          if (relationType == 'relationPraticienGroupe') getRelationsPraticienGroupesTab(peopleID);
          if (typeof ajaxModalPatientAdminCloseAndRefreshHeader === "function") ajaxModalPatientAdminCloseAndRefreshHeader();
        } else if (data.status == 'exist') {
          alert_popup("info", 'Cette association existe déjà !');
        } else if (data.status == 'ko') {
          alert_popup("danger", 'Problème, rechargez la page !');
        } else if (data.status == 'reachmaxgroups') {
          alert_popup("info", 'Nombre maximal de groupes atteint !');
        }
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  } else {
    alert_popup("danger", "La sélection n'est pas correctement effectuée");
  }
}


/**
 * Obtenir et afficher le tableau de relations patient / patient
 * @param  {int} patientID patientID
 * @return {void}
 */
function getRelationsPatientPatientsTab(patientID) {
  if (!patientID) return;
  console.log($('#bodyTabRelationPatientPatients').length);
  if (!$('#bodyTabRelationPatientPatients').length) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsPatientPatientsTab/',
    type: 'post',
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationPatientPatients').html('');
      if (data.length > 0) {
        $.each(data, function(index, value) {
          $('#bodyTabRelationPatientPatients').append('\
          <tr class="voirDossier cursor-pointer">\
            <td>\
              <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/patient/' + value.peopleID + '/">\
                <i class="fas fa-user fa-fw"></i>\
              </a>\
            </td>\
            <td>' + value.identiteComplete + '</td>\
            <td>' + value.birthdate + ' - ' + value.ageCalcule + '</td><td>' + value.typeRelation + '</td>\
            <td class="text-right">\
              <div class="btn-group">\
                <button class="btn btn-light btn-sm removeRelationPatient" type="button" data-peopleID="' + patientID + '" data-withID="' + value.peopleID + '"><i class="fas fa-times fa-fw"></i>\
                </button>\
              </div>\
            </td>\
          </tr>');
        });
      } else {
        $('#bodyTabRelationPatientPatients').append('\
        <tr class="bg-transparent text-muted">\
          <td></td>\
          <td colspan="3">\
            Pas de lien familial connu\
          </td>\
        </tr>');
      }

    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

/**
 * Obtenir et afficher le tableau de relations patient / prat d'un patient
 * @param  {int} patientID patientID
 * @return {void}
 */
function getRelationsPatientPraticiensTab(patientID) {
  if (!patientID) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsPatientPraticiensTab/',
    type: 'post',
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationPatientPrat').html('');
      if (data.length > 0) {
        $.each(data, function(index, value) {
          $('#bodyTabRelationPatientPrat').append('\
            <tr class="voirDossier" style="cursor:pointer">\
              <td>\
                <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/pro/' + value.peopleID + '/">\
                  <i class="fas fa-user-md fa-fw"></i>\
                </a>\
              </td>\
              <td>' + ((value.titre) ? (value.titre + ' ') : '') + value.identiteUsuelle + '</td><td>' + value.typeRelationTxt + '</td>\
              <td class="text-right">\
                <button class="btn btn-light btn-sm removeRelationPatient" type="button" data-peopleID="' + patientID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
              </td>\
            </tr>');
        });
      } else {
        $('#bodyTabRelationPatientPrat').append('\
          <tr class="bg-transparent text-muted">\
            <td></td>\
            <td colspan="3">\
              Pas de correspondant connu\
            </td>\
          </tr>');
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

/**
 * Obtenir le tableau de relation praticien / groupes
 * @param  {int} pratID ID praticien
 */
function getRelationsPraticienGroupesTab(pratID) {
  if (!pratID) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsPraticienGroupesTab/',
    type: 'post',
    data: {
      pratID: pratID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationPratGroupes').html('');
      console.log(data);
      if (data.length > 0) {
        $.each(data, function(index, value) {
          $('#bodyTabRelationPratGroupes').append('\
            <tr class="voirDossier cursor-pointer">\
              <td>\
                <a class="btn btn-light btn-sm mr-3" role="button" href="' + urlBase + '/groupe/' + value.peopleID + '/">\
                  <i class="fas fa-hospital-alt fa-fw"></i>\
                </a>\
              ' + value.groupname + '</td><td class="small">' + value.city + ' (' + value.country + ')</td><td class="small">' + value.typeRelationTxt + '</td>\
              <td class="text-right">\
                <button class="btn btn-light btn-sm removeRelationPatient collapseGroupeGestion collapse" type="button" data-peopleID="' + pratID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
              </td>\
            </tr>');
        });
      } else {
        $('#bodyTabRelationPratGroupes').append('\
          <tr class="bg-transparent text-muted">\
            <td class="pl-3">\
              Ce praticien n\'intègre aucun groupe\
            </td>\
          </tr>');
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

/**
 * Effacer une relation entre 2 individus
 * @param  {int} patientID patientID
 * @param  {int} peopleID  peopleID
 * @return {void}
 */
function deleteRelation(peopleID, withID) {
  if (peopleID > 0 && withID > 0) {
    $.ajax({
      url: urlBase + '/people/ajax/removeRelation/',
      type: 'post',
      data: {
        ID1: peopleID,
        ID2: withID,
      },
      dataType: "html",
      success: function(data) {
        if ($('#bodyTabRelationPatientPrat').length) getRelationsPatientPraticiensTab(peopleID);
        if ($('#bodyTabRelationPatientPatients').length) getRelationsPatientPatientsTab(peopleID);
        if ($('#bodyTabRelationPratGroupes').length) getRelationsPraticienGroupesTab(peopleID);
        if (typeof ajaxModalPatientAdminCloseAndRefreshHeader === "function") ajaxModalPatientAdminCloseAndRefreshHeader();
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  } else {
    alert_popup("danger", "Le praticien n'est pas correctement sélectionné");
  }
}
