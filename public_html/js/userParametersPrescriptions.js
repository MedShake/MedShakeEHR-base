/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * Fonctions JS pour les paramètres utilisateur : prescriptions (dont LAP)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  // afficher les catégories de prescriptions types quand l'onglet devient visible
  $('a[href="#prescriptionsTypes"], a[href="#presType"]').on('shown.bs.tab', function(e) {
    userParametersPrescriptionsCatList();
  })
  if ($('#prescriptionsTypesButton').hasClass('active')) userParametersPrescriptionsCatList();

  // afficher les prescriptions types quand l'onglet devient visible
  $('a[href="#presType"]').on('shown.bs.tab', function(e) {
    userParametersPrescriptionsList();
  })

  // editer (extract by primary key)
  $('body').on("click", "button.edit-by-prim-key", function(e) {

    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    var id = $(this).attr("data-id");
    var table = $(this).attr("data-table");

    $.ajax({
      url: urlBase + '/user/ajax/userParametersExtractByPrimaryKey/',
      type: 'post',
      data: {
        id: id,
        table: table
      },
      dataType: "json",
      success: function(data) {
        $(form).append('<input type="hidden" value="' + data.id + '" name="id" />');
        $(modal + ' form select option').removeProp('selected');
        $(modal + ' form textarea').val('');
        $.each(data, function(index, value) {
          if ($(form + ' input[name="' + index + '"]').length) {
            $(form + ' input[name="' + index + '"]').val(value);
          } else if ($(form + ' select[name="' + index + '"]').length) {
            $(form + ' select[name="' + index + '"]').find('option[value="' + value + '"]').prop("selected", "selected");
          } else if ($(form + ' textarea[name="' + index + '"]').length) {
            $(form + ' textarea[name="' + index + '"]').val(value);
          }
        });
        $(modal).modal('show');

      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });

  });

  // duplicate
  $('body').on("click", "button.duplicate", function(e) {
    var id = $(this).attr("data-id");
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    var table = $(this).attr("data-table");

    $.ajax({
      url: urlBase + '/user/ajax/userParametersExtractByPrimaryKey/',
      type: 'post',
      data: {
        id: id,
        table: table
      },
      dataType: "json",
      success: function(data) {
        $(modal + ' form select option').removeProp('selected');
        $(modal + ' form textarea').val('');

        $.each(data, function(index, value) {
          if ($(form + ' input[name="' + index + '"]').length) {
            $(form + ' input[name="' + index + '"]').val(value);
          } else if ($(form + ' select[name="' + index + '"]').length) {
            $(form + ' select[name="' + index + '"]').find('option[value="' + value + '"]').prop("selected", "selected");
          } else if ($(form + ' textarea[name="' + index + '"]').length) {
            $(form + ' textarea[name="' + index + '"]').val(value);
          }
        });
        if ($(modal + ' form input[name="name"]').length) {
          $(modal + ' form input[name="name"]').val('userDefined' + Date.now());
        }
        $(modal + ' form input[name="id"]').remove();
        $(modal).modal('show');

      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });

  });

  //ajax save form in modal
  $('body').on("click", "button.modal-save", function(e) {
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    ajaxModalSave(form, modal, function() {
      userParametersPrescriptionsCatList();
      userParametersPrescriptionsList();
      $(modal).modal('hide');
    });

  });

  //delete by primary key
  $('body').on("click", "button.delete-by-prim-key", function(e) {

    var id = $(this).attr("data-id");
    var table = $(this).attr("data-table");

    if (confirm("Êtes-vous certain ?")) {

      $.ajax({
        url: urlBase + '/user/ajax/userParametersDelByPrimaryKey/',
        type: 'post',
        data: {
          id: id,
          table: table
        },
        dataType: "json",
        success: function(data) {
          userParametersPrescriptionsCatList();
          userParametersPrescriptionsList();

        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });
    }
  });


  // nouvelle catégorie de prescription
  $('body').on("click", "button.nouvelle-cat", function(e) {
    var modal = $(this).attr("data-target");
    $(modal + ' form input[name="id"]').remove();
    $(modal + ' form input[name="label"], ' + modal + ' form input[name="description"]').val('');
    $(modal + ' form textarea').val('');
    $(modal + ' form select option').removeProp('selected');
    $(modal + ' form select option:eq(0)').prop('selected', 'selected');
    $(modal + ' form input[name="name"]').val('userDefined' + Date.now());

  });

  // nouvelle prescription type
  $('body').on("click", "button.nouvelle-pres", function(e) {
    var modal = $(this).attr("data-target");
    $(modal + ' form input[name="id"]').remove();
    $(modal + ' form input[name="label"], ' + modal + ' form input[name="description"]').val('');
    $(modal + ' form textarea').val('');
    $(modal + ' form select option').removeProp('selected');
    $(modal + ' form select option:eq(0)').prop('selected', 'selected');
    $(modal + ' form input[name="name"]').val('userDefined' + Date.now());

  });

  //////////////////////////////////////////////
  /////////////////////// LAP

  // alerter sur le passsage sous le seuil de certification
  $("input.alerteInfSeuilCertif").on("change", function() {
    alerteInfSeuilCertif($(this));
  });

  // afficher la liste des patients pour lesquels le SAM est off
  $(".userParametersDisplayListSamPatientsDisabled").on("click", function() {
    samID = $(this).attr('data-samID');
    if ($('#' + samID + 'List').length) {
      $('#' + samID + 'List').remove();
    } else {
      userParametersDisplayListSamPatientsDisabled($(this));
    }
  });

  // retirer un patient de la liste des patients pour lesquels le SAM est off
  $('body').on("click", ".removePatientFromDisabledSamList", function() {
    removePatientFromDisabledSamList($(this));
  });

});


/**
 * Alerte légale pour réduction du seuil de fonctionnement du LAP par rapport à
 * son niveau de certification
 * @param  {object} el object jquery source du click
 * @return {void}
 */
function alerteInfSeuilCertif(el) {
  if (el.is(":checked") != true) {
    alert("En décochant ce paramètre vous utiliserez le LAP avec des performances inférieures à celles prévues par la certification HAS");
    el.parents('tr').addClass('table-warning');

  } else {
    el.parents('tr').removeClass('table-warning');
  }
}

/**
 * Afficher la liste des patients pour lesquels le SAM est bloqué
 * @param  {object} el oject jquery source du click
 * @return {void}
 */
function userParametersDisplayListSamPatientsDisabled(el) {
  samID = el.attr('data-samID');
  $.ajax({
    url: urlBase + '/user/ajax/userParametersDisplayListSamPatientsDisabled/',
    type: 'post',
    data: {
      samID: samID,
    },
    dataType: "json",
    success: function(data) {
      if ($.isArray(data.patientsList)) {
        html = '<tr id="' + samID + 'List"><td colspan="4"><div class="card my-3"><div class="card-header">Liste des patients pour lesquels vous avez bloqué ce SAM</div><div class="card-body"><table class="table table-hover table-sm">';
        html += '<thead><tr><th>Identité</th><th>Bloqué depuis</th><th>Retirer</th></tr></thead><tbody>';
        $.each(data.patientsList, function(index, ligne) {
          html += '<tr class="lignePatientSamDisabled">';
          html += '<td>' + ligne.prenom + ' ' + ligne.nom + '</td>';
          html += '<td>' + ligne.date + '</td>';
          html += '<td><button type="button" class="btn btn-light btn-sm  removePatientFromDisabledSamList" data-samID="' + samID + '" data-patientID="' + ligne.patientID + '"><i class="fas fa-times"></i></button></td>';
          html += '</tr>';
        });
        html += '</tbody></table></div></div></td></tr>';

        el.parents('tr').after(html);
      } else {
        alert_popup("info", 'Ce SAM n\'est bloqué pour aucun patient');
      }
    },
    error: function() {
      alert_popup("danger", 'Impossible de récupérer la liste demandée');

    }
  });

}

/**
 * Retirer le blocage du SAM pour un patient
 * @param  {object} source object jquery source du clic
 * @return {void}
 */
function removePatientFromDisabledSamList(source) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapSamToggleForPatient/',
    type: 'post',
    data: {
      samID: source.attr('data-samID'),
      patientID: source.attr("data-patientID"),
    },
    dataType: "json",
    success: function(data) {
      source.parents('tr.lignePatientSamDisabled').remove();
      alert_popup("success", 'Ce SAM est réactivé pour ce patient');
    },
    error: function() {
      alert_popup("danger", 'La réactivation de ce SAM pour ce patient a échoué');
    }
  });
}

/**
 * Afficher les catégories de prescriptions types
 * @return {void}
 */
function userParametersPrescriptionsCatList() {
  $.ajax({
    url: urlBase + '/user/ajax/userParametersPrescriptionsCatList/',
    type: 'post',
    dataType: "json",
    success: function(data) {
      $('#catPrescrip').html(data.html);
      // on met à jour le select du modal nouvelle prescription
      if (data.catNonLap) {
        $('#formModalNewPres select[name="cat"]').html('');
        $.each(data.catNonLap, function(index, value) {
          $('#formModalNewPres select[name="cat"]').append('<option value="' + value.id + '">' + value.label + '</option>');
        });
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

/**
 * Afficher les prescriptions types
 * @return {void}
 */
function userParametersPrescriptionsList() {
  $.ajax({
    url: urlBase + '/user/ajax/userParametersPrescriptionsList/',
    type: 'post',
    dataType: "json",
    success: function(data) {
      $('#presType').html(data.html);
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}
