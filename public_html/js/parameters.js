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
 * Fonctions JS pour les paramètres utilisateur
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */

var gotGroups = false;
var gotCals = false;

$(document).ready(function() {

  //pour agenda
  $("body").on("click", '.date', function(e) {
    e.stopPropagation();
    $('.date').each(function(idx, el) {
      if ($(el).data("DateTimePicker")) $(el).data("DateTimePicker").destroy()
    });
    $(this).datetimepicker({
      format: 'HH:mm',
      icons: {
        time: 'far fa-clock',
        date: 'fa fa-calendar',
        up: 'fa fa-chevron-up',
        down: 'fa fa-chevron-down',
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-crosshairs',
        clear: 'fa fa-trash',
        close: 'fa fa-times'
      }
    });
    $(this).data("DateTimePicker").show();
  });
  $('body').on("click", function() {
    $('.date').each(function(idx, el) {
      if ($(el).data("DateTimePicker")) $(el).data("DateTimePicker").destroy()
    });
  });

  //pour les consultations

  $('body').on("click", ".colorpicker", function(e) {
    e.stopPropagation();
    $(".colorpicker").each(function(idx, el) {
      if ($(this).data('colorpicker')) $(this).data('colorpicker').destroy()
    });
    $(this).colorpicker();
    $(this).data('colorpicker').show();
  });

  $('body').on("click", function(e) {
    $(".colorpicker").each(function(idx, el) {
      if ($(el).data('colorpicker')) $(el).data('colorpicker').destroy()
    });
  });

  $('body').on('change', ".colorpicker input", function(e) {
    $(this).parent().find("i").css("background-color", $(this).parent().find("input").val());
  });

  $("body").on("click", ".delConsult", function(e) {
    e.preventDefault();
    $(this).parent().parent().remove();
  });

  $("body").on("click", ".addConsult", function(e) {
    e.preventDefault();
    e.stopPropagation();
    var id = (Math.random() * 100000) >> 0;
    $(".adder").before('\
              <tr>\
                <td>\
                  <a class="delConsult" href="javascript:void(0)">\
                    <span class="fa fa-minus"></span>\
                  </a>\
                </td>\
                <td>\
                  <input class="form-control form-control-sm" name="key_new' + id + '" placeholder="ex: C1" type="text" value="" autocomplete="off">\
                </td>\
                <td>\
                  <input class="form-control form-control-sm" name="desc_new' + id + '" type="text" placeholder="ex: consultation classique" value="" autocomplete="off">\
                </td>\
                <td>\
                  <div class="input-group input-group-sm colorpicker cpnew" data-toggle="false">\
                    <input class="form-control" name="back_new' + id + '" type="text" value="#2196f3" placeholder="ex: #2196f3" autocomplete="off">\
                    <div class="input-group-append"><span class="input-group-text"><i style="width:16px;height:16px;background-color:#2196f3"></i></span></div>\
                  </div>\
                </td>\
                <td>\
                  <div class="input-group input-group-sm colorpicker cpnew" data-toggle="false">\
                    <input class="form-control" name="border_new' + id + '" type="text" value="#1e88e5" placeholder="ex: #1e88e5" autocomplete="off">\
                    <div class="input-group-append"><span class="input-group-text"><i style="width:16px;height:16px;background-color:#1e88e5"></i></span></div>\
                  </div>\
                </td>\
                <td>\
                  <input class="form-control form-control-sm" name="duree_new' + id + '" type="text" value="" placeholder="ex: 20" autocomplete="off">\
                </td>\
              </tr>\
      ');
    $('.colorpicker.cpnew').removeClass('cpnew').colorpicker();
  });

  //pour clicRDV

  $('#id_clicRdvConsultId_id').hide();

  if ($('#id_clicRdvUserId_id').val() == '') {
    $('#id_clicRdvCalId_id').hide();
    $('label[for="id_clicRdvCalId_id"]').hide();
  }

  if ($('#id_clicRdvUserId_id').val() == '' || $('#id_clicRdvPassword_id').val() == '') {
    $('#id_clicRdvGroupId_id').hide();
    $('label[for="id_clicRdvGroupId_id"]').hide();
  }

  var k = 0;
  for (var idx in clicRdvConsultsRel) {
    if (!k)
      $('#pc input[type=submit]').parent().parent().before('<h4 class="consults">Correspondances de consultations</h4>');
    $('#pc input[type=submit]').parent().parent().before('<div class="row consults"><div class="col-md-3"><label class="control-label" for="id_clicRdvConsultId' + k + '_id">' + clicRdvConsultsRel[idx][1] + ' (clicRdv)</label><div class="form-group"></div></div></div>');
    $('#id_clicRdvConsultId_id').clone()
      .attr('id', 'id_clicRdvConsultId' + k + '_id')
      .attr('name', 'p_clicRdvConsultId' + k)
      .insertAfter('label[for="id_clicRdvConsultId' + k + '_id"]')
      .show();
    $('#id_clicRdvConsultId' + k + '_id option').each(function(i, el) {
      $(el).val($(el).val() + ':' + idx + ':' + clicRdvConsultsRel[idx][1]);
    });
    var j = 0;
    for (var i in clicRdvConsults) {
      if (i == clicRdvConsultsRel[idx][0]) {
        $('#id_clicRdvConsultId' + k + '_id').get(0).selectedIndex = j;
        break;
      }
      j++;
    }
    k++
  }

  $("#id_clicRdvUserId_id").on("keyup", function() {
    if (!$(this).val() || $(this).val() == '') {
      $('label[for="id_clicRdvGroupId_id"]').hide();
      $('#id_clicRdvGroupId_id').hide();
      $('label[for="id_clicRdvCalId_id"]').hide();
      $('#id_clicRdvCalId_id').hide();
      $('.consults').remove();
    }
  });

  $("#id_clicRdvPassword_id").on("keyup", function() {
    $('#id_clicRdvGroupId_id').show();
    $('label[for="id_clicRdvGroupId_id"]').show();
    if ($('#id_clicRdvUserId_id').val() == '' || $('#id_clicRdvPassword_id').val() == '') {
      $('#id_clicRdvGroupId_id').hide();
      $('label[for="id_clicRdvGroupId_id"]').hide();
      $('#id_clicRdvCalId_id').hide();
      $('label[for="id_clicRdvCalId_id"]').hide();
      $('.consults').remove();
    }
  });

  $("#id_clicRdvGroupId_id").on("click", function() {
    if (!gotGroups) {
      $('#id_clicRdvGroupId_id').empty();
      $('#id_clicRdvGroupId_id').append('<option value="empty">En attente de clicRDV...</option>');
      if ($('#id_clicRdvUserId_id').val() != '' && $('#id_clicRdvPassword').val() != '')
        updateGroupList();
      else
        alert_popup("danger", "Entrez d'abord les identifiants clicRDV");

    }
  });

  $("#id_clicRdvGroupId_id").on("change", function() {
    $('#id_clicRdvCalId_id').empty();
    $('#id_clicRdvCalId_id').show();
    $('label[for="id_clicRdvCalId_id"]').show();
    $('.consults').remove();
    if ($(this).val() != '') {
      gotCals = false;
      updateCalList();
    }
  });

  $("#id_clicRdvCalId_id").on("click", function() {
    if (!gotCals) {
      $('#id_clicRdvCalId_id').empty();
      $('#id_clicRdvCalId_id').append('<option value="empty">En attente de clicRDV...</option>');
      if ($('#id_clicRdvUserId_id').val() != '' && $('#id_clicRdvPassword').val() != '')
        updateCalList();
    }
  });

  $("#id_clicRdvCalId_id").on("change", function() {
    $('.consults').remove();
    updateConsultList();
  });

  // pour LAP
  $("input.alerteInfSeuilCertif").on("change", function() {
    alerteInfSeuilCertif($(this));
  });

  $(".displayListSamPatientsDisabled").on("click", function() {
    samID = $(this).attr('data-samID');
    if ($('#' + samID + 'List').length) {
      $('#' + samID + 'List').remove();
    } else {
      displayListSamPatientsDisabled($(this));
    }
  });

  $('body').on("click", ".removePatientFromDisabledSamList", function() {
    removePatientFromDisabledSamList($(this));
  });

});

