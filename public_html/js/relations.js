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
  $('body').on("click", "#searchPatientID", function(e) {
    $('#searchPatientID').val('');
    $('#searchPatientID').attr('data-id', '');
  });

  //autocomplete pour la relation patient <-> patient
  $('body').delegate('#searchPatientID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/people/ajax/getRelationsPatients/',
      select: function(event, ui) {
        $('#searchPatientID').val(ui.item.label);
        $('#searchPatientID').attr('data-id', ui.item.id);
      }
    });
  });

  //ajouter une relation patient <-> patient
  $('body').on("click", "#addRelationPatientPatients", function(e) {
    e.preventDefault();
    patient2ID = $('#searchPatientID').attr('data-id');
    patientID = $('#identitePatient').attr("data-patientID");
    preRelationPatientPatient = $('#preRelationPatientPatientID').val();

    if (patient2ID > 0) {
      $.ajax({
        url: urlBase + '/people/ajax/addRelationPatientPatient/',
        type: 'post',
        data: {
          patientID: patientID,
          patient2ID: patient2ID,
          preRelationPatientPatient: preRelationPatientPatient
        },
        dataType: "html",
        success: function(data) {
          getRelationsPatientPatientsTab();
        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });
    } else {
      alert_popup("danger", "Le patient n'est pas correctement sélectionné");

    }

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
    preRelationPatientPrat = $('#preRelationPatientPratID').val();
    if (praticienID > 0) {
      $.ajax({
        url: urlBase + '/people/ajax/addRelationPatientPraticien/',
        type: 'post',
        data: {
          patientID: patientID,
          praticienID: praticienID,
          preRelationPatientPrat: preRelationPatientPrat
        },
        dataType: "html",
        success: function(data) {
          getRelationsPatientPraticiensTab();
          ajaxModalPatientAdminCloseAndRefreshHeader();
        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });
    } else {
      alert_popup("danger", "Le praticien n'est pas correctement sélectionné");

    }

  });

  //retirer une relation patient <-> praticien/patient
  $('body').on("click", ".removeRelationPatient", function(e) {
    e.preventDefault();
    e.stopPropagation();
    ID2 = $(this).attr('data-peopleID');
    ID1 = $('#identitePatient').attr("data-patientID");
    if (ID1 > 0 && ID2 > 0) {
      $.ajax({
        url: urlBase + '/people/ajax/removeRelationPatient/',
        type: 'post',
        data: {
          ID1: ID1,
          ID2: ID2,
        },
        dataType: "html",
        success: function(data) {
          getRelationsPatientPraticiensTab();
          getRelationsPatientPatientsTab();
          ajaxModalPatientAdminCloseAndRefreshHeader();
        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });
    } else {
      alert_popup("danger", "Le praticien n'est pas correctement sélectionné");

    }

  });

  //ajax save form in modal
  $('body').on("click", "#newPro button.modal-save", function(e) {
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    ajaxModalFormSave(form, modal);

  });

  $('body').on('click', '.voirDossier', function(){
    window.location = $(this).find('a.btn').attr('href');
  });

  setTimeout(getRelationsPatientPraticiensTab, 500);
  setTimeout(getRelationsPatientPatientsTab, 500);

});

function getRelationsPatientPatientsTab() {
  patientID = $('#identitePatient').attr("data-patientID");
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsPatientPatientsTab/',
    type: 'post',
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationPatientPatients').html('');
      $.each(data, function(index, value) {
        $('#bodyTabRelationPatientPatients').append('\
          <tr class="voirDossier" style="cursor:pointer">\
            <td>\
              <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/patient/' + value.patientID + '/">\
                <span class="fa fa-folder-open" aria-hidden="true"></span>\
              </a>\
            </td>\
            <td>' + value.prenom + ' ' + value.nom + '</td>\
            <td>' + value.ddn + '</td><td>' + value.typeRelationDisplay + '</td>\
            <td class="text-right">\
              <div class="btn-group">\
                <button class="btn btn-light btn-sm removeRelationPatient" style="cursor:pointer" type="button" data-peopleID="' + value.patientID + '"><span class="fa fa-times" aria-hidden="true"></span>\
                </button>\
              </div>\
            </td>\
          </tr>');
      });

    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}


function getRelationsPatientPraticiensTab() {
  patientID = $('#identitePatient').attr("data-patientID");
  $.ajax({
    url: urlBase + '/people/ajax/getRelationsPatientPraticiensTab/',
    type: 'post',
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#bodyTabRelationPatientPrat').html('');
      $.each(data, function(index, value) {
        $('#bodyTabRelationPatientPrat').append('\
          <tr class="voirDossier" style="cursor:pointer">\
            <td>\
              <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/pro/' + value.pratID + '/">\
                <span class="fa fa-folder-open" aria-hidden="true"></span>\
              </a>\
            </td>\
            <td>' + (value.prenom ? value.prenom : '') + ' ' + value.nom + '</td><td>' + value.typeRelationDisplay + '</td>\
            <td class="text-right">\
              <button class="btn btn-light btn-sm removeRelationPatient" style="cursor:pointer" type="button" data-peopleID="' + value.pratID + '">\
                  <span class="fa fa-times" aria-hidden="true"></span>\
              </button>\
            </td>\
          </tr>');
      });

    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

function ajaxModalFormSave(form, modal) {
  var data = {};
  $(form + ' input, ' + form + ' select, ' + form + ' textarea').each(function(index) {
    var input = $(this);
    data[input.attr('name')] = input.val();
  });

  var url = $(form).attr('action');
  data["groupe"] = $(form).attr('data-groupe');

  $.ajax({
    url: url,
    type: 'post',
    data: data,
    dataType: "json",
    success: function(data) {
      if (data.status == 'ok') {
        $(modal).modal('hide');

      } else {
        $(modal + ' div.alert').show();
        $(modal + ' div.alert ul').html('');
        $.each(data.msg, function(index, value) {
          $(modal + ' div.alert ul').append('<li>' + value + '</li>');
        });
        $.each(data.code, function(index, value) {
          $(modal + ' #' + value + 'ID').closest("div.form-group").addClass('has-error');
        });
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}
