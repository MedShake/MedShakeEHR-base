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

  $('.imagesList').magnificPopup({
    delegate: 'a',
    type: 'image',
    gallery: {
      enabled: true
    }

  });

  $('span.voirframes').on("click", function(e) {
    frames = $(this).attr("data-frames");
    $('span.'+frames).toggle();
  });

  $('button.selectAll').on("click", function(e) {
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
    } else {
      $(this).attr('data-status', 'unchecked');
    }

  });

  $('.imagesList input[type=checkbox]').change(function() {
    checkUncheck($(this));
  });


});

function checkUncheck(el) {

  imgfor = '#' + el.attr('data-imgfor');

  if (el.is(':checked')) {
    $(imgfor).css("border", "10px solid green");
  } else {
    $(imgfor).css("border", "10px solid #EEE");
  }
}
