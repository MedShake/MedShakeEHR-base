/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Gestions des events pour les pages transmisions
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

var numPageTrans = 1;
var transmissionNewNextLocation = '';

$(document).ready(function() {

  $('body').on("click", "tr.voirTransmission", function(e) {
    id = $(this).attr('data-transmissionid');
    window.location.href = urlBase + '/transmission/' + id + '/';
  });
  $('body').on("click", "tr.voirTransmission a", function(e) {
    e.stopPropagation();
  });

  $('#toolbarTransmissions').on("click", "#ctrlTransLecture", function(e) {
    $(this).toggleClass('btn-secondary btn-light');
    getTransmissions();
  });

  $('#toolbarTransmissions').on("click", "button.ctrlTransListing", function(e) {
    numPageTrans = 1;
    $(this).siblings('.ctrlTransListing').addClass('btn-light');
    $(this).siblings('.ctrlTransListing').removeClass('btn-secondary');
    $(this).removeClass('btn-light');
    $(this).addClass('btn-secondary');
    getTransmissions();
  });

  $('body').on("click", "#pagePrecedente", function(e) {
    numPageTrans = numPageTrans+1;
    getTransmissions();
  });

  $('body').on("click", "#pageSuivante", function(e) {
    numPageTrans = numPageTrans-1;
    getTransmissions();
  });

  // poster une transmission
  $('body').on("click", "#transmissionEnvoyer", function(e) {
    e.preventDefault();
    posterTransmission();
  });

  // éditer une transmission
  $('body').on("click", ".editerTransmission", function(e) {
    e.preventDefault();
    resetTransmissionModal();
    id = $(this).attr('data-transmissionID');
    editerTransmission(id);
  });

  $('body').on("click", ".marquerTransmissionTraitee", function(e) {
    id = $(this).attr('data-transmissionid');
    setTransmissionTraitee(id);
  });

  $('body').on("click", ".effacerTransmission", function(e) {
    e.preventDefault();
    id = $(this).attr('data-transmissionid');
    setTransmissionEffacee(id);
  });

  // poster une réponse
  $('#transmissionRepondre').on("click", "#reponseEnvoyer", function(e) {
    e.preventDefault();
    posterReponse();
  });

  // éditer une réponse
  $('body').on("click", ".editerReponse", function(e) {
    e.preventDefault();
    id = $(this).attr('data-transmissionID');
    editerReponse(id);
  });

  //chercher patient
  $('#transConcerne').autocomplete({
    source: urlBase + '/transmissions/ajax/searchPatient/',
    select: function(event, ui) {
      $('#transPatientConcID').val(ui.item.patientID);
      $('#transPatientConcSel').html('<button id="transPatientConcSelDel" class="btn btn-sm  btn-light"><i class="far fa-trash-alt"></i></button> ' + ui.item.label);
      $('#transPatientConcSel').removeClass('d-none');
      $(this).val('');
      return false;
    },
  });
  $("#transConcerne").autocomplete("option", "appendTo", ".formModalNewTrans");

  //enlever destinataire choisi
  $('body').on("click", "#transPatientConcSelDel", function(e) {
    e.preventDefault();
    $('#transPatientConcID').val('');
    $('#transPatientConcSel').html('');
    $('#transPatientConcSel').addClass('d-none');
  });

  getTransmissions();

});
