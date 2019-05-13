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
 * Js pour le module ordonnance du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  //close button zone newOrdo
  $('#newOrdo').on("click", "#cleanNewOrdo", function(e) {
    $(window).unbind("beforeunload");
    $('#newOrdo').html('');
  });

  //supprimer ligne ordo
  $('#newOrdo').on("click", "button.cleanLigneOrdo", function(e) {
    name = $(this).parent().find("textarea").attr('name');
    newinput = '<input name="' + name + '" type="hidden" value="" >';
    $(this).parents('form').prepend(newinput);
    $(this).closest('div.ligneOrdo').remove();
  });

  //ajout ligne à ordo
  $('#newOrdo').on("change", 'select.selectPrescriptionStarter', function() {
    ajouterLigneOrdo($(this));
  });

  //retour à la racine du dossier patient quand submit d'ordo.
  $('#newOrdo').on("submit", '#ordoComposer', function() {
    setTimeout(function() {
      getHistorique();
      getHistoriqueToday();
      $('#newOrdo').html('');
    }, 500);
  });


  autoGrowOrdo();
});


//ajouter une ligne à l'ordo
function ajouterLigneOrdo(selecteur) {
  id = selecteur.attr('id');
  item = $('#' + id + ' option:selected').val();

  $.ajax({
    url: urlBase + '/patient/ajax/getLigneOrdo/',
    type: 'post',
    data: {
      ligneID: item,
    },
    dataType: "html",
    success: function(data) {
      selecteur.val('');
      $('#ordoComposer div.insertBeforeMe').before(data);
      autoGrowOrdo();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });

}

//auto_grow
function autoGrowOrdo() {
  $("#ordoComposer textarea").each(function() {
    autosize(this);
  });
}
