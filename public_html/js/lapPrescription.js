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
 * Fonctions pour la presciption médic dans le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

var medicData;
var ligneData = [];
var medicsData = {};
var ordoMedics = [];
var ordoMedicsG = [];
var ordoMedicsALD = [];

$(document).ready(function() {

  // envoyer médicament à la zone de prescription
  $('#modalRecherche').on("click", "button.sendToPrescription", function(e) {
    lapInstallPrescription($(this).attr('data-speThe'), $(this).attr('data-presThe'), $('#lapFrappePrescription').val());
  });

  // focus sur le champ de recherche
  $('#modalRecherche').on('show.bs.modal', function(event) {
    $('#txtRechercheMedic').focus();
  });

  // frappe de prescription et détection de prescription correcte
  $('#lapFrappePrescription').typeWatch({
    wait: 500,
    highlight: false,
    allowSubmit: true,
    captureLength: 3,
    callback: function() {
      matchAndGo();
    }
  });

  // changement sur le menu unité
  $('#uniteUtilisee').on("change", function(e) {
    matchAndGo();
  });

  // changement sur le menu voie administration
  $('#voieUtilisee').on("change", function(e) {
    matchAndGo();
  });

  // bouton voir indications et posologies
  $('#modalRecherche').on("click", "button.voirPosologies", function(e) {
    e.preventDefault();
    getPosologies(medicData['speThe']);
    $('#posologiesmedicTab').parent('li').show();
    $('#posologiesmedicTab').tab('show');
  });
  $('#modalRecherche').on('show.bs.collapse', 'div.fichearecevoir .collapse', function () {
     fichesPosos = $(this).attr('data-fiches');
     destination = $(this).children('div.panel-body');
     if(fichesPosos && destination.html() == '') getFichesPosos(fichesPosos, destination);
  })

  // envoyer prescription à l'ordonnance
  $('#modalRecherche').on("click", "button.sendToOrdonnance", function(e) {
    e.preventDefault();

    medicData['prescriptionHumanRecap'] = $('#prescriptionHumanRecap').html();
    medicData['prescriptionHumanPoso'] = $('#prescriptionHumanPoso').html();
    medicData['prescriptionMachinePoso'] = $('#lapFrappePrescription').val();

    var ligne = {
      medics: [medicData],
      html: ''
    };

    if ($('#prescriptionAldCheckbox').is(':checked')) {
      ordoMedicsALD.push(ligne);
    } else {
      ordoMedicsG.push(ligne);
    }
    ordoLiveSave();
    isALD = $('#prescriptionAldCheckbox').is(':checked');

    construireHtmlLigneOrdonnance(isALD, medicData);

    cleanModalRecherche();
    $('#recherchermedicTab').tab('show');
    $('#txtRechercheMedic').focus();
    console.log(ordoMedicsG);
    console.log(ordoMedicsALD);


  });
});

function getPosologies(codeSpe) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapGetPosologies/',
    type: 'post',
    data: {
      codeSpe: codeSpe,
    },
    dataType: "html",
    success: function(posologies) {
      $('#posologiesmedic').html(posologies);
      console.log('OK : obtenir posologies');
    },
    error: function() {
      console.log('PROBLEM : obtenir posologies');
    }
  });
}

function getFichesPosos(codesFiches, destination) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapGetFichesPosos/',
    type: 'post',
    data: {
      codesFiches: codesFiches,
    },
    dataType: "html",
    success: function(posologies) {
      destination.html(posologies);
      console.log('OK : obtenir posologies');
    },
    error: function() {
      console.log('PROBLEM : obtenir posologies');
    }
  });
}

function construireHtmlLigneOrdonnance(isALD, medicData) {
  var zoneDestination;
  if (isALD == true) {
    zoneDestination = $('#conteneurPrescriptionsALD');
  } else {
    zoneDestination = $('#conteneurPrescriptionsG');
  }
  $.ajax({
    url: urlBase + '/lap/ajax/lapMakeLigneOrdonnance/',
    type: 'post',
    data: {
      isALD: isALD,
      medicData: medicData,
    },
    dataType: "html",
    success: function(nouvelleLigneOrdo) {
      zoneDestination.append(nouvelleLigneOrdo);
      console.log('OK : ajout ligne à ordonnance ');
    },
    error: function() {
      console.log('PROBLEM : ajout ligne à ordonnance');
    }
  });
}

// nettoyage complet du modal de prescription
function cleanModalRecherche() {
  $('#txtRechercheMedic').val('');
  $("#rechercheResultats").html('');
  $("#lapFrappePrescription").val('');
  $("#prescriptionHumanMedicName").html('');
  $("#prescriptionHumanRecap").html('');
  $("#prescriptionHumanPoso").html('');
  $("#uniteUtilisee").html('');
  $("#voieUtilisee").html('');
}

