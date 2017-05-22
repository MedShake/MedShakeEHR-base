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
 */

$(document).ready(function() {

  //bouton de nouveau reglement
  $(".editReglement").on("click", function(e) {
    e.preventDefault();
    if ($('#newReglement').html() != '') {
      if (confirm('Voulez-vous remplacer le contenu de la zone de règlement en cours ?')) {
        sendFormToReglementDiv($(this));
      }
    } else {
      sendFormToReglementDiv($(this));
    }
  });

  //close button zone newReglement
  $('body').on("click", "#cleanNewReglement", function(e) {
    $('#newReglement').html('');
  });

  //mettre en route les tooltip bootsrap
  $(function() {
    $('[data-toggle="tooltip"]').tooltip()
  })

  $("#periodeQuickSelectID").on("change", function(e) {
    e.preventDefault();
    choix = $('#periodeQuickSelectID option:selected').val();
    if (choix == 'today') {
      $('#beginPeriodeID').val(moment().format('DD/MM/gggg'));
      $('#endPeriodeID').val(moment().format('DD/MM/gggg'));
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




//envoyer le form new Reglement dans le div Ordo
function sendFormToReglementDiv(el) {

  $.ajax({
    url: '/compta/ajax/extractReglementForm/',
    type: 'post',
    data: {
      objetID: el.attr('data-objetID'),
      patientID: el.attr('data-patientID'),
      montant: el.attr('data-montant')
    },
    dataType: "html",
    success: function(data) {
      $('#newReglement').html(data);
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}
