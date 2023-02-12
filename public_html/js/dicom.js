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
 * Fonctions JS pour les pages dicom
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  $('#tabDicom').magnificPopup({
    delegate: 'a',
    type: 'image',
    gallery: {
      enabled: true
    }

  });

  $('#tabDicom').on("click", 'span.voirframes', function(e) {
    frames = $(this).attr("data-frames");
    if($('table.' + frames).hasClass('d-none')) {
      $('table.' + frames).addClass('d-inline-block');
      $('table.' + frames).removeClass('d-none');
    } else {
      $('table.' + frames).addClass('d-none');
      $('table.' + frames).removeClass('d-inline-block');
    }
  });

  $('#tabDicom').on("click", 'button.selectAll', function(e) {
    status = $(this).attr('data-status');


    $('.imagesList input[type=checkbox]').each(function(index) {
      if (status == 'unchecked') {
        $(this).prop("checked", true);
      } else {
        $(this).prop("checked", false);
      }
      checkUncheck($(this));
    });

    if (status == 'unchecked') {
      $(this).attr('data-status', 'checked');
      $(this).html('<i class="fas fa-square mr-1"></i> Tout déselectionner');
    } else {
      $(this).attr('data-status', 'unchecked');
      $(this).html('<i class="fas fa-check-square mr-1"></i> Tout sélectionner');
    }

  });

  $('#tabDicom').on('change', '.imagesList input[type=checkbox]', function() {
    checkUncheck($(this));
  });


});

function checkUncheck(el) {

  numberOfChecked = $('.imagesList input:checkbox:checked').length;
  if (numberOfChecked > 0) {
    $('#makePdfWithDcImages').removeAttr('disabled');
    $('#makeZipWithDcImages').removeAttr('disabled');
  } else {
    $('#makePdfWithDcImages').attr('disabled', 'disabled');
    $('#makeZipWithDcImages').attr('disabled', 'disabled');
  }

  imgfor = '#' + el.attr('data-imgfor');

  if (el.is(':checked')) {
    $(imgfor).closest('td').css("border-color", "green");
  } else {
    $(imgfor).closest('td').css("border-color", "#eee");
  }
}
