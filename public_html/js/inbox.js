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
 * Fonctions JS pour inbox
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {


  //selection du mail
  $(".mailClicView").on("click", function(e) {
    e.preventDefault();
    viewMail($(this));
  });

  //sélection patient
  $("#view").on("click", ".patietSelect", function(e) {
    selectPatient($(this));
  });

  //spécifier manuellement le patientID
  $("#view").on("click", "#specifierPatientIDManu", function(e) {
    e.preventDefault();
    $("tr.patietSelect").removeClass('table-success font-weight-bold');
    $("#idConfirmPatientIDLabel").show();
    $("#idConfirmPatientID").attr('type', 'text');
  });

  //autocomplete pour la recherche patient
  $('body').delegate('#searchPeopleID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/inbox/ajax/getPatients/',
      select: function(event, ui) {
        $('#tabPatients').append(constructPatientLine(ui.item));
        selectPatient($("tr.patientSelect[data-patientid = " + ui.item.id + "]"));

        $('#searchPeopleID').val(ui.item.label);
        $('#searchPeopleID').attr('data-id', ui.item.id);
      }
    });
  });

  $("#view").on("change keyup", "#idConfirmPatientID", function(e) {
    patientID = $(this).val();
    if (patientID > 0) {
      $("#submitIndicID").html(patientID);
      $("#submitBoutonClasser").show();
    }
  });

  // rafraichir quand on classe
  $("#view").on("submit", "#classerDansDossier", function(e) {
    setTimeout(function() {
      $("tr.mailClicView.table-success").attr('data-status', 'c');
      viewMail($("tr.mailClicView.table-success"));
    }, 500);

  });


  // premier chargement
  viewMail($("tr.mailClicView.table-success"));


});

function viewMail(el) {
  $.ajax({
    url: urlBase + '/inbox/ajax/viewMail/',
    type: 'post',
    data: {
      mailID: el.attr('data-mailID'),
    },
    dataType: "html",
    success: function(data) {

      $("tr.mailClicView").each(function(index) {
        $(this).removeClass('table-success');
        if ($(this).attr('data-status') == 'c') $(this).addClass('table-warning');
      });

      $(el).removeClass('table-warning').addClass('table-success');

      $('#view').html(data);
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}


function selectPatient(el) {
  $("tr.patietSelect").removeClass('table-success font-weight-bold');
  $(el).addClass('table-success font-weight-bold');
  patientID = $(el).attr('data-patientID');
  $("#idConfirmPatientID").val(patientID);
  if (patientID > 0) {
    $("#submitIndicID").html(patientID);
    $("#submitBoutonClasser").show();
  }
}

function constructPatientLine(data) {
  if (data.birthname == null) {
    data.birthname = '';
  }
  if (data.lastname == null) {
    data.lastname = '';
  }

  if (data.birthname.length > 0 && data.lastname.length > 0) {
    identiteNom = data.lastname + ' (' + data.birthname + ')';
  } else if (data.lastname.length > 0) {
    identiteNom = data.lastname;
  } else if (data.birthname.length > 0) {
    identiteNom = data.birthname;
  } else {
    identiteNom = '';
  }

  line = '<tr class="patientSelect cursor-pointer" data-patientid="' + data.id + '"> \
    <td>#' + data.id + '</td> \
    <td>' + identiteNom + '</td> \
    <td>' + data.firstname + '</td> \
    <td>' + data.birthdate + '</td> \
    <td class="small">' + data.streetNumber + ' ' + data.street + ' ' + data.postalCodePerso + ' ' + data.city + '</td> \
    <td  class="small">' + (data.nss != null ? data.nss : '') + '</td> \
    <td> \
    <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/patient/' + data.id + '/" target="_blank"> \
      <span class="fas fa-folder-open" aria-hidden="true" title="Voir dossier"></span> \
    </a> \
    </td> \
  </tr>";'
  return line;
}
