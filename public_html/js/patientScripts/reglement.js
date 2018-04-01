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
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  //close button zone newReglement
  $('body').on("click", "#cleanNewReglement", function(e) {
    $(window).unbind("beforeunload");
    $('#newReglement').html('');
  });


  //sortir les data d'un acte
  $("#newReglement").on("change", ".selectActeStarter", function(e) {
    e.preventDefault();
    searchAndInsertActeData($(this));
  });

  //observer la situation Type de réglement
  $("#newReglement").on("change", ".regleSituationPatient", function(e) {
    e.preventDefault();
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //observer le changement sur dépassement
  $("#newReglement").on("change, keyup", ".regleDepaCejour", function(e) {
    e.preventDefault();
    $(this).val($(this).val().replace(' ',''));
    calcResteDu();
  });

  //le style du champ Reste du
  $("#newReglement").on("change", ".regleFacture", function(e) {
    val = $(".regleFacture").val();
    if (val > 0 || val < 0) {
      $(".regleFacture").closest("div.form-group").removeClass('has-success');
      $(".regleFacture").closest("div.form-group").addClass('has-error');
    } else {
      $(".regleFacture").closest("div.form-group").removeClass('has-error');
      $(".regleFacture").closest("div.form-group").addClass('has-success');
    }
  });

  //reinjection pour édition
  $(".regleTarifCejour").attr('data-tarifdefaut', $(".regleTarifCejour").val());
  $(".regleDepaCejour").attr('data-tarifdefaut',$(".regleDepaCejour").val());

});



//rapatrier en json les data sur un réglement
function searchAndInsertActeData(selecteur) {

  id = selecteur.attr('id');
  acteID = $('#' + id + ' option:selected').val();

  if (acteID == '') {
    resetModesReglement();
    $('#detFacturation').hide();
    $('.regleFacture').val('');
    $('.regleTarifCejour').val('');
    $('.regleDepaCejour').val('');
    return;
  }

  var pourcents=$('.pourcents').length;

  $(".selectActeStarter option[value='']").prop('selected', 'selected');
  $("#" + id + " option[value='" + acteID + "']").prop('selected', 'selected');
  
  $.ajax({
    url: urlBase+'/patient/ajax/getReglementData/',
    type: 'post',
    data: {
      acteID: acteID,
    },
    dataType: "json",
    success: function(data) {
      $(".regleTarifCejour").attr('data-tarifdefaut', data['tarif']);
      $(".regleDepaCejour").attr('data-tarifdefaut',data['depassement']);
      $('input[name="acteID"]').val(acteID);

      if(data['flagCmu'] == "1") {
        $(".regleSituationPatient").val('CMU');
      } else {
        $(".regleSituationPatient").val('G');
      }

      $('#detFacturation tbody').html('');
      $.each(data['details'], function( index, value ) {
        $('#detFacturation tbody').append("<tr><td>" + index + "</td><td>" + (pourcents ? (value['pourcents'] + "</td><td>") : '') + value['tarif'] + "</td><td>" + value['depassement'] + "</td><td>" + value['total'] + "</td></tr>");

      });
      $('#detFacturation').show();

      setDefautTarifEtDepa();
      calcResteDu();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });

}

function setDefautTarifEtDepa() {
  resetModesReglement();

  $(".regleTarifCejour").val($(".regleTarifCejour").attr('data-tarifdefaut'));
  $(".regleDepaCejour").val($(".regleDepaCejour").attr('data-tarifdefaut'));



  cas = $(".regleSituationPatient" + ' option:selected').val() || 'G';

  tarif = parseFloat($(".regleTarifCejour").val());
  depassement = parseFloat($(".regleDepaCejour").val());

  //tout venant
  if (cas == 'G') {
    $(".regleDepaCejour").removeAttr('readonly');
  //CMU
  } else if (cas == 'CMU') {
    $(".regleDepaCejour").attr('readonly', 'readonly');
    $(".regleDepaCejour").val('0');
  // TP
  } else if (cas == 'TP') {
    $(".regleDepaCejour").removeAttr('readonly');
    $(".regleDepaCejour").val('0');
  // TP ALD
  } else if (cas == 'TP ALD') {
    $(".regleDepaCejour").attr('readonly', 'readonly');
    $(".regleDepaCejour").val('0');
  }
}

function calcResteDu() {
  cas = $(".regleSituationPatient" +" option:selected").val() || 'G';
  tarif = parseFloat($(".regleTarifCejour").val());
  depassement = parseFloat($(".regleDepaCejour").val());

  //tout venant
  if (cas == 'G') {
    total = parseFloat(tarif) + parseFloat(depassement);
    $(".regleFacture").val(total).change();
    $(".regleTiersPayeur").val('');
    //CMU
  } else if (cas == 'CMU') {
    total = parseFloat(tarif);
    $(".regleTiersPayeur").val(total);
    $(".regleFacture").val(total).change();
  } else if (cas == 'TP') {
    total = parseFloat(tarif) + parseFloat(depassement);
    tiers = Math.round((tarif * 70 / 100)*100) /100;
    reste = Math.round((total-tiers)*100)/100;
    $(".regleTiersPayeur").val(tiers);
    $(".regleFacture").val(total).change();
    $(".regleTiersPayeur").parents(".form-group").children("label").html('Tiers (reste à payer : '+ reste +'€)');
  } else if (cas == 'TP ALD') {
    total = parseFloat(tarif);
    $(".regleTiersPayeur").val(total);
    $(".regleFacture").val(total).change();
  }

}

function resetModesReglement() {

  $(".regleCheque").val('');
  $(".regleCB").val('');
  $(".regleEspeces").val('');
  $(".regleFacture").val('').change();
  $(".regleTiersPayeur").parents(".form-group").children("label").html('Tiers');
}
