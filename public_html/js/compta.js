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
 * Fonctions JS pour la partie compta
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

var inhibdates = false;
$(document).ready(function() {

  //bouton de nouveau reglement
  $("body").on("click", ".editReglement", function(e) {
    $("#nomPatient").html($(this).attr('data-patientname'));
    $("#montant").html('Reste à payer: ' + $(this).attr('data-aregler') + '€');
    $("input[name=patientID]").val($(this).attr('data-patientID'));
    $("input[name=objetID]").val($(this).attr('data-objetID'));
    $("input[name=apayer]").val($(this).attr('data-aregler'));
    $("input[name=porteur]").val($(this).attr('data-porteur'));
    $("input[name=dejapaye]").val($(this).attr('data-dejapaye'));
    $("input[name=dejaCheque]").val($(this).attr('data-dejaCheque'));
    $("input[name=dejaCB]").val($(this).attr('data-dejaCB'));
    $("input[name=dejaEspeces]").val($(this).attr('data-dejaEspeces'));
  });

  $(".checkAmount").on("keyup", function() {
    var total = 0;
    var filled = [0,0,0];
    $(".checkAmount").each(function(idx,el){
      total += parseFloat($(el).val()) || parseInt($(el).val()) || 0;
      filled[idx] = $(el).val()!="";
    });
    $("input[type=submit]").removeClass("disabled");
    $(".checkAmount").each(function(idx,el){
      if (total > $("input[name=apayer]").val() && filled[idx]) {
        glow('danger', $(el));
        $("input[type=submit]").addClass("disabled");
      }
      else if (total==$("input[name=apayer]").val() && filled[idx])
        glow('success', $(el));

    });
  });

  //close button zone newReglement
  $('body').on("click", "#cleanNewReglement", function(e) {
    $('#newReglement').html('');
  });

  //mettre en route les tooltip bootstrap
  $(function() {
    $('[data-toggle="tooltip"]').tooltip()
  })

  $("#periodeQuickSelectID").on("change", function(e) {
    e.preventDefault();
    inhibdates=true;
    choix = $('#periodeQuickSelectID option:selected').val();
    if (choix == 'today') {
      $('#beginPeriodeID').val(moment().format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().format('DD/MM/gggg'));
    } else if (choix == 'yesterday') {
      $('#beginPeriodeID').val(moment().add(-1, 'days').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().add(-1, 'days').format('DD/MM/gggg'));
    } else if (choix == 'thisweek') {
      $('#beginPeriodeID').val(moment().startOf('week').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().endOf('week').format('DD/MM/gggg'));
    } else if (choix in {'thismonth':0, 'impayesmois':0} ) {
      $('#beginPeriodeID').val(moment().startOf('month').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().format('DD/MM/gggg'));
    } else if (choix in {'lastmonth':0, 'bilanmois':0}) {
      $('#beginPeriodeID').val(moment().subtract(1, 'months').startOf('month').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().subtract(1, 'months').endOf('month').format('DD/MM/gggg'));
    } else if (choix == 'lastweek') {
      $('#beginPeriodeID').val(moment().subtract(1, 'weeks').startOf('week').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().subtract(1, 'weeks').endOf('week').format('DD/MM/gggg'));
    } else if (choix == 'impayesannee') {
      $('#beginPeriodeID').val(moment().startOf('year').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().format('DD/MM/gggg'));
    } else if (choix == 'bilanannee') {
      $('#beginPeriodeID').val(moment().subtract(1, 'years').format('01/01/gggg'));
      $('#endPeriodeID').val(moment().subtract(1, 'years').format('31/12/gggg'));
    }
    if (choix in {impayesmois:0, impayesannee:0})
      $('input[name=impayes]').val("true")[0].checked=true;
    else
      $('input[name=impayes]').val('')[0].checked=false;

    if (choix in {bilanmois:0, bilanannee:0})
      $('input[name=bilan]').val("true")[0].checked=true;
    else
      $('input[name=bilan]').val('')[0].checked=false;

    if (this.selectedIndex)
      getTable();
//    $('form#periodeForm').submit();
  });

  $('#beginPeriodeIDB').on('dp.change', function(){
    if (inhibdates)
      return;
    $("#periodeQuickSelectID")[0].selectedIndex = 0;
    $('input[name=impayes]').val('');
    getTable();
  });


  $('#endPeriodeIDB').on('dp.change', function(){
    if (inhibdates)
      return;
    $("#periodeQuickSelectID")[0].selectedIndex = 0;
    $('input[name=impayes]').val('');
    getTable();
  });

  $('select[name="prat"]').on('change', function(){
    getTable();
  });

  $('input[name=impayes]').on('click', function(){
    $(this).val($(this).val() == 'true' ? '' : 'true');
    getTable();
  });

  $('input[name=bilan]').on('click', function(){
    $(this).val($(this).val() == 'true' ? '' : 'true');
    getTable();
  });

  //copier le bon montant d'un clic
  $('body').on("dblclick", "#modalReglement input", function(e) {
    montant = $("input[name=apayer]").val();
    $(this).val(montant);
    $(this).trigger("keyup");
  });

  $('.refresh').on('click', function(){
    getTable();
  });

});

function getTable() {
  if ($('#beginPeriodeID').val() == '' || $('#endPeriodeID').val() == '') {
    if ($('#beginPeriodeID').val() == '')
      glow('danger', $('#beginPeriodeID'));
    if ($('#endPeriodeID').val() == '')
      glow('danger', $('#beginPeriodeID'));
    return;
  }

  var prat = {};
  if ($('select[name="prat"]').length) {
    prat.id = $('select[name="prat"]').val();
    prat.name = 'Recettes de ' + $('select[name="prat"] option:selected').html();
  }
  else {
    prat.id = $('input[name="prat"]').val();
    prat.name = $('#titre').html();
  }

  $.ajax({
    url: urlBase + '/compta/ajax/getTableData/',
    type: 'post',
    data: {
      prat: prat.id,
      beginPeriode : $('#beginPeriodeID').val(),
      endPeriode : $('#endPeriodeID').val(),
      impayes: $('input[name=impayes]').val(),
      bilan: $('input[name=bilan]').val()
    },
    dataType: "html",
    success: function(data) {
    $('#titre').html(prat.name);
    $('.tableDiv').html(data);
    inhibdates = false;
    },
    error: function() {
      alert_popup('danger', 'Une erreur est survenue lors de la récupération des données');
    }
  });
}
