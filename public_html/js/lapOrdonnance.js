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

$(document).ready(function() {

  //Analyser l'ordonnance (avec tt en cours)
  $("button.analyserPrescription").on("click", function(e) {
    analyserPrescription();
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

  });

  // Détruire tout le contenu de l'ordonnance
  $('a.removeAllLignesPrescription').on("click", function(e) {
    if (confirm("Confirmez-vous la suppression de toutes les lignes de prescription ?")) {
      e.preventDefault();
      cleanOrdonnance();
      ordoLiveSave();
    }
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

  // Ordo live : restaurer la version sauvegardée (undo)
  $("a.ordoLiveRestore").on("click", function(e) {
    e.preventDefault();
    ordoLiveRestore();
  });

  // Ordo : print and save
  $("a.printAndSaveOrdo, button.printAndSaveOrdo").on("click", function(e) {
    e.preventDefault();
    saveOrdo(true);
    cleanOrdonnance();
  });

  // Ordo : sauvegarder
  $("a.saveOrdo, button.saveOrdo").on("click", function(e) {
    e.preventDefault();
    saveOrdo();
    cleanOrdonnance();
  });

  //Analyser l'ordonnance : voir datas brutes passées à Thériaque
  $("a.lapOrdoAnalyseResBrut").on("click", function(e) {
    e.preventDefault();
    lapOrdoAnalyseResBrut();
  });

});

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
    success: function(data) {
      console.log("Analyse ordonnance : OK");
      $('#modalLapAlerte div.modal-body').html(data['html']);
      $('#modalLapAlerte').modal('show')
    },
    error: function() {
      console.log("Analyse ordonnance : PROBLEME");
    }
  });
}

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
 * @return {[type]} [description]
 */
function saveOrdo(view) {
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
    },
    dataType: "json",
    success: function(data) {
      if (view == true) {
        window.open('/showpdf/' + data['ordoID'] + '/', '_blank');
      }
      console.log("Sauvegarde ordonnance : OK");
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
}

/**
 * Ordonnance vierge
 * @return {void}
 */
function cleanOrdonnance() {
  ordoMedicsALD = [];
  ordoMedicsG = [];
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
          construireOrdonnance(data['ordoLive']['ordoMedicsG'], data['ordoLive']['ordoMedicsALD'], '#conteneurOrdonnanceCourante');
        }
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
function construireOrdonnance(tabMedicsG, tabMedicsALD, parentdestination) {
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
  if(!mode) mode = voirOrdonnanceMode;

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

}

/**
 * Construire ligne ordonnance
 * @param  {object} data      data de la ligne de prescription
 * @param  {String} [mode=''] [description]
 * @return {[type]}           [description]
 */
