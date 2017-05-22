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
 */

$(document).ready(function() {


  //close button zone newMail
  $('body').on("click", "#cleanNewMail", function(e) {
    $('#newMail').html('');
  });

  //injection du modèle
  $('body').on("change", "#p_446ID", function(e) {
    modeleID = $('#p_446ID option:selected').val();
    $.ajax({
      url: '/patient/ajax/extractMailModele/',
      type: 'post',
      data: {
        modeleID: modeleID,
      },
      dataType: "html",
      success: function(data) {
        $('#p_111ID').val(data);
      },
      error: function() {
        alert('Problème, rechargez la page !');
      }
    });


  });

  //autocomplete pour les destinataire apicrypt
  $('body').delegate('#p_179ID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: '/ajax/getAutocompleteFormValues/data_types/59/',
      autoFocus: true
    });
  });

});
