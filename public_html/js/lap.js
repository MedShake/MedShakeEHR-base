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

var srcTab;
var srcIdx;

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

  // Enter envoie la recherche de medic
  $("#txtRechercheMedic").keypress(function(event) {
    var keycode = event.keyCode || event.which;
    if (keycode == '13') {
      sendMedicRecherche($('#txtRechercheMedic').val());
    }
  });

  // Relancer la recherche médic quand on change le groupe de recherche (généréique, spé ...)
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
  $('#modalRecherche').on("aftertablesort", "#tabMedicaments", function(event, data) {
    var th = $(this).find("th");
    th.find(".arrow").remove();
    var dir = $.fn.stupidtable.dir;
    var arrow = data.direction === dir.ASC ? "glyphicon-chevron-up" : "glyphicon-chevron-down";
    th.eq(data.column).append(' <span class="arrow glyphicon ' + arrow + '"></span>');
    console.log("The sorting direction: " + data.direction);
    console.log("The column index: " + data.column);
  });

  // Ordonner par drag & drop l'ordonnance
  $("#conteneurPrescriptionsALD, #conteneurPrescriptionsG").sortable({
    connectWith: ".connectedOrdoZones"
  });
  $("#conteneurPrescriptionsALD, #conteneurPrescriptionsG").disableSelection();

  $(".connectedOrdoZones").on("sortstart", function(event, ui) {
    srcTab = ui.item.hasClass('ald') ? ordoMedicsALD : ordoMedicsG;
    srcIdx = ui.item.index();
  });
  $(".connectedOrdoZones").on("sortupdate", function(event, ui) {
    if (this === ui.item.parent()[0]) {
      ordoLiveSave();
      console.log('indexArrivee : ' + ui.item.index());
      console.log('indexDepart : ' + srcIdx);

      moveLignePrescription(srcTab,
        ui.item.parent('div.connectedOrdoZones').hasClass('ald') ? ordoMedicsALD : ordoMedicsG,
        srcIdx,
        ui.item.index());

      if (ui.item.parent('div.connectedOrdoZones').hasClass('ald')) {
        ui.item.addClass('ald');
      } else {
        ui.item.removeClass('ald');
      }

      console.log(ordoMedicsALD);
      console.log(ordoMedicsG);
    }
  });

  // Détruire une ligne d'ordonnance
  $("#conteneurOrdonnance").on("click", 'button.removeLignePrescription', function(e) {
    ordoLiveSave();
    index = $(this).parents('div.lignePrescription').index();
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      ordoMedicsALD.splice(index, 1);
    } else {
      ordoMedicsG.splice(index, 1);
    }
    $(this).parents('div.lignePrescription').remove();
    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);

  });

  // Détruire tout le contenu de l'ordonnance
  $('a.removeAllLignesPrescription').on("click", function(e) {
    if (confirm("Confirmez-vous la suppression de toutes les lignes de prescription ?")) {
      e.preventDefault();
      ordoLiveSave();
      cleanOrdonnance();
    }
  });

  // Ordo live : restaurer la version sauvegardée (undo)
  $("a.ordoLiveRestore").on("click", function(e) {
    e.preventDefault();
    ordoLiveRestore();
  });

});

/**
 * Ordonnance vierge
 * @return {void}
 */
function cleanOrdonnance() {
  ordoMedicsALD = [];
  ordoMedicsG = [];
  $('#conteneurOrdonnance div.lignePrescription').remove();
}

/**
 * Déplacer une ligne de prescription dans les tableaux de medics
 * @param  {string} tabDepart    tableau de départ
 * @param  {string} tabArrivee   nom du tableau d'arrivée
 * @param  {int} indexDepart  n° de l'index de départ
 * @param  {int} indexArrivee n° de l'index d'arrivée
 * @return {void}
 */
function moveLignePrescription(tabDepart, tabArrivee, indexDepart, indexArrivee) {
  tabArrivee.splice(indexArrivee, 0, tabDepart.splice(indexDepart, 1)[0])
}

/**
 * Sauvegarder l'ordonnance en version JSON dans l'état précédant l'action
 * @return {void}
 */
function ordoLiveSave() {
  var ordoLive = {
    ordoMedicsALD: ordoMedicsALD,
    ordoMedicsG: ordoMedicsG
  };
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoLiveSave/',
    type: 'post',
    data: {
      ordoLive: ordoLive,
    },
    dataType: "json",
    success: function() {
      console.log("OK : ordonnance live save");
    },
    error: function() {
      console.log("PROBLEME : ordonnance live save");
    }
  });
}

/**
 * Restaurer la version précédente de l'ordonnance
 * @return {void}
 */
function ordoLiveRestore() {
  cleanOrdonnance();
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoLiveRestore/',
    type: 'post',
    data: {
      ordoLive: $('#conteneurOrdonnance').html(),
    },
    dataType: "json",
    success: function(data) {
      if (data['statut'] == 'ok') {
        console.log(data['ordoLive']);
        if (data['ordoLive']) {
          if (data['ordoLive']['ordoMedicsG']) {
            ordoMedicsG = data['ordoLive']['ordoMedicsG'];
            $.each(data['ordoLive']['ordoMedicsG'], function(ind, val) {
              $.each(val['medics'], function(indMed, med) {
                construireHtmlLigneOrdonnance(false, med);
                console.log('ajout ligne G');
              });
            });
          }
          if (data['ordoLive']['ordoMedicsALD']) {
            ordoMedicsALD = data['ordoLive']['ordoMedicsALD'];
            $.each(data['ordoLive']['ordoMedicsALD'], function(ind, val) {
              $.each(val['medics'], function(indMed, med) {
                construireHtmlLigneOrdonnance(true, med);
                console.log('ajout ligne ALD');
              });
            });
          }
        }

        console.log("OK : ordonnance live restore");
      } else if (data['statut'] == 'nofile') {
        alert("Aucune version antérieure trouvée");
        console.log("OK : ordonnance live restore (nofile)");
      }
    },
    error: function() {
      console.log("PROBLEME : ordonnance live restore");
    }
  });
}

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
