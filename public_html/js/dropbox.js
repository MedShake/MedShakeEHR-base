/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2020
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
 * Fonctions JS pour dropbox
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  //selection du doc
  $("li.selectFile").on("click", function(e) {
    e.preventDefault();
    viewDoc($(this));
  });

  //autocomplete pour la recherche patient
  $('body').delegate('#searchPeopleID', 'focusin', function() {
    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: urlBase + '/dropbox/ajax/getPatients/',
      select: function(event, ui) {
        $('#tabPatients').append(constructPatientLine(ui.item));
        selectPatient($("tr.patientSelect[data-patientid = " + ui.item.id + "]"));

        $('#searchPeopleID').val(ui.item.label);
        $('#searchPeopleID').attr('data-id', ui.item.id);
      }
    });
  });

  //sélection patient
  $("#view").on("click", ".patientSelect", function(e) {
    selectPatient($(this));
  });


  //taille prévisu image
  $("#view").on("click", ".reduceImagePreviewSize", function(e) {
    if ($('#docImageView').hasClass('w-50')) {
      $('#docImageView').removeClass('w-50');
      $('button.reduceImagePreviewSize i').addClass('fa-search-minus').removeClass('fa-search-plus');
    } else {
      $('#docImageView').addClass('w-50');
      $('button.reduceImagePreviewSize i').addClass('fa-search-plus').removeClass('fa-search-minus');
    }
  });

  // mettre à la poubelle
  $("li.selectFile div.poubelle").on("click", function(e) {
    e.preventDefault();
    if (confirm("Supprimer sans classer dans un dossier ?")) {
      delDoc($(this).parent('li.selectFile'));
    }
    e.stopPropagation();

  });

  // tab changement
  $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
    viewDoc($(e.target.hash).find('li.list-group-item').first());
    $(e.relatedTarget.hash).find('li.list-group-item').removeClass('list-group-item-success'); // previous active tab
  });

  // rafraichir quand on classe
  $("#view").on("submit", "#classerDansDossier", function(e) {
    setTimeout(function() {
      location.reload();
    }, 500);

  });

  // premier chargement
  viewDoc($("li.list-group-item-success"));

  // si onglet indiqué en hash
  if ($(location).attr('hash').length > 0) {
    $($(location).attr('hash') + '-tab').trigger('click');
  }

  //rotation de l'image
  $("#view").on("click", ".rotationImage90", function(e) {
    rotateImage90($(this));
  });

  function selectPatient(el) {
    $("tr.patientSelect").removeClass('table-success font-weight-bold');
    $(el).addClass('table-success font-weight-bold');
    patientID = $(el).attr('data-patientID');
    $("#idConfirmPatientID").val(patientID);
    if (patientID > 0) {
      $("#submitIndicID").html(patientID);
      $("#submitBoutonClasser").removeClass('d-none');

    }
  }
});

function rotateImage90(el) {
  box = el.attr('data-box');
  filename = el.attr('data-filename');
  direction = el.attr('data-direction');
  el.find('i').addClass('fa-spin');
  $.ajax({
    url: urlBase + '/dropbox/ajax/rotateDoc/',
    type: 'post',
    data: {
      box: box,
      filename: filename,
      direction: direction,
    },
    dataType: "html",
    success: function(data) {
      d = new Date();
      $("#docImageView").attr("src", $("#docImageView").attr('src') + "?" + d.getTime());
      el.find('i').removeClass('fa-spin');
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');
    }
  });
}


function constructPatientLine(data) {
  if (data.birthname == null) {
    data.birthname = '';
  }
  if (data.lastname == null) {
    data.lastname = '';
  }

  if (data.birthname.length > 0 && data.lastname.length > 0) {
    identiteNom = data.lastname + ' (' + data.birthname + ')';
  } else if (data.lastname.length > 0) {
    identiteNom = data.lastname;
  } else if (data.birthname.length > 0) {
    identiteNom = data.birthname;
  } else {
    identiteNom = '';
  }

  line = '<tr class="patientSelect cursor-pointer" data-patientid="' + data.id + '"> \
    <td>#' + data.id + '</td> \
    <td>' + identiteNom + '</td> \
    <td>' + data.firstname + '</td> \
    <td>' + data.birthdate + '</td> \
    <td class="small">' + data.streetNumber + ' ' + data.street + ' ' + data.postalCodePerso + ' ' + data.city + '</td> \
    <td  class="small">' + (data.nss != null ? data.nss : '') + '</td> \
    <td> \
    <a class="btn btn-light btn-sm" role="button" href="' + urlBase + '/patient/' + data.id + '/" target="_blank"> \
      <span class="fas fa-folder-open" aria-hidden="true" title="Voir dossier"></span> \
    </a> \
    </td> \
  </tr>";'
  return line;
}

function viewDoc(el) {
  box = el.attr('data-box');
  filename = el.attr('data-filename');
  $.ajax({
    url: urlBase + '/dropbox/ajax/viewDoc/',
    type: 'post',
    data: {
      box: box,
      filename: filename
    },
    dataType: "html",
    success: function(data) {
      $('li.selectFile').removeClass('list-group-item-success');
      $('li.selectFile').find("div.poubelle").addClass('d-none');
      el.addClass('list-group-item-success');
      el.find("div.poubelle").removeClass('d-none');
      $('#view').html(data);
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

function delDoc(el) {
  box = el.attr('data-box');
  filename = el.attr('data-filename');
  $.ajax({
    url: urlBase + '/dropbox/ajax/delDoc/',
    type: 'post',
    data: {
      box: box,
      filename: filename
    },
    dataType: "json",
    success: function(data) {
      if (data.status == 'ok') {
        el.remove();
        nbInBox = parseInt($('span.badge[data-box ="' + box + '"]').html());
        $('span.badge[data-box ="' + box + '"]').html((nbInBox - 1));

        nbInBox = parseInt($('span.badgeBoiteDepot').html());
        $('span.badgeBoiteDepot').html((nbInBox - 1));
      } else {
        alert_popup("danger", 'Problème, le fichier ne peut être supprimé !');
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}
