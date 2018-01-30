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

var selected_period;
var selected_event;
var popstop = $(window).width() < 1024;

$(document).ready(function() {

  $(function() {
    $('[data-toggle="popover"]').popover();

  })

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
    boutonsHeaderCenter = 'bloquer dossier,deplacer,cloner,honorer,supprimer';
  }


  if (!eventSources) {
    eventSources = [{
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
        click: function() {
          if (!selected_event || selected_event.patientid == "0")
            return alert("Sélectionnez d'abord un RDV, puis cliquez ce bouton pour ouvrir le dossier du patient");
          window.open(urlBase + '/patient/' + selected_event.patientid + '/', '_patient');
          selected_event = undefined;
          selected_period = undefined;
          clean();
        },
      },
      cloner: {
        click: function() {
          if (!selected_event || !selected_period)
            return alert("Sélectionnez d'abord un événement à cloner, puis la position où placer le clone, avant de cliquez sur ce bouton");
          if (selected_event.patientid != "0") {
            $('#eventID').val('');
            setRdv();
          } else {
            selected_period.end = moment(selected_period.start).add(moment(selected_event.end).diff(selected_event.start));
            closePeriod();
          }
          selected_event = undefined;
          selected_period = undefined;
        },
      },
      deplacer: {
        click: function() {
          if (!selected_event || !selected_period)
            return alert("Sélectionnez d'abord un événement à déplacer, puis sa nouvelle position, avant de cliquer ce bouton");
          selected_event.end = moment(selected_period.start).add(moment(selected_event.end).diff(selected_event.start));
          selected_event.start = selected_period.start;
          moveEvent(selected_event);
          selected_event = undefined;
          selected_period = undefined;
        },
      },
      supprimer: {
        click: function() {
          if (!selected_event)
            return alert("cliquez d'abord un événement à supprimer, avant de cliquer ce bouton");
          deleteEvent(selected_event.id);
          selected_event = undefined;
          selected_period = undefined;
        },
      },
      bloquer: {
        click: function() {
          if (!selected_period)
            return alert("Sélectionnez d'abord une période à fermer, avant de cliquer ce bouton");
          closePeriod();
          selected_period = undefined;
        },
      },
      honorer: {
        click: function() {
          if (!selected_event)
            return alert("selectionnez d'abord un RDV à marquer honoré/non honoré, avant de cliquer ce bouton");
          if ($('#patientID').val() > 0) {
            setEventPasVenu(selected_event.id);
            selected_event = undefined;
            selected_period = undefined;
          }
        },
      },
    },
    bootstrapGlyphicons: {
      lastMonth: 'glyphicon-chevron-left',
      nextMonth: 'glyphicon-chevron-right',
      prev: 'glyphicon-menu-left',
      next: 'glyphicon-menu-right',
      dossier: 'glyphicon-folder-open',
      deplacer: 'glyphicon-transfer',
      bloquer: 'glyphicon-ban-circle',
      cloner: 'glyphicon-duplicate',
      supprimer: 'glyphicon-remove',
      honorer: 'glyphicon-alert',
    },
    header: {
      left: 'lastMonth,prev,next,nextMonth today',
      center: boutonsHeaderCenter,
      right: 'title'
    },
    minTime: minTime,
    maxTime: maxTime,
    slotDuration: slotDuration,
    weekNumbers: true,
    allDaySlot: false,
    allDayText: '-',
    selectable: true,
    unselectCancel: '.context-menu-item,#buttonPrincipal,#buttonRemove,#buttonMark,#historiquePatient,#type,#type option,#motif,input,.fc-bloquer-button,.fc-supprimer-button,.fc-dossier-button,.fc-honorer-button,.fc-deplacer-button,.fc-cloner-button',
    slotLabelFormat: 'H:mm',
    slotLabelInterval: slotLabelInterval,
    nowIndicator: true,
    businessHours: businessHours,
    slotEventOverlap: false,
    contentHeight: 'auto',
    eventSources: eventSources,
    eventRender: function(event, element) {
      element.attr('data-eventid', event.id);
      if (event.rendering != 'background' && popstop == 0) {
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
      $(".fc-bg").removeClass("selected");

      selected_event = eventClicked;

      $("#buttonMark").html(eventClicked.absent == "oui" ? "Marquer comme honoré" : "Marquer comme non honoré");
      setTimeout(function() {
        $(jsEvent.currentTarget).find(".fc-bg").addClass("selected");
      }, 10);

      if (eventClicked.patientid != "0")
        getEventData4Edit(eventClicked);
      else
        clean();
    },
    eventDragStart: function(event, jsEvent, ui, view) {
      popstop = 1;
    },
    eventDragStop: function(event, jsEvent, ui, view) {
      popstop = $(window).width() < 1024;
    },
    eventDrop: function(event, delta, revertFunc) {
      $('div.popover').popover('hide');
      if (confirm("Confirmez-vous le déplacement de cet événement ?")) {
        moveEvent(event);
      } else {
        revertFunc();
      }
    },
    eventResizeStart: function(event, jsEvent, ui, view) {
      popstop = 1;
    },
    eventResizeStop: function(event, jsEvent, ui, view) {
      popstop = $(window).width() < 1024;
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
      selected_period = {
        start: start,
        end: end
      };
      if ($('#patientID').val() > 0) {
        $('#buttonPrincipal').removeAttr('disabled');
        $('#buttonCancel').removeAttr('disabled');

        $('div.popover').popover('hide');
        formatRdvData4Display(start, moment(start).add($($("#type")[0].selectedOptions[0]).attr("data-duree"), 'm'));
      }

    },
    unselect: function(jsEvent, view) {},
    navLinks: true,
    navLinkDayClick: function(date, jsEvent) {
      if (confirm("Souhaitez-vous fermer cette journée ?"))
        closePeriod(date);
    }
  })

  ////////////////////////////////////////////////////////////////////////
  ///////// Définition des titles boutons agenda

  $(".fc-lastMonth-button").attr("title", "Mois précédent");
  $(".fc-prev-button").attr("title", "Semaine précédente");
  $(".fc-next-button").attr("title", "Semaine suivante");
  $(".fc-nextMonth-button").attr("title", "Mois suivant");
  $(".fc-deplacer-button").attr("title", "Déplacer un événement\n\nSelectionnez d'abord l'événement à déplacer,\npuis son nouvel emplacement");
  $(".fc-cloner-button").attr("title", "Cloner un événement\n\nSelectionnez d'abord l'événement à cloner,\npuis l'emplacement du clone");
  $(".fc-supprimer-button").attr("title", "Supprimer un événement\n\nSelectionnez d'abord un événement");
  $(".fc-honorer-button").attr("title", "Marquer un RDV honoré/non honoré\n\nSelectionnez d'abord un RDV");
  $(".fc-bloquer-button").attr("title", "Fermer une période\n\nSelectionnez d'abord une période");
  $(".fc-dossier-button").attr("title", "Ouvrir dossier patient\n\nSelectionnez d'abord un RDV");

  ////////////////////////////////////////////////////////////////////////
  ///////// Définition des menus clics droit

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
            window.open(urlBase + '/patient/' + eventData[0]['patientid'] + '/', '_blank');
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
        separator3: "-----",
        logs: {
          name: "Historique des modifications de ce rdv",
          callback: function(key, opt) {
            window.open(urlBase + '/logs/agenda/' + $('#calendar').attr('data-userID') + '/' + this.attr('data-eventid') + '/', '_blank');
          }
        }
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

  ////////////////////////////////////////////////////////////////////////
  ///////// Panneau latéral :  observations boutons agenda

  $("#buttonCancel").on("click", function(e) {
    e.preventDefault();
    clean();
  });

  $("#buttonClone").on("click", function(e) {
    e.preventDefault();
    if (!selected_event || !selected_period) {
      alert("Sélectionnez d'abord un événement à cloner, puis la position où placer le clone, avant de cliquez sur ce bouton");
    } else {
      $('#eventID').val('');
      setRdv();
      clean();
    }
  });

  $("#buttonMark").on("click", function(e) {
    e.preventDefault();
    setEventPasVenu(selected_event.id);
    clean();
  });

  $("#buttonRemove").on("click", function(e) {
    e.preventDefault();
    deleteEvent($("#eventID").val());
  });

  $("#buttonPrincipal").on("click", function(e) {
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

  ////////////////////////////////////////////////////////////////////////
  ///////// Panneau latéral : chercher / nouveau / editer

  //chercher patient : porte d'entrée d'un nouveau rdv
  $('#search').autocomplete({
    source: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/searchPatient/',
    select: function(event, ui) {
      clean();
      $('#buttonCancel').removeAttr('disabled');
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
        instance = '0';
        setPeopleData(value, patientID, typeID, source, instance);
      }
    }
  });

  //modal création nouveau patient
  $("button.modal-save").on("click", function(e) {
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    ajaxModalFormSave(form, modal);

  });

});

