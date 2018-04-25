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


$(document).ready(function() {


  // Lancer la recherche de médicament par validation avec enter
  $("#txtRechercheMedic").keypress(function(event) {
    keycode = event.keyCode || event.which;
    if (keycode == '13') {
      sendMedicRecherche($('#txtRechercheMedic').val());
    }
  });

  // Relancer la recherche médic quand on change le groupe de recherche (générique, spé ...)
  $('#typeRechercheMedic, #retourRechercheMedic').on("change", function(e) {
    term = $('#txtRechercheMedic').val();
    elSel = $('#typeRechercheMedic').val();
    if (elSel == 'dci') {
      $('#retourRechercheMedic').val('1');
    } else if (elSel == 'dcispe') {
      $('#retourRechercheMedic').val('3');
    } else if (elSel == 'spe') {
      $('#retourRechercheMedic').val('0');
    }

    if (elSel != 'dci' && elSel != 'dcispe' && elSel != 'spe') {
      if ($('#retourRechercheMedic').is(":hidden")) $('#retourRechercheMedic').val('1');
      $('#retourRechercheMedicBloc').show();
    } else $('#retourRechercheMedicBloc').hide();

    if (term.length > 1) sendMedicRecherche(term);
  });

  // Trier le tableau des médics en cliquant sur les headers de colonne
  $('#rechercheResultats').on("aftertablesort", "#tabMedicaments", function(event, data) {

    th = $(this).find("th");
    console.log(th);
    th.find(".arrow").remove();
    dir = $.fn.stupidtable.dir;
    arrow = data.direction === dir.ASC ? "fa-chevron-up" : "fa-chevron-down";

    th.eq(data.column).append('<span class="ml-1 arrow fa ' + arrow + '"></span>');

  });

});

/**
 * Faire une recherche sur un terme
 * @param  {string} term texte de recherche
 * @return {void}
 */
function sendMedicRecherche(term) {

  //vider la recherche détaillée précéente
  $('#resultsDetaTabL').parent('li').hide();
  $('#resultsDetaTab').html('');

  $.ajax({
    url: urlBase + '/lap/ajax/lapOutilsSearchNewMedic/',
    type: 'post',
    data: {
      term: term,
      typeRecherche: $('#typeRechercheMedic').val(),
      retourRecherche: $('#retourRechercheMedic').val()
    },
    dataType: "html",
    beforeSend: function() {
      $('#txtRechercheMedicHB').html("Recherche en cours ...");
    },
    success: function(data) {
      $('#rechercheResultats').html(data);
      $('#txtRechercheMedicHB').html("Taper le texte de votre recherche ici");
      var tableMedics = $("#tabMedicaments").stupidtable({
        "alphanum": function(a, b) {
          return a.localeCompare(b, undefined, {
            numeric: true,
            sensitivity: 'base'
          })
        }
      });
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}
