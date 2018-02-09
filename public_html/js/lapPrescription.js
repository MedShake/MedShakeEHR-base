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
 * @type {Array}
 */
var medicData;

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

$(document).ready(function() {

  // Lancer la recherche de médicament par validation avec enter
  $("#txtRechercheMedic").keypress(function(event) {
    keycode = event.keyCode || event.which;
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
    th = $(this).find("th");
    th.find(".arrow").remove();
    dir = $.fn.stupidtable.dir;
    arrow = data.direction === dir.ASC ? "glyphicon-chevron-up" : "glyphicon-chevron-down";
    th.eq(data.column).append(' <span class="arrow glyphicon ' + arrow + '"></span>');
    //console.log("The sorting direction: " + data.direction);
    //console.log("The column index: " + data.column);
  });


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
  $('#modalRecherche').on('show.bs.collapse', 'div.fichearecevoir .collapse', function() {
    fichesPosos = $(this).attr('data-fiches');
    destination = $(this).children('div.panel-body');
    if (fichesPosos && destination.html() == '') getFichesPosos(fichesPosos, destination);
  })

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
  medicData['uniteUtiliseeOrigine'] = $('#uniteUtilisee option:selected').attr('name');
  medicData['voieUtilisee'] = $('#voieUtilisee option:selected').text();
  medicData['voieUtiliseeCode'] = $('#voieUtilisee option:selected').attr('name');
  medicData['prescriptionMotif'] = $('#prescriptionMotif').val();


  // infos sur la ligne
  ligneData = {};
  if ($('#prescriptionAldCheckbox').is(':checked')) ligneData['isALD'] = "true";
  else ligneData['isALD'] = "false";
  if ($('#prescriptionChroCheckbox').is(':checked')) ligneData['isChronique'] = "true";
  else ligneData['isChronique'] = "false"
  ligneData['voieUtilisee'] = $('#voieUtilisee option:selected').attr('name');

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
  construireHtmlLigneOrdonnance(ligne, 'append');

  // sauvegarde
  ordoLiveSave();

}

/**
 * Envoyer les informations de la fenêtre de prescription dans une ligne existante ordo.
 * @return {void}
 */
function sendToLigneOrdonnance() {
  //attraper les infos sur la prescription courante
  ligne = catchCurrentPrescriptionData();

  // mode addToLigne
  if (modeActionModal == 'addToLigne') {
    // envoyer dans le bon tableau en fonction ALD / NON ALD et construire
    if (ligne.ligneData.isALD == "true") {
      ordoMedicsALD[ligneCouranteIndex]['medics'].push(ligne.medics[0]);
      construireHtmlLigneOrdonnance(ordoMedicsALD[ligneCouranteIndex], 'replace', $('#conteneurPrescriptionsALD div.lignePrescription').eq(ligneCouranteIndex));
    } else {
      ordoMedicsG[ligneCouranteIndex]['medics'].push(ligne.medics[0]);
      construireHtmlLigneOrdonnance(ordoMedicsG[ligneCouranteIndex], 'replace', $('#conteneurPrescriptionsG div.lignePrescription').eq(ligneCouranteIndex));
    }
  }

  // mode edit
  else if (modeActionModal == 'edit') {
    if (ligne.ligneData.isALD == "true") {
      ordoMedicsALD[ligneCouranteIndex]['medics'].splice(indexMedic, 1, ligne.medics.splice(0, 1)[0]);
      if (indexMedic == 0) ordoMedicsALD[ligneCouranteIndex]['ligneData'] = ligne.ligneData;
      construireHtmlLigneOrdonnance(ordoMedicsALD[ligneCouranteIndex], 'replace', $('#conteneurPrescriptionsALD div.lignePrescription').eq(ligneCouranteIndex));
    } else {
      ordoMedicsG[ligneCouranteIndex]['medics'].splice(indexMedic, 1, ligne.medics.splice(0, 1)[0]);
      if (indexMedic == 0) ordoMedicsG[ligneCouranteIndex]['ligneData'] = ligne.ligneData;
      construireHtmlLigneOrdonnance(ordoMedicsG[ligneCouranteIndex], 'replace', $('#conteneurPrescriptionsG div.lignePrescription').eq(ligneCouranteIndex));
    }
  }

  // sauvegarde
  ordoLiveSave();

  // reset des var
  modeActionModal = '';
  indexMedic = '';
  ligneCouranteIndex = '';
  medicData = [];
  zoneOrdoAction = '';

}

/**
 * Faire une recherche sur un terme
 * @param  {string} term texte de recherche
 * @return {void}
 */
function sendMedicRecherche(term) {
  cleanModalRechercherOngletPosologies();
  cleanModalRechercherOngletPrescrire();
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
 * Obtenir les fiches posologiques
 * @param  {string} codesFiches codes de(s) fiche(s) séparé(s) par virgule
 * @param  {object} destination objet jquery de destination pour l'affichage
 * @return {void}
 */
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
      console.log('OK : obtenir fiches posologies');
    },
    error: function() {
      console.log('PROBLEM : obtenir posologies');
    }
  });
}

