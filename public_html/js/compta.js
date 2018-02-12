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

$(document).ready(function() {

  //bouton de nouveau reglement
  $(".editReglement").on("click", function(e) {
    $("#nomPatient").html($(this).attr('data-patientname'));
    $("#montant").html('Reste à payer: ' + $(this).attr('data-aregler') + '€');
    $("input[name=patientID]").val($(this).attr('data-patientID'));
    $("input[name=objetID]").val($(this).attr('data-objetID'));
    $("input[name=apayer]").val($(this).attr('data-aregler'));
    $("input[name=module]").val($(this).attr('data-module'));
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
    $(".checkAmount").parent().removeClass('has-error');
    $(".checkAmount").parent().removeClass('has-success');
    $(".checkAmount").each(function(idx,el){
      if (total > $("input[name=apayer]").val() && filled[idx]) {
        $(el).parent().addClass('has-error');
        $("input[type=submit]").addClass("disabled");
      }
      else if (total==$("input[name=apayer]").val() && filled[idx])
        $(el).parent().addClass('has-success');
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
    } else if (choix == 'thismonth') {
      $('#beginPeriodeID').val(moment().startOf('month').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().format('DD/MM/gggg'));
    } else if (choix == 'lastmonth') {
      $('#beginPeriodeID').val(moment().subtract(1, 'months').startOf('month').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().subtract(1, 'months').endOf('month').format('DD/MM/gggg'));
    } else if (choix == 'lastweek') {
      $('#beginPeriodeID').val(moment().subtract(1, 'weeks').startOf('week').format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().subtract(1, 'weeks').endOf('week').format('DD/MM/gggg'));
    }
    $('form#periodeForm').submit();
  });

  //copier le bon montant d'un clic
  $('body').on("dblclick", "#newReglement input", function(e) {
    objetID = parseInt($("#newReglement input[name='objetID']").val());
    montant = $("#ligne" + objetID).attr("data-montant");
    $(this).val(montant);
  });

});

