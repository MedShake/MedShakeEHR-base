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

$(document).ready(function() {

  ////////////////////////////////////////////////////////////////////////
  ///////// Définition des variables par défaut construction agenda

  if (!hiddenDays) {
    hiddenDays = [0];
  }
  if (!minTime) {
    minTime = '08:00:00';
  }
  if (!maxTime) {
    maxTime = '20:45:00';
  }
  if (firstDay == undefined) {
    firstDay = moment().day();
  }
  if (!slotDuration) {
    slotDuration = '00:15:00';
  }
  if (!slotLabelInterval) {
    slotLabelInterval = '00:30:00';
  }
  if (!businessHours) {
    businessHours = [{
      dow: [1, 2, 3, 4, 5, 6],
      start: '08:00',
      end: '21:20',
    }];
  }
  if (!boutonsHeaderCenter) {
    var boutonsHeaderCenter = '';
  }

  if (!eventTextColor) {
    var eventTextColor = '#fff';
  }

  if (!eventSources) {
    var eventSources = [{
        url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/getEvents/'
      },
      {
        events: [{
          start: '13:00',
          end: '14:00',
          dow: [1, 2, 3, 4, 5],
          rendering: 'background',
          className: 'fc-nonbusiness'
        }, {
          start: '13:00',
          end: maxTime,
          dow: [6],
          rendering: 'background',
          className: 'fc-nonbusiness'
        }]
      }
    ]
  }

  ////////////////////////////////////////////////////////////////////////
  ///////// Construction agenda

  $('#calendar').fullCalendar({

    defaultView: 'agendaWeek',
    locale: 'fr',
    themeSystem: 'bootstrap4',
    hiddenDays: hiddenDays,
    customButtons: {
      nextMonth: {
        click: function() {
          $('#calendar').fullCalendar('incrementDate', moment.duration(1, 'months'));
        }
      },
      prevMonth: {
        click: function() {
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
      prevMonth: 'fa-angle-double-left',
      prev: 'fa-angle-left',
      synchronize: 'fa-sync-alt',
      next: 'fa-angle-right',
      nextMonth: 'fa-angle-double-right',
    },
    header: {
      left: 'prevMonth,prev,synchronize,next,nextMonth today',
      center: boutonsHeaderCenter,
      right: 'title'
    },
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
    unselectCancel: '.fc-deplacer-button,.fc-cloner-button',
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
        element.attr("title", event.name);
        element.attr("data-container", "body");
        element.attr("data-placement", 'right');
        element.attr("data-boundary", 'viewport');
        element.attr("data-html", "true");
        if (event.patientid == "0") {
          element.attr("data-content", "Fermé");
        }
        element.attr("data-template", '\
<div class=\"popover\" role=\"tooltip\">\
<h3 class=\"popover-header\">Détail</h3>\
<div class=\"popover-body\"></div>\
<div class=\"popover-footer btn-group m-1\">' +
          (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-dossier-button\" title=\"Dossier\"><span class=\"fa fa-folder-open\"></span></button>') +
          (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-editer-button\" title=\"Editer\"><span class=\"fa fa-wrench\"></span></button>') +
          '<button class=\"btn btn-light btn-sm fc-deplacer-button\" title=\"déplacer\"><span class=\"fa fa-arrows-alt\"></span></button>' +
          (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-cloner-button\" title=\"cloner\"><span class=\"fa fa-clone\"></span></button>') +
          (event.patientid == '0' ? '' : '<button class=\"btn btn-light btn-sm fc-honorer-button\" title=\"' + (event.absent == "oui" ? 'Présent' : 'Absent') + '\"><span class=\"fa fa-exclamation-triangle\"></span></button>') +
          '<button class=\"btn btn-light btn-sm fc-supprimer-button\" title=\"Supprimer\"><span class=\"fa fa-times\"></span></button>\
</div>\
</div>');
        element.popover();
      }
    },
    eventClick: function(eventClicked, jsEvent, view) {
      jsEvent.stopPropagation();
      selected_patient = eventClicked.patientid;
      selected_period = {
        start: eventClicked.start,
        end: eventClicked.end
      };
      selected_event = eventClicked;
      if (eventClicked.patientid != "0") {
        getPatientAdminData(eventClicked.patientid);
        $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
        $("#patientInfo").find("select").prop("disabled", true);
        $("#patientInfo").show();
        if ($('#calendar').attr('data-mode') == 'lateral') {
          $('#nettoyer').show();
        }
        $('#buttonModifier').prop('disabled', false);
        $('#buttonAutresActions').prop('disabled', false);
        $("#motif").val(eventClicked.motif);
        $("#type").val(eventClicked.type);
        $("#duree").html(" " + $("#type").children("option:selected").attr("data-duree") + "mn");
        $('#datepicker input').val(eventClicked.start.format('DD/MM/YYYY à HH:mm'));
        $(".fc-event[data-eventid=" + eventClicked.id + "]").attr('data-content',
          '<strong>' + eventClicked.title + '</strong><br>' +
          $("#type option[value='" + eventClicked.type + "']").html() + '<br>' + eventClicked.motif +
          (eventClicked.absent == "oui" ? '<br><strong>Absent(e)</strong>' : '')
        );
      } else {
        nettoyer();
        selected_patient = undefined;
        selected_action = undefined;
        selected_period = undefined;
      }
      $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").removeClass("cursor-cell");
      $(".fc-event").popover('hide');
      $(".fc-event[data-eventid=" + eventClicked.id + "]").popover('show');

      $(".fc-bg.selected").removeClass("selected");
      setTimeout(function() {
        $(jsEvent.currentTarget).find(".fc-bg").addClass("selected");
      }, 10);

    },
    eventDrop: function(event, delta, revertFunc) {
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le déplacement de cet événement ?")) {
        selected_event = event;
        modEvent(true);
      } else {
        revertFunc();
      }
    },

    eventResize: function(event, delta, revertFunc) {
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le changement de durée de cet événement ?")) {
        selected_event = event;
        modEvent(false);
      } else {
        revertFunc();
      }
    },
    select: function(start, end, jsEvent, view) {
      jsEvent.stopImmediatePropagation();
      selected_period = {
        start: start,
        end: end
      };
      $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").addClass("cursor-cell");
      $(".fc-bg.selected").removeClass("selected");
      if (selected_action == "clone") {
        if (selected_patient != "0") {
          setEvent();
        } else {
          selected_period.end = moment(selected_period.start).add(moment(selected_event.end).diff(selected_event.start));
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
        if ($('#calendar').attr('data-mode') == 'lateral' && $("#patientInfo").is(':hidden'))
          return alert_popup('warning', 'Sélectionnez ou créez d\'abord un patient');
        var duree = $("#type option:first").attr('data-duree');
        $("#duree").html(" " + duree + "mn");
        selected_period.end = moment(start).add(duree, 'm');
        $("#type").val($("#type option")[0].value);
        if ($('#calendar').attr('data-mode') == 'modal') {
          $("#patientSearch").show();
          $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
          $("#patientInfo").find("select:not(.updatable)").prop("disabled", true);
          $("#patientInfo").hide();
        } else {
          $('#titreRdv').html('Rendez-vous de ' + $('input[name=p_firstname]').val() + ' ' + ($('input[name=p_lastname]').val() || $('input[name=p_birthname]').val()));
        }
        $('#creerNouveau').modal('show');
        $(".modal-title").html("Nouveau rendez-vous");
        $("#buttonAutresActions").hide();
        $('#buttonCreer').show();
        $('#buttonModifier').hide();
        $('div.popover').popover('hide');
        $('#datepicker input').val(start.format('DD/MM/YYYY à HH:mm'));
      } else {
        if (confirm("Souhaitez-vous fermer cette période ?")) {
          closePeriod();
        } else {
          $('#calendar').fullCalendar('unselect');
          nettoyer();
          cleanSelectedVar();
        }
      }
    },
    unselect: function(jsEvent, view) {
      if (jsEvent)
        jsEvent.stopImmediatePropagation();
      $(".fc-event").popover('hide');
      $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").addClass("cursor-cell");
    },
    navLinks: true,
    navLinkDayClick: function(date, jsEvent) {
      jsEvent.stopImmediatePropagation();
      if (confirm("Souhaitez-vous fermer cette journée ?")) {
        selected_period = {
          start: moment(date.format('YYYY-MM-DD') + ' ' + minTime),
          end: moment(date.format('YYYY-MM-DD') + ' ' + maxTime)
        };
        closePeriod();
      } else {
        nettoyer();
        cleanSelectedVar();
      }
    }
  })

  $("#calendar").on("click", function(e) {
    e.stopImmediatePropagation();
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").addClass("cursor-cell");
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Définition des titles boutons agenda

  $(".fc-prevMonth-button").attr("title", "Mois précédent").removeClass('btn-primary').addClass('btn-sm');
  $(".fc-prev-button").attr("title", "Semaine précédente").removeClass('btn-primary').addClass('btn-sm');
  $(".fc-synchronize-button").attr("title", "Synchroniser le service d'agenda externe").removeClass('btn-primary').addClass('btn-sm');
  $(".fc-next-button").attr("title", "Semaine suivante").removeClass('btn-primary').addClass('btn-sm');
  $(".fc-nextMonth-button").attr("title", "Mois suivant").removeClass('btn-primary').addClass('btn-sm');
  $(".fc-left").addClass("pt-2");
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
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    $('#creerNouveau').modal('show');
    if ($('#calendar').attr('data-mode') == 'modal')
      $("#patientSearch").hide();
    else
      $('#titreRdv').html('Rendez-vous de ' + $('input[name=p_firstname]').val() + ' ' + ($('input[name=p_lastname]').val() || $('input[name=p_birthname]').val()));
    $("#type").val(selected_event.type);
    $(".modal-title").html("Modifier un rendez-vous");
    $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
    $("#patientInfo").find("select:not(.updatable)").prop("disabled", true);
    $("#patientInfo").show();
    $("#buttonAutresActions").show();
    $('#buttonCreer').hide();
    $('#buttonModifier').show();

    $('div.popover').popover('hide');
    $('#datepicker input').val(selected_event.start.format('DD/MM/YYYY à HH:mm'));
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

  $("body").on("click", ".fc-supprimer-button", function(e) {
    e.stopImmediatePropagation();
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    deleteEvent();
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// modal : observation des actions

  $("#type").on("change", function(e) {
    $("#duree").html(" " + $(this).children("option:selected").attr("data-duree") + "mn");
    selected_period.end = getEnd(selected_period.start);
    if (selected_event) {
      selected_event.start = selected_period.start;
      selected_event.end = selected_period.end;
    }
  });

  $("#newPatient").on("click", function() {
    $("#search").val("");
    $("#patientInfo").show();
    $("#patientInfo").find("input,textarea").prop("readonly", false).val("");
    $("#patientInfo").find("select").prop("disabled", false);
    $("#historiquePatient").hide();
    $('#buttonCreer').removeAttr('disabled');
    if ($('#calendar').attr('data-mode') == 'lateral') {
      $('#nettoyer').show();
    }
  });

  $("#datepicker").on("click", function(e) {
    e.stopPropagation();
    $("#datepicker").datetimepicker({
      locale: 'fr',
      format: 'DD/MM/YYYY à HH:mm',
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

  $("body").on("click", function(e) {
    if ($("#datepicker").data("DateTimePicker"))
      $("#datepicker").data("DateTimePicker").hide();
  });

  $("#buttonAutresActions").on("click", function(e) {
    e.stopImmediatePropagation();
    e.preventDefault();
    $(this).dropdown('toggle');
  });

  $("#buttonClone").on("click", function(e) {
    e.preventDefault();
    e.stopPropagation();
    $("#buttonAutresActions").dropdown('toggle');
    $('#creerNouveau').modal('hide');
    setEvent();
  });

  $("#buttonMark").on("click", function(e) {
    e.preventDefault();
    e.stopPropagation();
    $("#buttonAutresActions").dropdown('toggle');
    $('#creerNouveau').modal('hide');
    setPasVenu();
  });

  $("#buttonRemove").on("click", function(e) {
    e.preventDefault();
    e.stopPropagation();
    $("#buttonAutresActions").dropdown('toggle');
    $('#creerNouveau').modal('hide');
    deleteEvent();
  });

  $("#buttonCreer").on("click", function(e) {
    $('#creerNouveau').modal('hide');
    setEvent();
  });

  $("#buttonModifier").on("click", function(e) {
    $('#creerNouveau').modal('hide');
    setEvent(selected_event.id);
  });

  $("#buttonCancel").on("click", function(e) {
    $('#creerNouveau').modal('hide');
  });

  $("#creerNouveau").on("click", function(e) {
    e.stopPropagation();
    if ($("#buttonAutresActions").attr("aria-expanded") == "true")
      $("#buttonAutresActions").dropdown('toggle');
  });

  $('#nettoyer').on("click", function() {
    nettoyer();
    cleanSelectedVar();
  });

  $("#formRdv").on("click", ".donothing", function(e) {
    e.preventDefault();
  });

  $("#historiquePatient").on("click", "button.moveToDate", function(e) {
    e.preventDefault();
    $('#calendar').fullCalendar('gotoDate', $(this).attr('data-date'));
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Mettre à jour les infos patient

  $(".updatable").typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: false,
    captureLength: 1,
    callback: function(value) {
      if (selected_patient)
        setPeopleData($(this).val(), selected_patient, $(this).attr("data-typeID"), $(this), 0);
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// modal : chercher / nouveau / editer

  //chercher patient : porte d'entrée d'un nouveau rdv
  $('#search').autocomplete({
    source: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/searchPatient/',
    select: function(event, ui) {
      event.stopPropagation();
      $("#patientInfo").show();
      $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
      $("#patientInfo").find("select:not(.updatable)").prop("disabled", true);
      $('#nettoyer').show();
      getPatientAdminData(ui.item.patientID);
      selected_patient = ui.item.patientID;
      $('#buttonCreer').removeAttr('disabled');
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// action par défaut sur clic

  $("body").on("click", function(e) {
    $(".fc-event").popover('hide');
    $(".fc-bg.selected").removeClass("selected");
    $(".fc-body").removeClass("cursor-move").removeClass("cursor-copy").addClass("cursor-cell");
    if (e.currentTarget.id in {
        'creerNouveau': 0,
        'calendar': 0
      }) {
      e.stopPropagation();
      return;
    }
    selected_action = undefined;
    selected_event = undefined;
    selected_period = undefined;
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
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/synchronizeEvents/',
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
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/getPatientAdminData/',
    type: "post",
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $("#patientInfo input[name!='userid'], #patientInfo textarea").val('');
      $.each(data, function(index, value) {
        if ($("#id_" + index + "_id").length) $("#id_" + index + "_id").val(value);
      });
      getHistoriquePatient(patientID);
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
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/getHistoriquePatient/',
    type: "post",
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#historiquePatient ul').html('');
      $.each(data['historique'], function(index, dat) {
        chaine = '<li class="list-group-item p-1'
        if (dat['absente'] == 'oui') chaine = chaine + ' list-group-item-danger';
        if (dat['statut'] == 'deleted') chaine = chaine + ' list-group-item-warning';
        chaine = chaine + '">';
        chaine = chaine + '<button type="button" class="btn btn-light btn-sm moveToDate" data-date="' + dat['dateiso'] + '"><span class="fa fa-calendar" aria-hidden="true"></span></button>&nbsp;&nbsp;&nbsp;';
        chaine = chaine + dat['start'] + ' : ' + dat['type'];
        if (dat['statut'] == 'deleted') chaine = chaine + ' [annulé]';
        if (dat['absente'] == 'oui') chaine = chaine + ' [non honoré]';
        chaine = chaine + '</li>';

        $('#historiquePatient ul').append(chaine);

      });
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
  $('#nettoyer').hide();
  $("#patientInfo input[name!='userid'], #patientInfo textarea").val('');
  $("#patientInfo").find("input:not(.updatable),textarea:not(.updatable)").prop("readonly", true);
  $("#patientInfo").find("select:not(.updatable)").prop("disabled", true);
  $("#patientInfo select")[0].selectedIndex = 0;
  $('#buttonCreer').attr('disabled','disabled');

  // historique patient
  $('#historiquePatient').hide();
  $('#historiquePatient ul').html('');
  $('#HistoriqueRdvResume button').html('');

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
}

/**
 * Enregistrer / modifier un rendez-vous
 * @param {int} id id du rdv
 */
function setEvent(id) {
  var data;
  // si patient inconnu on utilise les data latéral et on en crée un nouveau
  if (!selected_patient) {
    if ($('#calendar').attr('data-mode') == 'lateral')
      data += $('#newPatientData').serialize() + '&' + $('#formRdv').serialize();
    else
      data += $('#formRdv').serialize();
    data += '&userID=' + $('#calendar').attr('data-userID');
    data += '&start=' + selected_period.start.format("YYYY-MM-DD%20HH:mm:SS");
    data += '&end=' + selected_period.end.format("YYYY-MM-DD%20HH:mm:SS");
  }
  // si patient connu
  else {
    data = {
      patientID: selected_patient,
      userID: $('#calendar').attr('data-userID'),
      start: selected_period.start.format("YYYY-MM-DD HH:mm:SS"),
      end: selected_period.end.format("YYYY-MM-DD HH:mm:SS"),
      type: (selected_patient == '0' ? '[off]' : $('#type').val()),
      motif: $('#motif').val(),
    };
    if (id != undefined)
      data.eventID = id;
  }
  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/setNewRdv/',
    type: "post",
    data: data,
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
 * Fermer une plage horaire
 * @return {void}
 */
function closePeriod() {
  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/setNewRdv/',
    type: "post",
    data: {
      eventID: '',
      patientID: '0',
      userID: $('#calendar').attr('data-userID'),
      start: selected_period.start.format("YYYY-MM-DD HH:mm:SS"),
      end: selected_period.end.format("YYYY-MM-DD HH:mm:SS"),
      type: '[off]',
      motif: 'Fermé',
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
 * Effacer un rendez-vous
 * @return {void}
 */
function deleteEvent() {
  var id = selected_event.id;
  if (confirm("Confirmez-vous la suppression de cet événement ?")) {
    $.ajax({
      url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/delEvent/',
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
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/setEventPasVenu/',
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
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/moveEvent/',
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
