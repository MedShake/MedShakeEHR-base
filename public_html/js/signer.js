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
 * Fonctions JS pour la signature sur périphérique tactil
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  $("#signature").jSignature()

  $(".resetSignature").on("click", function(e) {
    $("#signature").jSignature("reset");
  });



  $(".saveSignature").on("click", function(e) {
    if ($("#signature").jSignature('getData', 'native').length == 0) {
      alert('Merci de signer avant de valider !');

    } else {
      signatureSvg = $("#signature").jSignature("getData", "svg");

      $.ajax({
        url: urlBase + '/public/ajax/publicMakeDocSigne/',
        type: 'post',
        data: {
          signatureSvg: signatureSvg,
          signPeriphName: signPeriphName
        },
        dataType: "html",
        success: function() {
          window.location.href = urlBase + '/public/signer-merci/';
        },
        error: function() {
          alert('Problème, rechargez la page !');

        }
      });


    }
  });


});
