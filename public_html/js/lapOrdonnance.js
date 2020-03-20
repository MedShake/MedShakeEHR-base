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
 * Fonctions JS autour de l'ordonnance en cours pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

/**
 * tableau des médicaments hors ALD de l'ordo en cours
 * @type {Array}
 */
var ordoMedicsG = [];
/**
 * tableau des médicaments ALD de l'ordo en cours
 * @type {Array}
 */
var ordoMedicsALD = [];
/**
 * nom zone source de la ligne de prescription déplacée
 * @type {string}
 */
var srcTab;
/**
 * index de la ligne de prescription déplacée
 * @type {int}
 */
var srcIdx;

/**
 * Comportement de la fonction makeLigneOrdo
 * @type {String}
 */
var voirOrdonnanceMode;

/**
 * Evite la seconde analyse avant impression
 * @type {String}
 */
var ordoDejaAnalysee;

/**
 * Analyser sans restriction
 * @type {Boolean}
 */
var analyseWithNoRestriction = false;

/**
 * Version Thériaque
 * @type {String}
 */
var ordoVersionTheriaque;

$(document).ready(function() {

  //Analyser l'ordonnance (avec tt en cours)
  $(".analyserPrescription").on("click", function(e) {
    e.preventDefault();
    analyserPrescription();
  });

  //Analyser les SAM ordonnance (avec tt en cours) sans restriction
  $(".analyseWithNoRestrictionSam").on("click", function(e) {
    e.preventDefault();
    analyseWithNoRestriction = true;
    getDifferentsSamFromOrdo();
    samsToAlert = samsInOrdo;
    samsAlertViewed = [];
    testSamsAndAlert();
    analyseWithNoRestriction = false;
  });

  // Voir les alertes patient physio sans restriction
  $(".analyseWithNoRestrictionPhysio").on("click", function(e) {
    e.preventDefault();
    analyseWithNoRestriction = true;
    checkGrossesseSup46EtAllaitSup3();
    analyseWithNoRestriction = false;
  });

  // Ordonner par drag & drop l'ordonnance
  $("#conteneurOrdonnanceCourante div.conteneurPrescriptionsALD, #conteneurOrdonnanceCourante div.conteneurPrescriptionsG").sortable({
    connectWith: ".connectedOrdoZones",
  });
  $("#conteneurOrdonnanceCourante div.conteneurPrescriptionsALD, #conteneurOrdonnanceCourante div.conteneurPrescriptionsG").disableSelection();

  $(".connectedOrdoZones").on("sortstart", function(event, ui) {
    srcTab = ui.item.hasClass('ald') ? ordoMedicsALD : ordoMedicsG;
    srcIdx = ui.item.index();
  });
  $(".connectedOrdoZones").on("sortupdate", function(event, ui) {
    if (this === ui.item.parent()[0]) {
      console.log('Déplacement de ligne d\'ordonnance : START');
      landingIndex = ui.item.index();
      console.log('index de départ : ' + srcIdx);
      console.log('index d\'arrivée : ' + landingIndex);

      moveLignePrescription(
        srcTab,
        ui.item.parent('div.connectedOrdoZones').hasClass('ald') ? ordoMedicsALD : ordoMedicsG,
        srcIdx,
        landingIndex
      );


      if (ui.item.parent('div.connectedOrdoZones').hasClass('ald')) {
        ui.item.addClass('ald');
        ordoMedicsALD[landingIndex]['ligneData']['isALD'] = 'true';
      } else {
        ui.item.removeClass('ald');
        ordoMedicsG[landingIndex]['ligneData']['isALD'] = 'false';
      }

      console.log(ordoMedicsALD);
      console.log(ordoMedicsG);
      console.log('Déplacement de ligne d\'ordonnance : STOP');
      ordoLiveSave();
    }
  });

  // Détruire une ligne d'ordonnance
  $("#conteneurOrdonnanceCourante").on("click", 'button.removeLignePrescription', function(e) {
    console.log('Destruction de ligne de prescription : START');
    index = $(this).parents('div.lignePrescription').index();
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      ordoMedicsALD.splice(index, 1);
    } else {
      ordoMedicsG.splice(index, 1);
    }
    $(this).parents('div.lignePrescription').remove();

    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);
    console.log('Destruction de ligne de prescription : STOP');
    ordoLiveSave();
    afficherCoutOrdo();

    //SAMS : mise à jour
    getDifferentsSamFromOrdo();
    testSamsAndDisplay();

  });

  // Détruire un médicament dans ligne d'ordonnance
  $("#conteneurOrdonnanceCourante").on("click", 'button.removeMedicLignePrescription', function(e) {
    console.log('Destruction d\'un médic dans ligne de prescription : START');
    index = $(this).parents('div.lignePrescription').index();
    indexMedic = $(this).parents('table.tablePrescripMultiMedic tr').index();

    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      ordoMedicsALD[index]['medics'].splice(indexMedic, 1);
      construireHtmlLigneOrdonnance(ordoMedicsALD[index], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsALD div.lignePrescription').eq(index));
    } else {
      ordoMedicsG[index]['medics'].splice(indexMedic, 1);
      construireHtmlLigneOrdonnance(ordoMedicsG[index], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsG div.lignePrescription').eq(index));
    }

    console.log('Destruction du médic ' + indexMedic + ' ligne prescription : ' + index);
    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);
    console.log('Destruction d\'un médic dans ligne de prescription : STOP');
    ordoLiveSave();
    afficherCoutOrdo();

    //SAMS : mise à jour
    getDifferentsSamFromOrdo();
    testSamsAndDisplay();

  });

  // Détruire tout le contenu de l'ordonnance
  $('a.removeAllLignesPrescription').on("click", function(e) {
    if (confirm("Confirmez-vous la suppression de toutes les lignes de prescription ?")) {
      cleanOrdonnance();
      ordoLiveSave();
      afficherCoutOrdo();
    }
    e.preventDefault();
  });

  // convertire une ligne en DCI
  $("#conteneurOrdonnanceCourante").on("click", "a.convertDci", function(e) {
    e.preventDefault();
    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);

    ligneCouranteIndex = $(this).parents('div.lignePrescription').index();
    if ($(this).parents('div.connectedOrdoZones').hasClass('ald')) {
      zoneOrdoAction = ordoMedicsALD;
    } else {
      zoneOrdoAction = ordoMedicsG;
    }

    $.each(zoneOrdoAction[ligneCouranteIndex]['medics'], function(medicIndex, medic) {
      convertMedicDCI(zoneOrdoAction, ligneCouranteIndex, medicIndex);
    });
    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);
  });

  // convertire l'ordo en DCI
  $("#ordonnanceTab").on("click", "a.convertAllDci", function(e) {
    e.preventDefault();

    $.each(ordoMedicsALD, function(ligneIndex, ligneData) {
      $.each(ordoMedicsALD[ligneIndex]['medics'], function(medicIndex, medic) {
        convertMedicDCI(ordoMedicsALD, ligneIndex, medicIndex);
      });
    });
    $.each(ordoMedicsG, function(ligneIndex, ligneData) {
      $.each(ordoMedicsG[ligneIndex]['medics'], function(medicIndex, medic) {
        convertMedicDCI(ordoMedicsG, ligneIndex, medicIndex);
      });
    });
    console.log(ordoMedicsALD);
    console.log(ordoMedicsG);
  });

  // Ordo live : restaurer la version sauvegardée (undo)
  $("a.ordoLiveRestore").on("click", function(e) {
    e.preventDefault();
    ordoLiveRestore();
  });

  // Ordo : print and save
  $("a.printAndSaveOrdo, button.printAndSaveOrdo").on("click", function(e) {
    e.preventDefault();
    saveOrdo('print');
    $('#modalLapAlerte').modal('hide');
  });

  // Ordo : print anonymous and save
  $(".printAnonymeAndSaveOrdo").on("click", function(e) {
    e.preventDefault();
    saveOrdo('printAnonyme');
    $('#modalLapAlerte').modal('hide');
  });

  // Ordo : sauvegarder
  $("a.saveOrdo, button.saveOrdo").on("click", function(e) {
    e.preventDefault();
    saveOrdo('saveOnly');
    cleanOrdonnance();
  });

  //Analyser l'ordonnance : voir datas brutes passées à Thériaque
  $("a.lapOrdoAnalyseResBrut").on("click", function(e) {
    e.preventDefault();
    lapOrdoAnalyseResBrut();
  });

});

