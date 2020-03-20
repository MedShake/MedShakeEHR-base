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
 * Js pour le module email du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {


  //close button zone newMail
  $('body').on("click", "#cleanNewMail", function(e) {
    $(window).unbind("beforeunload");
    $('#newMail').html('');
  });

  //injection du modèle
  $('body').on("change", "select[name='mailModeles']", function(e) {
    modeleID = $("select[name='mailModeles'] option:selected").val();
    $.ajax({
      url: urlBase + '/patient/ajax/extractMailModele/',
      type: 'post',
      data: {
        modeleID: modeleID,
        patientID: $('#identitePatient').attr("data-patientID"),
        objetID: $("input[name='objetID']").val()
      },
      dataType: "html",
      success: function(data) {
        $("textarea[name='mailBody']").val(data);
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });


  });

  //autocomplete pour les destinataire apicrypt
  $('body').on('focusin', 'input[name="mailToApicrypt"]', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/ajax/getAutocompleteLinkType/emailApicrypt/emailApicrypt/birthname:lastname:firstname:emailApicrypt/',
      autoFocus: true
    });
  });

  //autocomplete pour les destinataire mail (adresse non apicrypt des pro)
  $('body').on('focusin', 'input[name="mailTo"]', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/ajax/getAutocompleteLinkType/profesionnalEmail/profesionnalEmail/birthname:lastname:firstname:profesionnalEmail/',
      autoFocus: true
    });
  });

  //autocomplete pour le destinataire ecofax
  $('body').on('focusin', 'input[name="mailToEcofaxName"]', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/ajax/getAutocompleteLinkType/faxPro/birthname:lastname:firstname/birthname:lastname:firstname:faxPro/',
      select: function(event, ui) {
        $('input[name="mailToEcofaxNumber"]').val(ui.item.faxPro);
      }
    });
  });

  //autocomplete pour le numero ecofax (reverse)
  $('body').on('focusin', 'input[name="mailToEcofaxNumber"]', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/ajax/getAutocompleteLinkType/faxPro/faxPro/birthname:lastname:firstname:faxPro/',
      select: function(event, ui) {
        if (ui.item.birthname && ui.item.lastname) {
          $('input[name="mailToEcofaxName"]').val(ui.item.lastname + ' (' + ui.item.birthname + ') ' + ui.item.firstname);
        } else if (ui.item.birthname) {
          $('input[name="mailToEcofaxName"]').val(ui.item.birthname + ' ' + ui.item.firstname);
        } else {
          $('input[name="mailToEcofaxName"]').val(ui.item.lastname + ' ' + ui.item.firstname);
        }

      }
    });
  });

});
