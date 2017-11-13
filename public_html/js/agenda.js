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
 * @edited fr33z00 <https://www.github.com/fr33z00>
 */

var selected_period;
var selected_event;

$(document).ready(function() {

  popstop = 0;
  $(function() {
    $('[data-toggle="popover"]').popover();

  })

  var userID = $('#calendar').attr('data-userID');
  if (typeof hiddenDays == 'undefined') {
    hiddenDays = [0];
  }
  if (typeof minTime == 'undefined') {
    minTime = '08:00:00';
  }
  if (typeof maxTime == 'undefined') {
    maxTime = '20:45:00';
  }
  if (typeof slotDuration == 'undefined') {
    slotDuration = '00:15:00';
  }
  if (typeof businessHours == 'undefined') {
    businessHours = [{
      dow: [1, 2, 3, 4, 5, 6],
      start: '08:00',
      end: '21:20',
    }];
  }
  if (typeof eventSources == 'undefined') {
    eventSources = [{
        url: urlBase+'/agenda/' + userID + '/ajax/getEvents/'
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


  $('#calendar').fullCalendar({

    defaultView: 'agendaWeek',
    locale: 'fr',
    themeSystem: 'bootstrap3',
    hiddenDays: hiddenDays,
    customButtons: {
      nextMonth: {
        click: function() {
          $('#calendar').fullCalendar('incrementDate', moment.duration(1, 'months'));
        }
      },
      lastMonth: {
        click: function() {
          $('#calendar').fullCalendar('incrementDate', moment.duration(-1, 'months'));
        }
      },
      dossier: {
        click: function(){
          if (!selected_event || selected_event.patientid=="0")
            return alert("Sélectionnez d'abord un RDV, puis cliquez ce bouton pour ouvrir le dossier du patient");
          window.open(urlBase+'/patient/'+selected_event.patientid+'/', '_patient');
          selected_event = undefined;
          selected_period = undefined;
        },
      },
      editer: {
        click: function(){
          if (!selected_event || !selected_period)
            return alert("Sélectionnez d'abord un événement à déplacer, puis sa nouvelle position, puis cliquez ce bouton");
	        selected_event.end = moment(selected_period.start).add(moment(selected_event.end).diff(selected_event.start));
	        selected_event.start = selected_period.start;
          moveEvent(selected_event);
          selected_event = undefined;
          selected_period = undefined;
        },
      },
      supprimer: {
        click: function(){
          if (!selected_event)
            return alert("cliquez d'abord un événement à supprimer, puis sur ce bouton");
          deleteEvent(selected_event.id);
          selected_event = undefined;
          selected_period = undefined;
        },
      },
      bloquer: {
        click: function(){
          if (!selected_period)
            return alert("Sélectionnez d'abord une période à fermer, puis cliquez ce bouton");
          closePeriod();
          selected_period = undefined;
        },
      },
      honorer: {
        click: function(){
          if (!selected_event)
            return alert("selectionnez d'abord un RDV à marquer honoré/non honoré, puis cliquez ce bouton");
          setEventPasVenu(selected_event.id);
          selected_event = undefined;
          selected_period = undefined;
        },
      },
    },
    bootstrapGlyphicons: {
      lastMonth: 'glyphicon-chevron-left',
      nextMonth: 'glyphicon-chevron-right',
      prev: 'glyphicon-menu-left',
      next: 'glyphicon-menu-right',
      dossier: 'glyphicon-folder-open',
      editer: 'glyphicon-edit',
      bloquer: 'glyphicon-cutlery',
      supprimer: 'glyphicon-remove',
      honorer: 'glyphicon-ok',
    },
    header: {
      left: 'lastMonth,prev,next,nextMonth today',
      center: 'bloquer dossier,editer,honorer,supprimer',
      right: 'title'
    },
    minTime: minTime,
    maxTime: maxTime,
    slotDuration: slotDuration,
    weekNumbers: true,
    allDaySlot: false,
    allDayText: '-',
    selectable: true,
    unselectCancel: '.context-menu-item,#buttonNew,#buttonEdit,#buttonRemove,#type,#type option,#motif,input,.fc-bloquer-button,.fc-supprimer-button,.fc-dossier-button,.fc-editer-button',
    slotLabelFormat: 'H:mm',
    nowIndicator: true,
    businessHours: businessHours,
    slotEventOverlap: false,
    contentHeight: 'auto',
    eventSources: eventSources,
    eventRender: function(event, element) {
      element.attr('data-eventid', event.id);
      if (event.rendering != 'background' && popstop == 0 ) {
        element.popover({
          title: event.name,
          placement: 'bottom',
          content: event.type + ' ' + event.motif,
          container: 'body',
          trigger: 'hover'
        });
      }
    },
    eventClick: function(eventClicked, jsEvent, view) {
      selected_event = eventClicked;
      setTimeout(deselectObject, 1);
      setTimeout(function(){
        $(jsEvent.currentTarget).find("div.fc-title").addClass("underlined");
        $(jsEvent.currentTarget).find(".fc-bg").addClass("selected");
        $("#buttonNew").html("Cloner");
      }, 10);
      if (eventClicked.patientid != "0")
        getEventData4Edit(eventClicked);
      else
        clean();
    },
    eventDragStart: function( event, jsEvent, ui, view ) {
      popstop = 1;
    },
    eventDragStop: function( event, jsEvent, ui, view ) {
      popstop = 0;
    },
    eventDrop: function(event, delta, revertFunc) {
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le déplacement de cet événement ?")) {
        moveEvent(event);
      } else {
        revertFunc();
      }
    },
    eventResizeStart: function( event, jsEvent, ui, view ) {
      popstop = 1;
    },
    eventResizeStop: function( event, jsEvent, ui, view ) {
      popstop = 0;
    },
    eventResize: function(event, delta, revertFunc) {
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le changement de durée de cet événement ?")) {
        resizeEvent(event);
      } else {
        revertFunc();
      }
    },
    select: function(start, end, jsEvent, view) {
      deselectObject();
      selected_period = {start: start, end : end};
      $("#buttonRemove").hide();
      $('div.popover').popover('hide');
      formatRdvData4Display(start, moment(start).add($($("#type")[0].selectedOptions[0]).attr("data-duree"), 'm'));
    },
    unselect: function( jsEvent, view) {
      selected_period = undefined;
      selected_event = undefined;
      clean();
    },
    navLinks: true,
    navLinkDayClick: function(date, jsEvent) {
      if (confirm("Souhaitez-vous fermer cette journée ?"))
        closePeriod(date);
    }
  })

  if ($(window).width() >= 1024) {
    $.contextMenu({
      selector: ".hasmenu",
      items: {
        editer: {
          name: "Editer ce rendez-vous",
          callback: function(key, opt) {
            var eventData = $('#calendar').fullCalendar('clientEvents', this.attr('data-eventid'));
            getEventData4Edit(eventData[0]);
          }
        },
        ouvrirDossier: {
          name: "Ouvrir le dossier patient (nouvel onglet)",
          callback: function(key, opt) {
            var eventData = $('#calendar').fullCalendar('clientEvents', this.attr('data-eventid'));
            window.open(urlBase+'/patient/' + eventData[0]['patientid'] + '/', '_blank');
          }
        },
        separator1: "-----",
        pasvenupasprev: {
          name: "Marquer RDV honoré / non honoré",
          callback: function(key, opt) {
            setEventPasVenu(this.attr('data-eventid'));
          }
        },
        separator2: "-----",
        supprimer: {
          name: "Supprimer",
          callback: function(key, opt) {
            deleteEvent(this.attr('data-eventid'));
          }
        },
      }
    });

    $.contextMenu({
      selector: ".fc-highlight",
      items: {
        fermer: {
          name: "Fermer cette période",
          callback: function(key, opt) {
            closePeriod();
            selected_period = undefined;
          }
        },
      }
    });

    $.contextMenu({
      selector: ".fc-nonbusiness",
      items: {
        fermer: {
          name: "Rouvrir cette période",
          callback: function(key, opt) {
            deleteEvent(this.attr('data-eventid'));
          }
        },
      }
    });
  };

  $(window).on("click", function(e){
    deselectObject();
  });

  $("#buttonCancel").on("click", function(e) {
    e.preventDefault();
    clean();
  });

  $("#buttonRemove").on("click", function(e) {
    e.preventDefault();
    deleteEvent($("#eventID").val());
  });

  $("#buttonNew").on("click", function(e) {
    e.preventDefault();
    setRdv(true);
  });

  $("#buttonEdit").on("click", function(e) {
    e.preventDefault();
    setRdv();
  });

  $("#formRdv").on("click", ".donothing", function(e) {
    e.preventDefault();
  });

  $('#search').on("focus", function(e) {
    clean();
  });

  $("#historiquePatient").on("click", "button.moveToDate", function(e) {
    e.preventDefault();
    $('#calendar').fullCalendar('gotoDate', $(this).attr('data-date'));
  });

  //chercher patient
  $('#search').autocomplete({
    source: urlBase+'/agenda/' + userID + '/ajax/searchPatient/',
    select: function(event, ui) {
      getPatientAdminData(ui.item.patientID);
    }
  });



  $("#formRdv input[data-typeid]").typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: false,
    captureLength: 1,
    callback: function(value) {
      patientID = $('#patientID').val();
      if (patientID > 0) {
        patientID = $('#patientID').val();
        typeID = $(this).attr("data-typeID");
        source = $(this);
        instance = '';
        setPeopleData(value, patientID, typeID, source, instance);
      }
    }
  });


});

