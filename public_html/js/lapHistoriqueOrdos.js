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
 * Fonctions JS autour de l'historiques des ordonnances pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


var ordonnanceVisu = {};

$(document).ready(function() {

  // liste des ordos en fonction de l'année sélectionnée
  $('#ordohistoriqueTab').on("change", "#selectHistoOrdo", function() {
    year = $("#selectHistoOrdo option:selected").text();
    getHistoriqueOrdos(year);
  });

  // Obtenir l'ordo
  $('body').on("click", '.voirOrdonnance', function(e) {
    var ordonnanceID = $(this).attr("data-ordonnanceID");
    getOrdonnance(ordonnanceID);
  });

  // Renouveler une ligne de l'ordo visualisée
  $('body').on("click", '#conteneurOrdonnanceVisu button.renouvLignePrescription, .placeForOrdoLap button.renouvLignePrescription', function(e) {

    var ligneIndex = $(this).parents('div.lignePrescription').index();
    if ($(this).parents('div.lignePrescription').hasClass('ald')) {
      var zone = ordoMedicsALD;
      var ligneAinjecter = ordonnanceVisu['ordoMedicsALD'][ligneIndex];
    } else {
      var zone = ordoMedicsG;
      var ligneAinjecter = ordonnanceVisu['ordoMedicsG'][ligneIndex];
    }

    ligneAinjecter = cleanLignePrescriptionAvantRenouv(ligneAinjecter);

    console.log(ligneAinjecter);
    zone.push(ligneAinjecter);
    construireHtmlLigneOrdonnance(ligneAinjecter, 'append', '', '#conteneurOrdonnanceCourante', 'editionOrdonnance');

    //SAMS : mise à jour
    getDifferentsSamFromOrdo();
    testSamsAndDisplay();

    // retirer les infos allergiques potentiellement présentes
    deleteRisqueAllergique();

    // sauvegarde
    ordoLiveSave();

    //reset objets
    resetObjets();

    flashBackgroundElement($(this).parents('div.lignePrescription'));
  });

  // Renouveler toutes les lignes de l'ordonnance affichée : lap > modal
  $('body').on("click", '#modalVoirOrdonnance button.renouvToutesLignes', function(e) {
    $('#modalVoirOrdonnance button.renouvLignePrescription').trigger('click');
  });

  // Renouveler toutes les lignes de l'ordonnance affichée : dossier patient > historiques
  $('body').on("click", '#tabDossierMedical button.renouvToutesLignes', function(e) {
    parentTd = $(this).parents('.placeForOrdoLap');
    parentTd.find('button.renouvLignePrescription').trigger('click');
  });

});

/**
 * Obtenir l'odonnance
 * @param  {int} ordonnanceID ordonnance
 * @return {[type]}              [description]
 */
function getOrdonnance(ordonnanceID, destination) {
  if(!destination) var destination = 'modal';
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoGet/',
    type: 'post',
    data: {
      ordonnanceID: ordonnanceID,
    },
    dataType: "json",
    success: function(data) {
      ordonnanceVisu = data;
      if(destination == 'modal') {
        $('#conteneurOrdonnanceVisu div.conteneurPrescriptionsALD , #conteneurOrdonnanceVisu div.conteneurPrescriptionsG').html('')
        construireOrdonnance(data['ordoData'], data['ordoMedicsG'], data['ordoMedicsALD'], '#conteneurOrdonnanceVisu');
        var dateOrdo = moment(data.ordoData.creationDate, "YYYY-MM-DD HH:mm:ss");
        $('#modalVoirOrdonnance h4.modal-title').html("Ordonnance du " + dateOrdo.format("DD/MM/YYYY HH:mm") + " - Prescripteur : " + data.ordoData.prenom + " " + data.ordoData.nom);
        $('#modalVoirOrdonnance').modal('show');
      } else {
        construireOrdonnance(data['ordoData'], data['ordoMedicsG'], data['ordoMedicsALD'], destination);
      }
      console.log(ordonnanceVisu);
      console.log("Obtenir ordonnance : OK");
    },
    error: function() {
      console.log("Obtenir ordonnance : PROBLEME");
    }
  });
}

/**
 * Obtenir l'historique ordonnances
 * @return {string} html
 */
function getHistoriqueOrdos(year) {
  if (!year)  var year = moment(new Date()).format('YYYY');
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoHistoriqueGet/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
      year: year
    },
    dataType: "html",
    success: function(data) {

      $('#historiqueOrdos').html(data);
      console.log("Historique des ordonnances : OK");
    },
    error: function() {
      console.log("Historique des ordonnances : PROBLEME");
    }
  });
}
