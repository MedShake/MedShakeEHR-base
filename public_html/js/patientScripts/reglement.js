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
 * @edited fr33z00 <https://www.github.com/fr33z00>
 */

init.reglement = function (){


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
  $("#newReglement").on("change", "select[name='regleSituationPatient']", function(e) {
    e.preventDefault();
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //observer lle changement sur dépassement
  $("#newReglement").on("change, keyup", "input[name='regleDepaCejour']", function(e) {
    e.preventDefault();
    calcResteDu();
  });

  //le style du champ Reste du
  $("#newReglement").on("change", "input[name='regleFacture']", function(e) {
    val = $("input[name='regleFacture']").val();
    if (val > 0 || val < 0) {
      $("input[name='regleFacture']").closest("div.form-group").removeClass('has-success');
      $("input[name='regleFacture']").closest("div.form-group").addClass('has-error');
    } else {
      $("input[name='regleFacture']").closest("div.form-group").removeClass('has-error');
      $("input[name='regleFacture']").closest("div.form-group").addClass('has-success');
    }
  });

  //reinjection pour édition
  $("input[name='regleTarifCejour']").attr('data-tarifdefaut', $("input[name='regleTarifCejour']").val());
  $("input[name='regleDepaCejour']").attr('data-tarifdefaut',$("input[name='regleDepaCejour']").val());

};



//rapatrier en json les data sur un réglement
function searchAndInsertActeData(selecteur) {

  id = selecteur.attr('id');
  acteID = $('#' + id + ' option:selected').val();

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
      $("input[name='regleTarifCejour']").attr('data-tarifdefaut', data['tarif']);
      $("input[name='regleDepaCejour']").attr('data-tarifdefaut',data['depassement']);
      $('input[name="acteID"]').val(acteID);

      if(data['flagCmu'] == "1") {
        $("select[name='regleSituationPatient']").val('CMU');
      } else {
        $("select[name='regleSituationPatient']").val('G');
      }

      $('#detFacturation tbody').html('');
      $.each(data['details'], function( index, value ) {
        $('#detFacturation tbody').append("<tr><td>" + index + "</td><td>" + value['pourcents'] + "</td><td>" + value['tarif'] + "</td><td>" + value['depassement'] + "</td><td>" + value['total'] + "</td></tr>");

      });
      $('#detFacturation').show();

      setDefautTarifEtDepa();
      calcResteDu();
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });

}

function setDefautTarifEtDepa() {
  resetModesReglement();

  $("input[name='regleTarifCejour']").val($("input[name='regleTarifCejour']").attr('data-tarifdefaut'));
  $("input[name='regleDepaCejour']").val($("input[name='regleDepaCejour']").attr('data-tarifdefaut'));



  cas = $('select[name="regleSituationPatient"] option:selected').val() || 'G';

  tarif = parseFloat($("input[name='regleTarifCejour']").val());
  depassement = parseFloat($("input[name='regleDepaCejour']").val());

  //tout venant
  if (cas == 'G') {
    $("input[name='regleDepaCejour']").removeAttr('readonly');
  //CMU
  } else if (cas == 'CMU') {
    $("input[name='regleDepaCejour']").attr('readonly', 'readonly');
    $("input[name='regleDepaCejour']").val('0');
  // TP
  } else if (cas == 'TP') {
    $("input[name='regleDepaCejour']").removeAttr('readonly');
    $("input[name='regleDepaCejour']").val('0');
  // TP ALD
  } else if (cas == 'TP ALD') {
    $("input[name='regleDepaCejour']").attr('readonly', 'readonly');
    $("input[name='regleDepaCejour']").val('0');
  }
}

function calcResteDu() {
  cas = $('select[name="regleSituationPatient"] option:selected').val() || 'G';
  tarif = parseFloat($("input[name='regleTarifCejour']").val());
  depassement = parseFloat($("input[name='regleDepaCejour']").val());

  //tout venant
  if (cas == 'G') {
    total = parseFloat(tarif) + parseFloat(depassement);
    $("input[name='regleFacture']").val(total).change();
    $("input[name='regleTiersPayeur']").val('');
    //CMU
  } else if (cas == 'CMU') {
    total = parseFloat(tarif);
    $("input[name='regleTiersPayeur']").val(total);
    $("input[name='regleFacture']").val(total).change();
  } else if (cas == 'TP') {
    total = parseFloat(tarif) + parseFloat(depassement);
    tiers = Math.round((tarif * 70 / 100)*100) /100;
    reste = Math.round((total-tiers)*100)/100;
    $("input[name='regleTiersPayeur']").val(tiers);
    $("input[name='regleFacture']").val(total).change();
    $("label[for='p_200ID']").html('Tiers (reste à payer : '+ reste +'€)');
  } else if (cas == 'TP ALD') {
    total = parseFloat(tarif);
    $("input[name='regleTiersPayeur']").val(total);
    $("input[name='regleFacture']").val(total).change();
  }

}

function resetModesReglement() {

  $("input[name='regleCheque']").val('');
  $("input[name='regleCB']").val('');
  $("input[name='regleEspeces']").val('');
  $("input[name='regleFacture']").val('').change();
  $("label[for='p_200ID']").html('Tiers');
}
