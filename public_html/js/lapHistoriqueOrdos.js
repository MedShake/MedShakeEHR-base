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

  // Historique ordo
  $('#ordohistoriqueTabL').on("show.bs.tab", function() {
    getHistoriqueOrdos();
  });

  // Obtenir l'ordo
  $('#ordohistoriqueTab').on("click", '.voirOrdonnance', function(e) {
    var ordonnanceID = $(this).attr("data-ordonnanceID");
    getOrdonnance(ordonnanceID);
  });

});

/**
 * Obtenir l'odonnance
 * @param  {int} ordonnanceID ordonnance
 * @return {[type]}              [description]
 */
function getOrdonnance(ordonnanceID) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoGet/',
    type: 'post',
    data: {
      ordonnanceID: ordonnanceID,
    },
    dataType: "json",
    success: function(data) {
      ordonnanceVisu = data;
      $('#conteneurOrdonnanceVisu div.conteneurPrescriptionsALD , #conteneurOrdonnanceVisu div.conteneurPrescriptionsG').html('')
      construireOrdonnance(data['ordoMedicsG'], data['ordoMedicsALD'], '#conteneurOrdonnanceVisu');
      $('#modalVoirOrdonnance').modal('show');
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
function getHistoriqueOrdos() {
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoHistoriqueGet/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
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
