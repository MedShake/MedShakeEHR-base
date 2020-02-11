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
 * Fonctions JS pour la gestion d'agendas
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

////////////////////////////////////////////////////////////////////////
///////// Déclaration variables

var selected_patient;
var selected_period;
var selected_event;
var selected_action;
var calendar_mode = $('#calendar').attr('data-mode');

var targetMenuPOTD = '_blank';
var canRefreshEvents = true;

$(document).ready(function() {

  $('#smallCalendar').datepicker({
    numberOfMonths: [3, 4],
    stepMonths: 12,
    inline: true,
    onSelect: function(dateText, inst) {
      $('#calendar').fullCalendar('gotoDate', moment(dateText, "DD-MM-YYYY"));
      $('#smallCalendar').toggle();
    }
  });

  // Autosize pour textarea
  autosize($('#id_notes_id'));
  autosize($('#motif'));
  autosize($('#motifOff'));
  $('#creerNouveau').on('shown.bs.modal', function(e) {
    autosize.update($('#motif'));
    autosize.update($('#id_notes_id'));
  })
  $('#editerOff').on('shown.bs.modal', function(e) {
    autosize.update($('#motifOff'));
  })

  ////////////////////////////////////////////////////////////////////////
  ///////// Actions carte vitale

  //lire la carte vitale
  $('#lectureCpsVital').on("click", function(e) {
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
        if (calendar_mode != 'lateral') $('#creerNouveau').modal('hide');
        console.log(vitaleToEhrTypeName(data));
        $('#lectureCpsVitale div.modal-body').html(ehrTypeDataToHtml('prevenirDossierExistant'));
        $('#lectureCpsVitale').modal('show');
      },
      error: function() {
        alert_popup("danger", 'Essayez à nouveau !');
      }
    });
  });

  $('body').on("click", ".goToPatientFromVitaleData", function(e) {
    e.stopPropagation();
  });

  $('body').on("click", ".peopleVitale", function(e) {
    e.preventDefault();
    indexVitale = $(this).attr('data-indexVitale');

    dataVitale[indexVitale]['firstname'] = ucfirst(dataVitale[indexVitale]['firstname']);

    $.each(dataVitale[indexVitale], function(key, value) {
      $('#id_' + key + '_id').val(value);
    });
    $('#lectureCpsVitale').modal('hide');
    if (calendar_mode != 'lateral') $('#creerNouveau').modal('show');
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Définition des variables par défaut construction agenda

  /// cf config/agendas/agendaDefault.js

  ////////////////////////////////////////////////////////////////////////
  ////////// On oblige les ouvertures de lien de la navbar sup à ouvrir sur un target _blank

  $('#top-navbar a').attr('target', '_blank');

  ////////////////////////////////////////////////////////////////////////
  ///////// Construction agenda

  $('#calendar').fullCalendar({

    defaultView: 'agendaWeek',
    locale: 'fr',
    themeSystem: 'bootstrap4',
    hiddenDays: hiddenDays,
    customButtons: {
      smallCalendar: {
        click: function() {
          $('#smallCalendar').toggle();
        }
      },
      nextMonth: {
        click: function() {
          $('div.popover').popover('hide');
          $('#calendar').fullCalendar('incrementDate', moment.duration(1, 'months'));
        }
      },
      prevMonth: {
        click: function() {
          $('div.popover').popover('hide');
          $('#calendar').fullCalendar('incrementDate', moment.duration(-1, 'months'));
        }
      },
      synchronize: {
        click: function() {
          synchronizeEvents();
        }
      },
    },
    bootstrapFontAwesome: {
      smallCalendar: 'fa-calendar-alt',
      prevMonth: 'fa-angle-double-left',
      prev: 'fa-angle-left',
      synchronize: 'fa-sync-alt',
      next: 'fa-angle-right',
      nextMonth: 'fa-angle-double-right',
    },
    header: {
      left: 'smallCalendar prevMonth,prev,today,next,nextMonth synchronize',
      center: boutonsHeaderCenter,
      right: 'title'
    },
    defaultDate: dateToGo,
    minTime: minTime,
    maxTime: maxTime,
    firstDay: firstDay,
    slotDuration: slotDuration,
    weekNumbers: true,
    weekNumberTitle: 'S.',
    allDaySlot: false,
    allDayText: '-',
    longPressDelay: 300,
    selectable: true,
    unselectCancel: '.fc-deplacer-button,.fc-cloner-button, div.alert',
    slotLabelFormat: 'H:mm',
    slotLabelInterval: slotLabelInterval,
    nowIndicator: true,
    businessHours: businessHours,
    slotEventOverlap: false,
    contentHeight: 'auto',
    eventTextColor: eventTextColor,
    eventSources: eventSources,
    viewRender: viewRender,
    eventRender: function(event, element) {
      element.attr('data-eventid', event.id);
      if (event.rendering != 'background') {
        if (event.icon) {
          element.find(".fc-content").after("<div class='faicon d-flex h-100 align-items-center justify-content-center'><i class='fa fa-10x fa-" + event.icon + "'></i></div>");
        }
        if (selected_event && event.id == selected_event.id) {
          element.find(".fc-bg").addClass("selected");
        }
        element.popover({
          sanitizeFn: function(content) {
            return content
          },
          title: event.name || '',
          container: "body",
          placement: 'right',
          boundary: "viewport",
          html: true,
          content: (event.patientid == "0" ? "Fermé" : ""),
          template: '\
            <div class=\"popover\" role=\"tooltip\">\
              <h3 class=\"popover-header\">Détail</h3>\
              <div class=\"popover-body\"></div>\
              <div class=\"popover-footer btn-group m-1 d-none\">' +
            (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-enattente-button\" title=\"' + (event.attente == "oui" ? 'Marquer non présent en salle d\'attente' : 'Marquer présent en salle d\'attente') + '\"><span class=\"fas fa-couch\"></span></button>') +
            (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-dossier-button\" title=\"Ouvrir le dossier\"><span class=\"fas fa-folder-open\"></span></button>') +
            (event.patientid == '0' ? '<button class=\"btn btn-light btn-sm fc-editer-off-button\" title=\"Éditer\"><span class=\"fas fa-pencil-alt\"></span></button>' : '<button class=\"btn btn-light btn-sm fc-editer-button\" title=\"Éditer ce rendez-vous\"><span class=\"fas fa-pencil-alt\"></span></button>') +
            '<button class=\"btn btn-light btn-sm fc-deplacer-button\" title=\"Déplacer ce rendez-vous\"><span class=\"fas fa-arrows-alt\"></span></button>' +
            (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-cloner-button\" title=\"Cloner ce rendez-vous\"><span class=\"fas fa-clone\"></span></button>') +
            (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-honorer-button\" title=\"' + (event.absent == "oui" ? 'Marquer ce rendez-vous comme honoré' : 'Marquer ce rendez-vous comme non honoré') + '\"><span class=\"fas fa-exclamation-triangle\"></span></button>') +
            '<button class=\"btn btn-light btn-sm fc-supprimer-button\" title=\"Supprimer\"><span class=\"fas fa-trash\"></span></button>\
              </div>\
            </div>'
        });
      }
    },
    eventMouseover: function(eventOver, jsEvent, view) {
      if (selected_event) return;
      if (eventOver.type == 'publicHoliday') return;

      $(".fc-event").popover('hide');
      $('.popover-footer').addClass('d-none');
      if (eventOver.patientid == '0') {
        $(".fc-event[data-eventid=" + eventOver.id + "]").attr('data-content',
          '<strong>' + eventOver.title + '</strong>' + '<br>' + nl2br(eventOver.motif));
      } else {
        $(".fc-event[data-eventid=" + eventOver.id + "]").attr('data-content',
          '<strong>' + eventOver.title + '</strong>' + '<br>' +
          $("#type option[value='" + eventOver.type + "']").html() + '<br>' + nl2br(eventOver.motif) + (eventOver.absent == "oui" ? '<br><strong>Absent(e)</strong>' : '')
        );
      }

      $(".fc-event[data-eventid=" + eventOver.id + "]").popover('show');
    },
    eventMouseout: function(eventOut, jsEvent, view) {
      if (selected_event) return;
      if (eventOut.type == 'publicHoliday') return;

      $('.popover-footer').addClass('d-none');
      $(".fc-event").popover('hide');
    },
    eventClick: function(eventClicked, jsEvent, view) {
      nettoyer();
      cleanSelectedVar();
      canRefreshEvents = false;
      $('.popover-footer').removeClass('d-none');
      jsEvent.stopPropagation();
      selected_patient = eventClicked.patientid;
      selected_period = {
        start: eventClicked.start,
        end: eventClicked.end
      };
      selected_event = eventClicked;
      if (eventClicked.type == 'publicHoliday') return;
      if (jsEvent.shiftKey) {
        window.open(urlBase + '/logs/agenda/' + selected_calendar + '/' + eventClicked.id + '/', '_blank');
      } else if (eventClicked.patientid != "0") {
        //panel patient
        getPatientAdminData(eventClicked.patientid);
        $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
        $("#patientInfo").find("select").prop("disabled", true);
        $("#motif").val(eventClicked.motif);
        $("#type").val(eventClicked.type);
        $("#duree").html('<i class="far fa-clock mr-2"></i>' + $("#type").children("option:selected").attr("data-duree") + "mn");
        $("#eventColor").css('color', $("#type").children("option:selected").attr("data-color"));
        $('#datepicker input').val(eventClicked.start.format('DD/MM/YYYY à HH:mm'));
        $('#nettoyer').show();
        $('.lireCpsVitale').hide();
        $("#patientInfo").show();

        // panel links pro
        getRelationsPatientPraticiensTab(eventClicked.patientid);
        $('button.addRelation').attr('data-peopleID', eventClicked.patientid);
        $("#patientLinksPro").show();

        // type rdv
        $('#typesRdv').hide();

        $(".fc-event[data-eventid=" + eventClicked.id + "]").attr('data-content',
          '<strong>' + eventClicked.title + '</strong><br>' +
          $("#type option[value='" + eventClicked.type + "']").html() + '<br>' + nl2br(eventClicked.motif) +
          (eventClicked.absent == "oui" ? '<br><strong>Absent(e)</strong>' : '')
        );
      } else if (eventClicked.patientid == "0") {
        nettoyer();
      }
      $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").removeClass("cursor-cell");
      $(".fc-event").popover('hide');
      $(".fc-event[data-eventid=" + eventClicked.id + "]").popover('show');
      $(".fc-bg.selected").removeClass("selected");
      setTimeout(function() {
        $(jsEvent.currentTarget).find(".fc-bg").addClass("selected");
      }, 10);

    },
    eventDragStart: function(event, jsEvent, ui, view) {
      canRefreshEvents = false;
    },
    eventDragStop: function(event, jsEvent, ui, view) {
      canRefreshEvents = true;
    },
    eventDrop: function(event, delta, revertFunc) {
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le déplacement de cet événement ?")) {
        selected_event = event;
        modEvent(true);
      } else {
        revertFunc();
      }
      canRefreshEvents = true;
    },
    eventResizeStart: function(event, jsEvent, ui, view) {
      canRefreshEvents = false;
    },
    eventResizeStop: function(event, jsEvent, ui, view) {
      canRefreshEvents = true;
    },
    eventResize: function(event, delta, revertFunc) {
      canRefreshEvents = true;
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le changement de durée de cet événement ?")) {
        selected_event = event;
        modEvent(false);
      } else {
        revertFunc();
      }
    },
    select: function(start, end, jsEvent, view) {
      canRefreshEvents = false;
      jsEvent.stopImmediatePropagation();
      selected_period = {
        start: start,
        end: end
      };
      $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").addClass("cursor-cell");
      $(".fc-bg.selected").removeClass("selected");
      if (selected_action == "clone") {
        selected_period.end = moment(selected_period.start).add(moment(selected_event.end).diff(selected_event.start));
        if (selected_patient != "0") {
          setEvent();
        } else {
          closePeriod();
        }
      } else if (selected_action == "move") {
        selected_event.end = moment(start).add(selected_event.end.diff(selected_event.start));
        selected_event.start = start;
        modEvent(true);
      } else if (selected_event) {
        $('div.popover').popover('hide');
        nettoyer();
        cleanSelectedVar();
        return;
      } else if (end.diff(start) == moment.duration(slotDuration, "HH:mm:ss").as('milliseconds')) {
        if (calendar_mode == 'lateral' && $("#patientInfo").is(':hidden')) {
          return alert_popup('info', 'Sélectionnez ou créez d\'abord un patient');
        }
        $('div.popover').popover('hide');

        var duree = $("#type option:first").attr('data-duree');
        selected_period.end = moment(start).add(duree, 'm');
        $("#duree").html('<i class="far fa-clock mr-2"></i> ' + duree + "mn");

        $("#eventColor").css('color', $("#type option:first").attr("data-color"));
        $('#titreRdv').html('Nouveau rendez-vous');
        $("#type").val($("#type option")[0].value);

        $('#datepicker input').val(start.format('DD/MM/YYYY à HH:mm'));

        if (calendar_mode == 'modal') {
          $("#patientSearch").show();
          $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
          $("#patientInfo").find("select:not(.updatable)").prop("disabled", true);
          $("#patientInfo").hide();
          $("#patientLinksPro").hide();
        }

        $('#buttonCreer').show();
        $('#buttonModifier').hide();

        $('#creerNouveau').modal('show');
      } else {
        $(".fc-event").popover('hide');
        $(".fc-bg.selected").removeClass("selected");
        $('#editerOff h4').html('Souhaitez-vous fermer cette période ?');
        $("#editerOff textarea").val('');
        $('#editerOff').modal('show');
      }
    },
    unselect: function(jsEvent, view) {
      canRefreshEvents = true;
      $(".fc-event").popover('hide');
      $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").addClass("cursor-cell");
    },
    navLinks: true,
    navLinkDayClick: function(date, jsEvent) {
      canRefreshEvents = false;
      jsEvent.stopImmediatePropagation();
      selected_period = {
        start: moment(date.format('YYYY-MM-DD') + ' ' + minTime),
        end: moment(date.format('YYYY-MM-DD') + ' ' + maxTime)
      };
      $(".fc-event").popover('hide');
      $(".fc-bg.selected").removeClass("selected");
      $('#editerOff h4').html('Souhaitez-vous fermer cette journée ?');
      $("#editerOff textarea").val('');
      $('#editerOff').modal('show');
    }
  })

  $(".fc-next-button, .fc-prev-button").on("click", function() {
    $(".popover").hide();
  });

  //auto rafraichir les rdv agenda
  if (agendaRefreshDelayEvents > 0) setInterval(autoRefreshEvents, agendaRefreshDelayEvents * 1000);

  function autoRefreshEvents() {
    if (document.visibilityState != "visible" || canRefreshEvents) {
      $('#calendar').fullCalendar('refetchEvents');
    }
  }

  $('#patientLinksPro').on("click", "#addCorrespondant", function(e) {
    $("#patientLinksProTab thead").toggle();
  });

  //ajouter une relation patient <-> praticien
  $('#patientLinksPro').on("click", "#addRelationPatientPratAgenda", function(e) {
    e.preventDefault();
    praticienID = $('#searchPratID').attr('data-id');
    patientID = selected_patient;
    preRelationPatientPrat = $('#preRelationPatientPratID').val();
    setRelationPatientPrat(patientID, praticienID, preRelationPatientPrat);
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Définition des titles boutons agenda + style

  $(".fc-smallCalendar-button").attr("title", "Accès rapide");
  $(".fc-prevMonth-button").attr("title", "Mois précédent");
  $(".fc-prev-button").attr("title", "Semaine précédente");
  $(".fc-synchronize-button").attr("title", "Synchroniser le service d'agenda externe");
  $(".fc-next-button").attr("title", "Semaine suivante");
  $(".fc-nextMonth-button").attr("title", "Mois suivant");

  // changer style par défaut
  $(".fc-toolbar button").removeClass('btn-primary').addClass('btn-sm btn-primary');
  $(".fc-body").addClass("cursor-cell");

  ////////////////////////////////////////////////////////////////////////
  ///////// observations boutons popover

  $("body").on("click", ".fc-dossier-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    window.open(urlBase + '/patient/' + selected_event.patientid + '/');
    nettoyer();
    cleanSelectedVar();
  });

  $("body").on("click", ".fc-editer-button", function(e) {
    e.stopImmediatePropagation();
    // mise off des popover
    $(".fc-event").popover('hide');

    $(".fc-bg.selected").removeClass("selected");

    // actions sur modal
    if (calendar_mode == 'modal') {
      $("#patientSearch").hide();
      $("#nettoyer, #lectureCpsVital").hide();
      $('#titreRdv').html('Édition du rendez-vous de ' + $('input[name=p_firstname]').val() + ' ' + ($('input[name=p_lastname]').val() || $('input[name=p_birthname]').val()));
    } else {
      $('#titreRdv').html('Rendez-vous de ' + $('input[name=p_firstname]').val() + ' ' + ($('input[name=p_lastname]').val() || $('input[name=p_birthname]').val()));
      $("#lectureCpsVital").hide();
    }
    $('#buttonCreer').hide();
    $('#buttonModifier').show();

    $('#creerNouveau').modal('show');

    selected_action = undefined;
  });

  $("body").on("click", ".fc-editer-off-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    $('#editerOff h4').html('Éditer le commentaire de la plage horaire fermée');
    $("#editerOff textarea").val(selected_event.motif);
    $('#editerOff').modal('show');
    selected_action = undefined;
  });

  $("body").on("click", ".fc-cloner-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-body").removeClass("cursor-move").addClass("cursor-copy").removeClass("cursor-cell");
    $(".fc-event").popover('hide');
    selected_action = "clone";
  });

  $("body").on("click", ".fc-deplacer-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-body").addClass("cursor-move").removeClass("cursor-copy").removeClass("cursor-cell");
    $(".fc-event").popover('hide');
    selected_action = "move";
  });

  $("body").on("click", ".fc-honorer-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    setPasVenu();
  });

  $("body").on("click", ".fc-enattente-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    setEnAttente();
  });

  $("body").on("click", ".fc-supprimer-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    deleteEvent();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// modal : observation des actions

  $("#type").on("change", function(e) {
    $("#duree").html('<i class="far fa-clock mr-2"></i>' + $(this).children("option:selected").attr("data-duree") + "mn");
    $("#eventColor").css('color', $(this).children("option:selected").attr("data-color"));
    selected_period.end = getEnd(selected_period.start);
    if (selected_event) {
      selected_event.start = selected_period.start;
      selected_event.end = selected_period.end;
    }
  });

  $("#newPatient").on("click", function() {
    if (calendar_mode == 'modal') {
      selected_patient = undefined;
    } else {
      nettoyer();
      cleanSelectedVar();
      $('#nettoyer').show();
    }
    $("#search").val("");
    $("#patientInfo").show();
    $("#patientLinksPro").hide();
    $('#typesRdv').hide();
    $("#patientInfo").find("input,textarea").prop("readonly", false).val("");
    $("#patientInfo").find("select").prop("disabled", false);
    $("#historiquePatient").hide();
    $('#buttonCreer').removeAttr('disabled');
    $('.lireCpsVitale').show();
  });

  $("#datepicker").on("click", function(e) {
    e.stopPropagation();
    $("#datepicker").datetimepicker({
      locale: 'fr',
      format: 'DD/MM/YYYY à HH:mm',
      sideBySide: true,
      icons: {
        time: 'far fa-clock',
        date: 'fas fa-calendar',
        up: 'fas fa-chevron-up',
        down: 'fas fa-chevron-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right',
        today: 'fas fa-crosshairs',
        clear: 'fas fa-trash',
        close: 'fas fa-times'
      }
    });
    $("#datepicker").data("DateTimePicker").toggle();
  });

  $("#datepicker").on("dp.change", function(e) {
    selected_period.start = e.date;
    selected_period.end = getEnd(e.date);
    if (selected_event) {
      selected_event.start = selected_period.start;
      selected_event.end = selected_period.end;
    }
  });

  $("#buttonCreer").on("click", function(e) {
    setEvent();
  });

  $("#buttonModifier").on("click", function(e) {
    $('#creerNouveau').modal('hide');
    setEvent(selected_event.id);
  });

  $("#buttonCancel").on("click", function(e) {
    $('#creerNouveau').modal('hide');
    nettoyer();
    cleanSelectedVar();
  });

  $("#buttonCancelOff").on("click", function(e) {
    $('#editerOff').modal('hide');
    nettoyer();
    cleanSelectedVar();
  });

  $('#nettoyer').on("click", function(e) {
    e.preventDefault();
    nettoyer();
    if (calendar_mode == "modal") {
      selected_patient = undefined;
      selected_action = undefined;
      selected_event = undefined;
    } else {
      cleanSelectedVar();
    }
  });

  $("#formRdv").on("click", ".donothing", function(e) {
    e.preventDefault();
  });

  $("#historiquePatient").on("click", "button.moveToDate", function(e) {
    e.preventDefault();
    $(".fc-event").popover('hide');
    $('#calendar').fullCalendar('gotoDate', $(this).attr('data-date'));
  });

  $("#buttonValiderOff").on("click", function(e) {
    $('#editerOff').modal('hide');
    closePeriod();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Mettre à jour les infos patient

  $(".updatable:not([type='.custom-switch']):not([type='.custom-checkbox '])").typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: false,
    captureLength: 1,
    callback: function(value) {
      if (selected_patient)
        setPeopleData($(this).val(), selected_patient, $(this).attr("data-typeID"), $(this), 0);
    }
  });

  $(" .custom-switch, .custom-checkbox ").on("click", function(e) {
    if (selected_patient) {
      inputSource = $(this).find('input');
      typeID = inputSource.attr("data-typeID");
      value = inputSource.prop('checked');
      source = $(this);
      instance = $(this).closest("form").attr("data-instance");
      setPeopleData(value, selected_patient, typeID, source, 0);
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// modal : chercher / nouveau / editer

  //chercher patient : porte d'entrée d'un nouveau rdv
  $('#search').autocomplete({
    source: urlBase + '/agenda/' + selected_calendar + '/ajax/searchPatient/',
    select: function(event, ui) {
      event.stopPropagation();
      if (calendar_mode == 'lateral') {
        cleanSelectedVar();
        nettoyer();
      }
      $('#typesRdv').hide();
      $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
      $("#patientInfo").find("select:not(.updatable)").prop("disabled", true);
      $('#nettoyer').show();
      $('#lectureCpsVital').hide();
      getPatientAdminData(ui.item.patientID);
      getRelationsPatientPraticiensTab(ui.item.patientID);
      $('button.addRelation').attr('data-peopleID', ui.item.patientID);
      selected_patient = ui.item.patientID;
      $('#buttonCreer').removeAttr('disabled');
      $("#patientInfo").show();
      $("#patientLinksPro").show();
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// action par défaut sur clic

  $("body").on("click", function(event) {
    $target = $(event.target);
    if (!$target.closest('div.fc-view-container').length && !$target.closest('div.modal').length && !$target.closest('div.nePasNettoyer').length && !$target.closest('div.fc-left').length && !$target.closest('div.alert').length && !$target.closest('.ui-autocomplete').length) {
      $(".fc-bg.selected").removeClass("selected");
      nettoyer();
      cleanSelectedVar();
    }
    $(".fc-event").popover('hide');
    $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").addClass("cursor-cell");
    if ($("#datepicker").data("DateTimePicker"))
      $("#datepicker").data("DateTimePicker").hide();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// réorganisation du formulaire de données patient

  $("#patientInfo .form-group").addClass("mt-0 mb-2");
  $("#patientInfo h3").parent().remove();
  $("#patientInfo .col-md-6").each(function(idx, element) {
    $(element).removeClass("col-md-6").addClass(idx % 2 ? "col-lg-6 pl-lg-1" : "col-lg-6 pr-lg-1");
  });
  $("#patientInfo .col-md-4").removeClass("col-md-4").addClass("col-lg-4 pr-lg-1");
  $("#patientInfo .col-md-8").removeClass("col-md-8").addClass("col-lg-8 pl-lg-1");

});

////////////////////////////////////////////////////////////////////////
///////// Fonctions

/**
 * Obtenir le moment de fin
 * @param  {object} start début au format momentjs
 * @return {object}       fin au format momentjs
 */
function getEnd(start) {
  return moment(start).add($("#type").children("option:selected").attr("data-duree"), 'm');
}

/**
 * Synchroniser les agendas interne / externe
 * @return {void}
 */
function synchronizeEvents() {
  $(".fc-synchronize-button").attr("disabled", "");
  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/synchronizeEvents/',
    type: "post",
    data: {},
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
      $(".fc-synchronize-button").removeAttr("disabled");
    },
    error: function() {
      alert_popup('error', 'Il y a un problème. Il faut recharger la page.');
      $(".fc-synchronize-button").removeAttr("disabled");
    },
  });
}

/**
 * Obtenir les données patient
 * @param  {int} patientID patientid
 * @return {void}
 */
function getPatientAdminData(patientID) {
  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/getPatientAdminData/',
    type: "post",
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $("#patientInfo input[name!='userid'], #patientInfo textarea").val('');
      $.each(data, function(index, value) {
        if ($("#id_" + index + "_id").length) $("#id_" + index + "_id").val(value);

        if ($("#id_" + index + "_id").hasClass('custom-control-input') && $("#id_" + index + "_id").attr('type') == 'checkbox') {
          if (value == "true") {
            $("#id_" + index + "_id").prop('checked', 'checked');
          } else {
            $("#id_" + index + "_id").prop('checked', false);
          }
        }
      });
      getHistoriquePatient(patientID);
      autosize.update($('#id_notes_id'));
    },
    error: function() {
      alert_popup('error', "Des données n'ont pas pu être récupérées.");
      nettoyer();
      cleanSelectedVar();
    },
  });
}

/**
 * Obtenir et afficher l'historique des rdv patient
 * @param  {int} patientID patientID
 * @return {void}
 */
function getHistoriquePatient(patientID) {
  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/getHistoriquePatient/',
    type: "post",
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#historiquePatientList').html('');
      if (data['historique'].length > 0) {
        $.each(data['historique'], function(index, dat) {
          var duration = moment.duration(moment(dat['dateiso']).startOf('day').diff(moment().endOf('day')));
          var days = Math.ceil(duration.asDays());

          chaine = '<li class="list-group-item p-1'
          if (dat['absente'] == 'oui') chaine = chaine + ' list-group-item-danger';
          if (dat['statut'] == 'deleted') chaine = chaine + ' list-group-item-warning';
          if (moment(dat['dateiso']).isAfter()) chaine = chaine + ' font-weight-bold';
          chaine = chaine + '">';
          if (dat['agendaID'] == selected_calendar) {
            chaine = chaine + '<button title="Voir" type="button" class="btn btn-light btn-sm moveToDate" data-date="' + dat['dateiso'] + '"><span class="far ';
            if (moment(dat['dateiso']).isAfter()) chaine = chaine + 'fa-calendar-plus';
            else chaine = chaine + 'fa-calendar';
            chaine = chaine + '" aria-hidden="true"></span></button>';
          } else {
            chaine = chaine + '<a title="Voir - rdv sur un autre agenda" href="' + urlBase + '/agenda/' + dat['agendaID'] + '/' + dat['dateJump'] + '/" class="btn btn-light btn-sm" data-date="' + dat['dateiso'] + '"><span class="fas ';
            if (moment(dat['dateiso']).isAfter()) chaine = chaine + 'fa-calendar-plus';
            else chaine = chaine + 'fa-calendar';
            chaine = chaine + '" aria-hidden="true"></span></a>';
          }
          chaine = chaine + '&nbsp;&nbsp;&nbsp;' + dat['start'] + ' : ' + dat['type'];
          if (dat['statut'] == 'deleted') chaine = chaine + ' [annulé]';
          if (dat['absente'] == 'oui') chaine = chaine + ' [non honoré]';
          if (days > 1 && dat['statut'] == 'actif') {
            chaine = chaine + ' dans ' + days + ' jours';
          } else if (days == 0 && dat['statut'] == 'actif') {
            chaine = chaine + ' aujourd\'hui';
          } else if (days == 1 && dat['statut'] == 'actif') {
            chaine = chaine + ' demain';
          }
          chaine = chaine + '</li>';
          $('#historiquePatientList').append(chaine);
        });
      } else {
        chaine = '<li class="list-group-item p-1 text-muted">Pas de rendez-vous connus</li>';
        $('#historiquePatientList').append(chaine);
      }

      $('#HistoriqueRdvResume button[title=total]').html(data['stats']['total']);
      $('#HistoriqueRdvResume button[title=honorés]').html(data['stats']['ok']);
      $('#HistoriqueRdvResume button[title=annulés]').html(data['stats']['annule']);
      $('#HistoriqueRdvResume button[title=absent]').html(data['stats']['absent']);
      $('#historiquePatient').show();
    },
    error: function() {
      alert_popup('error', "Des données n'ont pas pu être récupérées.");
      nettoyer();
      cleanSelectedVar();
    },
  });
}