////////////////////////////////////////////////////////////////////////
///////// Fonctions

// Enregistrer un rendez-vous
function setRdv() {
  if ($('#eventID').val() > 0) {
    isnew = false;
  } else {
    isnew = true;
  }

  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/setNewRdv/',
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
      clean();
    },
  });
}

// Fermer une période
function closePeriod(date) {
  if (!date && !selected_period)
    return;
  var start = date ? date.format('YYYY-MM-DD') + ' ' + minTime : selected_period.start.format("YYYY-MM-DD HH:mm:SS");
  var end = date ? date.format('YYYY-MM-DD') + ' ' + maxTime : selected_period.end.format("YYYY-MM-DD HH:mm:SS");
  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/setNewRdv/',
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
      clean();
    },
  });
}

// Obtenir les données du patient
function getPatientAdminData(patientID) {
  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/getPatientAdminData/',
    type: "post",
    data: {
      patientID: patientID,
    },
    dataType: "json",
    success: function(data) {
      $('#patientID').val(patientID);
      $.each(data, function(index, value) {
        if ($("#id_lastname_id").length) $("#id_" + index + "_id").val(value);
      });
      getHistoriquePatient(patientID);
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
      clean();
    },
  });
}

// Obtenir l'historique de rdv du patient
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
      alert('Il y a un problème. Il faut recharger la page.');
      clean();
    },
  });
}