//installation d'une nouvelle prescription d'un médic dans la modale
function lapInstallPrescription(speThe, presThe, txtPrescription) {
  $('#lapFrappePrescription').attr('data-speThe', speThe);
  $('#lapFrappePrescription').attr('data-presThe', presThe);

  $.ajax({
    url: urlBase + '/lap/ajax/lapInstallPrescription/',
    type: 'post',
    data: {
      txtPrescription: txtPrescription,
      speThe: speThe,
      presThe: presThe
    },
    dataType: "json",
    success: function(data) {

      // garder le retour sur ce medic
      medicData = data;
      medicsData[speThe] = {};
      medicsData[speThe][presThe] = data;
      console.log(medicsData);

      // supprimer la posologie qui aurait pu rester
      $('#lapFrappePrescription').val('');
      $('#prescriptionHumanPoso').html('');

      // placer le nom de la spé
      $('#prescriptionHumanMedicName').html(medicData['nomUtileFinal']);

      // voies d'administration
      $('#prescriptionHumanRecap').html('');
      $('#voieUtilisee').html('');
      $.each(data['voiesPossibles'], function(index, value) {
        $('#voieUtilisee').append('<option name="' + value['codevoie'] + '">voie ' + value['txtvoie'].toLowerCase() + '</option>');
      });

      // unités possibles
      $('#uniteUtilisee').html('');
      $.each(data['unitesPossibles'], function(index, value) {
        $('#uniteUtilisee').append('<option name="' + index + '">' + value + '</option>');
      });
      $('#prescriremedicTab').tab('show');
      $('#lapFrappePrescription').focus();
      console.log('OK : installation prescription');
    },
    error: function() {
      console.log('PROBLEM : installation prescription');
    }
  });
}

//mise à jour de la prescription à la frappe et au choix menus
function matchAndGo() {
  var lignesOK = [];
  var lignes = $.trim($('#lapFrappePrescription').val()) + ' ';
  lignes = lignes.split("\n");
  $.each(lignes, function(index, value) {
    if (matchLigne(index, value)) {
      lignesOK.push('1');
    }
  });

  if (lignes.length == lignesOK.length && lignesOK.length > 0) {
    $.ajax({
      url: urlBase + '/lap/ajax/lapAnalyseFrappePrescription/',
      type: 'post',
      data: {
        medicData: medicData,
        txtPrescription: $('#lapFrappePrescription').val(),
        uniteUtilisee: $('#uniteUtilisee option:selected').text(),
        uniteUtiliseeOrigine: $('#uniteUtilisee option:selected').attr('name'),
        voieUtilisee: $('#voieUtilisee option:selected').text(),
      },
      dataType: "json",
      success: function(data) {
        console.log('OK: analyse prescription');
        $('#prescriptionHumanPoso').html(data['human']);
        $('#prescriptionHumanRecap').html(data['voieUtilisee']);
        if (data['nbLignes'] > 1) $('#prescriptionHumanRecap').append(' - Durée totale : ' + data['dureeTotaleHuman']);
        if (data['nbLignes'] > 0) $("button.sendToOrdonnance").removeAttr('disabled');
        if (data['alerteSecabilite'] == true) {
          $("#prescriptionAlertSecabilite").show();
        } else {
          $("#prescriptionAlertSecabilite").hide();
        }
      },
      error: function() {
        console.log('PROBLEM: analyse prescription');
      }
    });
  } else {
    $('#prescriptionHumanPoso').html('');
  }
}

// les ereg qui matchent et qui envoient en ajax un traitement des lignes de prescription
function matchLigne(index, ligne) {
  var regExp = [];
  ligne = ligne + ' ';
  // 1-1-1 6j|s|m jp|ji texte de traine
  regExp[0] = /^(et|puis)?\s*([0-9\/,\.+]+) ([0-9\/,\.+]+) ([0-9\/,\.+]+)(?: ([0-9\/,\.+]+))?(?: ([lmMjvsdip]*))? (?:([0-9]+)(j|s|m))?(.*)/i;
  // 1 6xh|j|s|m 6h|j|s|m jp|ji
  regExp[1] = /^(et|puis)?\s*([0-9\/,\.]+) ([0-9]+)x(h|j|s|m){1}(?: ([lmMjvsdip]*))? (?:([0-9]+)(h|j|s|m))?(.*)/i;
  // 1 mms 6j|s|m jp|ji texte de traine
  regExp[2] = /^(?:et |puis )?([0-9\/,\.]+) ([a-z]{1})([a-z]{1})([a-z]{1}) ([0-9]+)(j|s|m){1}\s?(jp|ji)?(.*)/i;


  if (m = regExp[0].exec(ligne)) {
    return true;
  } else if (m = regExp[1].exec(ligne)) {
    return true;
  }
  // else if (m = regExp[2].exec(ligne)) {
  //   return true;
  // }
  return false;
}