/**
 * Nettoyage pour retour à l'état initial
 * @return {void}
 */
function nettoyer() {

  //recherche patient
  $("#search").val('');
  $("#patientSearch").show();

  //formulaire patient
  $("#patientInfo").hide();
  $("#patientLinksPro").hide();
  $('#nettoyer').hide();
  $("#patientInfo input[name!='userid'], #patientInfo textarea").val('');
  $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
  $("#patientInfo").find("select:not(.updatable)").prop("disabled", true);
  $("#patientInfo select")[0].selectedIndex = 0;
  $('#buttonCreer').attr('disabled', 'disabled');
  $('.lireCpsVitale').hide();
  $("#patientInfo input.custom-control-input").prop('checked', false);

  // historique patient
  $('#historiquePatient').hide();
  $('#historiquePatientList').html('');
  $('#HistoriqueRdvResume button').html('');

  // formulaire rendez-vous
  $('#motif').val('');

  // modal éditer off
  $('#motifOff').val('');
  $('#editerOff h4').html('');

  $('#typesRdv').show();

  //gestion correspondants
  $('#searchPratID').val('');
  $('#searchPratID').attr('data-id', '');
  $('button.addRelation').attr('data-peopleID', '');

  $(".fc-bg.selected").removeClass("selected");
}

