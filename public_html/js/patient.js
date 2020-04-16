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

var goToDicom = false;

$(document).ready(function() {

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations pour saut entre tabs

  // rafraichir historique au retour dossier med
  $('#ongletDossierMedical').on("show.bs.tab", function() {
    getHistoriqueToday();
    getHistorique();
  });

  //chargement/mise à jour tab Biométrie
  $("#ongletGraph").on("click", function() {
    getGraph();
    getGraphCardio();
  });

  // 1er chargement tab dicom
  $('#ongletDicom').on("show.bs.tab", function() {
    if ($('#tabDicom').html() == '') {
      var url = $('#tabDicom').attr('data-rootUrl');
      loadTabPatient(url, 'tabDicom');
    }
  });

  // refresh tabs dicom
  $('body').on("click", "button.tabDicomRefresh", function() {
    var url = $('#tabDicom').attr('data-rootUrl');
    loadTabPatient(url, 'tabDicom');
  });

  $('#tabDicom').on("click", "#listeExamens tr.viewStudy", function() {
    $.getScriptOnce(urlBase + "/js/dicom.js");
    var url = '/patient/' + $('#identitePatient').attr("data-patientID") + '/tab/tabDicomStudyView/';
    var param = {
      'dcStudyID': $(this).attr('data-study')
    };
    loadTabPatient(url, 'tabDicom', param);
  });

  // 1er chargement tab relations patient
  $('#ongletLiensPatient').on("show.bs.tab", function() {
    $.getScriptOnce(urlBase + "/js/relations.js");
    if ($('#tabLiensPatient').html() == '') {
      var url = $('#tabLiensPatient').attr('data-rootUrl');
      loadTabPatient(url, 'tabLiensPatient');
    }
  });

  // 1er chargement tab Bio
  $('#ongletBio').on("show.bs.tab", function() {
    if ($('#tabBio').html() == '') {
      var url = $('#tabBio').attr('data-rootUrl');
      loadTabPatient(url, 'tabBio');
    }
  });

  function loadTabPatient(url, tab, param) {
    $.ajax({
      url: urlBase + url,
      type: 'post',
      data: {
        tab: tab,
        param: param
      },
      dataType: "html",
      success: function(data) {
        $('#' + tab).html(data);
      },
      error: function() {
        alert("Problème, rechargez la page !");
      }
    });
  }

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations pour sauvegarde automatique des champs modifiés

  activeWatchChange('.changeObserv');
  activeWatchChange('.changeObservByTypeName');

  $(".changeObserv .datepick, .changeObservByTypeName .datepick").on("dp.change", function(e) {
    patientID = $('#identitePatient').attr("data-patientID");
    typeID = $(this).children('input').attr("data-typeID");
    if (e.date) value = e.date.format('L');
    else value = '';
    source = $(this).children('input');
    instance = $(this).closest("form").attr("data-instance");
    setPeopleData(value, patientID, typeID, source, instance);
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations touch pour vue mobile
  if ($('.swipable').length) {
    $('.swipable').swipe({
      triggerOnTouchEnd: true,
      swipeStatus: function(event, phase, direction, distance) {
        if (!$('.swipable').hasClass('swipableon'))
          return;
        if (phase == 'move') {
          if (direction == 'left' && $('.swipable').hasClass('swipable-left')) {
            $('.swipable').css('overflow-y', 'hidden');
            $('.atcd').css('left', -distance);
            $('.dossier').show().css('left', $(window).width() - distance).css('top', -$('.atcd')[0].offsetHeight);
          } else if (direction == 'right' && $('.swipable').hasClass('swipable-right')) {
            $('.swipable').css('overflow-y', 'hidden');
            $('.atcd').show().css('left', -$(window).width() + distance);
            $('.dossier').css('left', distance).css('top', -$('.atcd')[0].offsetHeight);
          }
        } else if (phase == 'cancel') {
          if (direction == 'left' && $('.swipable').hasClass('swipable-left')) {
            $('.atcd').animate({
              left: 0
            }, 400);
            $('.dossier').animate({
              left: $(window).width()
            }, 400, function() {
              $('.dossier').hide().css('top', 0);
            });
          } else if (direction == 'right' && $('.swipable').hasClass('swipable-right')) {
            $('.atcd').animate({
              left: -$(window).width()
            }, 400, function() {
              $('.atcd').hide();
              $('.swipable').css('overflow-y', 'auto')
            });
            $('.dossier').animate({
              left: 0
            }, 400, function() {
              $('.dossier').css('top', 0)
            });
          }
        } else if (phase == 'end') {
          if (direction == 'left' && $('.swipable').hasClass('swipable-left')) {
            $('.atcd').animate({
              left: -$(window).width()
            }, 400, function() {
              $('.atcd').hide();
              $('.swipable').removeClass('swipable-left').addClass('swipable-right');
              $('.swipable').css('overflow-y', 'auto')
            });
            $('.dossier').animate({
              left: 0
            }, 400, function() {
              $('.dossier').css('top', 0)
            });
          } else if (direction == 'right' && $('.swipable').hasClass('swipable-right')) {
            $('.atcd').animate({
              left: 0
            }, 400);
            $('.dossier').animate({
              left: $(window).width()
            }, 400, function() {
              $('.dossier').hide().css('top', 0);
              $('.swipable').removeClass('swipable-right').addClass('swipable-left');
              $('.swipable').css('overflow-y', 'auto')
            });
          }
        }
      },
      allowPageScroll: 'vertical',
      preventDefaultEvents: false,
      threshold: 100
    });
    if (window.innerWidth < 768) {
      $('.swipable').addClass('swipableon swipable-right');
      $('.atcd').hide();
      $('.dossier').show();
    } else {
      $('.atcd').show();
      $('.dossier').show();
    };
    $(window).on("resize", function() {
      if (window.innerWidth < 768) {
        if (!$('.swipable').hasClass('swipableon')) {
          $('.swipable').addClass('swipableon swipable-right');
          $('.atcd').hide();
          $('.dossier').show();
        }
      } else {
        $('.swipable').removeClass('swipableon').removeClass('swipable-right').removeClass('swipable-left');
        $('.atcd').show().css('left', '');
        $('.dossier').show().css('left', '').css('top', '');
      }
    });
  }
  ////////////////////////////////////////////////////////////////////////
  ///////// Observations Tab Biologie

  $('#tabBio').on('click', 'a.bioDateSelect, button.bioDateSelect', function(e) {
    e.preventDefault();
    var url = $('#tabBio').attr('data-rootUrl');
    var param = {
      'dateBio': $(this).attr('data-dateBio')
    };
    loadTabPatient(url, 'tabBio', param);
  });

  $('#tabBio').on('change', 'select.bioDateSelect', function(e) {
    var url = $('#tabBio').attr('data-rootUrl');
    var param = {
      'dateBio': $(this).val()
    };
    loadTabPatient(url, 'tabBio', param);
  });

  $('#tabBio').on('click', '#accordionDocs div.card-header', function(e) {
    objetID = $(this).attr('data-objetid');
    destination = $("#collapse" + objetID + " div.card-body");
    if (destination.html() == '') {
      $.ajax({
        url: urlBase + '/patient/ajax/getFilePreviewDocument/',
        type: 'get',
        data: {
          objetID: objetID,
        },
        dataType: "html",
        success: function(data) {
          destination.html(data)
        },
        error: function() {
          destination.remove();
          alert_popup("danger", 'Problème, rechargez la page !');
        }
      });
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations DICOM
  $("body").on("click", ".prepareEcho", function(e) {
    e.preventDefault();
    prepareEcho();
  });
  if (typeof(dicomAutoSendPatient) != "undefined") {
    if (dicomAutoSendPatient == true) {
      prepareEcho('nopopup');
    }
  }

  $(".catchLastDicomSrData").on("click", function(e) {
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
  // prépare la réception de documents par phonecapture
  $("body").on("click", ".prepareReceptionDoc", function(e) {
    e.preventDefault();
    goToDicom = $(this).hasClass('dicom') ? true : false;
    $.ajax({
      url: urlBase + '/patient/ajax/' + ($(this).hasClass('dicom') ? 'prepareEcho/' : 'prepareReceptionDoc/'),
      type: 'post',
      data: {
        patientID: $('#identitePatient').attr("data-patientID"),
      },
      dataType: "html",
      success: function(data) {
        $("#patientPhonecapture").modal('show');
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  $("#patientPhonecapture #patientPhonecaptureEndButton").on("click", function() {
    $('#patientPhonecapture').modal('toggle');
    if (goToDicom) {
      $('#ongletDicom')[0].click();
      if ($('#tabDicom').html() != '') {
        var url = $('#tabDicom').attr('data-rootUrl');
        loadTabPatient(url, 'tabDicom');
      }
      return;
    }
    getHistorique();
    getHistoriqueToday();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations déclenchement actions d'injections dans la page

  //bouton de nouvelle consultation
  $("body").on("click", ".addNewCS, .editCS", function(e) {
    e.preventDefault();

    if($(this).attr('data-targetdiv')) {
      targetDiv = $('#'+ $(this).attr('data-targetdiv'));
    } else {
      targetDiv = $('#nouvelleCs');
    }

    if (targetDiv.html() != '') {
      if (confirm('Voulez-vous remplacer le contenu de la consultation en cours ?')) {
        sendFormToCsDiv($(this));
      }
    } else {
      sendFormToCsDiv($(this))
    }
  });

  //bouton de nouveau courrier
  $("body").on("click", ".newCourrier", function(e) {
    e.preventDefault();
    $('#ongletDossierMedical').tab('show');
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
  $('body').on("click", ".addNewOrdo, .editOrdo", function(e) {
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
  $(".linkAddNewDoc, .cleanNewDocImport").on("click", function(e) {
    e.preventDefault();
    $('#newDoc').toggle();
    $.getScriptOnce(urlBase + "/js/patientScripts/" + scriptsList.docupload);
    scrollTo(scrollDestination.newDoc, scrollDestination.delai);
  });

  //bouton de nouveau mail
  $('body').on("click", ".newMail", function(e) {
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
  $('body').on("click", ".addNewReglement, .editReglement", function(e) {
    e.preventDefault();
    if ($('#newReglement').html() != '') {
      if (confirm('Voulez-vous remplacer le contenu de la zone de règlement en cours ?')) {
        sendFormToReglementDiv($(this));
      }
    } else {
      sendFormToReglementDiv($(this));
    }
  });

  // bouton de nouvelle transmission
  $('body').on("click", ".newTransmission", function(e) {
    e.preventDefault();

    if ($(this).parents('tr').attr('data-creationDate')) {
      $('#transSujet').val("Pièce du dossier patient");
      datepiece = $(this).parents('tr').attr('data-creationDate');
      datepiece = moment(datepiece).format('DD/MM/YYYY HH:mm');
      texte = "Voir la ligne de l'historique \"" + $(this).parents('td').next('td').text() + '" du ' + datepiece;
      texte = texte.replace(/\r?\n|\r/g, "");
      texte = texte.replace(/  +/g, ' ');
      $('#transTransmission').val(texte);
    }
    $('#transConcerne').addClass('d-none');
    $('#transPatientConcID').val($('#identitePatient').attr('data-patientid'));
    $('#transPatientConcSel').html($('#identitePatient').attr('data-patientIdentite'));
    $('#transPatientConcSel').removeClass('d-none');

    $('#modalTransmission').modal('show');
  });
  // poster une transmission
  $('body').on("click", "#transmissionEnvoyer", function(e) {
    e.preventDefault();
    transmissionNewNextLocation = 'stayHere';
    posterTransmission();
  });

  // bouton de nouvelle FSE
  $('body').on("click", ".newFse", function(e) {
    e.preventDefault();
    getFseData($(this));
    $('#modalFaireFse').modal('show');
  });

  // bouton de validation FSE
  $('body').on("click", "#modalFaireFseValider", function(e) {
    e.preventDefault();
    doFse($(this));
    //$('#modalFaireFse').modal('hide');
  });

  // bouton de fin FSE
  $('body').on("click", "#modalFaireFseTerminer", function(e) {
    e.preventDefault();
    $('#modalFaireFse').modal('hide');
    $('#modalFaireFseTerminer').addClass('d-none');
    $('#modalFaireFseValider').removeClass('d-none');
    $('#modalFaireFse div.modal-body').html(originalModalBody);
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations fermeture actions non terminées

  //close button zone newCS
  $('body').on("click", "#cleanNewCS, .addNewCS, .cleanNewCS", function(e) {
    $(this).parents('div.nouvelleCs').html('');
    $('#nouvelleCs').html('');
    $(window).unbind("beforeunload");
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations spécifiques aux lignes de l'historique  (dont modal)

  // contraction d'une année
  $("body").on("click", ".anneeHistorique", function() {
    setTimeout((function($el) {
      if ($el.hasClass('collapsed'))
        $el.find('.fa-minus-square').hide() && $el.find('.fa-plus-square').show();
      else
        $el.find('.fa-minus-square').show() && $el.find('.fa-plus-square').hide()
    }), 200, $(this));
  });

  //sélectionner un groupe dans l'historique
  $("body").on("click change", "#historiqueTypeSelect button, #historiqueTypeSelect option", function(e) {
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
    } else {
      $('.historiqueMedicalComplet tr.trLigneExamen').show();

    }

  });

  //toogle importance d'une ligne
  $('body').on("click", ".toggleImportant", function(e) {
    e.preventDefault();
    toogleImportant($(this));
  });

  //supprimer une ligne de l'historique
  $("body").on("click", ".suppCs", function(e) {
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
  $('body').on('dblclick', '.trLigneExamen td:nth-child(4)', function() {
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
  $("body").on('click', '.trLigneExamen', function(e) {
    if (!$(e.target).hasClass('dropdown-item') && !$(e.target).hasClass('btn') && !$(e.target.parentNode).hasClass('btn')) {
      e.preventDefault();
      showObjetDet($(this));
    }
  });

  $("body").on('click', '.showDetDoc', function(e) {
    e.preventDefault();
    showObjetDet($(this));
  });

  // réduire la taille d'une image pour aperçu dans historique
  $("body").on("click", "button.reduceImagePreviewSize", function(e) {
    reduceImagePreviewSize($(this))
  });

  // rotation (définitive) d'une image via aperçu historiques
  $("body").on("click", ".rotationImage90", function(e) {
    rotateImage90($(this));
  });

  // voir étude dicom correspondant à l'examen
  $('#tabDossierMedical').on("click", "a.viewStudy", function(e) {
    e.preventDefault();
    $.getScriptOnce(urlBase + "/js/dicom.js");
    var url = '/patient/' + $('#identitePatient').attr("data-patientID") + '/tab/tabDicomStudyView/';
    var param = {
      'dcStudyID': $(this).attr('data-study')
    };
    $('#ongletDicom').tab('show');
    loadTabPatient(url, 'tabDicom', param);
  });

  // envoyer à la signature
  $('#tabDossierMedical').on("click", "a.sendSign", function(e) {
    e.preventDefault();
    source = $(this);
    $.ajax({
      url: urlBase + '/patients/ajax/patientsSendSign/',
      type: 'post',
      data: {
        patientID: $('#identitePatient').attr("data-patientID"),
        typeID: $(this).attr('data-csID'),
        signPeriphName: signPeriphName,
        objetID: $(this).parents('tr').attr('data-objetID'),
        fromID: $(this).attr('data-fromID')
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

  ////////////////////////////////////////////////////////////////////////
  // gestion des historiques et courbes de poids/taille/imc

  $(".graph-print").on("click", function() {});

  $(".duplicate").parent().on("click", function() {
    var $input = $(this).closest(".input-group").children('input');
    setPeopleData($input.val(), $('#identitePatient').attr("data-patientID"), $input.attr("data-typeID"), $input, '0');
  });
  $(".duplicate").parent().attr("title", "Reporter une mesure identique").css("cursor", "pointer");
  $(".graph").parent().attr("title", "Voir l'historique").css("cursor", "pointer");

  //stupide table pour classer tableau de la modal
  $("table.histo").on("aftertablesort", function(event, data) {
    th = $(this).find("th");
    th.find(".arrow").remove();
    dir = $.fn.stupidtable.dir;
    arrow = data.direction === dir.ASC ? "fa-chevron-up" : "fa-chevron-down";
    th.eq(data.column).append(' <span class="arrow fa ' + arrow + '"></span>');
  });

  //choix de l'année pour les data cardio
  $('#tabGraph').on("change", "#selectAnneeHistoBiometrieCardio", function() {
    year = $("#selectAnneeHistoBiometrieCardio option:selected").text();
    getGraphCardio(year);
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Observations diverses dont celles concernant la partie identité patient

  //Editer relation patient
  $('body').on("click", "button.editerRelationsPatient", function(e) {
    e.preventDefault();
    $('#ongletLiensPatient').tab('show');
  });

  //changer les infos admin patient en modal
  $('body').on("click", "#editAdmin button.modal-save", function(e) {
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    ajaxModalSave(form, modal, function() {
      $(modal).modal('hide');
      ajaxModalPatientAdminCloseAndRefreshHeader();
    });
  });

  //Ouvrir le LAP
  $('body').on("click", ".openLAP", function(e) {
    e.preventDefault();
    $('#ongletLAP').tab('show');
  });

  // Observation ctrl + click pour historique
  $('body').on("click", "textarea, input, select", function(e) {
    if (e.shiftKey) {
      e.preventDefault();
      patientID = $('#identitePatient').attr('data-patientid');
      instance = $(this).parents('form').attr('data-instance');
      dataType = $(this).attr('data-internalName');
      window.location.href = urlBase + '/logs/historique/' + patientID + '/' + dataType + '/' + instance + '/';
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Changer la date de création d'une ligne d'historique

  // datepicker bootstrap
  $('#datepickHisto')
    .on("click", function() {
      $(this).data("DateTimePicker").toggle();
    })
    .datetimepicker({
      locale: 'fr',
      format: 'Y-MM-DD HH:mm:ss',
      sideBySide: true,
      icons: {
        time: 'far fa-clock',
        date: 'fa fa-calendar',
        up: 'fa fa-chevron-up',
        down: 'fa fa-chevron-down',
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-crosshairs',
        clear: 'fa fa-trash',
        close: 'fa fa-times'
      }
    });

  $('body').on('dblclick', '.trLigneExamen td:nth-child(2)', function(e) {
    e.preventDefault();
    e.stopPropagation();
    groupe = $(this).closest('tr').attr('data-groupe');
    if (groupe == 'typecs' || groupe == 'reglement') changeCreationDate($(this));
  });
  $("body").on("click", ".changeCreationDate", function(e) {
    e.preventDefault();
    e.stopPropagation();
    changeCreationDate($(this));
  });

  $("body").on("click", ".modalCreationDateClose", function(e) {
    e.preventDefault();
    $.ajax({
      url: $("#formNewCreationDate").attr("action"),
      type: 'post',
      data: $("#formNewCreationDate").serialize(),
      dataType: "html",
      success: function(data) {
        if (data.indexOf("Erreur:") == 0) {
          $("#errormessage").html(data);
          alert_popup("danger", data);
        } else if (data.indexOf("Avertissement:") == 0) {
          alert_popup("warning", data);
        } else {
          getHistorique();
          getHistoriqueToday();
        }
      },
      error: function() {
        alert_popup("danger", "Une erreur s'est produite durant l'enregistrement des données");
      }
    });
  });


  ////////////////////////////////////////////////////////////////////////
  ///////// Envoyer les formulaires et recharger l'historique

  //enregistrement de forms en ajax
  $('body').on('click', "#tabDossierMedical form input[type=submit], #tabDossierMedical button[type=submit]", function(e) {

    $('#tabDossierMedical .is-invalid').removeClass('is-invalid');
    if ($(this).closest("form").attr("action").indexOf('/actions/') >= 0) {
      return;
    };
    e.preventDefault();
    var stop = false;
    $(this).closest("form").find('input[required],textarea[required]').each(function(idx, el) {
      if (el.value == '') {
        glow('danger', $(el));
        stop = true;
      }
    });
    if (stop) {
      alert_popup("warning", "Certains champs requis n'ont pas été remplis.");
      return;
    }
    $(window).unbind("beforeunload");
    var form = $(this).closest("form");
    var objetid = form.find("input[name=objetID]").val();
    $.ajax({
      url: form.attr("action"),
      type: 'post',
      data: form.serialize(),
      dataType: "json",
      success: function(data) {

        if (data.statut == "erreur") {
          $('div.alert ul').html('');
          $.each(data.msg, function(index, value) {
            $(' div.alert ul').append('<li>' + value + '</li>');
          });
          $.each(data.code, function(index, value) {
            $('#tabDossierMedical *[name="' + value + '"]').addClass('is-invalid');
          });
          $('div.alert').removeClass('d-none');
          scrollTo('body', 2);
          return;
        }

        // on remet au propre
        form.closest(".toclear").html("");
        $('div.alert').addClass('d-none');

        // on recharge la colonne lat
        getLatCol();

        // on agit sur historiques
        if (!data.html.length || form.hasClass('ignoreReturn')) {
          return;
        } else if (data.statut == "avertissement") {
          alert_popup("warning", data.msg);
        } else if (data.statut == "ok-fullrefresh") {
          getHistoriqueToday();
          getHistorique();
          scrollTo('body', 2);
        } else if (data.statut == "ok") {
          var $tr = $("#historique .anneeHistorique:nth-child(1)");
          if ($tr.length && $tr.children("td:nth-child(2)").html().substr(8, 4) == moment().format("YYYY")) {
            $tr.find('.fa-minus-square').show();
            $tr.find('.fa-plus-square').hide();
            $($tr.attr('data-target')).collapse('show');
            var $l = $("#historique tr.tr" + objetid);
            if (objetid && $l.length)
              $l.replaceWith(data.html);
            else
              $tr.after(data.html);
          } else {
            $('#historique tbody').prepend('<tr class="anneeHistorique table-primary" data-toggle="collapse" data-target=".historiqueMedicalComplet .trLigneExamen[data-annee=' + moment().format("YYYY") + ']" aria-expanded="true" aria-controls="annee' + moment().format("YYYY") + '">\
              <td class="pl-3">\
                <span class="far fa-minus-square"></span>\
                <span class="far fa-plus-square" style="display:none"></span>\
              </td>\
              <td colspan="4"><strong>' + moment().format("YYYY") + '</strong></td>\
            </tr>' + data.html);
            refreshHistorique();
          }

          var $lt = $("#historiqueToday tr.tr" + objetid);
          if (objetid && $lt.length) {
            $lt.replaceWith(data.html);
          } else if (data.today == 'oui') {
            $('#historiqueToday tbody').prepend(data.html);
          }
          refreshHistoriqueToday();
          scrollTo('body', 2);

          // construire immédiatement le PDF si demandé dans les options du formulaire
          if (data.buildPdfNow) {
            buildPdfForObjet(data.objetID);
          }
        }
      },
      error: function() {
        alert_popup("danger", "Une erreur s'est produite durant l'enregistrement des données.");
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
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

function prepareEcho(mode) {

  $.ajax({
    url: urlBase + '/patient/ajax/prepareEcho/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "html",
    success: function(data) {
      if (mode != 'nopopup') alert_popup("success", 'L\'appareil d\'imagerie est maintenant correctement configuré');
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

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
      alert_popup("danger", 'Problème, rechargez la page !');

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
      alert_popup("danger", 'Problème, rechargez la page !');

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
  //destruction préventive lignes de détails historiques
  if (el.attr('data-objetID') > 0) $('tr.detObjet' + el.attr('data-objetID')).remove();

  if(el.attr('data-targetdiv')) {
    targetDiv = $('#'+ el.attr('data-targetdiv'));
  } else {
    targetDiv = $('#nouvelleCs');
  }

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
      targetDiv.html(data);
      $.getScriptOnce(urlBase + "/js/module/formsScripts/" + el.attr('data-formtocall') + ".js");
      scrollTo(scrollDestination.nouvelleCs, scrollDestination.delai);
      // pour éviter de perdre des données
      if (el.attr('data-mode') != 'view')
        $(window).on("beforeunload", preventDataLoss);
      $('form').submit(function() {
        $(window).unbind("beforeunload");
      });

    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

function preventDataLoss(e) {
  e.returnValue = "\o/";
  return "\o/";
}

//envoyer le form de Courrier dans le div newCourrier
function sendFormToCourrierDiv(el) {
  //destruction préventive lignes de détails historiques
  if (el.attr('data-objetID') > 0) $('tr.detObjet' + el.attr('data-objetID')).remove();

  $.ajax({
    url: urlBase + '/patient/ajax/extractCourrierForm/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
      objetID: el.attr('data-objetID'),
      modele: el.attr('data-modele'),
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
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

//envoyer le form new Ordo dans le div Ordo
function sendFormToOrdoDiv(el) {
  //destruction préventive lignes de détails historiques
  if (el.hasClass('editOrdo')) $('tr.detObjet' + el.closest('tr').attr('data-objetID')).remove();

  $.ajax({
    url: urlBase + '/patient/ajax/extractOrdoForm/',
    type: 'post',
    data: {
      asUserID: el.hasClass('editOrdo') && el.closest('tr').attr('data-asuserid') != undefined ? el.closest('tr').attr('data-asuserid') : null,
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
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

//envoyer le form newMail dans le div newMail
function sendFormToMailDiv(el) {
  //destruction préventive lignes de détails historiques
  if (el.attr('data-objetID') > 0) $('tr.detObjet' + el.attr('data-objetID')).remove();

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
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

//envoyer le form new Reglement dans le div Reglement
function sendFormToReglementDiv(el) {
  //destruction préventive lignes de détails historiques
  if (el.hasClass('editReglement')) $('tr.detObjet' + el.closest('tr').attr('data-objetID')).remove();

  $.ajax({
    url: urlBase + '/patient/ajax/extractReglementForm/',
    type: 'post',
    data: {
      asUserID: el.attr('data-asuserid') != undefined ? el.attr('data-asUserID') : null,
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
      if ($.isArray(scriptsList.reglement)) {
        $.each(scriptsList.reglement, function(index, value) {
          $.getScriptOnce(urlBase + "/js/patientScripts/" + value);
        });
      } else {
        $.getScriptOnce(urlBase + "/js/patientScripts/" + scriptsList.reglement);
      }
      if (el.hasClass('editReglement')) {
        //reinjection pour édition
        $(".regleTarifCejour").attr('data-tarifdefaut', $(".regleTarifCejour").val());
        $(".regleDepaCejour").attr('data-tarifdefaut', $(".regleDepaCejour").val());
        // Non car change param antérieur ... à revoir
        //setTimeout(function() { searchAndInsertActeData($("#newReglement select.selectActeStarter option[selected='selected']").parent('select')); }, 500);
      }
      scrollTo(scrollDestination.newReglement, scrollDestination.delai);
      $(window).on("beforeunload", preventDataLoss);
      $('form').submit(function() {
        $(window).unbind("beforeunload");
      });
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

////////////////////////////////////////////////////////////////////////
/////////Fonctions spécifiques aux header dossier patient

/**
 * Rafraichir le header du dossier patient
 * @return {void}
 */
function ajaxModalPatientAdminCloseAndRefreshHeader() {
  $.ajax({
    url: urlBase + '/patient/ajax/refreshHeaderPatientAdminData/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID")
    },
    dataType: "html",
    success: function(data) {
      $('#identitePatient').html(data);
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

///////////////////////////////////////////
/////// fonctions relatives à la biométrie

function getGraphCardio(year) {
  if (!year) year = (new Date()).getFullYear();
  $.ajax({
    url: urlBase + '/patient/ajax/getGraphDataCardio/',
    type: 'post',
    data: {
      year: year,
      patientID: $('#identitePatient').attr("data-patientID")
    },
    dataType: "json",
    success: function(data) {
      $('#biometrieCardio').html(data.html);
    },
    error: function() {}
  });
}

function getGraph() {
  $(".histo-suppr").remove();
  $.ajax({
    url: urlBase + '/patient/ajax/getGraphData/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID")
    },
    dataType: "html",
    success: function(data) {
      if (data == 'ok')
        return;
      else if (data.substr(0, 7) == 'Erreur:')
        return alert_popup('danger', data.substr(8));
      data = JSON.parse(data);
      for (var i in data) {
        if (i != 'bornes') {
          $("table.histo tbody").append('\
            <tr class="histo-suppr">\
              <td data-sort-value="' + i + '" style="text-align:center">' + (i < 3 * 365.25 ? ((i * 24 / 365.25) >> 1) + ' mois' : ((i * 2 / 365.25) >> 1) + ' ans') + '</td>\
              <td style="text-align:center" data-sort-value="' + moment(data[i].date, "YYYY-MM-DD HH:mm:ss").format("YYYYMMDD") + '">' + moment(data[i].date, "YYYY-MM-DD HH:mm:ss").format("DD/MM/YYYY") + '</td>\
              <td style="text-align:center;color:' + (data[i].poids.reel ? 'black' : 'grey') + '">' + data[i].poids.value + '</td>\
              <td style="text-align:center;color:' + (data[i].taille.reel ? 'black' : 'grey') + '">' + data[i].taille.value + '</td>\
              <td style="text-align:center;color:' + (data[i].imc.reel ? 'black' : 'grey') + '">' + data[i].imc.value + '</td>\
            </tr>');
        }
      }
      $("table.histo").stupidtable();
      drawGraph(data);
    },
    error: function() {}
  });
}

function drawGraph(data) {
  var canvas;
  var ctx;
  var Xmax = parseInt(data.bornes.Xmax);
  if (Xmax < 3 * 365.25) {
    canvas = $(".graph-poids")[0];
    ctx = canvas.getContext("2d");
    drawGraphPoidsNourisson(data, ctx);
    canvas = $(".graph-taille")[0];
    ctx = canvas.getContext("2d");
    drawGraphTailleNourisson(data, ctx);
    canvas = $(".graph-imc")[0];
    ctx = canvas.getContext("2d");
    if ($('#identitePatient').attr("data-genre") == "F")
      drawGraphIMCFille(data, ctx);
    else
      drawGraphIMCGarcon(data, ctx);
  } else if (Xmax < 18 * 365.25) {
    if ($('#identitePatient').attr("data-genre") == "F") {
      canvas = $(".graph-poids")[0];
      ctx = canvas.getContext("2d");
      drawGraphPoidsFille(data, ctx);
      canvas = $(".graph-taille")[0];
      ctx = canvas.getContext("2d");
      drawGraphTailleFille(data, ctx);
      canvas = $(".graph-imc")[0];
      ctx = canvas.getContext("2d");
      drawGraphIMCFille(data, ctx);
    } else {
      canvas = $(".graph-poids")[0];
      ctx = canvas.getContext("2d");
      drawGraphPoidsGarcon(data, ctx);
      canvas = $(".graph-taille")[0];
      ctx = canvas.getContext("2d");
      drawGraphTailleGarcon(data, ctx);
      canvas = $(".graph-imc")[0];
      ctx = canvas.getContext("2d");
      drawGraphIMCGarcon(data, ctx);
    }
  } else {
    canvas = $(".graph-poids")[0];
    ctx = canvas.getContext("2d");
    drawGraphGeneral(data, 'poids', ctx, canvas);
    canvas = $(".graph-taille")[0];
    ctx = canvas.getContext("2d");
    drawGraphGeneral(data, 'taille', ctx, canvas);
    canvas = $(".graph-imc")[0];
    ctx = canvas.getContext("2d");
    drawGraphGeneral(data, 'imc', ctx, canvas);
  }
}

function drawGraphPoidsNourisson(data, ctx) {
  $(".graph-poids")
    .attr("width", "610")
    .attr("height", "790")
    .css("background-image", "url(" + urlBase + "/img/poids_nourissons.svg)")
    .css("background-size", "cover");
  drawDots(18, 567 / (3 * 365.25), 756 / 22, 0, 22, ctx, 'poids', data);
}

function drawGraphTailleNourisson(data, ctx) {
  $(".graph-taille")
    .attr("width", "614")
    .attr("height", "532")
    .css("background-image", "url(" + urlBase + "/img/taille_nourissons.svg)")
    .css("background-size", "cover");
  drawDots(22, 567 / (3 * 365.25), 500 / 85, 0, 115, ctx, 'taille', data);
}

function drawGraphPoidsGarcon(data, ctx) {
  $(".graph-poids")
    .attr("width", "596")
    .attr("height", "615")
    .css("background-image", "url(" + urlBase + "/img/poids_garcons.svg)")
    .css("background-size", "cover");
  drawDots(16, 567 / (18 * 365.25), 596 / 110, 0, 110, ctx, 'poids', data);
}

function drawGraphTailleGarcon(data, ctx) {
  $(".graph-taille")
    .attr("width", "602")
    .attr("height", "831")
    .css("background-image", "url(" + urlBase + "/img/taille_garcons.svg)")
    .css("background-size", "cover");
  drawDots(22, 567 / (18 * 365.25), 812 / 150, 0, 200, ctx, 'taille', data);
}

function drawGraphIMCGarcon(data, ctx) {
  $(".graph-imc")
    .attr("width", "622")
    .attr("height", "861")
    .css("background-image", "url(" + urlBase + "/img/IMC_garcons.svg)")
    .css("background-size", "cover");
  drawDots(31, 567 / (18 * 365.25), 843 / 25, 0, 35, ctx, 'imc', data);
}

function drawGraphPoidsFille(data, ctx) {
  $(".graph-poids")
    .attr("width", "608")
    .attr("height", "614")
    .css("background-image", "url(" + urlBase + "/img/poids_filles.svg)")
    .css("background-size", "cover");
  drawDots(19, 567 / (18 * 365.25), 596 / 110, 0, 110, ctx, 'poids', data);
}

function drawGraphTailleFille(data, ctx) {
  $(".graph-taille")
    .attr("width", "611")
    .attr("height", "831")
    .css("background-image", "url(" + urlBase + "/img/taille_filles.svg)")
    .css("background-size", "cover");
  drawDots(23, 567 / (18 * 365.25), 812 / 150, 0, 200, ctx, 'taille', data);
}

function drawGraphIMCFille(data, ctx) {
  $(".graph-imc")
    .attr("width", "619")
    .attr("height", "861")
    .css("background-image", "url(" + urlBase + "/img/IMC_filles.svg)")
    .css("background-size", "cover");
  drawDots(29, 567 / (18 * 365.25), 843 / 25, 0, 35, ctx, 'imc', data);
}

function drawGraphGeneral(data, sel, ctx, canvas) {
  $(".graph-" + sel).css("background", "none");
  var Xmin = Math.floor(parseInt(data.bornes.Xmin) / 365.25);
  var Xmax = Math.ceil(parseInt(data.bornes.Xmax) / 365.25);
  var Ymin = (sel == 'imc' ? 1 : 5) * (Math.floor(parseFloat(data.bornes.Ymin[sel]) / (sel == 'imc' ? 1 : 5)) - 1);
  var Ymax = (sel == 'imc' ? 1 : 5) * (Math.ceil(parseFloat(data.bornes.Ymax[sel]) / (sel == 'imc' ? 1 : 5)) + 1);
  var marge = 22;
  var scaleX = (canvas.width - marge) / (Xmax - Xmin);
  var scaleY = (canvas.height - marge) / (Ymax - Ymin);
  canvas.width = canvas.width; //effacement du canvas
  ctx.strokeStyle = "#a9c0a6";
  ctx.fillStyle = "#8b5ba5";
  ctx.lineWidth = 1;
  ctx.beginPath();
  //dessin des graduations X
  var inc = (Xmax - Xmin) < 2 ? 0.25 : (Xmax - Xmin) < 4 ? 0.5 : 1;
  for (var i = Xmin; i < Xmax; i += inc) {
    ctx.moveTo((i - Xmin) * scaleX + marge, 0);
    ctx.lineTo((i - Xmin) * scaleX + marge, canvas.height - marge + 2);
    ctx.fillText(i % 1 ? '+' + (12 * (i % 1)) + 'mois' : i, (i - Xmin) * scaleX + marge - (i % 1 ? 20 : 5), canvas.height - 5);
  }
  ctx.moveTo(canvas.width - 1, 0);
  ctx.lineTo(canvas.width - 1, canvas.height - marge + 2);
  ctx.fillText("ans", canvas.width - 20, canvas.height - 5);
  //détermination de la précision des graduations Y
  inc = sel == 'poids' ? 5 : 1;
  //dessin des graduations Y
  for (var i = Ymin; i < Ymax; i += inc) {
    ctx.moveTo(marge - 3, (Ymax - i) * scaleY + 1);
    ctx.lineTo(canvas.width, (Ymax - i) * scaleY + 1);
    ctx.fillText(i, 2, (Ymax - i) * scaleY + 5);
  }
  ctx.moveTo(marge - 3, 0);
  ctx.lineTo(canvas.width, 0);
  ctx.fillText(sel == 'poids' ? 'kg' : sel == 'taille' ? "cm" : "kg/m²", 0, 10);
  ctx.stroke();
  drawDots(marge, scaleX / 365.25, scaleY, Xmin * 365.25, Ymax, ctx, sel, data);
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

////////////////////////////////////////////////////////////////////////
///////// Fonctions relatives aux historiques

/**
 * Supprimer un élément de l'historique
 * @param  {object} el objet jquery cliqué
 * @return {void}
 */
function suppCs(el) {
  objetID = el.attr('data-objetID');

  $.ajax({
    url: urlBase + '/patient/ajax/suppCs/',
    type: 'post',
    data: {
      objetID: objetID
    },
    dataType: "json",
    success: function() {
      $('.tr' + objetID).remove();
      refreshHistorique();
      refreshHistoriqueToday();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

/**
 * Permuter l'importance d'une ligne d'historique
 * @param  {object} el objet jquery cliqué
 * @return {void}
 */
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
        el.html('<i class="fas fa-exclamation-triangle fa-fw text-muted mr-1"></i> Rendre non important');
        el.attr('data-importanceActu', 'y');
        el.closest('tr').addClass(el.closest('tr').hasClass('trReglement') ? 'table-danger' : 'table-info');
      }
      if (importanceActu == 'y') {
        el.closest('tr').removeClass('table-info').removeClass('table-danger');
        el.html('<i class="fas fa-exclamation-triangle fa-fw text-muted mr-1"></i> Marquer important');
        el.attr('data-importanceActu', 'n');
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

/**
 * Compléter le titre de la ligne d'historique via modal
 * @return {void}
 */
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
      if (titreActu.length > 0) {
        $('.alternatTitre' + objetID).html(' : ' + titreActu);
      } else {
        $('.alternatTitre' + objetID).html('');
      }
      $('.alternatTitre' + objetID).parents('tr.trLigneExamen').attr('data-alternatTitre', titreActu);
      $('#alternatTitreModal').modal('toggle');
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

/**
 * Changer la date de création d'une ligne d'historique
 * @param  {object} $el objet jquery cliqué
 * @return {void}
 */
function changeCreationDate($el) {
  var objetID = $el.closest('tr').attr('data-objetID');
  var registerDate = $el.closest('tr').attr('data-registerDate');
  var creationDate = $el.closest('tr').attr('data-creationDate');

  $("#modalCreationDate input[name='objetID']").val(objetID);
  $("#modalRegisterDateDisplay").html(registerDate);
  $("#modalCreationDateDisplay").html(creationDate);
  $("#modalCreationDate input[name='newCreationDate']").val(creationDate);
  $("#modalCreationDate").modal('show');
}

/**
 * Voir les détails d'une ligne d'historique
 * @param  {object} element objet jquery cliqué (ligne d'historique)
 * @param  {[type]} timed   [description]
 * @return {void}
 */
var objDetTimer;

function showObjetDet(element, timed) {
  if (objDetTimer == undefined) {
    objDetTimer = setTimeout(showObjetDet, 200, element, true);
    return;
  }
  clearTimeout(objDetTimer);
  objDetTimer = undefined;
  if (timed == undefined) {
    return;
  }
  zone = element.closest('table').attr('data-zone');
  objetID = element.closest('tr').attr('data-objetID');
  ligne = element.closest('tr');
  destination = $("." + zone + " .detObjet" + objetID);

  if (destination.length == 0) {
    if (element.closest('tr').attr('data-typeName') == 'lapOrdonnance') {
      ligne.after('<tr class="detObjet' + objetID + ' detObjet" style="background : transparent"><td></td><td colspan="4" class="placeForOrdoLap py-4"><div class="text-right"><button class="btn btn-secondary btn-sm renouvToutesLignes mb-1" type="button" title="Renouveler"><i class="fas fa-sync-alt" aria-hidden="true"></i> Tout renouveler</button></div><div class="alert alert-primary font-weight-bold" role="alert">Prescriptions ALD</div><div class="ald conteneurPrescriptionsALD"></div><div class="alert alert-dark font-weight-bold" role="alert">Prescriptions standards</div><div style="min-height:15px;" class="conteneurPrescriptionsG"></div></td></tr>');
      voirOrdonnanceMode = 'voirOrdonnance';
      getOrdonnance(objetID, "." + zone + " .detObjet" + objetID + ' td.placeForOrdoLap');
    } else {
      ligne.after('<tr class="detObjet' + objetID + ' detObjet" style="background : transparent"></tr>');
      destination = $("." + zone + " .detObjet" + objetID);

      $.ajax({
        url: urlBase + '/patient/ajax/ObjetDet/',
        type: 'get',
        data: {
          objetID: objetID,
        },
        dataType: "html",
        success: function(data) {
          destination.html(data);
        },
        error: function() {
          destination.remove();
          alert_popup("danger", 'Problème, rechargez la page !');
        }
      });
    }
  } else {
    destination.toggle();
  }

}

/**
 * Réduction à w-50 d'une image dans apercu lignes d'historiques
 * @param  {object} el objet cliqué
 * @return {void}
 */
function reduceImagePreviewSize(el) {
  cible = $('#' + el.attr('data-cible'));
  if (cible.hasClass('w-50')) {
    cible.removeClass('w-50');
    el.find('i').addClass('fa-search-minus').removeClass('fa-search-plus');
  } else {
    cible.addClass('w-50');
    el.find('i').addClass('fa-search-plus').removeClass('fa-search-minus');
  }
}

/**
 * Rotation d'une image de 90° à droite ou à gauche
 * @param  {object} el objet cliqué
 * @return {void}
 */
function rotateImage90(el) {
  fichierID = el.attr('data-doc');
  cible = $('#' + el.attr('data-cible'));
  direction = el.attr('data-direction');
  el.find('i').addClass('fa-spin');
  $.ajax({
    url: urlBase + '/patient/ajax/rotateDoc/',
    type: 'post',
    data: {
      fichierID: fichierID,
      direction: direction,
    },
    dataType: "html",
    success: function(data) {
      d = new Date();

      cible.attr("src", cible.attr('src') + "?" + d.getTime());
      el.find('i').removeClass('fa-spin');
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

/**
 * Rafraichir l'historique complet
 * @return {void}
 */
function refreshHistorique() {
  if (!$('.historiqueMedicalComplet .trLigneExamen').length) {
    $('.historiqueMedicalComplet').hide();
    $('.noticeDossierPatientVide').removeClass('d-none');
    return;
  }
  $('.historiqueMedicalComplet').show();
  $('.noticeDossierPatientVide').addClass('d-none');
}

/**
 * Obtenir l'historique (complet)
 * @return {void}
 */
function getHistorique() {
  $.ajax({
    url: urlBase + '/patient/ajax/getHistorique/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "html",
    success: function(data) {
      $("#historique").html(data);
      refreshHistorique();
    },
    error: function() {}
  });
}

/**
 * Rafrachir historique du jour
 * @return {void}
 */
function refreshHistoriqueToday() {
  if (!$('.historiqueToday .trLigneExamen').length)
    $('.historiqueToday').hide();
  else
    $('.historiqueToday').show();
}

/**
 * Obtenir l'historique du jour
 * @return {void}
 */
function getHistoriqueToday() {
  $.ajax({
    url: urlBase + '/patient/ajax/getHistoriqueToday/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "html",
    success: function(data) {
      $("#historiqueToday").html(data);
      refreshHistoriqueToday();
    },
    error: function() {}
  });
}

/**
 * Obtenir et rafraichir la colonne latérale
 * @return {void}
 */
function getLatCol() {
  $.ajax({
    url: urlBase + '/patient/ajax/refreshLatColPatientAtcdData/',
    type: 'post',
    data: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: "html",
    success: function(data) {
      $("#patientLatCol").html(data);
      activeWatchChange('#patientLatCol');
    },
    error: function() {}
  });
}

/**
 * Construire le PDF à partir de l'objetID
 * @param  {int} objetID objetID
 * @return {void}
 */
function buildPdfForObjet(objetID) {
  $.ajax({
    url: urlBase + '/print/ajax/buildPdfForObjet/',
    type: 'post',
    data: {
      objetID: objetID
    },
    dataType: "html",
    success: function() {},
    error: function() {}
  });
}

////////////////////////////////////////////////////////////////////////
///////// Fonctions relatives à la surveillance de changement dans
///////// formulaires pour enregistrement automatique

/**
 * Activer la surveillance des changements sur les input / textarea / select
 * enfants de l'élément et sauvegarder les nouvelles valeurs
 * @param  {string} parentTarget élément parent
 * @return {void}
 */
function activeWatchChange(parentTarget) {
  // frappe sur input ou textarea
  $(parentTarget + " input:not(.datepic), " + parentTarget + " textarea").typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: false,
    captureLength: 1,
    callback: function(value) {
      patientID = $('#identitePatient').attr("data-patientID");
      source = $(this);
      instance = $(this).closest("form").attr("data-instance");
      if ($(this).hasClass('changeObservByTypeName')) {
        typeName = $(this).attr("data-typeName");
        setPeopleDataByTypeName(value, patientID, typeName, source, instance);
      } else {
        typeID = $(this).attr("data-typeID");
        setPeopleData(value, patientID, typeID, source, instance);
      }
    }
  });
  // select
  $(parentTarget + " select").on("change", function(e) {
    patientID = $('#identitePatient').attr("data-patientID");
    typeID = $(this).attr("data-typeID");
    value = $(this).val();
    source = $(this);
    instance = $(this).closest("form").attr("data-instance");
    setPeopleData(value, patientID, typeID, source, instance);
  });
  // custom checkbox (type checkbox ou switch)
  $(parentTarget + " .custom-switch, " + parentTarget + " .custom-checkbox ").on("click", function(e) {
    patientID = $('#identitePatient').attr("data-patientID");
    inputSource = $(this).find('input');
    typeID = inputSource.attr("data-typeID");
    value = inputSource.prop('checked');
    source = $(this);
    instance = $(this).closest("form").attr("data-instance");
    setPeopleData(value, patientID, typeID, source, instance);
  });
}
