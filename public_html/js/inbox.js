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
    $("tr.patietSelect").removeClass('table-success gras');
    $("#idConfirmPatientIDLabel").show();
    $("#idConfirmPatientID").attr('type', 'text');
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
    url: urlBase+'/inbox/ajax/viewMail/',
    type: 'post',
    data: {
      mailID: el.attr('data-mailID'),
    },
    dataType: "html",
    success: function(data) {

      $("tr.mailClicView").each(function( index ) {
        $(this).removeClass('table-success');
        if($(this).attr('data-status') == 'c') $(this).addClass('table-warning');
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
  $("tr.patietSelect").removeClass('table-success gras');
  $(el).addClass('table-success gras');
  patientID = $(el).attr('data-patientID');
  $("#idConfirmPatientID").val(patientID);
  if (patientID > 0) {
    $("#submitIndicID").html(patientID);
    $("#submitBoutonClasser").show();
  }
}