function updateGroupList() {
  $.ajax({
    url: urlBase + '/user/ajax/updateGroups/',
    type: 'post',
    data: {
      userid: $('#id_clicRdvUserId_id').val(),
      password: $('#id_clicRdvPassword_id').val()
    },
    dataType: "json",
    success: function(data) {
      gotGroups = true;
      $('#id_clicRdvGroupId_id').empty();
      for (var i in data.records) {
        $('#id_clicRdvGroupId_id').append('<option value="' + data.records[i].id + ':' + data.records[i].name + '">' + data.records[i].name + '</option>');
      }
    },
    error: function() {
      alert_popup("danger", 'Erreur de connexion au compte clicRDV. Vérifiez vos identifiants et votre connexion internet');

    }
  });

}

function updateCalList() {
  $.ajax({
    url: urlBase + '/user/ajax/updateCals/',
    type: 'post',
    data: {
      userid: $('#id_clicRdvUserId_id').val(),
      password: $('#id_clicRdvPassword_id').val(),
      groupid: $('#id_clicRdvGroupId_id').val()
    },
    dataType: "json",
    success: function(data) {
      gotCals = true;
      $('#id_clicRdvCalId_id').empty();
      $('#id_clicRdvCalId_id').append('<option value="empty"> </option>');
      for (var i in data.records) {
        $('#id_clicRdvCalId_id').append('<option value="' + data.records[i].id + ':' + (data.records[i].name || data.records[i].publicname) + '">' + (data.records[i].name || data.records[i].publicname) + '</option>');
      }
    },
    error: function() {
      alert_popup("danger", 'Une érreur inconnue s\'est produite, impossible de récupérer les agendas sur clicRDV...');

    }
  });
}