/**
 * Obtenir le HTML d'une ligne d'ordonnance
 * @param  {Boolean} isALD     true si c'est une ligne ald
 * @param  {object}  medicData datas sur le médic
 * @return {string}
 */
function construireHtmlLigneOrdonnance(ligne, methode, destination) {
  console.log('Génération html d\'une ligne de prescription : START');
  var zoneDestination;
  if (ligne.ligneData.isALD == "true") {
    zoneDestination = $('#conteneurPrescriptionsALD');
  } else {
    zoneDestination = $('#conteneurPrescriptionsG');
  }

  nouvelleLigneOrdo = makeLigneOrdo(ligne);
  if (methode == 'append') {
    zoneDestination.append(nouvelleLigneOrdo);
  } else if (methode == 'replace') {
    destination.replaceWith(nouvelleLigneOrdo);
    $("#conteneurPrescriptionsALD, #conteneurPrescriptionsG").sortable('refresh')
  }


  // $.ajax({
  //   url: urlBase + '/lap/ajax/lapMakeLigneOrdonnance/',
  //   type: 'post',
  //   data: {
  //     ligne: ligne
  //   },
  //   dataType: "html",
  //   success: function(nouvelleLigneOrdo) {
  //     if (methode == 'append') {
  //       zoneDestination.append(nouvelleLigneOrdo);
  //     } else if (methode == 'replace') {
  //       destination.replaceWith(nouvelleLigneOrdo);
  //       $("#conteneurPrescriptionsALD, #conteneurPrescriptionsG").sortable('refresh')
  //     }
  //
  //   },
  //   error: function() {
  //     console.log('Génération html d\'une ligne de prescription : PROBLEME');
  //   }
  // });
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
  $("#uniteUtilisee").html('');
  $("#voieUtilisee").html('');
  $('#prescriptionAldCheckbox').prop('checked', false);
  $('#prescriptionChroCheckbox').prop('checked', false);
  $('#prescriptionNpsCheckbox').prop('checked', false);
  $("#prescriptionNpsMotif").val('');
  $("#prescriptionAldCheckbox, #prescriptionChroCheckbox, #voieUtilisee").removeAttr('disabled');
}


/**
 * installation d'une nouvelle prescription d'un médic dans la modal
 * @param  {string} speThe          code Spécialité
 * @param  {string} presThe         code Présentation
 * @param  {string} txtPrescription texte de prescription
 * @return {void}
 */