/**
 * Convertir une ligne en DCI
 * @param  {object} zoneOrdo           objet de la zone (ALD / G)
 * @param  {int} ligneCouranteIndex index de la ligne courante
 * @param  {int} medicIndex         index du medic de la ligne courante
 * @return {void}
 */
function convertMedicDCI(zoneOrdo, ligneCouranteIndex, medicIndex) {
  if (zoneOrdo[ligneCouranteIndex]['medics'][medicIndex]['prescriptibleEnDC'] == 1) {
    zoneOrdo[ligneCouranteIndex]['medics'][medicIndex]['nomUtileFinal'] = zoneOrdo[ligneCouranteIndex]['medics'][medicIndex]['nomDC'];
    if (zoneOrdo[ligneCouranteIndex]['ligneData']['isALD'] == "true") {
      construireHtmlLigneOrdonnance(ordoMedicsALD[ligneCouranteIndex], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsALD div.lignePrescription').eq(ligneCouranteIndex));
    } else {
      construireHtmlLigneOrdonnance(ordoMedicsG[ligneCouranteIndex], 'replace', $('#conteneurOrdonnanceCourante div.conteneurPrescriptionsG div.lignePrescription').eq(ligneCouranteIndex));
    }
  }
}

/**
 * Analyser les prescriptions (ordo courante + tt en cours)
 * @return {void}
 */
function analyserPrescription() {

  var ordo = {
    ordoMedicsG: ordoMedicsG,
    ordoMedicsALD: ordoMedicsALD,
  };

  if (ordo.ordoMedicsG.length < 1 && ordo.ordoMedicsALD.length < 1) {
    if (!confirm("L'ordonnance courante est vide, souhaitez-vous poursuivre ?")) {
      return;
    }
  }

  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoAnalyse/',
    type: 'post',
    data: {
      ordo: ordo,
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "json",
    beforeSend: function() {
      $('#waitingModal').modal('toggle');
    },
    success: function(data) {
      $('#waitingModal').modal('toggle');
      console.log("Analyse ordonnance : OK");
      $('#modalLapAlerte div.modal-body').html(data['html']);
      $('#modAlerteImprimer, #modAlerteModifier').show();
      $('#modAlerteFermer').hide();
      incrusterRisqueAllergique(data.lignesRisqueAllergique);
      $('#modalLapAlerte').modal('show')
      ordoDejaAnalysee = true;
      ordoVersionTheriaque = data.versionTheriaque;
    },
    error: function() {
      console.log("Analyse ordonnance : PROBLEME");
    }
  });
}

