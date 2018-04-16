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
 * Fonctions JS autour de l'historiques des traitements pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


var year = moment(new Date()).format('YYYY');

$(document).ready(function() {

  $('#tthistoriqueTab').on("change", "#selectHistoTTAnnee", function() {
    year = $("#selectHistoTTAnnee option:selected").text();
    getHistoriqueTT(year);
  });


});

/**
 * Obtenir l'historique des tt
 * @return {string} html
 */
function getHistoriqueTT(year) {
  if (!year)  var year = moment(new Date()).format('YYYY');
  $.ajax({
    url: urlBase + '/lap/ajax/lapOrdoHistoriqueTTGet/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
      year: year
    },
    dataType: "html",
    success: function(data) {

      $('#historiqueTT').html(data);
      console.log("Historique des tt : OK");
    },
    error: function() {
      console.log("Historique des tt : PROBLEME");
    }
  });
}