function lapInstallPrescription(speThe, presThe, txtPrescription) {
  //$('#lapFrappePrescription').attr('data-speThe', speThe);
  //$('#lapFrappePrescription').attr('data-presThe', presThe);

  console.log("Installation d'une nouvelle prescription d'un médic dans la modal : START");

  $.ajax({
    url: urlBase + '/lap/ajax/lapInstallPrescription/',
    type: 'post',
    data: {
      txtPrescription: txtPrescription,
      speThe: speThe,
      presThe: presThe,
      ligneCouranteIndex: ligneCouranteIndex
    },
    dataType: "json",
    success: function(data) {

      // placer le retour sur ce medic dans le medic en cours de manipulation
      medicData = data;

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
      $.each(data['voiesPossibles'], function(index, value) {
        if (preSelectedCodeVoie == value['codevoie']) {
          selectedCodeVoie = ' selected="selected" ';
        } else {
          selectedCodeVoie = '';
        }
        $('#voieUtilisee').append('<option name="' + value['codevoie'] + '" ' + selectedCodeVoie + '>voie ' + value['txtvoie'].toLowerCase() + '</option>');
      });

      // unités possibles
      $.each(data['unitesPossibles'], function(index, value) {
        $('#uniteUtilisee').append('<option name="' + index + '">' + value + '</option>');
      });

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
  if (modeActionModal == 'new') {
    $('#recherchermedicTab').parent('li').show();
    $('#prescriremedicTab').parent('li').hide();
    $('#recherchermedicTab').tab('show');
    $('#modalRecherche h4.modal-title').html('Nouvelle prescription');
    $('#modalRecherche button.sendToOrdonnance').show();
    $('#modalRecherche button.sendModifToOrdonnance').hide();
    $('#modalRecherche button.addToLigneOnOrdonnance').hide();
  } else if (modeActionModal == 'edit') {
    $('#recherchermedicTab').parent('li').hide();
    $('#prescriremedicTab').parent('li').show();
    $('#prescriremedicTab').tab('show');
    $('#modalRecherche h4.modal-title').html('Edition de la prescription');
    $('#modalRecherche button.sendToOrdonnance').hide();
    $('#modalRecherche button.sendModifToOrdonnance').show();
    $('#modalRecherche button.addToLigneOnOrdonnance').hide();
    $("#prescriptionAldCheckbox").attr('disabled', 'disabled');
  } else if (modeActionModal == 'addToLigne') {
    $('#recherchermedicTab').parent('li').show();
    $('#prescriremedicTab').parent('li').hide();
    $('#recherchermedicTab').tab('show');
    $('#modalRecherche h4.modal-title').html('Ajout à la ligne de prescription');
    $('#modalRecherche button.sendToOrdonnance').hide();
    $('#modalRecherche button.sendModifToOrdonnance').hide();
    $('#modalRecherche button.addToLigneOnOrdonnance').show();
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
  medicData = ordoZone[index]['medics'][indexMedic];
  var dataLigne = ordoZone[index]['ligneData'];

  // ménage préalable au cas ou et changements esthétiques
  cleanModalRechercherOngletPrescrire();
  prepareModalPrescription();

  //placer la prescription
  $('#lapFrappePrescription').val(medicData.prescriptionMachinePoso);

  //placer le motif de presciption
  $('#prescriptionMotif').val(medicData.prescriptionMotif);

  // voies d'administration
  $.each(medicData['voiesPossibles'], function(index, value) {
    if (medicData.voieUtiliseeCode == value['codevoie']) {
      selectedCodeVoie = ' selected="selected" ';
    } else {
      selectedCodeVoie = '';
    }
    $('#voieUtilisee').append('<option name="' + value['codevoie'] + '" ' + selectedCodeVoie + '>voie ' + value['txtvoie'].toLowerCase() + '</option>');
  });

  // unités possibles
  $.each(medicData['unitesPossibles'], function(index, value) {
    if (medicData.uniteUtiliseeOrigine == index) {
      selectedUniteUtilisee = ' selected="selected" ';
    } else {
      selectedUniteUtilisee = '';
    }
    $('#uniteUtilisee').append('<option name="' + index + '" ' + selectedUniteUtilisee + '>' + value + '</option>');
  });

  //cases à cocher
  if (dataLigne.isALD == "true") $("#prescriptionAldCheckbox").prop("checked", true);
  if (dataLigne.isChronique == "true") $("#prescriptionChroCheckbox").prop("checked", true);
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
        // remonté dans medicData
        medicData['posoFrappeeNbDelignes'] = data['posoFrappeeNbDelignes'];
        medicData['posoFrappeeNbDelignesPosologiques'] = data['posoFrappeeNbDelignesPosologiques'];
        medicData['posoHumanComplete'] = data['posoHumanComplete'];
        medicData['posoJournaliereMax'] = data['posoJournaliereMax'];
        medicData['posoMaxParPrise'] = data['posoMaxParPriseMax'];
        medicData['posoMinParPrise'] = data['posoMinParPriseMin'];
        medicData['dureeTotaleHuman'] = data['dureeTotaleHuman'];
        medicData['dureeTotaleMachine'] = data['dureeTotaleMachine'];
        medicData['versionInterpreteur'] = data['versionInterpreteur'];

        // actions visuelle
        $('#prescriptionHumanMedicName').html(medicData.nomUtileFinal);
        if (medicData.motifNPS) insertMotif = ' - ' + medicData.motifNPS;
        else insertMotif = '';
        if (medicData.isNPS == 'true') $('#prescriptionHumanMedicName').append(' [non substituable' + insertMotif + ']');
        $('#prescriptionHumanPoso').html(nl2br(data['posoHumanComplete']));
        $('#prescriptionHumanRecap').html(data['voieUtilisee']);
        if (data['posoFrappeeNbDelignes'] > 1) $('#prescriptionHumanRecap').append(' - Durée totale : ' + data['dureeTotaleHuman']);
        if (data['posoFrappeeNbDelignes'] > 0) $("button.sendToOrdonnance").removeAttr('disabled');
        if (data['alerteSecabilite'] == true) {
          $("#prescriptionAlertSecabilite").show();
        } else {
          $("#prescriptionAlertSecabilite").hide();
        }
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