/**
 * Analyser l'ordo et tt en cours et renvoyer le résultat brut
 * @return {void}
 */
function lapOrdoAnalyseResBrut() {

  var ordo = {
    ordoMedicsG: ordoMedicsG,
    ordoMedicsALD: ordoMedicsALD,
  };

  if (ordo.ordoMedicsG.length < 1 && ordo.ordoMedicsALD.length < 1) {
    if (!confirm("L'ordonnance courante est vide, souhaitez-vous poursuivre ?")) {
      return;
    }
  }

  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoAnalyseResBrut/',
    type: 'post',
    data: {
      ordo: ordo,
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "html",
    success: function(data) {
      $('#modalLapAlerte div.modal-body').html(data);
      $('#modalLapAlerte').modal('show')
    },
    error: function() {

    }
  });
}


/**
 * Sauver l'ordonnance en cours
 * @return {void}
 */
function saveOrdo(action) {

  // on impose l'analyse avant si non faite
  if (ordoDejaAnalysee != true) {
    analyserPrescription();
    return;
  }

  var ordo = {
    ordoMedicsG: ordoMedicsG,
    ordoMedicsALD: ordoMedicsALD,
  };

  if (ordo.ordoMedicsG.length < 1 && ordo.ordoMedicsALD.length < 1) {
    if (!confirm("L'ordonnance courante est vide, souhaitez-vous poursuivre ?")) {
      return;
    }
  }
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoSave/',
    type: 'post',
    data: {
      ordo: ordo,
      patientID: $('#identitePatient').attr("data-patientID"),
      ordoName: $('#ordoName').val(),
      versionTheriaque: ordoVersionTheriaque
    },
    dataType: "json",
    success: function(data) {
      if (action == 'print') {
        window.open('/showpdf/' + data['ordoID'] + '/', '_blank');
      } else if (action == 'printAnonyme') {
        window.open('/makepdf/' + $('#identitePatient').attr("data-patientID") + '/ordoLAP/' + data['ordoID'] + '/anonyme/', '_blank');
      }
      console.log("Sauvegarde ordonnance : OK");
      cleanOrdonnance();
      ordoDejaAnalysee = false;
    },
    error: function() {
      console.log("Sauvegarde ordonnance : PROBLEME");
    }
  });

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
  tabArrivee.splice(indexArrivee, 0, tabDepart.splice(indexDepart, 1)[0]);

  // on réimpose l'analyse avant impression
  ordoDejaAnalysee = false;
}

/**
 * Rendre vierge l'ordonnance
 * @return {void}
 */
function cleanOrdonnance() {
  ordoMedicsALD = [];
  ordoMedicsG = [];
  samsInOrdo = [];
  samsInSamsZone = [];
  testSamsAndDisplay();
  $('#conteneurOrdonnanceCourante div.lignePrescription').remove();
}

/**
 * Sauvegarder l'ordonnance en version JSON dans l'état précédent l'action
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
      console.log("Sauvegarde automatique ordonnance : OK");
    },
    error: function() {
      console.log("Sauvegarde automatique ordonnance : PROBLEME");
    }
  });
}


/**
 * Restaurer la version précédente de l'ordonnance
 * @return {void}
 */
