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
 * Fonctions JS pour la recherche patients / pros
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 * @contrib Michaël Val
 */

 $(document).ready(function() {

  updateListingPatients();

  //focus sur nom
  $('#d2').focus();

  // navigation au clavier
  var listingRow = 0;
  $(".searchupdate").on("keyup", function(e) {
    //console.log(e.which);
    if (e.keyCode != 13 && e.keyCode != 37 && e.keyCode != 38 && e.keyCode != 39 && e.keyCode != 40) {
      updateListingPatients();
      listingRow = 0;
    }
  });

  $("input[type='checkbox'].searchupdate").on("click", function(e) {
      updateListingPatients();
      listingRow = 0;
  });

  $(document).on("keydown", function(e) {
    if (e.keyCode == 40) { //down
      listingRow++;
      if (listingRow + 1 > $('#listing table tbody tr').length) listingRow = $('#listing table tbody tr').length - 1;
      selectListingRow(listingRow);
    } else if (e.keyCode == 38) { //up
      listingRow--;
      if (listingRow < 0) {
        $('#d2').focus();
        listingRow = 0;
      }
      selectListingRow(listingRow);
    } else if (e.keyCode == 13) { //enter
      $('#listing table tbody tr').eq(listingRow).find("a.ouvrirDossier").click();
    }
  });

  // clic sur ligne
  $('body').on("click", ".openPatient td:not(:last-child)", function(e) {
    window.location.href = urlBase + $(this).closest('tr').attr('data-url');
  });

  //lire la carte vitale
  $('body').on("click", ".lireCpsVitale", function(e) {
    btnLec = $(this);
    $.ajax({
      url: urlBase + '/ajax/getCpsVitaleDataRappro/',
      type: 'post',
      data: {
        patientID: $(this).attr('data-patientID'),
      },
      dataType: "json",
      beforeSend: function() {
        btnLec.find('i').addClass('fa-spin');
      },
      complete: function() {
        btnLec.find('i').removeClass('fa-spin');
      },
      success: function(data) {
        console.log(vitaleToEhrTypeName(data));
        $('#lectureCpsVitale div.modal-body').html(ehrTypeDataToHtml());
        $('#lectureCpsVitale').modal('show');
      },
      error: function() {
        alert_popup("danger", 'Essayez à nouveau !');
      }
    });
  });

  // Rechercher a partir des data vitale
  $('body').on("click", ".searchPatientFromVitaleDataNss", function(e) {
    e.preventDefault();
    $('#lectureCpsVitale').modal('hide');
    indexVitale = $(this).attr('data-indexVitale');

    // TODO utiliser le NIR bénéficiaire (chmap [104][9]) par default pour la recheche si fournis ?
    //$('#autreCrit').val('nss');

    dataVitale[indexVitale]['firstname'] = ucfirst(dataVitale[indexVitale]['firstname']);
    //$('#autreCritVal').val(dataVitale[indexVitale]['nss']);
    if(dataVitale[indexVitale]['birthname'] && dataVitale[indexVitale]['lastname']) {
      $('#formRecherchePatients input[name="lastname"]').val(dataVitale[indexVitale]['lastname']);
    } else if (dataVitale[indexVitale]['birthname']) {
      $('#formRecherchePatients input[name="lastname"]').val(dataVitale[indexVitale]['birthname']);
    } else if (dataVitale[indexVitale]['lastname']) {
      $('#formRecherchePatients input[name="lastname"]').val(dataVitale[indexVitale]['lastname']);
    }
    $('#formRecherchePatients input[name="firstname"]').val(dataVitale[indexVitale]['firstname']);

    // Si la date de naissance est fournis l'utiliser aussi comme critaire de recherche
    if (dataVitale[indexVitale]['birthdate']) {
      $('#formRecherchePatients input[name="autre"]').val(dataVitale[indexVitale]['birthdate']);
      $('#formRecherchePatients select[name="autre"]').val('birthdate');
    }

    $('#autreCritVal').trigger('keyup');

  });

  //envoyer pour signature
  $('body').on("click", "a.sendSign, button.sendSign", function(e) {
    e.preventDefault();
    source = $(this);
    $.ajax({
      url: urlBase + '/patients/ajax/patientsSendSign/',
      type: 'post',
      data: {
        patientID: $(this).attr('data-patientID'),
        typeID: $(this).attr('data-typeID'),
        signPeriphName: $(this).attr('data-signPeriphName'),
      },
      dataType: "html",
      success: function(data) {
        el = source.closest('tr');
        el.css("background", "#efffe8");
        setTimeout(function() {
          el.css("background", "");
        }, 1000);
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  //marquer dossier comme supprimé
  $('body').on("click", "a.markDeleted", function(e) {
    e.preventDefault();
    if (confirm("Ce dossier ne sera plus visible et sera marqué comme supprimé.\nIl pourra cependant être restauré si besoin\nSouhaitez-vous poursuivre ? ")) {
      if (confirm("Confirmez-vous le marquage comme supprimé de ce dossier ?")) {

        motif = prompt("Motif de suppression ?");

        source = $(this);
        $.ajax({
          url: urlBase + '/patients/ajax/markDeleted/',
          type: 'post',
          data: {
            patientID: $(this).attr('data-patientID'),
            motif: motif
          },
          dataType: "html",
          success: function(data) {
            el = source.closest('tr');
            el.css("background", "#efffe8");
            setTimeout(function() {
              el.css("background", "");
              el.remove();
            }, 1000);

          },
          error: function() {
            alert_popup("danger", 'Problème, rechargez la page !');

          }
        });
      }
    }
  });

  //rendre utilisateur
  $('body').on("click", "a.rendreUtilisateur", function(e) {
    e.preventDefault();
    patientID = $(this).parents('tr').attr('data-patientID');
    $('#modalRendreUtilisateur').modal('toggle');
    $('#modalRendreUtilisateur input[name="preUserID"]').val(patientID);
  });

  // bouton de nouvelle transmission
  $('body').on("click", ".newTransmission", function(e) {
    e.preventDefault();
    $('#transConcerne').addClass('d-none');
    $('#transPatientConcID').val($(this).attr('data-patientID'));
    $('#transPatientConcSel').html($(this).parents('tr').find('span.identite').html());
    $('#transPatientConcSel').removeClass('d-none');

    $('#modalTransmission').modal('show');
  });
  // poster une transmission
  $('body').on("click", "#transmissionEnvoyer", function(e) {
    e.preventDefault();
    transmissionNewNextLocation = 'stayHere';
    posterTransmission();
  });

  //ajouter / retirer liste des Praticiens
  $('body').on("click", "a.switchPraticienListe", function(e) {
    e.preventDefault();
    source = $(this);
    $.ajax({
      url: urlBase + '/patients/ajax/switchPraticienListe/',
      type: 'post',
      data: {
        patientID: $(this).attr('data-patientID')
      },
      dataType: "json",
      success: function(data) {
        el = source.closest('tr').find('a.ouvrirDossier');
        if (data.type == 'pro') {
          source.html('<i class="fas fa-user-slash fa-fw text-muted mr-1"></i> Retirer de la liste Praticiens');
          el.addClass('btn-info');
          el.removeClass('btn-secondary');
          el.find('i.fas').removeClass('fa-user');
          el.find('i.fas').addClass('fa-user-md');
        } else {
          el.removeClass('btn-info');
          el.addClass('btn-secondary');
          source.html('<i class="fas fa-user-md fa-fw text-muted mr-1"></i> Ajouter de la liste Praticiens');
          el.find('i.fas').addClass('fa-user');
          el.find('i.fas').removeClass('fa-user-md');
        };
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

/**
 * Associer un patient externe à un patient interne trouvé via la recherche
 * @param  {object} e event
 * @return {void}
 */
  $('body').on("click", ".extAsPatient", function(e) {
    e.preventDefault();
    e.stopPropagation();
    var externID = $('.extToNew').attr("data-externid");
    var patientID = $(this).attr("data-patientid");
    $.ajax({
      url: urlBase + '/people/ajax/setExternAsPatient/',
      type: 'post',
      data: {
        externID: externID,
        patientID: patientID
      },
      dataType: "json",
      success: function(data) {
        window.location = urlBase + '/patient/' + patientID + '/';
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  });

/**
 * Associer un patient externe à nouveau patient
 * @param  {object} e event
 * @return {void}
 */
  $('.extToNew').on("click", function(e) {
    e.preventDefault();
    var externID = $(this).attr("data-externid");
    $.ajax({
      url: urlBase + '/people/ajax/setExternAsNewPatient/',
      type: 'post',
      data: {
        externID: externID
      },
      dataType: "json",
      success: function(data) {
        window.location = urlBase + '/patient/edit/' + externID + '/';
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });

  });

  //nouvel utilisateur
  $("button.modal-save").on("click", function(e) {
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    ajaxModalSave(form, modal, function() {
      updateListingPatients();
      $(modal).modal('hide');
    });

  });

  // Formatage automatique pour les numéros de téléphone
  $('#autreCritVal').on('input', function() {
    const critere = $('#autreCrit').val();
    // Adapter ici selon les valeurs exactes de tes options
    const phoneCriteria = ['homePhone', 'mobilePhone', 'mobilePhonePro', 'telPro', 'telPro'];
    if (phoneCriteria.includes(critere)) {
      let value = $(this).val().replace(/\D/g, ''); // On enlève tout sauf les chiffres
      value = value.replace(/(\d{2})(?=\d)/g, '$1 ').trim(); // Ajoute un espace tous les 2 chiffres
      $(this).val(value);
    }
  });

  // Formatage automatique JJ/MM/AAAA pour les dates dans le champ autre critère
  $('#autreCritVal').on('input', function() {
    const critere = $('#autreCrit').val();
    const dateCriteria = ['birthdate', 'deathdate'];
    if (dateCriteria.includes(critere)) {
      let value = $(this).val().replace(/\D/g, ''); // On enlève tout sauf les chiffres
      if (value.length > 2) value = value.slice(0,2) + '/' + value.slice(2);
      if (value.length > 5) value = value.slice(0,5) + '/' + value.slice(5,9);
      $(this).val(value);
    }
  });

});

/**
 * Changer le style d'une ligne du listing patient
 * @param  {int} listingRow index de la ligne
 * @return {void}
 */
function selectListingRow(listingRow) {
  $('#listing table tbody tr').removeClass('table-active');
  $('#listing table tbody tr').eq(listingRow).addClass('table-active');
}

/**
 * Obtenir le listing patient
 * @return {void}
 */
function updateListingPatients(getNextResult = false) {
  var univTagsFilter = $('.univTagsSelectFilter').serializeArray();
  var currentNbOfResultsDisplayed = getNextResult ? $('#listing>table>tbody>tr').length : 0;
  if (getNextResult) {
    var currentNbOfResultsDisplayed = $('#listing>table>tbody>tr').length;
  }
  $.ajax({
    url: urlBase + '/patients/ajax/patientsListByCrit/',
    type: 'post',
    data: {
      porp: $('#listing').attr('data-porp'),
      d2: $('#d2').val(),
      d3: $('#d3').val(),
      autreCrit: $('#autreCrit option:selected').val(),
      autreCritVal: $('#autreCritVal').val(),
      patientsPropres: $('#patientsPropres').is(':checked'),
      univTagsFilter: univTagsFilter,
      currentNbOfResultsDisplayed: currentNbOfResultsDisplayed,
      externalPatientID: $('#externalPatientID').val()
    },
    dataType: "html",
    success: function(data) {
      if (currentNbOfResultsDisplayed > 0) {
        $('#listing>table>tbody').append(data);
      } else {
        $('#listing').html(data);
      }
      if($('#nbResultsDisplayed').length) $('#nbResultsDisplayed')[0].innerText = $('#listing>table>tbody>tr').length;
      // NOTE : totalNbOfResultsDisplayed fait partis du scope global et est initialisé dans le template listing.html.twig
      if (totalNbOfResultsDisplayed <= $('#listing>table>tbody>tr').length) {
        $('#listing>table>caption').hide();
      }
      listingRow = 0;
      selectListingRow(listingRow);
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });

}
