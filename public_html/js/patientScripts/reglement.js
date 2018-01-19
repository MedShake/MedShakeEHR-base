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

if (!champsReglement)
  var champsReglement = {
    tarif: "input[name='regleTarifCejour']",
    depacement: "input[name='regleDepaCejour']",
    situation: "select[name='regleSituationPatient']",
    facture: "input[name='regleFacture']",
    tiers: "input[name='regleTiersPayeur']",
    id_tiers: "label[for='id_regleTiersPayeur_id']"
  };

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
  $("#newReglement").on("change", champsReglement.situation, function(e) {
    e.preventDefault();
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //observer le changement sur dépassement
  $("#newReglement").on("change, keyup", champsReglement.depacement, function(e) {
    e.preventDefault();
    calcResteDu();
  });

  //le style du champ Reste du
  $("#newReglement").on("change", champsReglement.facture, function(e) {
    val = $(champsReglement.facture).val();
    if (val > 0 || val < 0) {
      $(champsReglement.facture).closest("div.form-group").removeClass('has-success');
      $(champsReglement.facture).closest("div.form-group").addClass('has-error');
    } else {
      $(champsReglement.facture).closest("div.form-group").removeClass('has-error');
      $(champsReglement.facture).closest("div.form-group").addClass('has-success');
    }
  });

  //reinjection pour édition
  $(champsReglement.tarif).attr('data-tarifdefaut', $(champsReglement.tarif).val());
  $(champsReglement.depacement).attr('data-tarifdefaut',$(champsReglement.depacement).val());

});



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
      $(champsReglement.tarif).attr('data-tarifdefaut', data['tarif']);
      $(champsReglement.depacement).attr('data-tarifdefaut',data['depassement']);
      $('input[name="acteID"]').val(acteID);

      if(data['flagCmu'] == "1") {
        $(champsReglement.situation).val('CMU');
      } else {
        $(champsReglement.situation).val('G');
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

  $(champsReglement.tarif).val($(champsReglement.tarif).attr('data-tarifdefaut'));
  $(champsReglement.depacement).val($(champsReglement.depacement).attr('data-tarifdefaut'));



  cas = $(champsReglement.situation + ' option:selected').val() || 'G';

  tarif = parseFloat($(champsReglement.tarif).val());
  depassement = parseFloat($(champsReglement.depacement).val());

  //tout venant
  if (cas == 'G') {
    $(champsReglement.depacement).removeAttr('readonly');
  //CMU
  } else if (cas == 'CMU') {
    $(champsReglement.depacement).attr('readonly', 'readonly');
    $(champsReglement.depacement).val('0');
  // TP
  } else if (cas == 'TP') {
    $(champsReglement.depacement).removeAttr('readonly');
    $(champsReglement.depacement).val('0');
  // TP ALD
  } else if (cas == 'TP ALD') {
    $(champsReglement.depacement).attr('readonly', 'readonly');
    $(champsReglement.depacement).val('0');
  }
}

function calcResteDu() {
  cas = $(champsReglement.situation +" option:selected").val() || 'G';
  tarif = parseFloat($(champsReglement.tarif).val());
  depassement = parseFloat($(champsReglement.depacement).val());

  //tout venant
  if (cas == 'G') {
    total = parseFloat(tarif) + parseFloat(depassement);
    $(champsReglement.facture).val(total).change();
    $(champsReglement.tiers).val('');
    //CMU
  } else if (cas == 'CMU') {
    total = parseFloat(tarif);
    $(champsReglement.tiers).val(total);
    $(champsReglement.facture).val(total).change();
  } else if (cas == 'TP') {
    total = parseFloat(tarif) + parseFloat(depassement);
    tiers = Math.round((tarif * 70 / 100)*100) /100;
    reste = Math.round((total-tiers)*100)/100;
    $(champsReglement.tiers).val(tiers);
    $(champsReglement.facture).val(total).change();
    $(champsReglement.id_tiers).html('Tiers (reste à payer : '+ reste +'€)');
  } else if (cas == 'TP ALD') {
    total = parseFloat(tarif);
    $(champsReglement.tiers).val(total);
    $(champsReglement.facture).val(total).change();
  }

}

function resetModesReglement() {

  $("input[name='regleCheque']").val('');
  $("input[name='regleCB']").val('');
  $("input[name='regleEspeces']").val('');
  $(champsReglement.facture).val('').change();
  $(champsReglement.id_tiers).html('Tiers');
}