/**
 * Passer à undefined les variables clefs de fonctionnement
 * @return {void}
 */
function cleanSelectedVar() {
  selected_patient = undefined;
  selected_action = undefined;
  selected_event = undefined;
  selected_period = undefined;
  canRefreshEvents = true;
}

/**
 * Enregistrer / modifier un rendez-vous
 * @param {int} id id du rdv
 */
function setEvent(id) {
  var stop = false;
  var data;
  // si patient inconnu on utilise les data latéral et on en crée un nouveau
  if (!selected_patient) {
    $('#newPatientData').find('input[required],textarea[required]').each(function(idx, el) {
      if (el.value == '') {
        glow('danger', $(el));
        stop = true;
      }
    });
    if (stop) {
      alert_popup("danger", "Certains champs requis n'ont pas été remplis.");
      if (calendar_mode == "lateral") $('#creerNouveau').modal('hide');
      return;
    }
    data += $('#newPatientData').serialize() + '&' + $('#formRdv').serialize();
    data += '&userID=' + selected_calendar;
    data += '&start=' + selected_period.start.format("YYYY-MM-DD%20HH:mm:SS");
    data += '&end=' + selected_period.end.format("YYYY-MM-DD%20HH:mm:SS");
  }
  // si patient connu
  else {
    data = {
      patientID: selected_patient,
      userID: selected_calendar,
      start: selected_period.start.format("YYYY-MM-DD HH:mm:SS"),
      end: selected_period.end.format("YYYY-MM-DD HH:mm:SS"),
      type: (selected_patient == '0' ? '[off]' : $('#type').val()),
      motif: $('#motif').val(),
    };
    if (id != undefined)
      data.eventID = id;
  }
  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/setNewRdv/',
    type: "post",
    data: data,
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
      nettoyer();
      cleanSelectedVar();
      $('#creerNouveau').modal('hide');
    },
    error: function() {
      alert_popup('error', "Les modifications n'ont pas pu être appliquées.");
      nettoyer();
      cleanSelectedVar();
    },
  });
}

