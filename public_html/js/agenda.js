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
 */

$(document).ready(function() {
  popstop = 0;
  $(function() {
    $('[data-toggle="popover"]').popover();

  })

  userID = $('#calendar').attr('data-userID');
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
        url: '/agenda/' + userID + '/ajax/getEvents/'
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
    hiddenDays: hiddenDays,
    customButtons: {
      nextMonth: {
        icon: 'right-double-arrow',
        click: function() {
          $('#calendar').fullCalendar('incrementDate', moment.duration(1, 'months'));
        }
      },
      lastMonth: {
        icon: 'left-double-arrow',
        click: function() {
          $('#calendar').fullCalendar('incrementDate', moment.duration(-1, 'months'));
        }
      },
    },
    header: {
      left: 'lastMonth,prev,next,nextMonth today',
      center: '',
      right: 'title'
    },
    minTime: minTime,
    maxTime: maxTime,
    slotDuration: slotDuration,
    weekNumbers: true,
    allDaySlot: false,
    allDayText: '-',
    selectable: true,
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
    eventClick: function(eventClicked, jsEvent, view) {},
    eventDragStart: function( event, jsEvent, ui, view ) {
      popstop = 1;
    },
    eventDragStop: function( event, jsEvent, ui, view ) {
      popstop = 0;
    },
    eventDrop: function(event, delta, revertFunc) {
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le déplacement de ce rendez-vous ?")) {
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
      if (confirm("Confirmez-vous le changement de durée de ce rendez-vous ?")) {
        resizeEvent(event);
      } else {
        revertFunc();
      }
    },
    select: function(start, end, jsEvent, view) {
      $('div.popover').popover('hide');
      formatRdvData4Display(start, end);
    },
    navLinks: true,
    navLinkDayClick: function(date, jsEvent) {
      if (confirm("Souhaitez-vous fermer cette journée ?")) {
        closeDay(date);
      }
    }
  })


  $.contextMenu({
    selector: ".hasmenu",
    items: {
      editer: {
        name: "Editer ce rendez-vous",
        callback: function(key, opt) {
          eventid = this.attr('data-eventid');
          eventData = $('#calendar').fullCalendar('clientEvents', eventid);
          getEventData4Edit(eventData[0]);
        }
      },
      ouvrirDossier: {
        name: "Ouvrir le dossier patient",
        callback: function(key, opt) {
          eventid = this.attr('data-eventid');
          eventData = $('#calendar').fullCalendar('clientEvents', eventid);
          window.open('/patient/' + eventData[0]['patientid'] + '/', '_blank');
        }
      },
      separator1: "-----",
      pasvenupasprev: {
        name: "Marquer RDV honoré / non honoré",
        callback: function(key, opt) {
          eventid = this.attr('data-eventid');
          setEventPasVenu(eventid);
        }
      },
      separator2: "-----",
      supprimer: {
        name: "Supprimer",
        callback: function(key, opt) {
          eventid = this.attr('data-eventid');
          deleteEvent(eventid);
        }
      },
    }
  });


  $("#buttonCanceled").on("click", function(e) {
    e.preventDefault();
    clean();
  });

  $("#buttonSubmit").on("click", function(e) {
    e.preventDefault();
    setNewRdv();
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

  //chercher patiente
  $('#search').autocomplete({
    source: '/agenda/' + userID + '/ajax/searchPatient/',
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

function setNewRdv() {
  $.ajax({
    url: '/agenda/' + userID + '/ajax/setNewRdv/',
    type: "post",
    data: {
      eventID: $('#eventID').val(),
      patientID: $('#patientID').val(),
      userID: $('#calendar').attr('data-userID'),
      start: $('#eventStartID').val(),
      end: $('#eventEndID').val(),
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

function closeDay(date) {
  datedujour = date.format('YYYY-MM-DD');
  $.ajax({
    url: '/agenda/' + userID + '/ajax/setNewRdv/',
    type: "post",
    data: {
      eventID: '',
      patientID: '0',
      userID: $('#calendar').attr('data-userID'),
      start: datedujour + ' ' + minTime,
      end: datedujour + ' ' + maxTime,
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
    url: '/agenda/' + userID + '/ajax/getPatientAdminData/',
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
    url: '/agenda/' + userID + '/ajax/getHistoriquePatient/',
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

  formatRdvData4Display(eventClicked.start, eventClicked.end);
}

function formatRdvData4Display(start, end) {
  duree = end.diff(start, 'minutes');
  $('.dateHeureDisplay').removeClass('bg-danger');
  $('.dateHeureDisplay').html('<button class="btn btn btn-success donothing"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> ' + start.format('DD/MM à HH:mm') + '</button> <button class="btn btn-default donothing"><span class="glyphicon glyphicon-time" aria-hidden="true"></span> ' + duree + "mn</button>");

  $('#eventStartID').val(start.format('YYYY-MM-DD HH:mm:SS'));
  $('#eventEndID').val(end.format('YYYY-MM-DD HH:mm:SS'));

  if ($("#patientID").val() > 0) $('#buttonSubmit').removeAttr('disabled');
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

  $('#buttonSubmit').attr('disabled', 'disabled');
  $('#historiquePatient').hide();
  $('#historiquePatient ul').html('');
  $('#HistoriqueRdvResume button').html('');

}

function deleteEvent(eventid) {
  if (confirm("Confirmez-vous la suppression de ce rendez-vous ?")) {
    userID = $('#calendar').attr('data-userID');
    $.ajax({
      url: '/agenda/' + userID + '/ajax/delEvent/',
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
    url: '/agenda/' + userID + '/ajax/setEventPasVenu/',
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
    url: '/agenda/' + userID + '/ajax/moveEvent/',
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

function resizeEvent(event) {

  userID = $('#calendar').attr('data-userID');

  $.ajax({
    url: '/agenda/' + userID + '/ajax/moveEvent/',
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
      url: '/ajax/setPeopleData/',
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