function ordoLiveRestore() {
  console.log("Restoration automatique ordonnance : START");
  cleanOrdonnance();
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoLiveRestore/',
    type: 'post',
    data: {
      ordoLive: $('#conteneurOrdonnanceCourante').html(),
    },
    dataType: "json",
    success: function(data) {
      if (data['statut'] == 'ok') {
        if (data['ordoLive']) {
          if (data['ordoLive']['ordoMedicsG']) {
            ordoMedicsG = data['ordoLive']['ordoMedicsG'];
          } else {
            ordoMedicsG = [];
          }
          if (data['ordoLive']['ordoMedicsALD']) {
            ordoMedicsALD = data['ordoLive']['ordoMedicsALD'];
          } else {
            ordoMedicsALD = [];
          }
          construireOrdonnance(data['ordoData'], data['ordoLive']['ordoMedicsG'], data['ordoLive']['ordoMedicsALD'], '#conteneurOrdonnanceCourante');
        }

        // retrait des infos allergiques
        deleteRisqueAllergique();
        // Cout ordo
        afficherCoutOrdo();
        //SAMS : mise à jour
        getDifferentsSamFromOrdo();
        testSamsAndDisplay();
        samsToAlert = samsInOrdo;
        samsAlertViewed = [];
        testSamsAndAlert();

        console.log("Restoration automatique ordonnance : OK");

      } else if (data['statut'] == 'nofile') {
        alert("Aucune version antérieure trouvée");
        console.log("Restoration automatique ordonnance : NOFILE");
      }
    },
    error: function() {
      console.log("Restoration automatique ordonnance : PROBLEME");
    }
  });
}

/**
 * Construire html ordonnance
 * @param  {array} tabMedicsG   données médicaments hors ALD
 * @param  {array} tabMedicsALD données médicaments ald
 * @return {void}
 */
function construireOrdonnance(ordoData, tabMedicsG, tabMedicsALD, parentdestination) {
  console.log('reconstruction d\'ordonnance : START');
  if (tabMedicsG) {
    $.each(tabMedicsG, function(index, ligne) {
      construireHtmlLigneOrdonnance(ligne, 'append', '', parentdestination);
      console.log('reconstruction d\'ordonnance : ajout ligne G');
    });
  }
  if (tabMedicsALD) {
    $.each(tabMedicsALD, function(index, ligne) {
      construireHtmlLigneOrdonnance(ligne, 'append', '', parentdestination);
      console.log('econstruction d\'ordonnance : ajout ligne ALD');
    });
  }
  $('div.placeForVersionTheriaque').remove();
  if (ordoData != undefined && ordoData.value != undefined && ordoData.value.versionTheriaque != undefined) {
    $(parentdestination).append('<div class="small mt-2 placeForVersionTheriaque">Version Thériaque : ' + ordoData['value']['versionTheriaque'] + '</div>');
  }

  $(function() {
    $('[data-toggle="popover"]').popover()
  })
  console.log(ordoMedicsALD);
  console.log(ordoMedicsG);
  console.log('reconstruction d\'ordonnance : STOP');
}

/**
 * Obtenir le HTML d'une ligne d'ordonnance
 * @param  {Boolean} isALD     true si c'est une ligne ald
 * @param  {object}  medicData datas sur le médic
 * @return {string}
 */
function construireHtmlLigneOrdonnance(ligne, methode, destination, parentdestination, mode) {
  console.log('Génération html d\'une ligne de prescription : START');
  if (!parentdestination) var parentdestination = '';
  if (!mode) mode = voirOrdonnanceMode;

  var zoneDestination;
  if (ligne.ligneData.isALD == "true") {
    zoneDestination = $(parentdestination + ' div.conteneurPrescriptionsALD');
  } else {
    zoneDestination = $(parentdestination + ' div.conteneurPrescriptionsG');
  }

  nouvelleLigneOrdo = makeLigneOrdo(ligne, mode);
  if (methode == 'append') {
    zoneDestination.append(nouvelleLigneOrdo);
  } else if (methode == 'replace') {
    destination.replaceWith(nouvelleLigneOrdo);
  }
  $(function() {
    $('[data-toggle="popover"]').popover()
  })
}

/**
 * Calculer le coût total des prescriptions de l'ordonnance
 * @return {float} coût total
 */
function calculerCoutOrdo() {
  prixTotal = 0;
  $('a.coutMedic').each(function(index) {
    prix = $(this).attr('data-cout');
    prixTotal = parseFloat(prixTotal) + parseFloat(prix);
  });
  return prixTotal;
}

/**
 * Afficher le coût de l'ordonnance
 * @return {void}
 */
function afficherCoutOrdo() {
  cout = calculerCoutOrdo();
  if (cout > 0) {
    $('div.coutOrdo').html('Coût des prescriptions : ' + cout.toFixed(2).replace(".", ",") + ' €');
  } else {
    $('div.coutOrdo').html('');
  }
}

