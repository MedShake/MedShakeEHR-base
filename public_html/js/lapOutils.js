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
    th.find(".arrow").remove();
    dir = $.fn.stupidtable.dir;
    arrow = data.direction === dir.ASC ? "fa-chevron-up" : "fa-chevron-down";

    th.eq(data.column).append('<span class="ml-1 arrow fa ' + arrow + '"></span>');

  });

  // afficher la liste des patients dont le SAM choisi concerne la dernière prescription
  $(".displayListSamPatients").on("click", function() {
    samID = $(this).attr('data-samID');
    if ($('#' + samID + 'List').length) {
      $('#' + samID + 'List').remove();
    } else {
      displayListSamPatients($(this));
    }
  });

  $('body').on('click', "#lapOutilsSearchPres", function(e) {
    e.preventDefault();
    lapOutilsSearchPres($(this));
  });


});


/**
 * Recherche de prescriptions sur critères
 * @param  {el} el object jquery source
 * @return {void}
 */
function lapOutilsSearchPres(el) {
  stop=false;
  el.closest("form").find('input[required],textarea[required]').each(function(idx, el) {
    if (el.value==''){
      glow('danger', $(el));
      stop=true;
    }
  });
  if (stop) {
    return;
  }
  $.ajax({
    url: urlBase + '/lap/ajax/lapOutilsSearchPres/',
    type: 'post',
    data: el.parents("form").serialize(),
    dataType: "json",
    success: function(data) {
      html = '<div class="row mb-2"><div class="col text-right"><button class="btn btn-secondary btn-sm" onclick="exportTableToCSV(\'#tableResultatsReMulti\',\'recherche.csv\')">Exporter en CSV</button></div></div>';
      html += '<div class="row"><div class="col">';
      html += '<table id="tableResultatsReMulti" class="table table-hover table-sm small">';
      html += '<thead class="thead-light"><tr>';
      html += '<th class="col-auto">ID patient</th>';
      html += '<th class="col-auto">Identité patient</th>';
      html += '<th class="col-auto">Ddn</th>';
      html += '<th class="col-auto">Médicament</th>';
      html += '<th class="col-auto">Date prescription</th>';
      html += '<th class="col-auto">Age à la prescription</th>';
      html += '<th class="col-auto">Allergies</th>';
      html += '<th class="col-auto">Atcd</th>';
      html += '</tr></thead><tbody></div></div>';

      $.each(data.patientsList, function(index, ligne) {
        html += '<tr>';
        html += '<td><a href="' + urlBase + '/patient/' + ligne.toID + '/" title="Ouvrir le dossier">' + ligne.toID + '</a></td>';
        html += '<td>' + ligne.identiteDossier + '</td>';
        html += '<td>' + ligne.birthdate + '</td>';
        html += '<td>' + ligne.specialite + ' (' + ligne.specialite + ')</td>';
        html += '<td>' + ligne.registerDate + '</td>';
        html += '<td>' + ligne.ageALaPresc + ' ' + ligne.ageALaPrescUnite + '</td>';
        html += '<td>' + ligne.allergies + '</td>';
        html += '<td>' + ligne.atcd + '</td>';
        html += '</tr>';
      });

      html += '</tbody></table>';
      $('#lapOutilsSearchPresResults').html(html);

    },
    error: function() {
      alert_popup("danger", "Une erreur s'est produite durant l'opération");
    }
  });
}


/**
 * Afficher la liste des patients dont la condition du SAM choisi est réalisée
 * lors de la dernière prescription
 * @param  {object} el object jquery source du click
 * @return {void}
 */
function displayListSamPatients(el) {
  samID = el.attr('data-samID');
  $.ajax({
    url: urlBase + '/lap/ajax/lapOutilDisplayListSamPatients/',
    type: 'post',
    data: {
      samID: samID,
    },
    dataType: "json",
    success: function(data) {
      if ($.isArray(data.patientsList)) {
        html = '<tr id="' + samID + 'List"><td colspan="4"><div class="card my-3"><div class="card-header">Liste des patients dont la condition du SAM est réalisée lors de la dernière prescription</div><div class="card-body"><table class="table table-hover table-sm">';
        html += '<thead><tr><th class="col-auto"></th><th class="col-auto">Identité</th><th class="col-auto">Date de la prescription</th></tr></thead><tbody>';
        $.each(data.patientsList, function(index, ligne) {
          html += '<tr>';
          html += '<td><a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/patient/' + ligne.toID + '/" title="Ouvrir le dossier"><span class="fas fa-folder-open" aria-hidden="true"></span></a></td>';
          html += '<td>' + ligne.identiteDossier + '</td>';
          html += '<td>' + ligne.registerDate + '</td>';
          html += '</tr>';
        });
        html += '</tbody></table></div></div></td></tr>';

        el.parents('tr').after(html);
      } else {
        alert_popup("info", 'Ce SAM n\'est bloqué pour aucun patient');
      }
    },
    error: function() {
      alert_popup("danger", 'Impossible de récupérer la liste demandée');

    }
  });
}
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
      $('#rechercheResultats').html('<div class="text-center p-4"><i class="fas fa-spinner fa-4x fa-spin text-warning"></i></div>');
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