function deselectObject () {
  $("div.fc-title.underlined").removeClass("underlined");
  $("div.fc-bg.selected").removeClass("selected");
  $("#buttonNew").html("Créer");
};

function setRdv(isnew) {
  $.ajax({
    url: urlBase+'/agenda/' + userID + '/ajax/setNewRdv/',
    type: "post",
    data: {
      eventID: isnew ? "" : $('#eventID').val(),
      patientID: $('#patientID').val(),
      userID: $('#calendar').attr('data-userID'),
      start: $('#eventStartID').val(),
      end: moment($('#eventStartID').val()).add($($("#type")[0].selectedOptions[0]).attr("data-duree"), 'm').format("YYYY-MM-DD HH:mm:SS"),
      type: $('#type').val(),
      motif: $('#motif').val(),
    },
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
      clean();
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
    },
  });
}

function closePeriod(date) {
  if (!date && !selected_period)
    return;
  var start = date ? date.format('YYYY-MM-DD') + ' ' + minTime : selected_period.start.format("YYYY-MM-DD HH:mm:SS");
  var end = date ? date.format('YYYY-MM-DD') + ' ' + maxTime : selected_period.end.format("YYYY-MM-DD HH:mm:SS");
  $.ajax({
    url: urlBase+'/agenda/' + userID + '/ajax/setNewRdv/',
    type: "post",
    data: {
      eventID: '',
      patientID: '0',
      userID: $('#calendar').attr('data-userID'),
      start: start,
      end: end,
      type: '[off]',
      motif: 'Fermé',
    },
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
      clean();
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
    },
  });
}