/**
 * Calculer le coût de la prescription de ce médicament (hors renouv)
 * @param  {int} totalUnitesPrescrites nombre d'unités prescrites
 * @param  {string} uniteUtiliseeOrigine  unité utilisée
 * @param  {float} prixucd               prix par ucd
 * @param  {array} unitesConversion      tableau de conversion
 * @return {string}                       prix arrondi à 2 décimales + euro ou nc
 */
function calculerCoutMedic(totalUnitesPrescrites, uniteUtiliseeOrigine, prixucd, unitesConversion) {
  var prix;
  prixucd = parseFloat(prixucd);
  if (prixucd > 0) {
    if (uniteUtiliseeOrigine == 'ucd') {
      prix = prixucd * totalUnitesPrescrites;
    } else if (uniteUtiliseeOrigine == 'unite_prescription') {
      prix = (totalUnitesPrescrites / unitesConversion['nb_up']) * prixucd;
    } else if (uniteUtiliseeOrigine == 'unite_prise') {
      prix = (totalUnitesPrescrites / unitesConversion['nb_ups']) * prixucd;
    } else if (uniteUtiliseeOrigine == 'ua') {
      prix = (totalUnitesPrescrites / unitesConversion['nb_ua']) * prixucd;
    }
    return {
      texte: prix.toFixed(2).replace(".", ",") + ' €',
      math: prix.toFixed(2)
    };
  } else {
    return {
      texte: 'n.c.',
      math: 0
    };
  }
}

/**
 * Tester si un médicament entre dans la prise en charge ALD pour les ALD du patient
 * @param  {array} tabList liste des ALD num => o/n
 * @return {boolean}         true / false
 */
function testIfAldOk(tabList) {
  if (aldActivesListe.length < 1) return false;
  for (var k in tabList) {
    if (tabList.hasOwnProperty(k)) {
      if (tabList[k] == 'o' && $.inArray(k, aldActivesListe) > 0) {
        return true;
      }
    }
  }
  return false;
}

/**
 * COmpléter les data de l'ordo courante avec risque allergique au retour d'analyse
 * @param  {array} data tableau des risques allergiques par zone
 * @return {void}
 */

function incrusterRisqueAllergique(data) {
  if ($.isArray(data['ordoMedicsG'])) {
    $.each(data['ordoMedicsG'], function(indexLigne, ligne) {
      $.each(ligne, function(indexMedic, medic) {
        ordoMedicsG[indexLigne]['medics'][indexMedic]['risqueAllergique'] = true;
      });
    });
  }
  if ($.isArray(data['ordoMedicsALD'])) {
    $.each(data['ordoMedicsALD'], function(indexLigne, ligne) {
      $.each(ligne, function(indexMedic, medic) {
        ordoMedicsALD[indexLigne]['medics'][indexMedic]['risqueAllergique'] = true;
      });
    });
  }
  console.log(ordoMedicsG);
  console.log(ordoMedicsALD);
}

/**
 * Supprimer de l'ordo courante les risques allergiques
 * @return {[type]} [description]
 */
function deleteRisqueAllergique() {
  $.each(ordoMedicsALD, function(ligneIndex, ligneData) {
    $.each(ordoMedicsALD[ligneIndex]['medics'], function(medicIndex, medic) {
      delete ordoMedicsALD[ligneIndex]['medics'][medicIndex]['risqueAllergique'];
    });
  });
  $.each(ordoMedicsG, function(ligneIndex, ligneData) {
    $.each(ordoMedicsG[ligneIndex]['medics'], function(medicIndex, medic) {
      delete ordoMedicsG[ligneIndex]['medics'][medicIndex]['risqueAllergique'];
    });
  });
}

/**
 * Construire ligne ordonnance
 * @param  {object} data      data de la ligne de prescription
 * @param  {String} [mode=''] [description]
 * @return {[type]}           [description]
 */
