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
 * Js pour le module creation patient / praticien
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  //réactiver un dossier marqué comme supprimé
  $('body').on("click", ".unmarkDeleted", function(e) {
    e.preventDefault();
    if (confirm("Ce dossier sera à nouveau visible dans les listings de recherche.\nSouhaitez-vous poursuivre ? ")) {
      source = $(this);
      $.ajax({
        url: urlBase+'/patients/ajax/unmarkDeleted/',
        type: 'post',
        data: {
          patientID: $(this).attr('data-patientID'),
        },
        dataType: "html",
        success: function(data) {
          el = source.closest('tr');
          el.css("background", "#efffe8");
          setTimeout(function() {
            el.css("background", "");
            el.remove();
          }, 1000);

        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });

    }
  });


});