function makeLigneOrdo(data, mode) {
  console.log(data);
  if (data.medics.length == 1) {
    retour = '<div class="well well-sm ui-sortable-handle lignePrescription';
    if (data.ligneData.isALD == 'true') retour += ' ald ';
    retour += '" ';
    if (mode == 'TTenCours') {
      retour += ' data-ligneID="' + data.ligneData.id + '"';
    }
    retour += ' >';
    retour += '  <div class="row">';
    retour += '    <div class="col-md-7">';
    retour += '      <div><strong>';
    retour += '        ' + data.medics[0].nomUtileFinal + '</strong>';
    if (data.medics[0].isNPS == 'true') {
      retour += ' [non substituable';
      if (data.medics[0].motifNPS) retour += '   - ' + data.medics[0].motifNPS
      retour += ']';
    }
    if (data.ligneData.isALD == 'true' && mode == 'TTenCours') {
      retour += '        <span class="label label-danger">ald</span>';
    }
    if (data.ligneData.isChronique == 'true') {
      retour += '        <span class="label label-default">chronique</span>';
    }
    retour += '      </div>';
    retour += '      <div>' + data.ligneData.voieUtilisee;
    if (data.medics[0].posoFrappeeNbDelignesPosologiques > 1) {
      retour += '          -';
      retour += '          ' + nl2br(data.ligneData.dureeTotaleHuman);
    }
    if (data.ligneData.nbRenouvellements > 0) {
      retour += ' - à renouveller ' + data.ligneData.nbRenouvellements + ' fois';
    }
    if (data.ligneData.dateDebutPrise != data.ligneData.dateFinPriseAvecRenouv) {
      retour += ' <small> - ' + data.ligneData.dateDebutPrise + ' au ' + data.ligneData.dateFinPriseAvecRenouv + '</small>';
    } else {
      retour += ' <small> - le ' + data.ligneData.dateDebutPrise + '</small>';
    }
    retour += '      </div>';
    retour += '      <div>' + nl2br(data.medics[0].posoHumanComplete) + '</div>';
    retour += '    </div>';

    retour += '    <div class="col-md-4">';
    if (data.medics[0].prescripteurInitialTT) {
      retour += '        <div class="small">Prescripteur :';
      retour += '          ' + data.medics[0].prescripteurInitialTT + '</div>';
    }
    if (data.medics[0].prescriptionMotif) {
      retour += '        <div class="small">Motif de prescription :<br>';
      retour += '          ' + nl2br(data.medics[0].prescriptionMotif) + '</div>';
    }
    retour += '  </div>';
    retour += '  <div class="col-md-1 text-right">';

    //Actions pour mode TT en cours
    if (mode == 'TTenCours') {
      retour += '<button class="btn btn-default btn-xs renouvLignePrescription" title="Renouveller">';
      retour += '<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button> ';

      retour += '<div class="btn-group">';
      retour += '  <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> <span class="caret"></span>';
      retour += '  </button>';
      retour += '  <ul class="dropdown-menu dropdown-menu-right">';
      retour += '    <li><a href="#" class="marquerArretEffectifCeJour">Arrêt effectif ce jour</a></li>';
      retour += '    <li><a href="#"  class="marquerArretEffectif">Arrết effectif à date antérieure</a></li>';
      retour += '  </ul>';
      retour += '</div>';

    }
    // voir ordo
    else if (mode == 'voirOrdonnance') {
      retour += '<button class="btn btn-default btn-xs renouvLignePrescription" title="Renouveller">';
      retour += '<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button> ';
    }
    //Actions pour mode ordonnance
    else if (mode == 'editionOrdonnance') {
      retour += '    <button class="btn btn-default btn-xs editLignePrescription">';
      retour += '      <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>';

      retour += '    <div class="btn-group">';
      retour += '      <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
      retour += '        <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>';
      retour += '        <span class="caret"></span>';
      retour += '      </button>';
      retour += '      <ul class="dropdown-menu dropdown-menu-right">';
      retour += '        <li>';
      retour += '          <a href="#" class="addToLigne">';
      retour += '            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
      retour += '            Ajouter un médicament à cette ligne de prescription</a>';
      retour += '        </li>';

      if (data.medics[0].prescriptibleEnDC == '1' && data.medics[0].nomDC != data.medics[0].nomUtileFinal) {
        retour += '          <li role="separator" class="divider"></li>';
        retour += '          <li>';
        retour += '            <a href="#" class="convertDci">';
        retour += '              <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>';
        retour += '              Convertir en DCI</a>';
        retour += '          </li>';
      }
      retour += '      </ul>';
      retour += '    </div>';

      retour += '    <button class="btn btn-default btn-xs removeLignePrescription">';
      retour += '      <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
    }

    retour += '  </div>';
    retour += '</div>';
    retour += '</div>';

  } else if (data.medics.length > 1) {

    retour = '<div class="well well-sm lignePrescription';
    if (data.ligneData.isALD == 'true') retour += ' ald ';
    retour += '">';
    retour += '  <div class="row" style="margin-bottom: 12px">';
    retour += '    <div class="col-md-7 gras text-capitalize">';
    retour += '      ' + data.ligneData.voieUtilisee + ' - ' + data.ligneData.dureeTotaleHuman
    if (data.ligneData.nbRenouvellements > 0) {
      retour += ' - à renouveller ' + data.ligneData.nbRenouvellements + ' fois';
    }
    if (data.ligneData.dateDebutPrise != data.ligneData.dateFinPriseAvecRenouv) {
      retour += ' <small class="nongras"> - ' + data.ligneData.dateDebutPrise + ' au ' + data.ligneData.dateFinPriseAvecRenouv + '</small>';
    } else {
      retour += ' <small class="nongras"> - le ' + data.ligneData.dateDebutPrise + '</small>';
    }
    retour += '    </div>';
    retour += '    <div class="col-md-4"></div>';
    retour += '    <div class="col-md-1 text-right">';

    //Actions pour mode TT en cours
    if (mode == 'TTenCours') {}

    // voir ordo
    else if (mode == 'voirOrdonnance') {
      retour += '<button class="btn btn-default btn-xs renouvLignePrescription" title="Renouveller">';
      retour += '<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button> ';
    }

    //Actions pour mode ordonnance
    else if (mode == 'editionOrdonnance') {
      retour += '      <div class="btn-group">';
      retour += '        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
      retour += '          <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>';
      retour += '          <span class="caret"></span>';
      retour += '        </button>';
      retour += '        <ul class="dropdown-menu dropdown-menu-right">';
      retour += '          <li>';
      retour += '            <a href="#" class="addToLigne">';
      retour += '              <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
      retour += '              Ajouter un médicament à cette ligne de prescription</a>';
      retour += '          </li>';

      if (data.medics[0].prescriptibleEnDC == '1') {
        retour += '            <li role="separator" class="divider"></li>';
        retour += '            <li>';
        retour += '              <a href="#" class="convertDci">';
        retour += '                <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>';
        retour += '                Convertir en DCI</a>';
        retour += '            </li>';
      }
      retour += '        </ul>';
      retour += '      </div>';
      retour += '      ';
      retour += '      <button class="btn btn-default btn-xs removeLignePrescription">';
      retour += '        <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
    }
    retour += '    </div>';
    retour += '  </div>';

    retour += '  <table class="table table-hover tablePrescripMultiMedic">';
    retour += '    <tbody>';
    var i = 1;
    $.each(data.medics, function(index, medic) {

      retour += '        <tr>';
      retour += '          <td class="col-md-6">';
      retour += '            <div>';
      retour += '              ' + i++ + ' - <strong>' + medic.nomUtileFinal + '</strong>';
      if (medic.isNPS == 'true') {
        retour += ' [non substituable';
        if (medic.motifNPS) retour += ' - ' + medic.motifNPS
        retour += ']';
      }
      if (data.ligneData.isALD == 'true' && mode == 'TTenCours') {
        retour += '        <span class="label label-danger">ald</span>';
      }
      if (data.ligneData.isChronique == 'true') {
        retour += ' <span class = "label label-default" >chronique</span>';
      }
      retour += '    </div>';
      retour += '    <div>' + medic.posoHumanBase + '</div>';
      retour += '  </td>';

      retour += '  <td class="col-md-4">';
      if (medic.prescripteurInitialTT) {
        retour += '        <div class="small">Prescripteur :';
        retour += '          ' + data.medics[0].prescripteurInitialTT + '</div>';
      }
      if (medic.prescriptionMotif.length > 0) {
        retour += '<div class="small">Motif de prescription :<br>';
        retour += nl2br(medic.prescriptionMotif) + '</div>';
      }
      retour += '</td>';

      retour += '<td class="col-md-2">';

      //Actions pour mode TT en cours
      if (mode == 'TTenCours') {
        retour += '    <button class="btn btn-default btn-xs editLignePrescription" title="Renouveller">';
        retour += '    <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></button>';
      }
      // voir ordo
      else if (mode == 'voirOrdonnance') {}
      //Actions pour mode ordonnance
      else if (mode == 'editionOrdonnance') {
        retour += '  <button class="btn btn-default btn-xs editMedicLignePrescription">';
        retour += '    <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button>';

        retour += '  <button class="btn btn-default btn-xs removeMedicLignePrescription">';
        retour += '    <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
      }

      retour += '</td>';

      retour += '</tr>';
    });
    retour += '</tbody>';
    retour += '</table>';

    retour += '</div>';
    retour += '</div>';
  }

  return retour;
}