function getPatientAdminData(patientID) {
  userID = $('#calendar').attr('data-userID');
  $.ajax({
    url: urlBase+'/agenda/' + userID + '/ajax/getPatientAdminData/',
    type: "post",
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#patientID').val(patientID);
      $.each(data, function(index, value) {
        if ($("#p_2ID").length) $("#p_" + index + "ID").val(value);
      });
      getHistoriquePatient(patientID);
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
    },
  });
}

function getHistoriquePatient(patientID) {
  $.ajax({
    url: urlBase+'/agenda/' + userID + '/ajax/getHistoriquePatient/',
    type: "post",
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $.each(data['historique'], function(index, dat) {
        chaine = '<li class="list-group-item'
        if (dat['absente'] == 'oui') chaine = chaine + ' list-group-item-danger';
        if (dat['statut'] == 'deleted') chaine = chaine + ' list-group-item-warning';
        chaine = chaine + '">';
        chaine = chaine + '<button type="button" class="btn btn-default btn-xs moveToDate" data-date="' + dat['dateiso'] + '"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></button>&nbsp;&nbsp;&nbsp;';
        chaine = chaine + dat['start'] + ' : ' + dat['type'];
        if (dat['statut'] == 'deleted') chaine = chaine + ' <small>[annulé]</small>';
        if (dat['absente'] == 'oui') chaine = chaine + ' <small>[non honoré]</small>';
        chaine = chaine + '</li>';

        $('#historiquePatient ul').append(chaine);

      });
      $('#HistoriqueRdvResume button.btn-default').html(data['stats']['total']);
      $('#HistoriqueRdvResume button.btn-success').html(data['stats']['ok']);
      $('#HistoriqueRdvResume button.btn-warning').html(data['stats']['annule']);
      $('#HistoriqueRdvResume button.btn-danger').html(data['stats']['absent']);
      $('#historiquePatient').show();
    },
    error: function() {

    },
  });
}

