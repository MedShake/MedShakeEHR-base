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
 * Js pour le module creation patient / praticien
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  //réactiver un dossier marqué comme supprimé
  $('body').on("click", ".unmarkDeleted", function(e) {
    e.preventDefault();
    if (confirm("Ce dossier sera à nouveau visible dans les listings de recherche.\nSouhaitez-vous poursuivre ? ")) {
      source = $(this);
      $.ajax({
        url: urlBase + '/patients/ajax/unmarkDeleted/',
        type: 'post',
        data: {
          patientID: $(this).attr('data-patientID'),
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
  });


  //lire la carte vitale
  $('body').on("click", ".lireCpsVitale", function(e) {
    btnLec = $(this);
    $.ajax({
      url: urlBase + '/ajax/getCpsVitaleDataRappro/',
      type: 'post',
      data: {
        patientID: $(this).attr('data-patientID'),
      },
      dataType: "json",
      beforeSend: function() {
        btnLec.find('i').addClass('fa-spin');
      },
      complete: function() {
        btnLec.find('i').removeClass('fa-spin');
      },
      success: function(data) {
        console.log(vitaleToEhrTypeName(data));
        $('#lectureCpsVitale div.modal-body').html(ehrTypeDataToHtml('prevenirDossierExistant'));
        $('#lectureCpsVitale').modal('show');
      },
      error: function() {
        alert_popup("danger", 'Essayez à nouveau !');
      }
    });

  });


  $('body').on("click", ".goToPatientFromVitaleData", function(e) {
    e.stopPropagation();
  });

  $('body').on("click", ".peopleVitale", function(e) {
    e.preventDefault();
    indexVitale = $(this).attr('data-indexVitale');

    dataVitale[indexVitale]['firstname'] = ucfirst(dataVitale[indexVitale]['firstname']);

    $.each(dataVitale[indexVitale], function(key, value) {
      $('#id_' + key + '_id').val(value);
    });
    $('#lectureCpsVitale').modal('hide');
  });


  $('body').on("click", "#id_PSCodeProSpe_idAddOn, #id_PSCodeStructureExercice_idAddOn", function(e) {
    groupe = $(this).parents('div.input-group');
    selectFils = groupe.children('select');
    curVal = selectFils.val();
    if(!curVal) curVal = selectFils.attr('data-defautValue');
    id = selectFils.attr('id');
    title = selectFils.attr('title');
    name = selectFils.attr('name');
    dataTypeid = selectFils.attr('data-typeid');
    dataInternalName = selectFils.attr('data-typeid');
    groupe.replaceWith('<input class="form-control form-control-sm" type="text" id="' + id + '" title="' + title + '" name="' + name + '" data-typeid="' + dataTypeid + '" data-internalname="' + dataInternalName + '" value="' + curVal + '"/>');
    activeWatchChange('.changeObserv');
  });

});
