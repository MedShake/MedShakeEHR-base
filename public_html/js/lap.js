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

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations pour saut entre tabs

  // Onglet général LAP
  $('#ongletLAP').on("show.bs.tab", function() {
    lapRefreshLateralPatientData();
    voirOrdonnanceMode='';
  });

  // Onglet nouvelle ordonnance
  $('#ordonnanceTabL').on("show.bs.tab", function() {
    voirOrdonnanceMode='editionOrdonnance';
  });

  // Onglet TT en cours
  $('#tttencoursTabL').on("show.bs.tab", function() {
    refreshTTenCours();
    voirOrdonnanceMode='TTenCours';
  });

  // Onglet Historique ordo
  $('#ordohistoriqueTabL').on("show.bs.tab", function() {
    getHistoriqueOrdos();
    voirOrdonnanceMode='voirOrdonnance';
  });

  // Onglet historique traitement
  $('#tthistoriqueTabL').on("show.bs.tab", function() {
    getHistoriqueTT(year);
  });

  // Afficher prescriptions préétablies
  $('#prescriptionspreTabL').on("show.bs.tab", function() {
    if ($('#listePresPre').html() == '') {
      //getPresPre();
    }
    getPresPre();
    voirOrdonnanceMode='voirOrdonnance';
  });


  ////////////////////////////////////////////////////////////////////////
  ///////// Observations état allaitement

  // switcher ON OFF l'état d'allaitement
  $('#tabLAP').on("click", ".allaitementStart", function() {
    setPeopleDataByTypeName(true, $('#identitePatient').attr("data-patientID"), 'allaitementActuel', '#allaitementDet', 0);
    lapRefreshLateralPatientData();
  });
  $('#tabLAP').on("click", ".allaitementStop", function() {
    setPeopleDataByTypeName(false, $('#identitePatient').attr("data-patientID"), 'allaitementActuel', '#allaitementDet', 0);
    lapRefreshLateralPatientData();
  });

});

/**
 * Rafraichier la colonne latérale du LAP
 * @return {void}
 */
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
