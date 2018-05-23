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
 * Fonctions JS pour la gestion des prescriptions types
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

var prescriptionTypeVisu;

$(document).ready(function() {

  // Prescription type : préparer la modal d'ajout
  $('#modalLapPresPreSave').on('show.bs.modal', function(e) {
    modalLapPresPreSave();
  })

  // Prescription type : sauvegarder
  $('#modalLapPresPreSaveDo').on('click', function(e) {
    modalLapPresPreSaveDo();
  })


  $('body').on('click', '#creatNewPresTypeCat', function(e) {
    $('#modalNewPresTypeCat form input[name="name"]').val('userDefined' + Date.now());
    $('#modalNewPresTypeCat form input[name="id"]').remove();
    $('#modalNewPresTypeCat form input[name="label"], #modalNewPresTypeCat form input[name="description"]').val('');
    $('#modalNewPresTypeCat').modal('toggle');
  })

  // Catégorie de prescription type : sauvegarder
  $('#newPresTypeCatSave').on('click', function(e) {
    var data = {};
    $('#formModalNewPresTypeCat input, #formModalNewPresTypeCat select, #formModalNewPresTypeCat textarea').each(function(index) {
      var input = $(this);
      data[input.attr('name')] = input.val();
    });

    var url = $('#formModalNewPresTypeCat').attr('action');
    $.ajax({
      url: url,
      type: 'post',
      data: data,
      dataType: "json",
      success: function(data) {
        getPresPre();
        $('#modalNewPresTypeCat').modal('toggle');
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  })

  // voir la prescription type
  $('body').on('change', 'select.presTypeSelect', function() {
    $('#voirPresPre .conteneurPrescriptionsALD, #voirPresPre .conteneurPrescriptionsG').html('');
    getPresTypeDetails(this.value);

    //préparer bouton de suppression
    if (this.value > 0) {
      $('#prescriptionspreTab button.presPreDelete').removeClass('disabled');
      $('#prescriptionspreTab button.presPreDelete').attr('data-presID', this.value);
    }
  })

  // supprimer prescription type
  $('#prescriptionspreTab').on('click', 'button.presPreDelete', function(e) {
    if(!confirm("Êtes-vous certain de vouloir supprimer cette préscription type ?")) return;
    id = $('#prescriptionspreTab button.presPreDelete').attr('data-presID');
    $.ajax({
      url: urlBase + '/lap/ajax/lapPresTypeGetDelete/',
      type: 'post',
      data: {
        id: id,
      },
      dataType: "json",
      success: function(data) {
        getPresPre();
        prescriptionTypeVisu = {};
        $('#prescriptionspreTab button.presPreDelete').addClass('disabled');
        $('#prescriptionspreTab button.presPreDelete').attr('data-presID', '');
        $('#prescriptionspreTab div.conteneurPrescriptionsG').html('');
        $('#prescriptionspreTab div.conteneurPrescriptionsALD').html('');
      },
    });
  })

  // Ajouter une ligne de la prescription type
  $('body').on("click", '#voirPresPre button.renouvLignePrescription', function(e) {
    //renouveler
    renouvLignePrescriptionPresType($(this));
    //SAMS : mise à jour
    getDifferentsSamFromOrdo();
    testSamsAndDisplay();
    // retirer de l'ordo les infos allergiques potentiellement présentes
    deleteRisqueAllergique();
    // sauvegarde
    ordoLiveSave();
    //reset objets
    resetObjets();
  });

  // Ajouter toutes les lignes de la prescription type affichée
  $('body').on("click", '#prescriptionspreTab button.renouvToutesLignes', function(e) {
    $('#voirPresPre button.renouvLignePrescription').trigger('click');
  });

});

/**
 * Renouveler une ligne de prescription type
 * @param  {object} el object jquery source du click
 * @return {void}
 */
function renouvLignePrescriptionPresType(el) {
  ligneIndex = el.parents('div.lignePrescription').index();
  if (el.parents('div.lignePrescription').hasClass('ald')) {
    var zone = ordoMedicsALD;
    var ligneAinjecter = prescriptionTypeVisu['ordoMedicsALD'][ligneIndex];
  } else {
    var zone = ordoMedicsG;
    var ligneAinjecter = prescriptionTypeVisu['ordoMedicsG'][ligneIndex];
  }

  ligneAinjecter = cleanLignePrescriptionAvantRenouv(ligneAinjecter);
  zone.push(ligneAinjecter);
  construireHtmlLigneOrdonnance(ligneAinjecter, 'append', '', '#conteneurOrdonnanceCourante', 'editionOrdonnance');
  flashBackgroundElement(el.parents('div.lignePrescription'));
}

/**
 * Obtenir le détails JSON d'une prescription type
 * @param  {int} id ID de la prescription
 * @return {void}
 */
function getPresTypeDetails(id) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapPresTypeGetDetails/',
    type: 'post',
    data: {
      id: id,
    },
    dataType: "json",
    success: function(data) {
      if (data) {
        prescriptionTypeVisu = data;
        construireOrdonnance('', data['ordoMedicsG'], data['ordoMedicsALD'], '#voirPresPre');
      }

    },
    error: function() {

    }
  });
}

/**
 * Obtenir le html des prescriptions types
 * @return {void}
 */
function getPresPre() {
  $.ajax({
    url: urlBase + '/lap/ajax/lapPresPreGet/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "html",
    success: function(data) {
      $('#listePresPre').html(data);
      console.log("Liste des prescriptions préétablies : OK");
    },
    error: function() {
      console.log("Liste des prescriptions préétablies : PROBLEME");
    }
  });
}

/**
 * Préparer la modal de sauvegarde de prescription type
 * @return {void}
 */
function modalLapPresPreSave() {
  $.ajax({
    url: urlBase + '/lap/ajax/lapPresPreGetCat/',
    type: 'post',
    dataType: "json",
    success: function(data) {
      $('#ordoTypeCat').html('');
      if(!data) {
        $('#modalLapPresPreSaveDo').addClass('disabled');
        $('#modalLapPresPreSave div.modal-body').prepend('<div class="alert alert-danger" role="alert">Rendez-vous à l\'onglet Prescriptions types pour créer préalablement les catégories supports à l\'enregistrement !</div>');
      } else {
        $('#modalLapPresPreSaveDo').removeClass('disabled');
        $.each(data, function(catIndex, cat) {
          $('#ordoTypeCat').append('<option value="' + cat.id + '">' + cat.label + '</value>');
          $('#modalLapPresPreSave div.modal-body div.alert-danger').remove();
        });
      }
    },
    error: function() {
      console.log("Problème");
    }
  });
}

/**
 * Enregistrer la prescription type
 * @return {void}
 */
function modalLapPresPreSaveDo() {
  deleteRisqueAllergique();
  $.ajax({
    url: urlBase + '/lap/ajax/modalLapPresPreSaveDo/',
    type: 'post',
    data: {
      label: $('#ordoTypeName').val(),
      cat: $('#ordoTypeCat').val(),
      ordo: {
        ordoMedicsALD: ordoMedicsALD,
        ordoMedicsG: ordoMedicsG
      }
    },
    dataType: "json",
    success: function(data) {
      $('#modalLapPresPreSave').modal('toggle');
    },
    error: function() {
      console.log("Problème");
    }
  });
}
