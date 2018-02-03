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
 * Fonctions générales JS pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  // Refresh des data patient au retour sur la page LAP.
  document.addEventListener("visibilitychange", function() {
    if (document.visibilityState == 'visible') {
      $.ajax({
        url: urlBase + '/lap/ajax/lapPatientLateralDataRefresh/',
        type: 'post',
        data: {
          patientID: $('#identitePatient').attr("data-patientID")
        },
        dataType: "html",
        success: function(data) {
          $('#patientLateralData').html(data);
        },
        error: function() {
          alert('Problème, rechargez la page !');
        }
      });
    }
  });

  $("#txtRechercheMedic").keypress(function(event) {
    var keycode = event.keyCode || event.which;
    if(keycode == '13') {
      sendMedicRecherche($('#txtRechercheMedic').val());
    }
  });


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

  $('#rechercher').on("aftertablesort", "#tabMedicaments", function(event, data) {
    var th = $(this).find("th");
    th.find(".arrow").remove();
    var dir = $.fn.stupidtable.dir;
    var arrow = data.direction === dir.ASC ? "glyphicon-chevron-up" : "glyphicon-chevron-down";
    th.eq(data.column).append(' <span class="arrow glyphicon ' + arrow + '"></span>');
  });


});

function sendMedicRecherche(term) {
  $.ajax({
    url: urlBase + '/lap/ajax/searchNewMedic/',
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
      var tableMedics = $("#tabMedicaments").stupidtable();
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

var duree = {
  'i': 'minute',
  'h': 'heure',
  'j': 'jour',
  's': 'semaine',
  'm': 'mois'
};

var fractions = {
  '1/4': 0.25,
  '1/3': 0.33,
  '1/2': 0.5,
  '3/4': 0.75
}
