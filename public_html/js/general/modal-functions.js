/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2019
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
 * JS relatif aux modals : functions
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

 /**
  * Sauvergarder la fenêtre modal
  * @param  {string} form  selecteur du form
  * @param  {string} modal selecteur de la modal concernée
  * @return {void}
  */
 function ajaxModalSave(form, modal, success = function(){return true;}) {
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
         success();

       } else {
         $(modal + ' div.alert.cleanAndHideOnModalHide').removeClass('d-none');
         $(modal + ' div.alert.cleanAndHideOnModalHide ul').html('');
         $.each(data.msg, function(index, value) {
           $(modal + ' div.alert.cleanAndHideOnModalHide ul').append('<li>' + value + '</li>');
         });
         $(modal + ' .is-invalid').removeClass('is-invalid');
         $.each(data.code, function(index, value) {
           $(modal + ' *[name="' + value + '"]').addClass('is-invalid');
         });
       }
     },
     error: function() {
       alert_popup("danger", 'Problème, rechargez la page !');
     }
   });
 }