/**
 * Fermer une plage horaire
 * @return {void}
 */
function closePeriod() {

  if (selected_event) {
    start = selected_event.start.format("YYYY-MM-DD HH:mm:SS");
    end = selected_event.end.format("YYYY-MM-DD HH:mm:SS");
    id = selected_event.id;
  } else if (selected_period) {
    start = selected_period.start.format("YYYY-MM-DD HH:mm:SS");
    end = selected_period.end.format("YYYY-MM-DD HH:mm:SS");
    id = '';
  } else {
    return;
  }

  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/setNewRdv/',
    type: "post",
    data: {
      eventID: id,
      patientID: '0',
      userID: selected_calendar,
      start: start,
      end: end,
      type: '[off]',
      motif: $('#motifOff').val(),
    },
    dataType: "json",
    success: function() {
      $('#calendar').fullCalendar('refetchEvents');
      nettoyer();
      cleanSelectedVar();
    },
    error: function() {
      alert_popup('error', "Les modifications n'ont pas pu être appliquées.");
      nettoyer();
      cleanSelectedVar();
    },
  });
}


/**
 * Effacer un rendez-vous
 * @return {void}
 */
function deleteEvent() {
  var id = selected_event.id;
  if (confirm("Confirmez-vous la suppression de cet événement ?")) {
    $.ajax({
      url: urlBase + '/agenda/' + selected_calendar + '/ajax/delEvent/',
      type: "post",
      data: {
        eventid: selected_event.id,
      },
      dataType: "json",
      success: function(data) {
        $('#calendar').fullCalendar('removeEvents', id);
        nettoyer();
        cleanSelectedVar();
      },
      error: function() {
        alert_popup('error', "Les modifications n'ont pas pu être appliquées.");
        nettoyer();
        cleanSelectedVar();
      },
    });
  }
}

