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
 * Fonctions JS pour la recherche patients / pros
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {
  updateListingPatients();

  $(".searchupdate").on("keyup", function(e) {
    updateListingPatients();
  });
  $('body').on("click", ".openPatient td:nth-child(-n+6)", function(e) {
    window.location.href = urlBase+$(this).closest('tr').attr('data-url');
  });



  //envoyer pour signature
  $('body').on("click", "a.sendSign, button.sendSign", function(e) {
    e.preventDefault();
    source = $(this);
    $.ajax({
      url: urlBase+'/patients/ajax/patientsSendSign/',
      type: 'post',
      data: {
        patientID: $(this).attr('data-patientID'),
        typeID: $(this).attr('data-typeID')
      },
      dataType: "html",
      success: function(data) {
        el = source.closest('tr');
        el.css("background", "#efffe8");
        setTimeout(function() {
          el.css("background", "");
        }, 1000);
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  //marquer dossier comme supprimé
  $('body').on("click", "a.markDeleted", function(e) {
    e.preventDefault();
    if (confirm("Ce dossier ne sera plus visible et sera marqué comme supprimé.\nIl pourra cependant être restauré si besoin\nSouhaitez-vous poursuivre ? ")) {
      if (confirm("Confirmez-vous le marquage comme supprimé de ce dossier ?")) {

        motif=prompt("Motif de suppression ?");

        source = $(this);
        $.ajax({
          url: urlBase+'/patients/ajax/markDeleted/',
          type: 'post',
          data: {
            patientID: $(this).attr('data-patientID'),
            motif: motif
          },
          dataType: "html",
          success: function(data) {
            el = source.closest('tr');
            el.css("background", "#efffe8");
            setTimeout(function() {
              el.css("background", "");
              el.remove();
            }, 1000);

          },
          error: function() {
            alert_popup("danger", 'Problème, rechargez la page !');

          }
        });
      }
    }
  });

  // bouton de nouvelle transmission
  $('body').on("click", ".newTransmission", function(e) {
    e.preventDefault();
    $('#transConcerne').addClass('d-none');
    $('#transPatientConcID').val($(this).attr('data-patientID'));
    $('#transPatientConcSel').html($(this).parents('tr').find('span.identite').html());
    $('#transPatientConcSel').removeClass('d-none');

    $('#modalTransmission').modal('show');
  });
  // poster une transmission
  $('body').on("click", "#transmissionEnvoyer", function(e) {
    e.preventDefault();
    transmissionNewNextLocation = 'stayHere';
    posterTransmission();
  });

  //ajouter / retirer liste des Praticiens
  $('body').on("click", "a.switchPraticienListe", function(e) {
    e.preventDefault();
    source = $(this);
    $.ajax({
      url: urlBase+'/patients/ajax/switchPraticienListe/',
      type: 'post',
      data: {
        patientID: $(this).attr('data-patientID')
      },
      dataType: "json",
      success: function(data) {
        el = source.closest('tr');
        if (data.type == 'pro') {
          source.html('Retirer de la liste Praticiens');
          el.addClass('table-info')
        } else {
          el.removeClass('table-info')
          source.html('Ajouter de la liste Praticiens');
        };
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  $('body').on("click", ".extAsPatient", function(e){
    e.preventDefault();
    var externID = $('.extToNew').attr("data-externid");
    var patientID = $(this).attr("data-patientid");
    $.ajax({
      url: urlBase+'/people/ajax/setExternAsPatient/',
      type: 'post',
      data: {
        externID: externID,
        patientID: patientID
      },
      dataType: "json",
      success: function(data) {
        window.location=urlBase+'/patient/'+patientID+'/';
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  $('.extToNew').on("click", function(e){
    e.preventDefault();
    var externID = $(this).attr("data-externid");
    $.ajax({
      url: urlBase+'/people/ajax/setExternAsNewPatient/',
      type: 'post',
      data: {
        externID: externID
      },
      dataType: "json",
      success: function(data) {
        window.location=urlBase+'/patient/edit/'+externID+'/';
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });

  });

});


function updateListingPatients() {

  $.ajax({
    url: urlBase+'/patients/ajax/patientsListByCrit/',
    type: 'post',
    data: {
      porp: $('#listing').attr('data-porp'),
      d2: $('#d2').val(),
      d3: $('#d3').val(),
      autreCrit: $('#autreCrit option:selected').val(),
      autreCritVal: $('#autreCritVal').val(),
    },
    dataType: "html",
    success: function(data) {
      $('#listing').html(data);
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });

}
