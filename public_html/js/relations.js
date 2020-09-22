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
  $('body').on("click", "input.searchPeopleID", function(e) {
    $(this).val('');
    $(this).attr('data-id', '');
  });

  //autocomplete recherche people
  $('body').delegate('input.searchPeopleID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/people/ajax/' + $(this).attr('data-ajax') + '/',
      select: function(event, ui) {
        $(this).val(ui.item.label);
        $(this).attr('data-id', ui.item.id);
      }
    });
  });

  // ajouter une relation
  $('body').on("click", "button.addRelation", function(e) {
    e.preventDefault();
    peopleID = $(this).attr('data-peopleID');
    people2ID = $(this).closest('form').find('input.searchPeopleID').attr('data-id');
    toStatus = $(this).closest('form').find('select.toStatutRelation').val();
    typeRelation = $(this).attr('data-typeRelation');
    setRelation(typeRelation, peopleID, people2ID, toStatus);
  });

  //retirer une relation
  $('body').on("click", ".removeRelation", function(e) {
    e.preventDefault();
    e.stopPropagation();
    if (confirm('Confirmez-vous ?')) {
      removeRelation($(this).attr("data-peopleID"), $(this).attr('data-withID'));
    }
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
          if (relationType == 'relationPraticienGroupe') {
            if ($('#bodyTabRelationPraticienGroupes').length) getRelationsPraticienGroupesTab(peopleID);
            if ($('#bodyTabRelationGroupePraticiens').length) getRelationsGroupePraticiensTab(peopleID);
          }
          if (relationType == 'relationGroupeRegistre') {
            if ($('#bodyTabRelationGroupeRegistres').length) getRelationsGroupeRegistresTab(peopleID);
            if ($('#bodyTabRelationRegistreGroupes').length) getRelationsRegistreGroupesTab(peopleID);
          }
          if ($('#bodyTabRelationRegistrePraticiens').length) getRelationsRegistrePraticiensTab(peopleID);

          if ($('#bodyTabRelationPatientGroupes').length) getRelationsPatientGroupesTab(peopleID);


          if (typeof ajaxModalPatientAdminCloseAndRefreshHeader === "function") ajaxModalPatientAdminCloseAndRefreshHeader();
        } else if (data.status == 'exist') {
          alert_popup("info", 'Cette association existe déjà !');
        } else if (data.status == 'ko') {
          alert_popup("danger", 'Problème, rechargez la page !');
        } else if (data.status == 'reachmaxgroups') {
          alert_popup("info", 'Nombre maximal de groupes atteint !');
        }

        $("input.searchPeopleID").val('');
        $("input.searchPeopleID").attr('data-id', '');
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
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + patientID + '" data-withID="' + value.peopleID + '"><i class="fas fa-times fa-fw"></i>\
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
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + patientID + '" data-withID="' + value.peopleID + '">\
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
 * Obtenir le tableau de relation groupe / praticiens
 * @param  {int} groupeID ID groupe
 */
function getRelationsGroupePraticiensTab(groupeID) {
  if (!groupeID) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsGroupePraticiensTab/',
    type: 'post',
    data: {
      groupeID: groupeID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationGroupePraticiens').html('');
      if (data.length > 0) {
        lienSup = $('#bodyTabRelationGroupePraticiens').attr("data-lienSup");
        $.each(data, function(index, value) {
          $('#bodyTabRelationGroupePraticiens').append('\
            <tr class="voirDossier" style="cursor:pointer">\
              <td>\
                <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/pro/' + value.peopleID + '/">\
                  <i class="fas fa-user-md fa-fw"></i>\
                </a>\
              </td>\
              <td>' + ((value.titre) ? (value.titre + ' ') : '') + value.identiteUsuelle + '</td><td class="small">' + value.typeRelationTxt + '</td>\
              <td class="text-right">\
              ' + ((lienSup == 'true') ? ('\
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + groupeID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
                ') : '') + ' \
              </td>\
            </tr>');
        });
      } else {
        $('#bodyTabRelationGroupePraticiens').append('\
          <tr class="bg-transparent text-muted">\
            <td class="pl-3">\
              Aucun praticien dans ce groupe\
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
      $('#bodyTabRelationPraticienGroupes').html('');
      if (data.length > 0) {
        $('form.assignMyOwnGroups').addClass('d-none');
        $.each(data, function(index, value) {
          $('#bodyTabRelationPraticienGroupes').append('\
            <tr class="voirDossier cursor-pointer">\
              <td>\
                <a class="btn btn-light btn-sm mr-3" role="button" href="' + urlBase + '/groupe/' + value.peopleID + '/">\
                  <i class="fas fa-hospital-alt fa-fw"></i>\
                </a>\
              ' + value.groupname + '</td><td class="small">' + value.city + ' (' + value.country + ')</td><td class="small">' + value.typeRelationTxt + '</td>\
              <td class="text-right">\
              ' + ((value.currentUserStatus == 'admin' || value.currentUserRank == 'admin') ? ('\
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + pratID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
                ') : '') + ' \
              </td>\
            </tr>');
        });
      } else {
        $('form.assignMyOwnGroups').removeClass('d-none');
        $('#bodyTabRelationPraticienGroupes').append('\
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
 * Obtenir le tableau de relation patient / groupes
 * @param  {int} pratID ID praticien
 */
function getRelationsPatientGroupesTab(patientID) {
  if (!patientID) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsPatientGroupesTab/',
    type: 'post',
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationPatientGroupes').html('');
      if (data.length > 0) {
        $('form.assignMyOwnGroups').addClass('d-none');
        $.each(data, function(index, value) {
          $('#bodyTabRelationPatientGroupes').append('\
            <tr class="voirDossier cursor-pointer">\
              <td>\
                <a class="btn btn-light btn-sm mr-3" role="button" href="' + urlBase + '/groupe/' + value.peopleID + '/">\
                  <i class="fas fa-hospital-alt fa-fw"></i>\
                </a>\
              ' + value.groupname + '</td><td class="small">' + value.city + ' (' + value.country + ')</td>\
              <td class="text-right">\
              ' + ((value.currentUserRank == 'admin') ? ('\
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + patientID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
                ') : '') + ' \
              </td>\
            </tr>');
        });
      } else {
        $('form.assignMyOwnGroups').removeClass('d-none');
        $('#bodyTabRelationPatientGroupes').append('\
          <tr class="bg-transparent text-muted">\
            <td class="pl-3">\
              Ce patient n\'intègre aucun groupe\
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
 * Obtenir le tableau de relation registre / groupes
 * @param  {int} registreID ID registre
 */
function getRelationsRegistreGroupesTab(registreID) {
  if (!registreID) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsRegistreGroupesTab/',
    type: 'post',
    data: {
      registreID: registreID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationRegistreGroupes').html('');
      if (data.length > 0) {
        lienSup = $('#bodyTabRelationRegistreGroupes').attr("data-lienSup");
        $.each(data, function(index, value) {
          $('#bodyTabRelationRegistreGroupes').append('\
            <tr class="voirDossier cursor-pointer">\
              <td>\
                <a class="btn btn-light btn-sm mr-3" role="button" href="' + urlBase + '/groupe/' + value.peopleID + '/">\
                  <i class="fas fa-hospital-alt fa-fw"></i>\
                </a>\
              ' + value.groupname + '</td><td class="small">' + value.city + ' (' + value.country + ')</td><td class="small">' + value.typeRelationTxt + '</td>\
              <td class="text-right">\
              ' + ((lienSup == 'true') ? ('\
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + registreID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
                ') : '') + ' \
              </td>\
            </tr>');
        });
      } else {
        $('#bodyTabRelationRegistreGroupes').append('\
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
 * Obtenir le tableau de relation registre / praticiens admin
 * @param  {int} pratID ID praticien
 */
function getRelationsRegistrePraticiensTab(registreID) {
  if (!registreID) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsRegistrePraticiensTab/',
    type: 'post',
    data: {
      registreID: registreID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationRegistrePraticiens').html('');
      if (data.length > 0) {
        lienSup = $('#bodyTabRelationRegistrePraticiens').attr("data-lienSup");
        $.each(data, function(index, value) {
          $('#bodyTabRelationRegistrePraticiens').append('\
            <tr class="voirDossier" style="cursor:pointer">\
              <td>\
                <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/pro/' + value.peopleID + '/">\
                  <i class="fas fa-user-md fa-fw"></i>\
                </a>\
              </td>\
              <td>' + ((value.titre) ? (value.titre + ' ') : '') + value.identiteUsuelle + '</td><td class="small">' + value.typeRelationTxt + '</td>\
              <td class="text-right">\
                ' + ((lienSup == 'true') ? ('\
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + registreID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
                ') : '') + ' \
              </td>\
            </tr>');
        });
      } else {
        $('#bodyTabRelationRegistrePraticiens').append('\
          <tr class="bg-transparent text-muted">\
            <td class="pl-3">\
              Aucun administrateur désigné pour ce registre\
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
 * Obtenir le tableau de relation groupe / registres
 * @param  {int} groupeID ID groupe
 */
function getRelationsGroupeRegistresTab(groupeID) {
  if (!groupeID) return;
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsGroupeRegistresTab/',
    type: 'post',
    data: {
      groupeID: groupeID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationGroupeRegistres').html('');
      if (data.length > 0) {
        lienSup = $('#bodyTabRelationGroupeRegistres').attr("data-lienSup");
        $.each(data, function(index, value) {
          $('#bodyTabRelationGroupeRegistres').append('\
            <tr class="voirDossier cursor-pointer">\
              <td>\
                <a class="btn btn-light btn-sm mr-3" role="button" href="' + urlBase + '/registre/' + value.peopleID + '/">\
                  <i class="fas fa-archive fa-fw"></i>\
                </a>\
              ' + value.registryname + '</td><td class="small">' + value.typeRelationTxt + '</td>\
              <td class="text-right">\
              ' + ((lienSup == 'true') ? ('\
                <button class="btn btn-light btn-sm removeRelation" type="button" data-peopleID="' + groupeID + '" data-withID="' + value.peopleID + '">\
                    <i class="fas fa-times fa-fw"></i>\
                </button>\
                ') : '') + ' \
              </td>\
            </tr>');
        });
      } else {
        $('#bodyTabRelationGroupeRegistres').append('\
          <tr class="bg-transparent text-muted">\
            <td class="pl-3">\
              Ce groupe n\'est autorisé pour aucun registre\
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
function removeRelation(peopleID, withID) {
  if (peopleID > 0 && withID > 0) {
    $.ajax({
      url: urlBase + '/people/ajax/removeRelation/',
      type: 'post',
      data: {
        ID1: peopleID,
        ID2: withID,
      },
      dataType: "json",
      success: function(data) {
        if (data.status == 'ok') {
          if ($('#bodyTabRelationPatientPrat').length) getRelationsPatientPraticiensTab(peopleID);
          if ($('#bodyTabRelationPatientPatients').length) getRelationsPatientPatientsTab(peopleID);
          if ($('#bodyTabRelationPraticienGroupes').length) getRelationsPraticienGroupesTab(peopleID);
          if ($('#bodyTabRelationGroupeRegistres').length) getRelationsGroupeRegistresTab(peopleID);
          if ($('#bodyTabRelationRegistrePraticiens').length) getRelationsRegistrePraticiensTab(peopleID);
          if ($('#bodyTabRelationGroupePraticiens').length) getRelationsGroupePraticiensTab(peopleID);
          if ($('#bodyTabRelationRegistreGroupes').length) getRelationsRegistreGroupesTab(peopleID);
          if ($('#bodyTabRelationPatientGroupes').length) getRelationsPatientGroupesTab(peopleID);

          if (typeof ajaxModalPatientAdminCloseAndRefreshHeader === "function") ajaxModalPatientAdminCloseAndRefreshHeader();
        } else {
          alert_popup("danger", 'Problème, rechargez la page !');
        }
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  } else {
    alert_popup("danger", "Le praticien n'est pas correctement sélectionné");
  }
}
