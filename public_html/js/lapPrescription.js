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

/**
 * médicament en cours de manipulation
 * @type {object}
 */
var medicData;

/**
 * ligne en cours de manipulation
 * @type {object}
 */
var ligneData = {};

/**
 * index de ligne de prescription en cours de manipulation
 * @type {int}
 */
var ligneCouranteIndex;

/**
 * index de ligne du medicament dans la ligne de prescription en cours de manipulation
 * @type {int}
 */
var indexMedic;

/**
 * zone de l'ordonnance courante (ALD/G)
 * @type {int}
 */
var zoneOrdoAction;

/**
 * Mode de fonctionnement de la modal : new / edit / addToLigne
 * @type {string}
 */
var modeActionModal = 'new';

/**
 * Liste des codes indications
 * @type {String}
 */
listeCodesIndics = '';

/**
 * Index de la sélection dans le tableau de recherche de médicaments
 * @type {Number}
 */
var listingTabMedicRow = -1;

$(document).ready(function() {

  //Nouvelle prescription
  $('body').on("click", "button.nouvellePrescription", function(e) {
    modeActionModal = 'new';
    resetObjets();
    prepareModalPrescription();
    cleanModalRecherche();
    $('#modalRecherche').modal('toggle');
  });

  // Editer un médicament d'une ligne de prescription
  $("#conteneurOrdonnanceCourante").on("click", 'button.editLignePrescription', function(e) {
    console.log("Editer medic unique ligne prescription : START");
    modeActionModal = 'edit';
    ligneCouranteIndex = $(this).parents('div.lignePrescription').index();
    indexMedic = '0';
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      var zone = 'ordoMedicsALD';
    } else {
      var zone = 'ordoMedicsG';
    }
    editPrescription(zone, ligneCouranteIndex);
    console.log("Editer medic unique ligne prescription : STOP");
  });

  // Editer un médicament d'une ligne de prescription où ils sont multiples
  $("#conteneurOrdonnanceCourante").on("click", 'button.editMedicLignePrescription', function(e) {
    console.log("Editer medic multiple ligne prescription : START");
    modeActionModal = 'edit';
    ligneCouranteIndex = $(this).parents('div.lignePrescription').index();
    indexMedic = $(this).parents('table.tablePrescripMultiMedic tr').index();
    console.log("index ligne prescription : " + ligneCouranteIndex + " index medic : " + indexMedic);
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      var zone = 'ordoMedicsALD';
    } else {
      var zone = 'ordoMedicsG';
    }
    editPrescription(zone, ligneCouranteIndex);
    console.log("Editer medic multiple ligne prescription : STOP");
  });

  // ajouter un médicament à une ligne de prescription
  $("#conteneurOrdonnanceCourante").on("click", "a.addToLigne", function(e) {
    console.log('Installation ajout d\'un médic dans ligne de prescription : START');
    e.preventDefault();
    modeActionModal = 'addToLigne';
    cleanModalRecherche();
    prepareModalPrescription();
    ligneCouranteIndex = $(this).parents('div.lignePrescription').index();
    if ($(this).parents('div.connectedOrdoZones').hasClass('ald')) {
      zoneOrdoAction = 'ALD';
    } else {
      zoneOrdoAction = 'G';
    }

    $('#modalRecherche').modal('show');
    console.log('Installation ajout de medic sur la ligne ' + ligneCouranteIndex + ' en zone ' + zoneOrdoAction);
    console.log('Installation ajout d\'un médic dans ligne de prescription : STOP');
  });

  // Lancer la recherche de médicament par validation avec enter
  $("#txtRechercheMedic").keypress(function(event) {
    listeCodesIndics = '';
    keycode = event.keyCode || event.which;
    if (keycode == '13') {
      sendMedicRecherche($('#txtRechercheMedic').val());
    }
  });

  //navigation au clavier dans les résultats de recherche de médicaments
  $('#rechercheResultats, #txtRechercheMedic').on("keydown", function(e) {
    if ($('#recherchermedic').is(':visible') && $('#tabMedicaments tbody tr').length > 0) {
      if (e.keyCode == 40) { //down
        $('#rechercheResultats').focus();

        listingTabMedicRow++;
        if (listingTabMedicRow + 1 > $('#tabMedicaments tbody tr').length) {
          listingTabMedicRow = $('#tabMedicaments tbody tr').length - 1;
        }
        $('#tabMedicaments tbody tr').removeClass('table-active');
        if (listingTabMedicRow >= 0) {
          $('#tabMedicaments tbody tr').eq(listingTabMedicRow).addClass('table-active');
        }
      } else if (e.keyCode == 38) { //up
        listingTabMedicRow--;
        if (listingTabMedicRow < 0) {
          listingTabMedicRow = -1;
          $('#txtRechercheMedic').focus();
        }
        $('#tabMedicaments tbody tr').removeClass('table-active');
        if (listingTabMedicRow >= 0) {
          $('#tabMedicaments tbody tr').eq(listingTabMedicRow).addClass('table-active');
        }
      } else if (e.keyCode == 13 && listingTabMedicRow >= 0) { //enter
        $('#tabMedicaments tbody tr').eq(listingTabMedicRow).find("td.sendToPrescription:first").click();
      }
    }
  });

  // Focus sur le champ de recherche : on réinit la position
  $('#txtRechercheMedic').on('focus', function(e) {
    listingTabMedicRow = -1;
    $('#tabMedicaments tbody tr').removeClass('table-active');
  });

  // Activation onglet de recherche : on replace le focus sur le champ de recherche
  $('#recherchermedicTab').on('shown.bs.tab', function(event) {
    $('#txtRechercheMedic').focus();
  });

  // Quand on a trié les médicaments dans le résultat de recherche : on réinit la position
  $('body').on("aftertablesort", "#tabMedicaments", function(event, data) {
    if (listingTabMedicRow >= 0) {
      listingTabMedicRow = 0;
      $('#tabMedicaments tbody tr').removeClass('table-active');
      $('#tabMedicaments tbody tr').eq(listingTabMedicRow).addClass('table-active');
    }
  });

  // Lancer la recherche quand on clique sur une indication
  $('body').on('click', 'button.searchIndic', function(e) {
    $('div.modalListeIndications button').addClass('btn-secondary').removeClass('btn-primary');
    $(this).addClass('btn-primary').removeClass('btn-secondary');
    listeCodesIndics = $(this).attr('data-codes');
    sendMedicRecherche($('#txtRechercheMedic').val());
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
  $('#modalRecherche').on("aftertablesort", "#tabMedicaments", function(event, data) {
    th = $(this).find("th");
    th.find(".arrow").remove();
    dir = $.fn.stupidtable.dir;
    arrow = data.direction === dir.ASC ? "fa-chevron-up" : "fa-chevron-down";
    th.eq(data.column).append('<span class="ml-1 arrow fa ' + arrow + '"></span>');
    //console.log("The sorting direction: " + data.direction);
    //console.log("The column index: " + data.column);
  });

  // approfondir la recherche
  $('#modalRecherche').on("click", ".approRecherche", function(e) {
    $.ajax({
      url: urlBase + '/lap/ajax/searchNewMedicDetails/',
      type: 'post',
      data: {
        codesSpe: $(this).attr('data-codesSpe'),
      },
      dataType: "json",
      success: function(data) {
        $('#modalRecherche').modal('toggle');
        $('#resultsDetaTabL').parent('li').show();
        $('#resultsDetaTab').html(data['html']);
        $('#resultsDetaTabL').tab('show');
        var tableMedics = $("#tabDetMedicaments").stupidtable({
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
  });

  // Trier le tableau de recherche détaillée des médics en cliquant sur les headers de colonne
  $('body').on("aftertablesort", "#tabDetMedicaments", function(event, data) {
    th = $(this).find("th");
    th.find(".arrow").remove();
    dir = $.fn.stupidtable.dir;
    arrow = data.direction === dir.ASC ? "fa-chevron-up" : "fa-chevron-down";
    th.eq(data.column).append(' <span class="arrow fa ' + arrow + '"></span>');
  });

  // envoyer médicament à la zone de prescription
  $('#modalRecherche').on("click", ".sendToPrescription", function(e) {
    var tab = {
      speThe: $(this).parent('tr').attr('data-speThe'),
      presThe: $(this).parent('tr').attr('data-presThe'),
      tauxrbt: $(this).parent('tr').attr('data-tauxrbt'),
      prixucd: $(this).parent('tr').attr('data_prixucd'),
    };
    lapInstallPrescription(tab);
  });

  // focus sur le champ de recherche
  $('#modalRecherche').on('shown.bs.modal', function(event) {
    $('#txtRechercheMedic').focus();
  });

  //Reset quand la modal disparait pour ne pas trainer
  $('#modalRecherche').on('hide.bs.modal', function(event) {
    resetObjets();
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

  //changement date début prescription
  $("#beginPeriodeIDB").on("dp.change", function(e) {
    var debut = moment($('#beginPeriodeID').val(), "DD-MM-YYYY");
    if (ligneData['dureeTotaleMachineJoursAvecRenouv'] > 0) {
      $('#endPeriodeID').val(debut.add(ligneData['dureeTotaleMachineJoursAvecRenouv'] - 1, 'days').format('DD/MM/YYYY'));
    } else {
      $('#endPeriodeID').val(debut.format('DD/MM/YYYY'));
    }
  });
  // passer à demain au dblclick
  $("#beginPeriodeID").on("dblclick", function(e) {
    var debut = moment(new Date()).add(1, 'days');
    $('#beginPeriodeID').val(debut.format('DD/MM/YYYY'));
    if (ligneData['dureeTotaleMachineJoursAvecRenouv'] > 0) {
      $('#endPeriodeID').val(debut.add(ligneData['dureeTotaleMachineJoursAvecRenouv'] - 1, 'days').format('DD/MM/YYYY'));
    } else {
      $('#endPeriodeID').val(debut.format('DD/MM/YYYY'));
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

  // changement sur le menu nb renouvellements
  $('#nbRenouvellements').on("change", function(e) {
    matchAndGo();
  });

  // bouton voir indications et posologies
  $('#modalRecherche').on("click", "button.voirPosologies", function(e) {
    e.preventDefault();
    getPosologies(medicData['speThe']);
    $('#posologiesmedicTab').parent('li').show();
    $('#posologiesmedicTab').tab('show');
  });

  // checkbox Ne pas substituer
  $('#modalRecherche').on("click", "#prescriptionNpsCheckbox", function(e) {
    if ($('#prescriptionNpsCheckbox').is(':checked')) {
      $('#prescriptionNpsMotif').show();
    } else {
      $('#prescriptionNpsMotif').hide();
    }
    matchAndGo();
  });

  // motif de non susbtition
  $('#prescriptionNpsMotif').typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: true,
    captureLength: 3,
    callback: function() {
      matchAndGo();
    }
  });

  // consignes : mise à jour à la frappe
  $('#lapConsignesPrescription').typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: true,
    captureLength: 3,
    callback: function() {
      $("#prescriptionHumanConsignes").html(nl2br($('#lapConsignesPrescription').val()));
    }
  });

  // envoyer prescription à l'ordonnance
  $('#modalRecherche').on("click", "button.sendToOrdonnance", function(e) {
    console.log("Envoyer prescription à ordonnance : START");
    e.preventDefault();
    //envoyer
    sendToOrdonnance();
    // ménage
    cleanModalRecherche();
    $('#recherchermedicTab').tab('show');
    $('#txtRechercheMedic').focus();
    console.log(ordoMedicsG);
    console.log(ordoMedicsALD);
    console.log("Envoyer prescription à ordonnance : STOP");
  });

  // envoyer nouveau medic à la ligne de l'ordonnance existente
  $('#modalRecherche').on("click", "button.addToLigneOnOrdonnance", function(e) {
    console.log("Envoyer medic à ligne de prescription ordonnance : START");
    e.preventDefault();
    //envoyer
    sendToLigneOrdonnance();
    // ménage
    cleanModalRecherche();
    $('#recherchermedicTab').tab('show');
    $('#txtRechercheMedic').focus();
    console.log(ordoMedicsG);
    console.log(ordoMedicsALD);
    console.log("Envoyer medic à ligne de prescription ordonnance : STOP");
  });

  // envoyer le medic edité à la ligne de l'ordonnance
  $('#modalRecherche').on("click", "button.sendModifToOrdonnance", function(e) {
    console.log("Envoyer medic modifié à ligne de prescription ordonnance : START");
    e.preventDefault();
    //envoyer
    sendToLigneOrdonnance();
    // ménage et fermeture modal
    $('#modalRecherche').modal('toggle');
    cleanModalRecherche();
    console.log(ordoMedicsG);
    console.log(ordoMedicsALD);
    console.log("Envoyer medic modifié à ligne de prescription ordonnance : STOP");
  });

});

/**
 * Attraper les informations de prescription courante sur la fenetre actuelle
 * @return {objet} objet contentant info medic et ligne de prescription
 */
function catchCurrentPrescriptionData() {
  //ajouter les datas du form à l'ordo.
  medicData['prescriptionMachinePoso'] = $('#lapFrappePrescription').val();
  if ($('#prescriptionNpsCheckbox').is(':checked')) medicData['isNPS'] = "true";
  else medicData['isNPS'] = "false";
  medicData['motifNPS'] = $('#prescriptionNpsMotif').val();
  medicData['uniteUtilisee'] = $('#uniteUtilisee option:selected').text();
  medicData['uniteUtiliseeOrigine'] = $('#uniteUtilisee option:selected').val();
  medicData['prescriptionMotif'] = $('#prescriptionMotif').val();
  medicData['prescripteurInitialTT'] = $('#prescripteurInitialTT').val();

  // infos sur la ligne
  //ligneData = {};
  ligneData['consignesPrescription'] = $('#lapConsignesPrescription').val();
  if ($('#prescriptionAldCheckbox').is(':checked')) ligneData['isALD'] = "true";
  else ligneData['isALD'] = "false";
  if ($('#prescriptionChroCheckbox').is(':checked')) ligneData['isChronique'] = "true";
  else ligneData['isChronique'] = "false"
  ligneData['nbRenouvellements'] = parseInt($('#nbRenouvellements option:selected').val());
  ligneData['voieUtilisee'] = $('#voieUtilisee option:selected').text();
  ligneData['voieUtiliseeCode'] = $('#voieUtilisee option:selected').val();
  ligneData['dateDebutPrise'] = $('#beginPeriodeID').val();
  if (ligneData['dureeTotaleMachineJours'] > 0) {
    ligneData['dateFinPrise'] = moment($('#beginPeriodeID').val(), "DD-MM-YYYY").add(ligneData['dureeTotaleMachineJours'] - 1, 'days').format('DD/MM/YYYY');
  } else {
    ligneData['dateFinPrise'] = $('#beginPeriodeID').val();
  }
  if (ligneData['dureeTotaleMachineJoursAvecRenouv'] > 0) {
    ligneData['dateFinPriseAvecRenouv'] = moment($('#beginPeriodeID').val(), "DD-MM-YYYY").add(ligneData['dureeTotaleMachineJoursAvecRenouv'] - 1, 'days').format('DD/MM/YYYY');
  } else {
    ligneData['dateFinPrise'] = $('#beginPeriodeID').val();
  }

  ligne = {
    medics: [medicData],
    ligneData: ligneData
  };

  return ligne;
}

/**
 * Envoyer les informations de la fenêtre de prescription dans une nouvelle ligne ordo.
 * @return {[type]} [description]
 */
function sendToOrdonnance() {
  //attraper les infos sur la prescription courante
  ligne = catchCurrentPrescriptionData();

  // envoyer dans le bon tableau en fonction ALD/ NON ALD et construire
  if (ligne.ligneData.isALD == "true") {
    ordoMedicsALD.push(ligne);
  } else {
    ordoMedicsG.push(ligne);
  }
  construireHtmlLigneOrdonnance(ligne, 'append', '', '#conteneurOrdonnanceCourante');

  // SAMS
  if (ligne['medics'][0]['sams'].length > 0) {
    $.each(ligne['medics'][0]['sams'], function(samIndex, sam) {
      // sams dans l'ordo
      if ($.inArray(sam, samsInOrdo) == -1) samsInOrdo.push(sam);
      //sams qui doivent générer une alerte
      if ($.inArray(sam, samsToAlert) == -1 && $.inArray(sam, samsAlertViewed) == -1) samsToAlert.push(sam);
    });
    testSamsAndDisplay();

  }

  // sauvegarde
  ordoLiveSave();

  //reset objets
  resetObjets();

  // on réimpose l'analyse avant impression
  ordoDejaAnalysee = false;

  // on met à jour le coût ordonnance
  afficherCoutOrdo();
}

/**
 * Envoyer les informations de la fenêtre de prescription dans une ligne existante ordo.
 * @return {void}
 */
function sendToLigneOrdonnance() {
  //attraper les infos sur la prescription courante
  var ligne = catchCurrentPrescriptionData();

  // mode addToLigne
  if (modeActionModal == 'addToLigne') {
    // envoyer dans le bon tableau en fonction ALD / NON ALD et construire
    if (ligne.ligneData.isALD == "true") {
      ordoMedicsALD[ligneCouranteIndex]['medics'].push(ligne.medics[0]);
      construireHtmlLigneOrdonnance(ordoMedicsALD[ligneCouranteIndex], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsALD div.lignePrescription').eq(ligneCouranteIndex));
    } else {
      ordoMedicsG[ligneCouranteIndex]['medics'].push(ligne.medics[0]);
      construireHtmlLigneOrdonnance(ordoMedicsG[ligneCouranteIndex], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsG div.lignePrescription').eq(ligneCouranteIndex));
    }

    //SAMS : mise à jour
    getDifferentsSamFromOrdo();
    testSamsAndDisplay();
  }

  // mode edit
  else if (modeActionModal == 'edit') {

    if (ligne.ligneData.isALD == "true") {
      ordoMedicsALD[ligneCouranteIndex]['medics'].splice(indexMedic, 1, ligne.medics.splice(0, 1)[0]);
      if (indexMedic == 0) {
        ordoMedicsALD[ligneCouranteIndex]['ligneData'] = ligne.ligneData;
        console.log('ALD Edition ligne : ' + ligneCouranteIndex + ' -  medic : ' + indexMedic);
      }
      construireHtmlLigneOrdonnance(ordoMedicsALD[ligneCouranteIndex], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsALD div.lignePrescription').eq(ligneCouranteIndex));
    } else {
      ordoMedicsG[ligneCouranteIndex]['medics'].splice(indexMedic, 1, ligne.medics.splice(0, 1)[0]);
      if (indexMedic == 0) {
        ordoMedicsG[ligneCouranteIndex]['ligneData'] = ligne.ligneData;
        console.log('G Edition ligne : ' + ligneCouranteIndex + ' -  medic : ' + indexMedic);
      }
      construireHtmlLigneOrdonnance(ordoMedicsG[ligneCouranteIndex], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsG div.lignePrescription').eq(ligneCouranteIndex));
    }
  }

  // sauvegarde
  ordoLiveSave();

  //reset objets
  resetObjets();

  // on réimpose l'analyse avant impression
  ordoDejaAnalysee = false;

  // on met à jour le coût ordonnance
  afficherCoutOrdo();
}

/**
 * Faire une recherche sur un terme
 * @param  {string} term texte de recherche
 * @return {void}
 */
function sendMedicRecherche(term) {
  cleanModalRechercherOngletPosologies();

  //vider la recherche détaillée précéente
  $('#resultsDetaTabL').parent('li').hide();
  $('#resultsDetaTab').html('');

  $.ajax({
    url: urlBase + '/lap/ajax/searchNewMedic/',
    type: 'post',
    data: {
      term: term,
      typeRecherche: $('#typeRechercheMedic').val(),
      retourRecherche: $('#retourRechercheMedic').val(),
      listeCodesIndics: listeCodesIndics
    },
    dataType: "html",
    beforeSend: function() {
      $('#txtRechercheMedicHB').html("Recherche en cours ...");
      $('#rechercheResultats').html('<div class="text-center p-4"><i class="fas fa-spinner fa-4x fa-spin text-warning"></i></div>');
    },
    success: function(data) {
      listeCodesIndics = '';
      $('#rechercheResultats').html(data);
      $('#txtRechercheMedic').focus();
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
      listeCodesIndics = '';
      alert('Problème, rechargez la page !');
    }
  });
}

/**
 * Obtenir arborescence terrain > indication de l'onglet Posologies
 * @param  {int} codeSpe code de la Spécialité
 * @return {void}
 */
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

/**
 * Nettoyage complet du modal de prescription
 * @return {void}
 */
function cleanModalRecherche() {
  $('#txtRechercheMedic').val('');
  $("#rechercheResultats").html('');
  cleanModalRechercherOngletPosologies();
  cleanModalRechercherOngletPrescrire();
}

/**
 * Nettoyage / suppression de l'onglet Posologie
 * @return {void}
 */
function cleanModalRechercherOngletPosologies() {
  $('#posologiesmedicTab').parent('li').hide();
  $('#posologiesmedic').html('');
}

/**
 * Nettoyage / suppression de l'onglet Prescrire
 * @return {void}
 */
function cleanModalRechercherOngletPrescrire() {
  $('#prescriremedicTab').parent('li').hide();
  $("#lapFrappePrescription").val('');
  $("#prescriptionMotif").val('');
  $("#prescriptionHumanMedicName").html('');
  $("#prescriptionHumanRecap").html('');
  $("#prescriptionHumanPoso").html('');
  $('.prescriptionChampsDuree input').val('');
  $("#uniteUtilisee").html('');
  $("#voieUtilisee").html('');
  $('#prescriptionAldCheckbox').prop('checked', false);
  $('#prescriptionChroCheckbox').prop('checked', false);
  $('#prescriptionNpsCheckbox').prop('checked', false);
  $("#prescriptionNpsMotif").val('');
  $("#prescriptionAldCheckbox, #prescriptionChroCheckbox, #voieUtilisee, #nbRenouvellements").removeAttr('disabled');
  $('#beginPeriodeID, #endPeriodeID').val('');
  $('#prescripteurInitialTT').val('');
  $('#nbRenouvellements option[value="0"]').prop('selected', true);
  $('#prescriptionHumanConsignes').show();
  $('#lapConsignesPrescription').val('');
  $('#prescriptionHumanConsignes').html('');
}


/**
 * installation d'une nouvelle prescription d'un médic dans la modal
 * @param  {string} speThe          code Spécialité
 * @param  {string} presThe         code Présentation
 * @param  {string} txtPrescription texte de prescription
 * @return {void}
 */
function lapInstallPrescription(tab) {

  console.log("Installation d'une nouvelle prescription d'un médic dans la modal : START");

  $.ajax({
    url: urlBase + '/lap/ajax/lapInstallPrescription/',
    type: 'post',
    data: {
      toID: $('#identitePatient').attr("data-patientID"),
      speThe: tab.speThe,
      presThe: tab.presThe,
      tauxrbt: tab.tauxrbt,
      prixucd: tab.prixucd,
      ligneCouranteIndex: ligneCouranteIndex
    },
    dataType: "json",
    success: function(data) {
      console.log(data);
      // placer le retour sur ce medic dans le medic en cours de manipulation
      medicData = data['medicData'];

      // ménage préalable au cas ou ...
      cleanModalRechercherOngletPrescrire();
      cleanModalRechercherOngletPosologies();

      // si contexte d'ajout de médicament à une ligne
      var preSelectedCodeVoie = '';
      if (ligneCouranteIndex >= 0 && zoneOrdoAction && modeActionModal == 'addToLigne') {
        console.log('Zone action : ' + zoneOrdoAction);
        console.log('Ligne courante index : ' + ligneCouranteIndex);
        //console.log(ordoMedicsALD);
        if (zoneOrdoAction == 'ALD') {
          var medicZero = ordoMedicsALD[ligneCouranteIndex]['medics'][0];
          var ligneCouranteData = ordoMedicsALD[ligneCouranteIndex]['ligneData'];
        } else {
          var medicZero = ordoMedicsG[ligneCouranteIndex]['medics'][0];
          var ligneCouranteData = ordoMedicsG[ligneCouranteIndex]['ligneData'];
        }
        preSelectedCodeVoie = medicZero.voieUtiliseeCode;

        //cases à cocher
        if (ligneCouranteData.isALD == "true") $("#prescriptionAldCheckbox").prop("checked", true);
        if (ligneCouranteData.isChronique == "true") $("#prescriptionChroCheckbox").prop("checked", true);
        // disabled
        $("#prescriptionAldCheckbox, #prescriptionChroCheckbox, #voieUtilisee").attr('disabled', 'disabled');

      }

      // placer le nom de la spé
      $('#prescriptionHumanMedicName').html(medicData['nomUtileFinal']);

      // voies d'administration
      $.each(data['medicData']['voiesPossibles'], function(index, value) {
        if (preSelectedCodeVoie == value['codevoie']) {
          selectedCodeVoie = ' selected="selected" ';
        } else {
          selectedCodeVoie = '';
        }
        $('#voieUtilisee').append('<option value="' + value['codevoie'] + '" ' + selectedCodeVoie + '>voie ' + value['txtvoie'].toLowerCase() + '</option>');
      });

      // unités possibles
      $.each(data['medicData']['unitesPossibles'], function(index, value) {
        $('#uniteUtilisee').append('<option value="' + index + '">' + value + '</option>');
      });

      // si lastPrescription
      if (data.lastPrescription) {
        if (data.lastPrescription.consignesPrescription) {
          $('#lapConsignesPrescription').val(data.lastPrescription.consignesPrescription);
          $("#prescriptionHumanConsignes").html(nl2br($('#lapConsignesPrescription').val()));
        }
        if (data.lastPrescription.uniteUtiliseeOrigine) {
          $('#uniteUtilisee').val(data.lastPrescription.uniteUtiliseeOrigine);
        }
        if (data.lastPrescription.voieUtiliseeCode) {
          $('#voieUtilisee').val(data.lastPrescription.voieUtiliseeCode);
        }
      }

      //montre l'onglet et le panel
      $('#prescriremedicTab').parent('li').show();
      $('#prescriremedicTab').tab('show');
      $('#lapFrappePrescription').focus();
      console.log("Installation d'une nouvelle prescription d'un médic dans la modal : OK");

    },
    error: function() {
      console.log("Installation d'une nouvelle prescription d'un médic dans la modal : PROBLEME");

    }
  });
}

function prepareModalPrescription() {
  //onglets
  $('#recherchermedicTab').parent('li').hide();
  $('#prescriremedicTab').parent('li').hide();

  //boutons de fin d'action dans modal
  $('#modalRecherche button.sendToOrdonnance').hide();
  $('#modalRecherche button.sendModifToOrdonnance').hide();
  $('#modalRecherche button.addToLigneOnOrdonnance').hide();
  $('#modalRecherche button.addToTTenCours').hide();


  //divers
  $('#prescriptionAlertMultimedic').hide();
  $('.prescriptionChampsDuree, .prescriptionChampsEnd').show();
  $('#prescripteurInitialTT').parent('div.form-group').hide();
  $('#lapConsignesPrescription').show();

  if (modeActionModal == 'new') {
    $('#recherchermedicTab').parent('li').show();
    $('#recherchermedicTab').tab('show');
    $('#modalRecherche h4.modal-title').html('Nouvelle prescription');
    $('#modalRecherche button.sendToOrdonnance').show();
  } else if (modeActionModal == 'edit') {
    if (indexMedic > 0) {
      $('#prescriptionAlertMultimedic').show();
      $('.prescriptionChampsDuree').hide();
      $("#prescriptionChroCheckbox, #voieUtilisee").attr('disabled', 'disabled');
      $('#lapConsignesPrescription, #prescriptionHumanConsignes').hide();
    }
    $('#prescriremedicTab').parent('li').show();
    $('#prescriremedicTab').tab('show');
    $('#modalRecherche h4.modal-title').html('Edition de la prescription');
    $('#modalRecherche button.sendModifToOrdonnance').show();
    $("#prescriptionAldCheckbox").attr('disabled', 'disabled');
  } else if (modeActionModal == 'addToLigne') {
    $('#prescriptionAlertMultimedic').show();
    $('#recherchermedicTab').parent('li').show();
    $('#recherchermedicTab').tab('show');
    $('#modalRecherche h4.modal-title').html('Ajout à la ligne de prescription');
    $('#modalRecherche button.addToLigneOnOrdonnance').show();
    $('.prescriptionChampsDuree').hide();
    $('#lapConsignesPrescription').hide();
  } else if (modeActionModal == 'saisirTTenCours') {
    $('#recherchermedicTab').parent('li').show();
    $('#recherchermedicTab').tab('show');
    $('#modalRecherche h4.modal-title').html('Saisir traitement en cours');
    $('#modalRecherche button.addToTTenCours').show();
    $('#prescripteurInitialTT').parent('div.form-group').show();
  }
}

/**
 * Installer l'édition d'une prescription dans la modal
 * @param  {array} ordoZone array des medics de la zone
 * @param  {int} index    index du médicament à éditer
 * @return {void}
 */
function editPrescription(ordoZone, index) {
  console.log("Installation d'une édition de prescription d'un médic dans la modal : START");

  modeActionModal = 'edit';

  // mise en place des données
  if (ordoZone == 'ordoMedicsALD') {
    medicData = JSON.parse(JSON.stringify(ordoMedicsALD[index]['medics'][indexMedic]));
    ligneData = JSON.parse(JSON.stringify(ordoMedicsALD[index]['ligneData']));
  } else {
    medicData = JSON.parse(JSON.stringify(ordoMedicsG[index]['medics'][indexMedic]));
    ligneData = JSON.parse(JSON.stringify(ordoMedicsG[index]['ligneData']));
  }

  // ménage préalable au cas ou et changements esthétiques
  cleanModalRechercherOngletPrescrire();
  prepareModalPrescription();

  //placer la prescription
  $('#lapFrappePrescription').val(medicData.prescriptionMachinePoso);

  //placer les consignes
  $('#lapConsignesPrescription').val(ligneData.consignesPrescription);
  $("#prescriptionHumanConsignes").html(nl2br($('#lapConsignesPrescription').val()));

  //placer le motif de presciption
  $('#prescriptionMotif').val(medicData.prescriptionMotif);

  // voies d'administration
  $.each(medicData['voiesPossibles'], function(index, value) {
    if (ligneData.voieUtiliseeCode == value['codevoie']) {
      selectedCodeVoie = ' selected="selected" ';
    } else {
      selectedCodeVoie = '';
    }
    $('#voieUtilisee').append('<option value="' + value['codevoie'] + '" ' + selectedCodeVoie + '>voie ' + value['txtvoie'].toLowerCase() + '</option>');
  });

  //nb renouvellements
  $('#nbRenouvellements option[value="' + ligneData['nbRenouvellements'] + '"]').prop('selected', true);

  // date début et fin de prise
  $('#beginPeriodeID').val(ligneData['dateDebutPrise']);


  // unités possibles
  $.each(medicData['unitesPossibles'], function(index, value) {
    if (medicData.uniteUtiliseeOrigine == index) {
      selectedUniteUtilisee = ' selected="selected" ';
    } else {
      selectedUniteUtilisee = '';
    }
    $('#uniteUtilisee').append('<option value="' + index + '" ' + selectedUniteUtilisee + '>' + value + '</option>');
  });

  //cases à cocher
  if (ligneData.isALD == "true") $("#prescriptionAldCheckbox").prop("checked", true);
  if (ligneData.isChronique == "true") $("#prescriptionChroCheckbox").prop("checked", true);
  if (medicData.isNPS == "true") $("#prescriptionNpsCheckbox").prop("checked", true);

  //motif de nps
  $('#prescriptionNpsMotif').val(medicData.motifNPS);

  //montrer la modal
  $('#modalRecherche').modal('show');
  matchAndGo();
  console.log("Installation d'une édition de prescription d'un médic dans la modal : STOP");

}

/**
 * Mise à jour de la prescription à la frappe et au modif choix menus
 * @return {void}
 */
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
    console.log("Analyse coté serveur de lap rescription frappée : START");

    $.ajax({
      url: urlBase + '/lap/ajax/lapAnalyseFrappePrescription/',
      type: 'post',
      data: {
        ligneData: catchCurrentPrescriptionData()
      },
      dataType: "json",
      success: function(data) {

        // remonté dans ligneData
        ligneData['dureeTotaleHuman'] = data['dureeTotaleHuman'];
        ligneData['dureeTotaleMachine'] = data['dureeTotaleMachine'];
        ligneData['dureeTotaleMachineJours'] = data['dureeTotaleMachineJours'];
        ligneData['dureeTotaleMachineJoursAvecRenouv'] = data['dureeTotaleMachineJoursAvecRenouv'];
        ligneData['nbRenouvellements'] = data['nbRenouvellements'];


        // remonté dans medicData
        medicData['posoFrappeeNbDelignes'] = data['posoFrappeeNbDelignes'];
        medicData['posoFrappeeNbDelignesPosologiques'] = data['posoFrappeeNbDelignesPosologiques'];
        //medicData['posoHumanComplete'] = data['posoHumanComplete'];
        medicData['posoHumanBase'] = data['posoHumanBase'];
        medicData['posoHumanCompleteTab'] = data['posoHumanCompleteTab'];
        //medicData['posoJournaliereMax'] = data['posoJournaliereMax'];
        //medicData['posoMaxParPrise'] = data['posoMaxParPriseMax'];
        medicData['posoMinParPrise'] = data['posoMinParPriseMin'];
        medicData['versionInterpreteur'] = data['versionInterpreteur'];

        medicData['regEx'] = data['regEx'];
        medicData['posoTheriaqueMode'] = data['posoTheriaqueMode'];
        medicData['posoDosesSuccessives'] = data['posoDosesSuccessives'];
        medicData['posoDureesSuccessives'] = data['posoDureesSuccessives'];
        medicData['posoDureesUnitesSuccessives'] = data['posoDureesUnitesSuccessives'];
        medicData['nbPrisesParUniteTemps'] = data['nbPrisesParUniteTemps'];
        medicData['nbPrisesParUniteTempsUnite'] = data['nbPrisesParUniteTempsUnite'];
        medicData['posoJours'] = data['posoJours'];
        medicData['totalUnitesPrescrites'] = data['totalUnitesPrescrites'];

        // actions visuelle
        $('#prescriptionHumanMedicName').html(medicData.nomUtileFinal);
        if (medicData.motifNPS) insertMotif = ' - ' + medicData.motifNPS;
        else insertMotif = '';
        if (medicData.isNPS == 'true') $('#prescriptionHumanMedicName').append(' [non substituable' + insertMotif + ']');
        $('#prescriptionHumanPoso').html(data['posoHumanCompleteTab'].join('<br>'));
        $('#prescriptionHumanRecap').html(data['voieUtilisee']);
        if (data['posoFrappeeNbDelignes'] > 1) $('#prescriptionHumanRecap').append(' - Durée totale : ' + data['dureeTotaleHuman']);
        if (data['nbRenouvellements'] > 0) $('#prescriptionHumanRecap').append(' - À renouveler ' + data['nbRenouvellements'] + ' fois');
        if (data['posoFrappeeNbDelignes'] > 0) $("button.sendToOrdonnance").removeAttr('disabled');
        if (data['alerteSecabilite'] == true) {
          $("#prescriptionAlertSecabilite").show();
        } else {
          $("#prescriptionAlertSecabilite").hide();
        }

        //mise à jour dates début / fin
        var startDefaut = moment(new Date());
        if ($('#beginPeriodeID').val() == '') {
          var currentStart = startDefaut;
        } else {
          var currentStart = moment($('#beginPeriodeID').val(), "DD-MM-YYYY");
        }
        if (currentStart.format('DD/MM/YYYY') != startDefaut.format('DD/MM/YYYY')) {
          var start = currentStart;
        } else {
          var start = startDefaut;
        }
        $('#beginPeriodeID').val(start.format('DD/MM/YYYY'));

        if (data['dureeTotaleMachineJoursAvecRenouv'] > 0) {
          $('#endPeriodeID').val(start.add((data['dureeTotaleMachineJoursAvecRenouv'] - 1), 'days').format('DD/MM/YYYY'));
        } else {
          $('#endPeriodeID').val(start.add(data['dureeTotaleMachine']['h'], 'hours').format('DD/MM/YYYY'));
        }

        console.log(ligneData);
        console.log(medicData);
        console.log("Analyse coté serveur de lap rescription frappée : STOP");
      },
      error: function() {
        console.log('PROBLEM: analyse prescription');
      }
    });
  } else {
    $('#prescriptionHumanPoso').html('');
  }
}

/**
 * les ereg qui matchent et qui envoient en ajax un traitement des lignes de prescription
 * @param  {int} index index de la ligne
 * @param  {string} ligne texte de la ligne
 * @return {boolean}  true si match, false sinon
 */
function matchLigne(index, ligne) {
  var regExp = [];
  ligne = ligne + ' ';
  // 1-1-1 6j|s|m jp|ji texte de traine
  regExp[0] = /^(et|puis)?\s*([0-9\/,\.+]+) ([0-9\/,\.+]+) ([0-9\/,\.+]+)(?: ([0-9\/,\.+]+))?(?: ([lmMjvsdip]*))? (?:([0-9]+)(j|s|m))?(.*)/i;
  // 1 6xh|j|s|m 6h|j|s|m jp|ji
  regExp[1] = /^(et|puis)?\s*([0-9\/,\.]+) ([0-9]+)x(i|h|j|s|m){1}(?: ([lmMjvsdip]*))? (?:([0-9]+)(i|h|j|s|m))?(.*)/i;
  // posologie inconnue
  regExp[2] = /^(nc|\?) (?:([0-9]+)(j|s|m))/i;
  // x truc toutes les c durée pedant durée
  regExp[3] = /^(et|puis)?\s*([0-9\/,\.]+) ([0-9]+)(i|h|j|s|m){1}(?: ([lmMjvsdip]*))? (?:([0-9]+)(i|h|j|s|m))?(.*)/i;

  if (m = regExp[0].exec(ligne)) {
    return true;
  } else if (m = regExp[1].exec(ligne)) {
    return true;
  } else if (m = regExp[2].exec(ligne)) {
    return true;
  } else if (m = regExp[3].exec(ligne)) {
    return true;
  }
  return false;
}

function resetObjets() {
  medicData = {};
  ligneData = {};
  ligneCouranteIndex = '';
  indexMedic = '';
  zoneOrdoAction = '';
  modeActionModal = 'new';
}
