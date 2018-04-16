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
 * Js pour le formulaire de déclaration d'ald
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  $("#nouvelleCs").on("click","#id_aldCIM10_idAddOn", function() {
    $('#searchCIM10').modal('show');
  });

  $('#searchCIM10').on('shown.bs.modal', function() {
    $('#searchCIM10 #texteRechercheCIM10').focus();
  });

  $("#nouvelleCs").on("keyup","#id_aldCIM10_id", function() {
    if ($("#id_aldCIM10_id").val() == '') $("#id_aldCIM10label_id").val('');
  });

  $("#texteRechercheCIM10").typeWatch({
    wait: 1000,
    highlight: false,
    allowSubmit: false,
    captureLength: 3,
    callback: function(value) {
      $.ajax({
        url: urlBase+'/lap/ajax/cim10search/',
        type: 'post',
        data: {
          term: value
        },
        dataType: "html",
        beforeSend: function() {
          $('#codeCIM10trouves').html('<div class="col-md-12">Attente des résultats de la recherche ...</div>');
        },
        success: function(data) {
          $('#codeCIM10trouves').html(data);
        },
        error: function() {
          alert('Problème, rechargez la page !');
        }
      });
    }
  });

  $('#searchCIM10').on("click", "button.catchCIM10", function() {
    code = $(this).attr('data-code');
    label = $(this).attr('data-label');
    $("#id_aldCIM10_id").val(code);
    $("#id_aldCIM10label_id").val(label);
    $('#searchCIM10').modal('toggle');
    $('#codeCIM10trouves').html('');
    $("#texteRechercheCIM10").val('');

  });

});