function makeLigneOrdo(data, mode) {
  console.log('mode : ' + mode);
  if (data.medics.length == 1) {

    coutMedic = calculerCoutMedic(data.medics[0]['totalUnitesPrescrites'], data.medics[0]['uniteUtiliseeOrigine'], data.medics[0]['prixucd'], data.medics[0]['unitesConversion']);

    retour = '<div class="card bg-light mb-2 p-2 ui-sortable-handle lignePrescription';
    if (data.ligneData.isALD == 'true') retour += ' ald ';
    retour += '" ';
    if (mode == 'TTenCours') {
      retour += ' data-ligneID="' + data.ligneData.objetID + '"';
    }
    retour += ' >';
    retour += '  <div class="row">';
    retour += '    <div class="col-md-10">';
    retour += '      <div><strong>';
    retour += '        ' + data.medics[0].nomUtileFinal + '</strong>';
    if (data.medics[0].isNPS == 'true') {
      retour += ' [non substituable';
      if (data.medics[0].motifNPS) retour += '   - ' + data.medics[0].motifNPS
      retour += ']';
    }
    if (data.ligneData.isALD == 'true' && mode == 'TTenCours') {
      retour += '        <span class="badge badge-danger">ald</span>';
    }
    if (data.ligneData.isChronique == 'true') {
      retour += '        <span class="badge badge-secondary">chronique</span>';
    }
    if (testIfAldOk(data.medics[0].ald) && mode == 'editionOrdonnance' && aldActivesListe.length > 0) {
      retour += ' <span class="fas fa-check-sign text-success" aria-hidden="true" title="la base médicamenteuse confirme la prise en charge possible en ALD pour ce médicament"></span>';
    } else if (mode == 'editionOrdonnance' && aldActivesListe.length > 0) {
      retour += ' <span class="fas fa-exclamation-circle text-warning" aria-hidden="true" title="la base médicamenteuse ne peut confirmer la possible prise en charge en ALD pour ce médicament"></span>';
    }

    if (data.medics[0].prescripteurInitialTT) {
      retour += ' <button class="btn btn-light btn-sm" type="button" data-toggle="popover" data-container="body" data-placement="top"  title="Prescripteur" data-content="' + data.medics[0].prescripteurInitialTT + '"><span class="fas fa-user" aria-hidden="true"></span></button>';
    }

    if (data.medics[0].prescriptionMotif) {
      retour += ' <button class="btn btn-light btn-sm" type="button" data-toggle="popover" data-container="body" data-placement="top"  title="Commentaire personnel" data-content="' + nl2br(data.medics[0].prescriptionMotif) + '"><span class="fas fa-comment" aria-hidden="true"></span></button>';
    }

    retour += '      </div>';
    retour += '      <div>' + data.ligneData.voieUtilisee;
    if (data.medics[0].posoFrappeeNbDelignesPosologiques > 1) {
      retour += '          -';
      retour += '          ' + nl2br(data.ligneData.dureeTotaleHuman);
    }
    if (data.ligneData.nbRenouvellements > 0) {
      retour += ' - à renouveler ' + data.ligneData.nbRenouvellements + ' fois';
    }
    if (mode != 'voirPrescriptionType') {
      if (data.ligneData.dateDebutPrise != data.ligneData.dateFinPriseAvecRenouv) {
        retour += ' <small> - ' + data.ligneData.dateDebutPrise + ' au ' + data.ligneData.dateFinPriseAvecRenouv + '</small>';
      } else {
        retour += ' <small> - le ' + data.ligneData.dateDebutPrise + '</small>';
      }
    }
    retour += '      </div>';
    retour += '      <div>' + data.medics[0].posoHumanCompleteTab.join('<br>') + '</div>';
    if (data.ligneData.consignesPrescription) {
      retour += '      <div class="small">' + nl2br(data.ligneData.consignesPrescription) + '</div>';
    }
    retour += '    </div>';
    retour += '  <div class="col-md-2 text-right">';

    //Actions pour mode TT en cours
    if (mode == 'TTenCours') {
      retour += '<button class="btn btn-light btn-sm renouvLignePrescription" title="Renouveler">';
      retour += '<span class="fas fa-sync-alt" aria-hidden="true"></span></button> ';

      retour += '<div class="btn-group">';
      retour += '  <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-times" aria-hidden="true"></span>';
      retour += '  </button>';
      retour += '  <div class="dropdown-menu dropdown-menu-right">';
      retour += '    <a href="#" class="dropdown-item marquerArretEffectifCeJour">Arrêt effectif ce jour</a>';
      retour += '    <a href="#"  class="dropdown-item marquerArretEffectif">Arrết effectif à date antérieure</a>';
      retour += '  </div>';
      retour += '</div>';

      retour += ' <div class="btn-group">';
      retour += '  <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-book" aria-hidden="true"></span></span>';
      retour += '  </button>';
      retour += '  <div class="dropdown-menu dropdown-menu-right">';
      retour += '    <a href="#" data-speThe="' + data.medics[0].speThe + '" class="dropdown-item effetsIndesirables">Effets indésirables</a>';
      retour += '     <a href="' + urlBase + '/lap/monographie/' + data.medics[0].speThe + '/" target="_blank"  class="dropdown-item">Monographie</a>';
      retour += '  </div>';
      retour += '</div>';

    }
    // voir ordo
    else if (mode == 'voirOrdonnance' || mode == 'voirPrescriptionType') {
      retour += '<button class="btn btn-light btn-sm renouvLignePrescription" title="Renouveler">';
      retour += '<span class="fas fa-sync-alt" aria-hidden="true"></span></button> ';
    }
    //Actions pour mode ordonnance
    else if (mode == 'editionOrdonnance') {
      retour += '    <button class="btn btn-light btn-sm editLignePrescription">';
      retour += '      <span class="fas fa-pencil-alt" aria-hidden="true"></span></button>';


      retour += '    <div class="btn-group">';
      retour += '      <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
      retour += '        <span class="fas fa-euro-sign" aria-hidden="true"></span>';
      retour += '      </button>';
      retour += '      <div class="dropdown-menu dropdown-menu-right">';
      retour += '          <a href="#" class="dropdown-item coutMedic" disabled data-cout="' + coutMedic.math + '">Coût estimé : ';
      retour += coutMedic.texte;
      retour += '           </a>';
      retour += '          <a class="dropdown-item" disabled href="#">Taux remboursement : ';
      retour += data.medics[0]['tauxrbt'];
      retour += '           </a>';
      retour += '      </div>';
      retour += '    </div>';


      retour += '    <div class="btn-group">';
      retour += '      <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
      retour += '        <span class="fas fa-wrench" aria-hidden="true"></span>';
      retour += '      </button>';
      retour += '      <div class="dropdown-menu dropdown-menu-right">';
      retour += '          <a href="#" class="dropdown-item addToLigne">';
      retour += '            <span class="fas fa-plus" aria-hidden="true"></span>';
      retour += '            Ajouter un médicament à cette ligne de prescription</a>';


      retour += '          <a href="' + urlBase + '/lap/monographie/' + data.medics[0].speThe + '/" target="_blank"  class="dropdown-item">';
      retour += '            <span class="fas fa-book" aria-hidden="true"></span>';
      retour += '            Monographie</a>';


      if (data.medics[0].prescriptibleEnDC == '1' && data.medics[0].nomDC != data.medics[0].nomUtileFinal) {
        retour += '          <div class="dropdown-divider"></div>';
        retour += '            <a href="#" class="dropdown-item convertDci">';
        retour += '              <span class="fas fa-sync-alt" aria-hidden="true"></span>';
        retour += '              Convertir en DCI</a>';
      }
      retour += '      </div>';
      retour += '    </div>';

      retour += '    <button class="btn btn-light btn-sm removeLignePrescription">';
      retour += '      <span class="fas fa-trash" aria-hidden="true"></span></button>';
    }

    retour += '  </div>';
    retour += '</div>';
    retour += '</div>';

  } else if (data.medics.length > 1) {

    retour = '<div class="card bg-light mb-2 p-2 lignePrescription';
    if (data.ligneData.isALD == 'true') retour += ' ald ';
    retour += '"';
    if (mode == 'TTenCours') {
      retour += ' data-ligneID="' + data.ligneData.objetID + '"';
    }
    retour += '>';
    retour += '  <div class="row" style="margin-bottom: 12px">';
    retour += '    <div class="col-md-11 font-weight-bold">';
    retour += '      ' + data.ligneData.voieUtilisee + ' - ' + data.ligneData.dureeTotaleHuman
    if (data.ligneData.nbRenouvellements > 0) {
      retour += ' - à renouveler ' + data.ligneData.nbRenouvellements + ' fois';
    }
    if (mode != 'voirPrescriptionType') {
      if (data.ligneData.dateDebutPrise != data.ligneData.dateFinPriseAvecRenouv) {
        retour += ' <small class="font-weight-normal"> - ' + data.ligneData.dateDebutPrise + ' au ' + data.ligneData.dateFinPriseAvecRenouv + '</small>';
      } else {
        retour += ' <small class="font-weight-normal"> - le ' + data.ligneData.dateDebutPrise + '</small>';
      }
    }
    retour += '    </div>';
    retour += '    <div class="col-md-1 text-right">';

    //Actions pour mode TT en cours
    if (mode == 'TTenCours') {
      retour += '<button class="btn btn-light btn-sm renouvLignePrescription" title="Renouveler">';
      retour += '<span class="fas fa-sync-alt" aria-hidden="true"></span></button> ';

      retour += '<div class="btn-group">';
      retour += '  <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-times" aria-hidden="true"></span>';
      retour += '  </button>';
      retour += '  <div class="dropdown-menu dropdown-menu-right">';
      retour += '    <a href="#" class="dropdown-item marquerArretEffectifCeJour">Arrêt effectif ce jour</a>';
      retour += '    <a href="#"  class="dropdown-item marquerArretEffectif">Arrết effectif à date antérieure</a>';
      retour += '  </div>';
      retour += '</div>';

    }

    // voir ordo
    else if (mode == 'voirOrdonnance' || mode == 'voirPrescriptionType') {
      retour += '<button class="btn btn-light btn-sm renouvLignePrescription" title="Renouveler">';
      retour += '<span class="fas fa-sync-alt" aria-hidden="true"></span></button> ';
    }

    //Actions pour mode ordonnance
    else if (mode == 'editionOrdonnance') {
      retour += '      <div class="btn-group">';
      retour += '        <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
      retour += '          <span class="fas fa-wrench" aria-hidden="true"></span>';
      retour += '        </button>';
      retour += '        <div class="dropdown-menu dropdown-menu-right">';
      retour += '            <a href="#" class="dropdown-item addToLigne">';
      retour += '              <span class="fas fa-plus" aria-hidden="true"></span>';
      retour += '              Ajouter un médicament à cette ligne de prescription</a>';

      if (data.medics[0].prescriptibleEnDC == '1') {
        retour += '            <div class="dropdown-divider"></div>';
        retour += '              <a href="#" class="dropdown-item convertDci">';
        retour += '                <span class="fas fa-sync-alt" aria-hidden="true"></span>';
        retour += '                Convertir en DCI</a>';
      }
      retour += '        </div>';
      retour += '      </div>';
      retour += '      ';
      retour += '      <button class="btn btn-light btn-sm removeLignePrescription">';
      retour += '        <span class="fas fa-trash" aria-hidden="true"></span></button>';
    }
    retour += '    </div>';
    retour += '  </div>';

    retour += '  <table class="table table-hover tablePrescripMultiMedic">';
    retour += '    <tbody>';
    var i = 1;
    $.each(data.medics, function(index, medic) {

      retour += '        <tr>';
      retour += '          <td class="col-md-11">';
      retour += '            <div>';
      retour += '              ' + i++ + ' - <strong>' + medic.nomUtileFinal + '</strong>';
      if (medic.isNPS == 'true') {
        retour += ' [non substituable';
        if (medic.motifNPS) retour += ' - ' + medic.motifNPS
        retour += ']';
      }
      if (data.ligneData.isALD == 'true' && mode == 'TTenCours') {
        retour += '        <span class="badge badge-danger">ald</span>';
      }
      if (data.ligneData.isChronique == 'true') {
        retour += ' <span class = "badge badge-secondary" >chronique</span>';
      }

      if (testIfAldOk(medic.ald) && mode == 'editionOrdonnance' && aldActivesListe.length > 0) {
        retour += ' <span class="fas fa-check-sign text-success" aria-hidden="true" title="la base médicamenteuse confirme la prise en charge possible en ALD pour ce médicament"></span>';
      } else if (mode == 'editionOrdonnance' && aldActivesListe.length > 0) {
        retour += ' <span class="fas fa-exclamation-circle text-warning" aria-hidden="true" title="la base médicamenteuse ne peut confirmer la possible prise en charge en ALD pour ce médicament"></span>';
      }

      if (medic.prescripteurInitialTT) {
        retour += ' <button class="btn btn-light btn-sm" type="button" data-toggle="popover" data-container="body" data-placement="top"  title="Prescripteur" data-content="' + medic.prescripteurInitialTT + '"><span class="fas fa-user" aria-hidden="true"></span></button>';
      }

      if (medic.prescriptionMotif.length > 0) {
        retour += ' <button class="btn btn-light btn-sm" type="button" data-toggle="popover" data-container="body" data-placement="top"  title="Commentaire personnel" data-content="' + nl2br(medic.prescriptionMotif) + '"><span class="fas fa-comment" aria-hidden="true"></span></button>';
      }

      retour += '    </div>';
      retour += '    <div>' + medic.posoHumanBase + '</div>';
      retour += '  </td>';



      retour += '<td class="col-md-2 text-right">';

      //Actions pour mode TT en cours
      if (mode == 'TTenCours') {
        retour += ' <div class="btn-group">';
        retour += '  <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="fas fa-book" aria-hidden="true"></span>';
        retour += '  </button>';
        retour += '  <div class="dropdown-menu dropdown-menu-right">';
        retour += '    <a href="#" data-speThe="' + medic.speThe + '" class="dropdown-item effetsIndesirables">Effets indésirables</a>';
        retour += '     <a href="' + urlBase + '/lap/monographie/' + data.medics[0].speThe + '/" target="_blank"  class="dropdown-item">Monographie</a>';
        retour += '  </div>';
        retour += '</div>';
      }
      // voir ordo
      else if (mode == 'voirOrdonnance') {}
      //Actions pour mode ordonnance
      else if (mode == 'editionOrdonnance') {
        retour += '  <button class="btn btn-light btn-sm editMedicLignePrescription">';
        retour += '    <span class="fas fa-pencil-alt" aria-hidden="true"></span></button>';

        retour += '  <button class="btn btn-light btn-sm removeMedicLignePrescription">';
        retour += '    <span class="fas fa-trash" aria-hidden="true"></span></button>';
      }

      retour += '</td>';

      retour += '</tr>';
    });
    retour += '</tbody>';
    retour += '</table>';
    if (data.ligneData.consignesPrescription) {
      retour += '<div class="small">' + nl2br(data.ligneData.consignesPrescription) + '</div>';
    }
    retour += '</div>';
    retour += '</div>';
  }

  return retour;
}
