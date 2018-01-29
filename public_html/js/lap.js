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
 * Fonctions JS pour le dossier patient
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


  $("#txtRechercheMedic").typeWatch({
    wait: 1500,
    highlight: false,
    allowSubmit: true,
    captureLength: 3,
    callback: function(term) {
      sendMedicRecherche(term);
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

  $('#frappe').on("keyup", function(e) {
    lignes = $('#frappe').val().split("\n");
    var res = [];
    $.each(lignes, function(index, value) {
      res.push(checkLigne(index, value, 'cp'));
    });

    $("#resultat").html(res.join('<br>'));
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
      $('#txtRechercheMedicHB').html("Taper le texte de votre nouvelle recherche ici");
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

function checkLigne(index, ligne, forme) {
  human = '';
  regExp = [];
  // 1-1-1 6j|s|m jp|ji texte de traine
  regExp[0] = /^(?:ou |puis )?([0-9\/,\.]+)(?:-| )([0-9\/,\.]+)(?:-| )([0-9\/,\.]+)\s?([lmMjvsd]*)\s? ([0-9]+)(j|s|m){1}\s?(jp|ji)?(.*)/i;
  // 1 mms 6j|s|m jp|ji texte de traine
  regExp[1] = /^(?:ou |puis )?([0-9\/,\.]+) ([a-z]{1})([a-z]{1})([a-z]{1}) ([0-9]+)(j|s|m){1}\s?(jp|ji)?(.*)/i;
  // 1 6xh|j|s|m 6h|j|s|m jp|ji
  regExp[2] = /^(?:ou |puis )?([0-9\/,\.]+) ([0-9]+)x(h|j|s|m){1} ([0-9]+)(h|j|s|m)\s?(jp|ji)?(.*)/i;

  if (m = regExp[0].exec(ligne)) {
    console.log(m);
    if (m[5] > 1 && m[6] != 'm') {
      dureeHuman = duree[m[6]] + 's';
    } else {
      dureeHuman = duree[m[6]];
    }
    if (m[1] == m[2] && m[2] == m[3]) {
      human = m[1] + forme + ' matin midi et soir, ';
      if (m[4]) human += days(m[4]);
      if (m[7] == 'jp') human += 'les jours pairs, ';
      if (m[7] == 'ji') human += 'les jours impairs, ';
      human += 'pendant ' + m[5] + ' ' + dureeHuman
      if (m[8]) human += '. ' + m[8];
    } else {
      if (m[1] > 0 || m[1] in fractions) human += m[1] + forme + ' le matin, ';
      if (m[2] > 0 || m[2] in fractions) human += m[2] + forme + ' le midi, ';
      if (m[3] > 0 || m[3] in fractions) human += m[3] + forme + ' le soir, ';
      if (m[4]) human += days(m[4]);
      if (m[7] == 'jp') human += 'les jours pairs, ';
      if (m[7] == 'ji') human += 'les jours impairs, ';
      human += 'pendant ' + m[5] + ' ' + dureeHuman;
      if (m[8]) human += '. ' + m[8];
    }
    return human;
  } else if (m = regExp[1].exec(ligne)) {
    //console.log(m);
    if (m[5] > 1 && m[6] != 'm') {
      dureeHuman = duree[m[6]] + 's';
    } else {
      dureeHuman = duree[m[6]];
    }
    human = m[1] + forme + ' matin, midi et soir, ';
    if (m[7] == 'jp') human += 'les jours pairs, ';
    if (m[7] == 'ji') human += 'les jours impairs, ';
    human += 'pendant ' + m[5] + ' ' + dureeHuman
    if (m[8]) human += '. ' + m[8];
    return human;

  } else if (m = regExp[2].exec(ligne)) {
    console.log(m);
    if (m[4] > 1 && m[5] != 'm') {
      dureeHuman = duree[m[5]] + 's';
    } else {
      dureeHuman = duree[m[5]];
    }
    human = m[1] + forme + ' ' + m[2] + ' fois par ' + duree[m[3]] + ' ';
    if (m[6] == 'jp') human += 'les jours pairs, ';
    if (m[6] == 'ji') human += 'les jours impairs, ';
    human += 'pendant ' + m[4] + ' ' + dureeHuman
    if (m[7]) human += '. ' + m[7];
    return human;
  }
}

function days(liste) {
  human = [];
  if (liste.indexOf('l') > -1) human.push('lundis');
  if (liste.indexOf('m') > -1) human.push('mardis');
  if (liste.indexOf('M') > -1) human.push('mercredis');
  if (liste.indexOf('j') > -1) human.push('jeudis');
  if (liste.indexOf('v') > -1) human.push('vendredis');
  if (liste.indexOf('s') > -1) human.push('samedis');
  if (liste.indexOf('d') > -1) human.push('dimanches');
  humanString = human.join(' ');
  if (human.length > 0) {
    humanString = 'les ' + humanString + ' ';
    return humanString;
  } else {
    return '';
  }
}
