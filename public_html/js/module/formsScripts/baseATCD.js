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
 * Js pour le formulaire d'antécédents (colonne latérale dossier patient)
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  //calcul IMC
  if ($('input[name=p_imc]').length > 0) {

    imc = imcCalc($('input[name=p_poids]').val(), $('input[name=p_taillePatient]').val());
    if (imc > 0) {
      $('input[name=p_imc]').val(imc);
    }

    $("#patientLatCol").on("keyup", "input[name=p_poids] , input[name=p_taillePatient]", function() {
      poids = $('input[name=p_poids]').val();
      taille = $('input[name=p_taillePatient]').val();
      imc = imcCalc(poids, taille);
      $('input[name=p_imc]').val(imc);
      patientID = $('#identitePatient').attr("data-patientID");
      setPeopleDataByTypeName(imc, patientID, 'imc', 'input[name=p_imc]', '0');

    });
  }

  //ajutement auto des textarea en hauteur
  $("#formName_baseATCD textarea").each(function(index, element) {
    $(element).css("overflow", "hidden");
    if (element.offsetParent && element.value!='')
      auto_grow(element);
  });

  $("#patientLatCol").on("keyup", "#formName_baseATCD textarea", function() {
    $(this).css("overflow", "hidden");
    auto_grow(this);
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Gestion Allergies

  // ajout Allergies
  $("#patientLatCol").on("click", "button.getAllergiesPanel", function(e) {
    e.preventDefault();
    $('#texteRechercheAllergie').attr('data-parentid', $(this).attr('data-parentid'));
  });
  $('#searchAllergie').on('shown.bs.modal', function() {
    $("#texteRechercheAllergie").val('');
    $('#codeAllergietrouves').html('');
    $("#texteRechercheAllergie").focus();
  })

  // interogation sur mot clé
  $("#texteRechercheAllergie").typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: false,
    captureLength: 3,
    callback: function(value) {
      $.ajax({
        url: urlBase + '/lap/ajax/allergieSearch/',
        type: 'post',
        data: {
          term: value,
          parentid: $('#texteRechercheAllergie').attr('data-parentid')
        },
        dataType: "html",
        beforeSend: function() {
          $('#codeAllergietrouves').html('<div class="col-md-12">Attente des résultats de la recherche ...</div>');
        },
        success: function(data) {
          $('#codeAllergietrouves').html(data);
        },
        error: function() {
          alert('Problème, rechargez la page !');
        }
      });
    }
  });

  // ajouter allergie
  $("#searchAllergie").on("click", "button.addAllergieToPatient", function(e) {
    origin = $(this);
    label = $(this).attr('data-label');
    $.ajax({
      url: urlBase + '/lap/ajax/allergieAdd/',
      type: 'post',
      data: {
        patientID: $('#identitePatient').attr("data-patientID"),
        codeAller: $(this).attr('data-code'),
        libelleAller: label,
        parentID: $(this).attr('data-parentid')
      },
      dataType: "json",
      success: function(data) {
        if (data['statut'] == 'ok') {
          origin.closest('tr').addClass("success");
          origin.attr('disabled', 'disabled');
          $("#atcdTableauAllergieStruc").removeClass('d-none');
          $("#atcdTableauAllergieStruc tbody").append('<tr class="tr{{ id }} table-warning small"><td>' + label + '</td></tr>');
        } else {
          alert('Problème, rechargez la page !');
        }
      },
      error: function() {
        alert('Problème, rechargez la page !');
      }
    });
  });

  // retirer allergie
  $("#patientLatCol").on("click", "button.removeAllergie", function(e) {
    e.preventDefault();
    origin = $(this);
    objetid = $(this).attr('data-objetid');
    $.ajax({
      url: urlBase + '/lap/ajax/allergieDel/',
      type: 'post',
      data: {
        patientID: $('#identitePatient').attr("data-patientID"),
        objetID: objetid
      },
      dataType: "json",
      success: function(data) {
        if (data['statut'] == 'ok') {
          $("#atcdTableauAllergieStruc tr.tr" + objetid).remove();
        } else {
          alert('Problème, rechargez la page !');
        }
      },
      error: function() {
        alert('Problème, rechargez la page !');
      }
    });
  });

});
