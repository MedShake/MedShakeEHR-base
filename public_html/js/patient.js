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
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

 ////////////////////////////////////////////////////////////////////////
 ///////// Définition des variables par défaut

if (!scrollDestination) {
  var scrollDestination = {
    newDoc: '#newDoc',
    nouvelleCs: '#nouvelleCs',
    newCourrier: '#newCourrier',
    newOrdo: '#newOrdo',
    newMail: '#newMail',
    newReglement: '#newReglement',
    delai: 400
  };
}

if (!scriptsList) {
  var scriptsList = {
    ordonnance: "ordonnance.js",
    print: "print.js",
    docupload: "docupload.js",
    email: "email.js",
    reglement: "reglement.js"
  };
}
 
$(document).ready(function() {

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

  //modal liste dicom studies
  $('.catchOthersDicomSrData').on('click', function(e) {
    e.preventDefault();
    listePatientDicomStudies();
  })

  //modal liste dicom studies submit
  $('#listeDicomStudiesSubmit').on('click', function(e) {
    e.preventDefault();
    catchOtherDicomSrData();
  })

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
    $.getScriptOnce(urlBase + "/js/patientScripts/" + scriptsList.docupload);
    scrollTo(scrollDestination.newDoc, scrollDestination.delai);
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
    $(window).unbind("beforeunload");
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations spécifiques aux lignes de l'historique  (dont modal)

  //sélectionner un groupe dans l'historique

  $("body").on("click", "#historiqueTypeSelect button", function(e) {
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

  ////////////////////////////////////////////////////////////////////////
  // gestion des historiques et courbes de poids/taille/imc 

  $(".graph-print").on("click", function(){
  });

  $(".duplicate").parent().on("click", function(){
    setPeopleData($('input[name=p_poids]').val(), $('#identitePatient').attr("data-patientID"), $('input[name=p_poids]').attr("data-typeID"), 'input[name=p_poids]', '0');
    setPeopleData($('input[name=p_taillePatient]').val(), $('#identitePatient').attr("data-patientID"), $('input[name=p_taillePatient]').attr("data-typeID"), 'input[name=p_taillePatient]', '0');
  });
  $(".duplicate").parent().attr("title", "Reporter une mesure identique").css("cursor","pointer");
  $(".graph").parent().attr("title", "Voir l'historique").css("cursor","pointer");

  //modal Courbes de poids/taille/IMC
  $(".graph").parent().on("click", function(){
    $(".histo-suppr").remove();
    $.ajax({
      url: urlBase+'/patient/ajax/getGraphData/',
      type: 'post',
      data: {
        patientID: $('#identitePatient').attr("data-patientID")
      },
      dataType: "html",
      success: function(data) {
        if (data == 'ok')
            return;
        data = JSON.parse(data);
        for (var i in data)
          if (i!='bornes')
            $(".histo").append('\
              <tr class="histo-suppr">\
                <td style="text-align:center">' + (i < 3*365.25 ? ((i * 24 / 365.25) >> 1) + ' mois' : ((i * 2 / 365.25) >> 1) + ' ans') + '</td>\
                <td style="text-align:center">' + moment(data[i].date, "YYYY-MM-DD HH:mm:ss").format("DD/MM/YYYY") + '</td>\
                <td style="text-align:center;color:' + (data[i].poids.reel ? 'black' : 'grey') + '">' + data[i].poids.value + '</td>\
                <td style="text-align:center;color:' + (data[i].taille.reel ? 'black' : 'grey') + '">' + data[i].taille.value + '</td>\
                <td style="text-align:center;color:' + (data[i].imc.reel ? 'black' : 'grey') + '">' + data[i].imc.value + '</td>\
              </tr>');
        drawGraph(data);
        $('#viewGraph').modal('show');
      },
      error: function() {
      }
    });
  });


  //voir le détail sur un ligne: clic sur titre ou pour document, clic sur oeil
  $("body").on('click', '.trLigneExamen td:nth-child(3), a.showDetDoc', function(e) {
    showObjetDet($(this));
    e.preventDefault();
  });

  //fermeture modal data admin patient
  $("button.modalAdminClose").on("click", function(e) {
    ajaxModalPatientAdminCloseAndRefreshHeader();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Voir les notes sur le patient
  $('body').on("click", "#voirNotesPatient", function(e) {
    e.preventDefault();
    $('#notesPatient').toggle();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Changer la date de création d'une ligne d'historique

  // datepicker bootstrap
  $('#datepickHisto').datetimepicker({
    locale: 'fr',
    format: 'Y-MM-DD HH:mm:ss',
    sideBySide: true
  });

  $("body").on("click", ".changeCreationDate", function(e) {
    e.preventDefault();
    objetID = $(this).closest('tr').attr('data-objetID');
    registerDate = $(this).closest('tr').attr('data-registerDate');
    creationDate = $(this).closest('tr').attr('data-creationDate');

    $("#modalCreationDate input[name='objetID']").val(objetID);
    $("#modalRegisterDateDisplay").html(registerDate);
    $("#modalCreationDateDisplay").html(creationDate);
    $("#modalCreationDate input[name='newCreationDate']").val(creationDate);
    $("#modalCreationDate").modal('show');
  });

  $("body").on("click", ".modalCreationDateClose", function(e) {
    e.preventDefault();
    $('#formNewCreationDate').submit();
  });


  ////////////////////////////////////////////////////////////////////////
  ///////// Envoyer les formulaires et recharger l'historique

  //enregistrement de forms en ajax
  $('body').on('click', "form input[type=submit],button[type=submit]", function(e) {
    e.preventDefault();
    $(window).unbind("beforeunload");
    $(this).closest(".toclear").html("");
    $.ajax({
      url: $(this).parents("form").attr("action"),
      type: 'post',
      data: $(this).parents("form").serialize(),
      dataType: "html",
      success: function(data) {
        $("#historique .trLigneExamen").before(data);
        $("#historiqueToday tbody").prepend(data);
      },
      error: function() {
        $(".submit-error").animate({top: "50px"},300,"easeInOutCubic", function(){setTimeout((function(){$(".submit-error").animate({top:"0"},300)}), 4000)});
      }
    });
  });


});

////////////////////////////////////////////////////////////////////////
///////// Fonctions DICOM

function listePatientDicomStudies() {
  $.ajax({
    url: urlBase + '/patient/ajax/listPatientDicomStudies/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "json",
    success: function(data) {
      $('#listeDicomStudiesModal #listeDicomStudies').html('');
      $.each(data, function(index, item) {
        str = '<option value="' + item['ID'] + '">Examen du ' + moment(item['Datetime']).format('DD-MM-YYYY');
        if (item['ehr']) str = str + ' - ' + item['ehr']['label'];
        str = str + '</option>';
        $('#listeDicomStudiesModal #listeDicomStudies').append(str);

      });
      $('#listeDicomStudiesModal').modal('show');
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

function prepareEcho() {

  $.ajax({
    url: urlBase + '/patient/ajax/prepareEcho/',
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
    url: urlBase + '/patient/ajax/catchLastDicomSrData/',
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

function catchOtherDicomSrData() {
  $.ajax({
    url: urlBase + '/patient/ajax/catchLastDicomSrData/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
      studyID: $('#listeDicomStudies').val()
    },
    dataType: "json",
    success: function(data) {
      if (data['find'] == 1) {
        mapDicomSRData2CurrentForm(data['data']);
        addDicomSRInfo2CurrentForm(data['dicom']);
      }
      $('#listeDicomStudiesModal').modal('toggle');
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

function mapDicomSRData2CurrentForm(data) {
  jQuery.each(data, function(index, item) {
    $('input[data-typeID="' + index + '"]').val(item);
    $('input[data-typeID="' + index + '"]').trigger("keyup");
  });
}

function addDicomSRInfo2CurrentForm(data) {
  $('#nouvelleCs form').append('<input type="hidden" name="p_dicomStudyID" value="' + data['study'] + '" />');
  $('#nouvelleCs form').append('<input type="hidden" name="p_dicomSerieID" value="' + data['serie'] + '" />');
  $('#nouvelleCs form').append('<input type="hidden" name="p_dicomInstanceID" value="' + data['instance'] + '" />');

}

////////////////////////////////////////////////////////////////////////
///////// Fonctions spécifiques à l'injection de données dans la page

//envoyer le form de CS dans le div CS
function sendFormToCsDiv(el) {
  $.ajax({
    url: urlBase + '/patient/ajax/extractCsForm/',
    type: 'post',
    data: {
      formIN: el.attr('data-formtocall'),
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
      $.getScriptOnce(urlBase + "/js/module/formsScripts/" + el.attr('data-formtocall') + ".js");
      scrollTo(scrollDestination.nouvelleCs, scrollDestination.delai);
      // pour éviter de perdre des données
      if (el.attr('data-mode')!='copy')
        $(window).on("beforeunload", preventDataLoss);
      $('form').submit(function() {
        $(window).unbind("beforeunload");
      });

    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

function preventDataLoss(e) {
  e.returnValue = "\o/";
  return "\o/";
}

//envoyer le form de Courrier dans le div newCourrier
function sendFormToCourrierDiv(el) {
  $.ajax({
    url: urlBase + '/patient/ajax/extractCourrierForm/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
      objetID: el.attr('data-objetID'),
      modeleID: el.attr('data-modeleID'),
    },
    dataType: "html",
    success: function(data) {
      $('#newCourrier').html(data);
      $.getScriptOnce(urlBase + "/js/patientScripts/" + scriptsList.print);

      tinymce.init({
        selector: '#editeurCourrier',
        height: "500"
      });
      scrollTo(scrollDestination.newCourrier, scrollDestination.delai);
      // pour éviter de perdre des données
      $(window).on("beforeunload", preventDataLoss);
      $('form').submit(function() {
        $(window).unbind("beforeunload");
      });
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

//envoyer le form new Ordo dans le div Ordo
function sendFormToOrdoDiv(el) {

  $.ajax({
    url: urlBase + '/patient/ajax/extractOrdoForm/',
    type: 'post',
    data: {
      objetID: el.hasClass('editOrdo') ? el.closest('tr').attr('data-objetID') : null,
      patientID: $('#identitePatient').attr("data-patientID"),
      parentID: '',
      ordoForm: el.attr('data-ordoForm'),
      porteur: el.hasClass('editOrdo') ? el.closest('tr').attr('data-porteur') : el.attr('data-porteur'),
      module: el.hasClass('editOrdo') ? el.closest('tr').attr('data-module') : el.attr('data-module')
    },
    dataType: "html",
    success: function(data) {
      $('#newOrdo').html(data);
      $.getScriptOnce(urlBase + "/js/patientScripts/" + scriptsList.ordonnance);
      scrollTo(scrollDestination.newOrdo, scrollDestination.delai);
      if (typeof(autoGrowOrdo) != "undefined") {
        if ($.isFunction(autoGrowOrdo)) autoGrowOrdo();
      }
      $(window).on("beforeunload", preventDataLoss);
      $('form').submit(function() {
        $(window).unbind("beforeunload");
      });
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

//envoyer le form newMail dans le div newMail
function sendFormToMailDiv(el) {
  $.ajax({
    url: urlBase + '/patient/ajax/extractMailForm/',
    type: 'post',
    data: {
      formIN: el.attr('data-formtocall'),
      patientID: $('#identitePatient').attr("data-patientID"),
      objetID: el.attr('data-objetID'),
      mailType: el.attr('data-mailtype'),
    },
    dataType: "html",
    success: function(data) {
      $('#newMail').html(data);
      $.getScriptOnce(urlBase + "/js/patientScripts/" + scriptsList.email);
      scrollTo(scrollDestination.newMail, scrollDestination.delai);
      $(window).on("beforeunload", preventDataLoss);
      $('form').submit(function() {
        $(window).unbind("beforeunload");
      });
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

//envoyer le form new Reglement dans le div Reglement
function sendFormToReglementDiv(el) {
  $.ajax({
    url: urlBase + '/patient/ajax/extractReglementForm/',
    type: 'post',
    data: {
      objetID: el.hasClass('editReglement') ? el.closest('tr').attr('data-objetID') : null,
      patientID: $('#identitePatient').attr("data-patientID"),
      reglementForm: el.attr('data-reglementForm'),
      porteur: el.attr('data-porteur'),
      module: el.hasClass('editReglement') ? el.closest('tr').attr('data-module') : el.attr('data-module'),
      parentID: '',
    },
    dataType: "html",
    success: function(data) {
      $('#newReglement').html(data);
      $.getScriptOnce(urlBase + "/js/patientScripts/" + scriptsList.reglement);
      scrollTo(scrollDestination.newReglement, scrollDestination.delai);
      $(window).on("beforeunload", preventDataLoss);
      $('form').submit(function() {
        $(window).unbind("beforeunload");
      });
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
    url: urlBase + '/patient/ajax/suppCs/',
    type: 'post',
    data: {
      objetID: objetID
    },
    dataType: "html",
    success: function() {
      $('.tr' + objetID).remove();
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
    url: urlBase + '/patient/ajax/importanceCsToogle/',
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
    url: urlBase + '/patient/ajax/completerTitreCs/',
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
      url: urlBase + '/patient/ajax/ObjetDet/',
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

// rafraichier le header du dossier patient (infos administratives)
function ajaxModalPatientAdminCloseAndRefreshHeader() {
  $.ajax({
    url: urlBase+'/patient/ajax/refreshHeaderPatientAdminData/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID")
    },
    dataType: "html",
    success: function(data) {
      $('#identitePatient').html(data);
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}

function drawGraph(data) {
  var canvas;
  var ctx;
  var Xmax=parseInt(data.bornes.Xmax);
  if (Xmax<3*365.25) {
    canvas = $(".graph-poids").get(0);
    ctx = canvas.getContext("2d");
    drawGraphPoidsNourisson(data, ctx);
    canvas = $(".graph-taille").get(0);
    ctx = canvas.getContext("2d");
    drawGraphTailleNourisson(data, ctx);
    canvas = $(".graph-imc").get(0);
    ctx = canvas.getContext("2d");
    if ($('#identitePatient').attr("data-genre")=="F")
      drawGraphIMCFille(data, ctx);
    else
      drawGraphIMCGarcon(data, ctx);
  } else if (Xmax<18*365.25) {
    if ($('#identitePatient').attr("data-genre")=="F") {
      canvas = $(".graph-poids").get(0);
      ctx = canvas.getContext("2d");
      drawGraphPoidsFille(data, ctx);
      canvas = $(".graph-taille").get(0);
      ctx = canvas.getContext("2d");
      drawGraphTailleFille(data, ctx);
      canvas = $(".graph-imc").get(0);
      ctx = canvas.getContext("2d");
      drawGraphIMCFille(data, ctx);
    }
    else {
      canvas = $(".graph-poids").get(0);
      ctx = canvas.getContext("2d");
      drawGraphPoidsGarcon(data, ctx);
      canvas = $(".graph-taille").get(0);
      ctx = canvas.getContext("2d");
      drawGraphTailleGarcon(data, ctx);
      canvas = $(".graph-imc").get(0);
      ctx = canvas.getContext("2d");
      drawGraphIMCGarcon(data, ctx);
    }
  } else {
    canvas = $(".graph-poids").get(0);
    ctx = canvas.getContext("2d");
    drawGraphGeneral(data, 'poids', ctx, canvas);
    canvas = $(".graph-taille").get(0);
    ctx = canvas.getContext("2d");
    drawGraphGeneral(data, 'taille', ctx, canvas);
    canvas = $(".graph-imc").get(0);
    ctx = canvas.getContext("2d");
    drawGraphGeneral(data, 'imc', ctx, canvas);
  }
}

function drawGraphPoidsNourisson(data, ctx) {
  $(".graph-poids")
  .attr("width", "610")
  .attr("height", "790")
  .css("background-image", "url(/img/poids_nourissons.svg)")
  .css("background-size", "cover");
  drawDots(18, 567/(3*365.25), 756/22, 0, 22, ctx, 'poids', data);
}
function drawGraphTailleNourisson(data, ctx) {
  $(".graph-taille")
  .attr("width", "614")
  .attr("height", "532")
  .css("background-image", "url(/img/taille_nourissons.svg)")
  .css("background-size", "cover");
  drawDots(22, 567/(3*365.25), 500/85, 0, 115, ctx, 'taille', data);
}
function drawGraphPoidsGarcon(data, ctx) {
  $(".graph-poids")
  .attr("width", "596")
  .attr("height", "615")
  .css("background-image", "url(/img/poids_garcons.svg)")
  .css("background-size", "cover");
  drawDots(16, 567/(18*365.25), 596/110, 0, 110, ctx, 'poids', data);
}
function drawGraphTailleGarcon(data, ctx) {
  $(".graph-taille")
  .attr("width", "602")
  .attr("height", "831")
  .css("background-image", "url(/img/taille_garcons.svg)")
  .css("background-size", "cover");
  drawDots(22, 567/(18*365.25), 812/150, 0, 200, ctx, 'taille', data);
}
function drawGraphIMCGarcon(data, ctx) {
  $(".graph-imc")
  .attr("width", "622")
  .attr("height", "861")
  .css("background-image", "url(/img/IMC_garcons.svg)")
  .css("background-size", "cover");
  drawDots(31, 567/(18*365.25), 843/25, 0, 35, ctx, 'imc', data);
}
function drawGraphPoidsFille(data, ctx) {
  $(".graph-poids")
  .attr("width", "608")
  .attr("height", "614")
  .css("background-image", "url(/img/poids_filles.svg)")
  .css("background-size", "cover");
  drawDots(19, 567/(18*365.25), 596/110, 0, 110, ctx, 'poids', data);
}
function drawGraphTailleFille(data, ctx) {
  $(".graph-taille")
  .attr("width", "611")
  .attr("height", "831")
  .css("background-image", "url(/img/taille_filles.svg)")
  .css("background-size", "cover");
  drawDots(23, 567/(18*365.25), 812/150, 0, 200, ctx, 'taille', data);
}
function drawGraphIMCFille(data, ctx) {
  $(".graph-imc")
  .attr("width", "619")
  .attr("height", "861")
  .css("background-image", "url(/img/IMC_filles.svg)")
  .css("background-size", "cover");
  drawDots(29, 567/(18*365.25), 843/25, 0, 35, ctx, 'imc', data);
}

function drawGraphGeneral(data, sel, ctx, canvas) {
  $(".graph-"+sel).css("background", "none");
  var Xmin = Math.floor(parseInt(data.bornes.Xmin) / 365.25);
  var Xmax = Math.ceil(parseInt(data.bornes.Xmax) / 365.25);
  var Ymin = (sel == 'imc' ? 1 : 5) * (Math.floor(parseFloat(data.bornes.Ymin[sel]) / (sel == 'imc' ? 1 : 5)) - 1);
  var Ymax = (sel == 'imc' ? 1 : 5) * (Math.ceil(parseFloat(data.bornes.Ymax[sel]) / (sel == 'imc' ? 1 : 5)) + 1);
  var marge = 22;
  var scaleX = (canvas.width - marge) / (Xmax - Xmin);
  var scaleY = (canvas.height - marge) / (Ymax - Ymin);
  canvas.width=canvas.width; //effacement du canvas
  ctx.strokeStyle = "#a9c0a6";
  ctx.fillStyle = "#8b5ba5";
  ctx.lineWidth = 1;
  ctx.beginPath();
  //dessin des graduations X
  var inc = (Xmax - Xmin) < 2 ? 0.25 : (Xmax - Xmin) < 4 ? 0.5 : 1;
  for (var i = Xmin; i < Xmax; i+=inc) {
    ctx.moveTo((i - Xmin) * scaleX + marge, 0);
    ctx.lineTo((i - Xmin) * scaleX + marge, canvas.height - marge + 2);
    ctx.fillText(i % 1 ? '+' + (12 * (i%1)) + 'mois' : i , (i - Xmin) * scaleX + marge - (i % 1 ? 20 : 5), canvas.height - 5);
  }
  ctx.moveTo(canvas.width - 1, 0);
  ctx.lineTo(canvas.width - 1, canvas.height - marge + 2);
  ctx.fillText("ans", canvas.width - 20, canvas.height - 5);
  //détermination de la précision des graduations Y
  inc = sel == 'poids' ? 5 : 1;
  //dessin des graduations Y
  for (var i = Ymin; i < Ymax; i+=inc) {
    ctx.moveTo(marge-3, (Ymax - i) * scaleY + 1);
    ctx.lineTo(canvas.width, (Ymax - i) * scaleY + 1);
    ctx.fillText(i, 2, (Ymax - i) * scaleY + 5);
  }
  ctx.moveTo(marge - 3, 0);
  ctx.lineTo(canvas.width, 0);
  ctx.fillText(sel == 'poids' ? 'kg' : sel == 'taille' ? "cm" : "kg/m²", 0, 10);
  ctx.stroke();
  drawDots(marge, scaleX / 365.25, scaleY, Xmin*365.25, Ymax, ctx, sel, data);
}

function drawDots(margeX, scaleX, scaleY, Xmin, Ymax, ctx, sel, data) {
  var rayon = 3;
  for (var i in data) {
    if (i == 'bornes')
      continue;
    var Xpos = margeX + (parseInt(i) - Xmin) * scaleX;
    var Ypos = (Ymax - data[i][sel].value) * scaleY;
    ctx.fillStyle = data[i][sel].reel ? "green" : "grey";
    ctx.beginPath();
    ctx.moveTo(rayon + Xpos, Ypos);
    ctx.arc(Xpos, Ypos, rayon, 0, 2 * Math.PI);
    ctx.fill();
  }

}