// Mettre en place l'édition d'un rdv
function getEventData4Edit(eventClicked) {
  $('#titreRdv').html('Editer rendez-vous');
  $("#patientID").val(eventClicked.patientid);
  $("#eventID").val(eventClicked.id);

  $('#buttonPrincipal').removeAttr('disabled');
  $('#buttonPrincipal').html('Modifier');
  $('#buttonCancel').removeAttr('disabled');
  $('#buttonAutresActions').removeAttr('disabled');

  getPatientAdminData(eventClicked.patientid);

  $("#motif").val(eventClicked.motif);
  $("#type").val(eventClicked.type);
  formatRdvData4Display(eventClicked.start, eventClicked.end);
}

// Mettre en boutons date / heure dans le panneau lateral la plage horaire
function formatRdvData4Display(start, end) {
  duree = end.diff(start, 'minutes');
  $('.dateHeureDisplay').removeClass('bg-danger');
  $('.dateHeureDisplay').html('<button class="btn btn btn-success donothing"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> ' + start.format('DD/MM à HH:mm') + '</button> <button class="btn btn-default donothing"><span class="glyphicon glyphicon-time" aria-hidden="true"></span> ' + duree + "mn</button>");

  $('#eventStartID').val(start.format('YYYY-MM-DD HH:mm:SS'));
  $('#eventEndID').val(end.format('YYYY-MM-DD HH:mm:SS'));

  if ($("#patientID").val() > 0) $('#buttonNew').removeAttr('disabled');
}

// Nettoyage pour retour à l'état initial de la page
function clean() {

  $(".fc-bg").removeClass("selected");

  $('#titreRdv').html('Nouveau rendez-vous');
  $("#formRdv input[name!='userid']").val('');
  $("#formRdv textarea").val('');
  $("#formRdv select").val($("#formRdv select option:first").val());

  $('.dateHeureDisplay').addClass('bg-danger');
  $('.dateHeureDisplay').html("Selectionner sur l'agenda");

  $('#eventStartID').val('');
  $('#eventEndID').val('');

  $('#buttonPrincipal').attr('disabled', 'disabled');
  $('#buttonPrincipal').html('Créer');
  $('#buttonCancel').attr('disabled', 'disabled');
  $('#buttonAutresActions').attr('disabled', 'disabled');

  $('#historiquePatient').hide();
  $('#historiquePatient ul').html('');
  $('#HistoriqueRdvResume button').html('');
}

// Effacer un rdv
function deleteEvent(eventid) {
  if (confirm("Confirmez-vous la suppression de cet événement ?")) {
    $.ajax({
      url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/delEvent/',
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
        clean();
      },
    });
  }
}

// Marquer le rdv comme non honoré
function setEventPasVenu(eventid) {
  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/setEventPasVenu/',
    type: "post",
    data: {
      eventID: eventid,
    },
    dataType: "json",
    success: function(data) {
      $('#calendar').fullCalendar('refetchEvents');
      clean();
    },
    error: function() {
      alert('Il y a un problème. Il faut recharger la page.');
      clean();
    },
  });
}

// Déplacer un rdv
function moveEvent(event) {

  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/moveEvent/',
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
      clean();
    },
  });

}

// Redimensionner les horaires d'un rdv
function resizeEvent(event) {

  $.ajax({
    url: urlBase + '/agenda/' + $('#calendar').attr('data-userID') + '/ajax/moveEvent/',
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
      clean();
    },
  });

}

// Création de nouveau patient
function ajaxModalFormSave(form, modal) {
  var data = {};
  $(form + ' input, ' + form + ' select, ' + form + ' textarea').each(function(index) {
    var input = $(this);
    data[input.attr('name')] = input.val();
  });

  var url = $(form).attr('action');
  data["groupe"] = $(form).attr('data-groupe');

  $.ajax({
    url: url,
    type: 'post',
    data: data,
    dataType: "json",
    success: function(data) {
      if (data.status == 'ok') {
        // gestion modal
        $(modal).modal('hide');
        $(modal + ' form input[name^="p_"]').val('');
        $(modal + ' form textarea').val('');
        $(modal + ' form select option').prop('selected', function() {
            return this.defaultSelected;
        });
        $("#id_birthdate_id").prev('label').html('Date de naissance');

        // injection du patient pour nouveau rdv
        clean();
        $('#buttonCancel').removeAttr('disabled');
        getPatientAdminData(data.toID);

      } else {
        $(modal + ' div.alert').show();
        $(modal + ' div.alert ul').html('');
        $.each(data.msg, function(index, value) {
          $(modal + ' div.alert ul').append('<li>' + value + '</li>');
        });
        $.each(data.code, function(index, value) {
          $(modal + ' #' + value + 'ID').closest("div.form-group").addClass('has-error');
        });
      }
    },
    error: function() {
      alert('Problème, rechargez la page !');
    }
  });
}
