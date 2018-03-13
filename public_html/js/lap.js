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
 * Fonctions générales JS pour le lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */


$(document).ready(function() {

  // rafraichir données physio quand on revient
  $('#ongletLAP').on("show.bs.tab", function() {
    lapRefreshLateralPatientData();
  });


  $('#tabLAP').on("click", ".allaitementStart", function() {
    setPeopleDataByTypeName(true, $('#identitePatient').attr("data-patientID"), 'allaitementActuel', '#allaitementDet', 0);
    lapRefreshLateralPatientData();
  });

  $('#tabLAP').on("click", ".allaitementStop", function() {
    setPeopleDataByTypeName(false, $('#identitePatient').attr("data-patientID"), 'allaitementActuel', '#allaitementDet', 0);
    lapRefreshLateralPatientData();
  });

});

function lapRefreshLateralPatientData() {
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
