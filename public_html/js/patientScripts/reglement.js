/**
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
 * Js pour le module règlement du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {


  //close button zone newReglement
  $('body').on("click", "#cleanNewReglement", function(e) {
    $('#newReglement').html('');
  });


  //sortir les data d'un acte
  $("#newReglement").on("change", ".selectActeStarter", function(e) {
    e.preventDefault();
    searchAndInsertActeData($(this));
  });

  //observer la situation Type de réglement
  $("#newReglement").on("change", "#p_197ID", function(e) {
    e.preventDefault();
    calcResteDu();
  });

  //observer lle changement sur dépassement
  $("#newReglement").on("change, keyup", "#p_199ID", function(e) {
    e.preventDefault();
    calcResteDu();
  });

  //le style du champ Reste du
  $("#newReglement").on("change", "#p_196ID", function(e) {
    val = $("#p_196ID").val();
    if (val > 0 || val < 0) {
      $("#p_196ID").closest("div.form-group").removeClass('has-success');
      $("#p_196ID").closest("div.form-group").addClass('has-error');
    } else {
      $("#p_196ID").closest("div.form-group").removeClass('has-error');
      $("#p_196ID").closest("div.form-group").addClass('has-success');
    }
  });

});



//rapatrier en json les data sur un réglement
function searchAndInsertActeData(selecteur) {

  id = selecteur.attr('id');
  acteID = $('#' + id + ' option:selected').val();

  $(".selectActeStarter option[value='']").prop('selected', 'selected');
  $("#" + id + " option[value='" + acteID + "']").prop('selected', 'selected');

  $.ajax({
    url: '/patient/ajax/getReglementData/',
    type: 'post',
    data: {
      acteID: acteID,
    },
    dataType: "json",
    success: function(data) {
      $('#p_198ID').val(data['tarif']);
      $('#p_199ID').val(data['depassement']);
      $('input[name="acteID"]').val(acteID);

      calcResteDu();
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });

}

function calcResteDu() {
  resetModesReglement();

  cas = $('#p_197ID option:selected').val();

  tarif = parseFloat($('#p_198ID').val());
  depassement = parseFloat($('#p_199ID').val());

  //tout venant
  if (cas == 'G') {
    $('#p_199ID').removeAttr('readonly');
    total = parseFloat(tarif) + parseFloat(depassement);
    $('#p_196ID').val(total).change();
    $('#p_200ID').val('');
    //CMU
  } else if (cas == 'CMU' || cas == 'TP') {
    $('#p_199ID').attr('readonly', 'readonly');
    total = parseFloat(tarif);
    $('#p_200ID').val(total);
    $('#p_196ID').val(total).change();
  }

}

function resetModesReglement() {
  $('#p_193ID').val('');
  $('#p_194ID').val('');
  $('#p_195ID').val('');
  $('#p_196ID').val('').change();
}
