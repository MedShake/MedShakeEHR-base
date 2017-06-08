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
 * Fonctions JS pour le dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  ////////////////////////////////////////////////////////////////////////
  ///////// Charger les scripts JS correspondant au module et forms inclus dans la page

  //charger le fichier commun de fonctions aux forms médicaux
  //$.getScriptOnce("../js/module/common.js");

  ///////// Charger les scripts JS correspondant au form inclus dans la page
  if (typeof(formScripts) != "undefined") {
    if ($.isArray(formScripts)) {
      $.each(formScripts, function(index, value) {
        $.getScriptOnce("../js/module/formsScripts/" + value + ".js");
      });
    }
  }

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations pour sauvegarde automatique des champs modifiés
  $(".changeObserv input:not(.datepic), .changeObserv textarea").typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: false,
    captureLength: 1,
    callback: function(value) {
      patientID = $('#identitePatient').attr("data-patientID");
      typeID = $(this).attr("data-typeID");
      source = $(this);
      instance = $(this).closest("form").attr("data-instance");
      setPeopleData(value, patientID, typeID, source, instance);
    }

  });
  $(".changeObserv select").on("change", function(e) {
    patientID = $('#identitePatient').attr("data-patientID");
    typeID = $(this).attr("data-typeID");
    value = $(this).val();
    source = $(this);
    instance = $(this).closest("form").attr("data-instance");
    setPeopleData(value, patientID, typeID, source, instance);
  });
  $(".datepick").on("dp.change", function(e) {
    patientID = $('#identitePatient').attr("data-patientID");
    typeID = $(this).children('input').attr("data-typeID");
    if (e.date) value = e.date.format('L');
    else value = '';
    source = $(this).children('input');
    instance = $(this).closest("form").attr("data-instance");
    setPeopleData(value, patientID, typeID, source, instance);
  });

  $('input.jqautocomplete').on("autocompletechange", function(event, ui) {
    patientID = $('#identitePatient').attr("data-patientID");
    typeID = $(this).attr("data-typeID");
    value = $(this).val();
    source = $(this);
    instance = $(this).closest("form").attr("data-instance");
    setPeopleData(value, patientID, typeID, source, instance);

  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations DICOM
  $("a.prepareEcho").on("click", function(e) {
    e.preventDefault();
    prepareEcho();
  });
  if (typeof(dicomAutoSendPatient2Echo) != "undefined") {
    if (dicomAutoSendPatient2Echo == true) {
      prepareEcho();
    }
  }

  $("a.catchLastDicomSrData").on("click", function(e) {
    e.preventDefault();
    catchLastDicomSrData();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations déclenchement actions d'injections dans la page

  //bouton de nouvelle consultation
  $("button.newCS, a.newCS").on("click", function(e) {
    e.preventDefault();
    if ($('#nouvelleCs').html() != '') {
      if (confirm('Voulez-vous remplacer le contenu de la consultation en cours ?')) {
        sendFormToCsDiv($(this));
      }
    } else {
      sendFormToCsDiv($(this))
    }
  });

  //bouton de nouveau courrier
  $("a.newCourrier").on("click", function(e) {
    e.preventDefault();
    if ($('#newCourrier').html() != '') {
      if (confirm('Voulez-vous remplacer le contenu du courrier en cours ?')) {
        $("#editeurCourrier").tinymce().remove();
        sendFormToCourrierDiv($(this));
      }
    } else {
      sendFormToCourrierDiv($(this))
    }
  });

  //bouton de nouvelle ordo
  $("#linkAddNewOrdo, .editOrdo").on("click", function(e) {
    e.preventDefault();
    if ($('#newOrdo').html() != '') {
      if (confirm('Voulez-vous remplacer le contenu de la zone d\'ordonnance en cours ?')) {
        sendFormToOrdoDiv($(this));
      }
    } else {
      sendFormToOrdoDiv($(this));
    }
  });

  //bouton de nouveau document importé
  $("#linkAddNewDoc, #cleanNewDocImport").on("click", function(e) {
    e.preventDefault();
    $('#newDoc').toggle();
    $.getScriptOnce("../js/patientScripts/docupload.js");
  });

  //bouton de nouveau mail
  $(".newMail").on("click", function(e) {
    e.preventDefault();
    if ($('#newMail').html() != '') {
      if (confirm('Voulez-vous remplacer le contenu de la zone de mail en cours ?')) {
        sendFormToMailDiv($(this));
      }
    } else {
      sendFormToMailDiv($(this))
    }
  });

  //bouton de nouveau reglement
  $("#linkAddNewReglement, .editReglement").on("click", function(e) {
    e.preventDefault();
    if ($('#newReglement').html() != '') {
      if (confirm('Voulez-vous remplacer le contenu de la zone de règlement en cours ?')) {
        sendFormToReglementDiv($(this));
      }
    } else {
      sendFormToReglementDiv($(this));
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations fermeture actions non terminées

  //close button zone newCS
  $('body').on("click", "#cleanNewCS", function(e) {
    $('#nouvelleCs').html('');
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations spécifiques aux lignes de l'historique  (dont modal)

  //sélectionner un groupe dans l'historique

  $("#historiqueTypeSelect button").on("click", function(e) {
    e.preventDefault();
    groupe = $(this).attr('data-groupe');
    //boutons
    $("#historiqueTypeSelect button").removeClass('active');
    $(this).addClass('active');

    //lignes
    $('.historiqueMedicalComplet tr.detObjet').hide();
    if (groupe != 'tous') {
      $('.historiqueMedicalComplet tr.trLigneExamen').hide();
      $('.historiqueMedicalComplet tr[data-groupe="' + groupe + '"]').show();
    } 
    else {
      $('.historiqueMedicalComplet tr.trLigneExamen').show();

    }

  });

  //toogle importance d'une ligne
  $("a.toogleImportant").on("click", function(e) {
    e.preventDefault();
    toogleImportant($(this));
  });

  //supprimer une ligne de l'historique
  $("a.suppCs").on("click", function(e) {
    e.preventDefault();
    if (confirm('Confirmez-vous la demande de suppression ?')) {
      suppCs($(this));
    }
  });

  //modal compléter titre
  $('#alternatTitreModal').on('show.bs.modal', function(event) {
    var button = $(event.relatedTarget)
    titreActu = button.closest('tr').attr('data-alternatTitre');
    objetID = button.closest('tr').attr('data-objetID');
    $('#alternatTitreModal #titreActu').val(titreActu);
    $('#alternatTitreModal #objetID').val(objetID);
  })
  $('.trLigneExamen td').on('dblclick', function() {
    $('#alternatTitreModal').modal('show');
    titreActu = $(this).closest('tr').attr('data-alternatTitre');
    objetID = $(this).closest('tr').attr('data-objetID');
    $('#alternatTitreModal #titreActu').val(titreActu);
    $('#alternatTitreModal #objetID').val(objetID);
  });
  $('#alternatTitreModal').on('shown.bs.modal', function() {
    $('#alternatTitreModal #titreActu').focus();
  })
  $('#alternatTitreModalSubmit').on("click", function(e) {
    modalAlternateTitreChange();
  });

  $('#alternatTitreModal #titreActu').keypress(function(e) {
    var key = e.which;
    if (key == 13) {
      modalAlternateTitreChange();
      return false;
    }
  });

  //voir le détail sur un ligne: clic sur titre ou pour document, clic sur oeil
  $(' .trLigneExamen td:nth-child(3), a.showDetDoc').on('click', function(e) {
    showObjetDet($(this));
    e.preventDefault();
  });


});

////////////////////////////////////////////////////////////////////////
///////// Fonctions spécifiques à la sauvegarde automatique

//fonction pour la sauvegarde automatique
function setPeopleData(value, patientID, typeID, source, instance) {
  //alert(patientID);
  if (patientID && typeID && source) {
    $.ajax({
      url: '/ajax/setPeopleData/',
      type: 'post',
      data: {
        value: value,
        patientID: patientID,
        typeID: typeID,
        instance: instance
      },
      dataType: "json",
      success: function(data) {
        el = $(source);
        el.css("background", "#efffe8");
        setTimeout(function() {
          el.css("background", "");
        }, 700);
      },
      error: function() {
        //alert('Problème, rechargez la page !');
      }
    });
  }
}

////////////////////////////////////////////////////////////////////////
///////// Fonctions DICOM

function prepareEcho() {

  $.ajax({
    url: '/patient/ajax/prepareEcho/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "html",
    success: function(data) {

    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

function catchLastDicomSrData() {
  $.ajax({
    url: '/patient/ajax/catchLastDicomSrData/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "json",
    success: function(data) {
      if (data['find'] == 1) {
        mapDicomSRData2CurrentForm(data['data']);
        addDicomSRInfo2CurrentForm(data['dicom']);
      }
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

function mapDicomSRData2CurrentForm(data) {
  jQuery.each(data, function(index, item) {
    $('#p_' + index + 'ID').val(item);
    $('#p_' + index + 'ID').trigger("keyup");
  });
}

function addDicomSRInfo2CurrentForm(data) {
  $('#nouvelleCs form.newCS').append('<input type="hidden" name="p_433" value="' + data['study'] + '" />');
  $('#nouvelleCs form.newCS').append('<input type="hidden" name="p_434" value="' + data['serie'] + '" />');
  $('#nouvelleCs form.newCS').append('<input type="hidden" name="p_435" value="' + data['instance'] + '" />');

}

////////////////////////////////////////////////////////////////////////
///////// Fonctions spécifiques à l'injection de données dans la page

//envoyer le form de CS dans le div CS
function sendFormToCsDiv(el) {
  $.ajax({
    url: '/patient/ajax/extractCsForm/',
    type: 'post',
    data: {
      formID: el.attr('data-formtocall'),
      csID: el.attr('data-csID'),
      patientID: $('#identitePatient').attr("data-patientID"),
      objetID: el.attr('data-objetID'),
      parentID: el.attr('data-parentID'),
      prevalues: el.attr('data-prevalues'),
      mode: el.attr('data-mode')
    },
    dataType: "html",
    success: function(data) {
      $('#nouvelleCs').html(data);
      $.getScriptOnce("../js/module/formsScripts/" + el.attr('data-formtocall') + ".js");
      afficherFxNbFoetus();
      scrollTo('body');
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

//envoyer le form de Courrier dans le div newCourrier
function sendFormToCourrierDiv(el) {
  $.ajax({
    url: '/patient/ajax/extractCourrierForm/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
      objetID: el.attr('data-objetID'),
      modeleID: el.attr('data-modeleID'),
    },
    dataType: "html",
    success: function(data) {
      $('#newCourrier').html(data);
      $.getScriptOnce("../js/patientScripts/print.js");

      tinymce.init({
        selector: '#editeurCourrier',
        height: "500"
      });
      scrollTo('body');
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

//envoyer le form new Ordo dans le div Ordo
function sendFormToOrdoDiv(el) {
  if (el.hasClass('editOrdo')) {
    objetID = el.closest('tr').attr('data-objetID');
  } else {
    objetID = null;
  }

  $.ajax({
    url: '/patient/ajax/extractOrdoForm/',
    type: 'post',
    data: {
      objetID: objetID,
      patientID: $('#identitePatient').attr("data-patientID"),
      parentID: '',
    },
    dataType: "html",
    success: function(data) {
      $('#newOrdo').html(data);
      $.getScriptOnce("../js/patientScripts/ordonnance.js");
      scrollTo('body');
      if (typeof(autoGrowOrdo) != "undefined") {if($.isFunction(autoGrowOrdo)) autoGrowOrdo();}
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

//envoyer le form newMail dans le div newMail
function sendFormToMailDiv(el) {
  $.ajax({
    url: '/patient/ajax/extractMailForm/',
    type: 'post',
    data: {
      formID: el.attr('data-formtocall'),
      patientID: $('#identitePatient').attr("data-patientID"),
      objetID: el.attr('data-objetID'),
      mailType: el.attr('data-mailtype'),
    },
    dataType: "html",
    success: function(data) {
      $('#newMail').html(data);
      $.getScriptOnce("../js/patientScripts/email.js");
      scrollTo('body');
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

//envoyer le form new Reglement dans le div Reglement
function sendFormToReglementDiv(el) {
  if (el.hasClass('editReglement')) {
    objetID = el.closest('tr').attr('data-objetID');
  } else {
    objetID = null;
  }

  $.ajax({
    url: '/patient/ajax/extractReglementForm/',
    type: 'post',
    data: {
      objetID: objetID,
      patientID: $('#identitePatient').attr("data-patientID"),
      parentID: '',
    },
    dataType: "html",
    success: function(data) {
      $('#newReglement').html(data);
      $.getScriptOnce("../js/patientScripts/reglement.js");
      scrollTo('body');
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

////////////////////////////////////////////////////////////////////////
/////////Fonctions spécifiques aux lignes de l'historique  (dont modal)

//supprimer une ligne de l'historique
function suppCs(el) {
  objetID = el.attr('data-objetID');

  $.ajax({
    url: '/patient/ajax/suppCs/',
    type: 'post',
    data: {
      objetID: objetID
    },
    dataType: "html",
    success: function() {
      $('.tr' + objetID).hide();
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

// toggle importance d'une ligne
function toogleImportant(el) {
  importanceActu = el.attr('data-importanceActu');
  objetID = el.attr('data-objetID');

  $.ajax({
    url: '/patient/ajax/importanceCsToogle/',
    type: 'post',
    data: {
      importanceActu: importanceActu,
      objetID: objetID
    },
    dataType: "html",
    success: function() {
      if (importanceActu == 'n') {
        el.html('N\'est plus important');
        el.attr('data-importanceActu', 'y');
        $('.icoImportant' + objetID).html('<span class="glyphicon glyphicon-flash" aria-hidden="true"></span>');
      }
      if (importanceActu == 'y') {
        $('.icoImportant' + objetID).html('');
        el.html('Rendre important');
        el.attr('data-importanceActu', 'n');
      }
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

// compléter le titre avec la modal
function modalAlternateTitreChange() {
  titreActu = $('#alternatTitreModal #titreActu').val();
  objetID = $('#alternatTitreModal #objetID').val();

  $.ajax({
    url: '/patient/ajax/completerTitreCs/',
    type: 'post',
    data: {
      titre: titreActu,
      objetID: objetID
    },
    dataType: "html",
    success: function() {
      $('.alternatTitre' + objetID).html(' : ' + titreActu);
      $('#alternatTitreModal').modal('toggle');
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

// voir le détail d'une ligne d'historique
function showObjetDet(element) {
  zone = element.closest('table').attr('data-zone');
  objetID = element.closest('tr').attr('data-objetID');
  ligne = element.closest('tr');
  destination = $("." + zone + " .detObjet" + objetID);

  if (destination.length == 0) {

    $.ajax({
      url: '/patient/ajax/ObjetDet/',
      type: 'post',
      data: {
        objetID: objetID,
      },
      dataType: "html",
      success: function(data) {
        ligne.after('<tr class="detObjet' + objetID + ' detObjet" style="background : transparent">' + data + '</tr>');
      },
      error: function() {
        alert('Problème, rechargez la page !');
      }
    });

  } else {
    destination.toggle();
  }

}