function getEventData4Edit(eventClicked) {
  clean();
  $('#titreRdv').html('Editer rendez-vous');
  $("#patientID").val(eventClicked.patientid);
  $("#eventID").val(eventClicked.id);

  getPatientAdminData(eventClicked.patientid);

  $("#motif").val(eventClicked.motif);
  $("#type").val(eventClicked.type);
  $("#buttonRemove").show();
  $("#buttonEdit").show();
  formatRdvData4Display(eventClicked.start, eventClicked.end);
}

function formatRdvData4Display(start, end) {
  duree = end.diff(start, 'minutes');
  $('.dateHeureDisplay').removeClass('bg-danger');
  $('.dateHeureDisplay').html('<button class="btn btn btn-success donothing"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> ' + start.format('DD/MM à HH:mm') + '</button> <button class="btn btn-default donothing"><span class="glyphicon glyphicon-time" aria-hidden="true"></span> ' + duree + "mn</button>");

  $('#eventStartID').val(start.format('YYYY-MM-DD HH:mm:SS'));
  $('#eventEndID').val(end.format('YYYY-MM-DD HH:mm:SS'));

  if ($("#patientID").val() > 0) $('#buttonNew').removeAttr('disabled');
}

function clean() {
  $('#titreRdv').html('Nouveau rendez-vous');
  $("#formRdv input[name!='userid']").val('');
  $("#formRdv textarea").val('');
  $("#formRdv select").val($("#formRdv select option:first").val());

  $('.dateHeureDisplay').addClass('bg-danger');
  $('.dateHeureDisplay').html("Selectionner sur l'agenda");

  $('#eventStartID').val('');
  $('#eventEndID').val('');

  $('#buttonNew').attr('disabled', 'disabled');
  $('#historiquePatient').hide();
  $('#historiquePatient ul').html('');
  $('#HistoriqueRdvResume button').html('');
  $("#buttonRemove").hide();
  $("#buttonEdit").hide();
}

function deleteEvent(eventid) {
  if (confirm("Confirmez-vous la suppression de ce rendez-vous ?")) {
    userID = $('#calendar').attr('data-userID');
    $.ajax({
      url: urlBase+'/agenda/' + userID + '/ajax/delEvent/',
      type: "post",
      data: {
        eventid: eventid,
      },
      dataType: "json",
      success: function(data) {
        $('#calendar').fullCalendar('removeEvents', eventid);
        clean();
      },
      error: function() {
        alert('Il y a un problème. Il faut recharger la page.');
      },
    });
  }
}

function setEventPasVenu(eventid) {
  userID = $('#calendar').attr('data-userID');
  $.ajax({
    url: urlBase+'/agenda/' + userID + '/ajax/setEventPasVenu/',
    type: "post",
    data: {
      eventID: eventid,
    },
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
    },
  });
}

function moveEvent(event) {

  userID = $('#calendar').attr('data-userID');

  $.ajax({
    url: urlBase+'/agenda/' + userID + '/ajax/moveEvent/',
    type: "post",
    data: {
      eventid: event.id,
      start: event.start.format('YYYY-MM-DD HH:mm:SS'),
      end: event.end.format('YYYY-MM-DD HH:mm:SS')
    },
    dataType: "json",
    success: function(data) {
   	  $('#calendar').fullCalendar('refetchEvents');
      clean();
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
    },
  });

}

function resizeEvent(event) {

  userID = $('#calendar').attr('data-userID');

  $.ajax({
    url: urlBase+'/agenda/' + userID + '/ajax/moveEvent/',
    type: "post",
    data: {
      eventid: event.id,
      start: event.start.format('YYYY-MM-DD HH:mm:SS'),
      end: event.end.format('YYYY-MM-DD HH:mm:SS')
    },
    dataType: "json",
    success: function(data) {
      clean();
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
    },
  });

}

//fonction pour la sauvegarde automatique
function setPeopleData(value, patientID, typeID, source, instance) {
  //alert(patientID);
  if (patientID && typeID && source) {
    $.ajax({
      url: urlBase+'/ajax/setPeopleData/',
      type: 'post',
      data: {
        value: value,
        patientID: patientID,
        typeID: typeID,
        instance: '0'
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