/**
 * Marquer un rendez-vous comme non honoré
 */
function setPasVenu() {
  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/setEventPasVenu/',
    type: "post",
    data: {
      eventID: selected_event.id,
    },
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
      nettoyer();
      cleanSelectedVar();
    },
    error: function() {
      alert_popup('error', "Les modifications n'ont pas pu être appliquées.");
      nettoyer();
      cleanSelectedVar();
    },
  });
}

/**
 * Marquer un rendez-vous comme patient en salle d'attente
 */
function setEnAttente() {
  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/setEventEnAttente/',
    type: "post",
    data: {
      eventID: selected_event.id,
    },
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
      nettoyer();
      cleanSelectedVar();
    },
    error: function() {
      alert_popup('error', "Les modifications n'ont pas pu être appliquées.");
      nettoyer();
      cleanSelectedVar();
    },
  });
}

/**
 * Modifier un rendez-vous
 * @param  {boolean} refetch refetchEvents
 * @return {void}
 */
function modEvent(refetch) {

  $.ajax({
    url: urlBase + '/agenda/' + selected_calendar + '/ajax/moveEvent/',
    type: "post",
    data: {
      eventid: selected_event.id,
      start: selected_event.start.format('YYYY-MM-DD HH:mm:SS'),
      end: selected_event.end.format('YYYY-MM-DD HH:mm:SS')
    },
    dataType: "json",
    success: function(data) {
      if (refetch)
        $('#calendar').fullCalendar('refetchEvents');
      nettoyer();
      cleanSelectedVar();
    },
    error: function() {
      alert_popup('error', "Les modifications n'ont pas pu être appliquées.");
      nettoyer();
      cleanSelectedVar();
    },
  });
}