function updateConsultList() {
  $.ajax({
    url: urlBase + '/user/ajax/updateConsults/',
    type: 'post',
    data: {
      userid: $('#id_clicRdvUserId_id').val(),
      password: $('#id_clicRdvPassword_id').val(),
      groupid: $('#id_clicRdvGroupId_id').val(),
      calid: $('#id_clicRdvCalId_id').val()
    },
    dataType: "json",
    success: function(data) {
      $('.consults').remove();
      for (var i in data.records) {
        addConsult(i, data.records[i]);
      }
    },
    error: function() {
      alert_popup("danger", 'Une érreur inconnue s\'est produite, impossible de récupérer les types de consultations sur clicRDV...');

    }
  });
}

function addConsult(idx, consult) {
  if (idx == '0' || idx == 0)
    $('#pc input[type=submit]').parent().parent().before('<h4 class="consults">Correspondances de consultations</h4>');
  $('#pc input[type=submit]').parent().parent().before('<div class="row consults"><div class="col-md-3"><label class="control-label" for="id_clicRdvConsultId' + idx + '_id">' + consult.name + ' (clicRdv)</label><div class="form-group"></div></div></div>');
  $('#id_clicRdvConsultId_id').clone()
    .attr('id', 'id_clicRdvConsultId' + idx + '_id')
    .attr('name', 'p_clicRdvConsultId' + idx)
    .insertAfter('label[for="id_clicRdvConsultId' + idx + '_id"]')
    .show();
  $('#id_clicRdvConsultId' + idx + '_id option').each(function(i, el) {
    $(el).val($(el).val() + ':' + consult.id + ':' + consult.name);
  });
  var j = 0;
  for (var i in clicRdvConsults) {
    if (clicRdvConsults[i].duree == consult.length) {
      $('#id_clicRdvConsultId' + idx + '_id').get(0).selectedIndex = j;
      break;
    }
    j++;
  }
}

/**
 * ALerte légale pour réduction du seuil de fonctionnement du LAP par rapport à
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
function displayListSamPatientsDisabled(el) {
  samID = el.attr('data-samID');
  $.ajax({
    url: urlBase + '/user/ajax/displayListSamPatientsDisabled/',
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
