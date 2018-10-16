/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2018
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
 * Fonctions JS pour les transmisions
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

function posterTransmission() {
  error = 0;
  sujet = $('#transSujet').val();
  destinataires = $('#transDestinataires').val();
  patientConcerne = $('#transPatientConcID').val();
  texte = $('#transTransmission').val();
  priorite = $('#modalTransmission input[name=transPriorite]:checked').val();
  if (sujet.length < 1) {
    $('#transSujet').addClass('is-invalid');
    error++;
  } else {
    $('#transSujet').removeClass('is-invalid');
  }
  if (destinataires.length < 1) {
    $('#transDestinataires').addClass('is-invalid');
    error++;
  } else {
    $('#transDestinataires').removeClass('is-invalid');
  }
  if (error == 0) {
    $.ajax({
      url: urlBase + '/transmissions/ajax/transTransmissionPoster/',
      type: 'post',
      data: {
        transID: $('#transID').val(),
        sujet: sujet,
        destinataires: destinataires,
        patientConcerne: patientConcerne,
        texte: texte,
        priorite: priorite,
      },
      dataType: "json",
      success: function(data) {
        if (transmissionNewNextLocation == 'stayHere') {
          $('#modalTransmission').modal('hide');
          alert_popup("success", 'Transmission enregistrée !');
        } else {
          window.location.href = urlBase + '/transmission/' + data.sujetID + '/';
        }
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  }
}

function editerTransmission(id) {
  $.ajax({
    url: urlBase + '/transmissions/ajax/transGetTransmissionData/',
    type: 'post',
    data: {
      id: id,
    },
    dataType: "json",
    success: function(data) {
      $('h5.modal-title').html('Éditer la transmision');
      //peupler la modal
      $('#transSujet').val(data.sujet);
      $('#transID').val(data.id);
      $('#transTransmission').val(data.texte);
      $('#modalTransmission input[value=' + data.priorite + ']').attr('checked', 'checked');
      if (data.aboutID > 0) {
        $('#transPatientConcID').val(data.aboutID);
        $('#transPatientConcSel').html('<button id="transPatientConcSelDel" class="btn btn-sm  btn-light"><i class="far fa-trash-alt"></i></button> ' + data.identiteAbout);
        $('#transPatientConcSel').removeClass('d-none');
      }
      $('#transDestinataires option').removeAttr('selected');
      $.each(data.destinataires, function(index, v) {
        $('#transDestinataires option[value="' + v.toID + '"]').attr('selected', 'selected');
      });
      $('#modalTransmission').modal('toggle');
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

function posterReponse() {
  texte = $('#texteReponse').val();
  if (texte.length > 1) {
    $.ajax({
      url: urlBase + '/transmissions/ajax/transReponsePoster/',
      type: 'post',
      data: {
        sujetID: $('#formRepSujetID').val(),
        reponseID: $('#formRepReponseID').val(),
        texte: texte,
      },
      dataType: "json",
      success: function(data) {
        $('#blocReponses').html(data.html);
        $('#texteReponse').val('');
        $('#transmissionRepondre').collapse('toggle');
        updateIconesOnAnswer();
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  } else {
    alert("Pas de texte saisi pour la réponse");
  }
}

function editerReponse(id) {
  $.ajax({
    url: urlBase + '/transmissions/ajax/transGetTransmissionData/',
    type: 'post',
    data: {
      id: id,
    },
    dataType: "json",
    success: function(data) {
      $('#formRepReponseID').val(data.id)
      $('#texteReponse').val(data.texte);
      $('#transmissionRepondre').collapse('show');
      updateIconesOnAnswer();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

function setTransmissionTraitee(id) {
  $.ajax({
    url: urlBase + '/transmissions/ajax/transTransmissionMarquer/',
    type: 'post',
    data: {
      transID: id,
    },
    dataType: "json",
    success: function(data) {
      location.reload();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

function setTransmissionEffacee(id) {
  if (confirm("Confirmez-vous cette action ?")) {
    $.ajax({
      url: urlBase + '/transmissions/ajax/transTransmissionSupp/',
      type: 'post',
      data: {
        transID: id,
      },
      dataType: "json",
      success: function(data) {
        location.reload();
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  }
}

function getTransmissions() {
  if ($('#inbox').hasClass('btn-secondary')) {
    box = 'inbox';
  } else {
    box = 'outbox';
  }
  if ($('#traitees').hasClass('btn-secondary')) {
    traite = 'traitees';
  } else if ($('#nontraitees').hasClass('btn-secondary')) {
    traite = 'nontraitees';
  } else {
    traite = 'toutes';
  }

  if ($('#ctrlTransLecture').hasClass('btn-secondary')) {
    lecture = 'nonlues';
  } else {
    lecture = 'toutes';
  }

  $.ajax({
    url: urlBase + '/transmissions/ajax/transGetTransmissions/',
    type: 'post',
    data: {
      box: box,
      traite: traite,
      lecture: lecture,
      page: numPageTrans
    },
    dataType: "json",
    success: function(data) {
      $('#listeTransmissions tbody').html(data.html);
      if (data.nbTransRetour > 0) {
        $('#pageCourante').html('Page ' + data.page + ' / ' + Math.ceil(data.nbTotalTran / data.nbParPage));
      }
      if (data.page < Math.ceil(data.nbTotalTran / data.nbParPage)) {
        $('#pagePrecedente').removeClass('d-none');
      } else {
        $('#pagePrecedente').addClass('d-none');
      }
      if (data.page > 1) {
        $('#pageSuivante').removeClass('d-none');
      } else {
        $('#pageSuivante').addClass('d-none');
      }

    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}

function resetTransmissionModal() {
  $('#transID').val('');
  $('#transSujet').val('');
  $('#transTransmission').val('');
  $('#transDestinataires option').removeAttr('selected');
  $('#transPatientConcID').val('');
  $('#transPatientConcSel').html('');
  $('#modalTransmission input[value=0]').attr('checked', 'checked');
  $('#transPatientConcSel').addClass('d-none');
}

function updateIconesOnAnswer() {
  $('.toRemoveOnUpdate').remove();
  $('.toChangeOnUpdate').addClass('text-danger');
  $('.toChangeOnUpdate').removeClass('text-success');
  $('.toChangeOnUpdate').addClass('fa-eye-slash');
  $('.toChangeOnUpdate').removeClass('fa-eye');
}
