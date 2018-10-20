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
 * Fonctions JS pour logs
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {
  $('body').on("click", "#searchAccessLog", function(e) {
    e.preventDefault();
    console.log($(this).closest("form").serialize());
    source = $(this);
    $.ajax({
      url: urlBase + '/logs/ajax/searchAccessLog/',
      type: 'post',
      data: $(this).closest("form").serialize(),
      dataType: "json",
      success: function(data) {
        html = '';
        $.each(data.output, function(index, value) {
          html += "<tr>";
          html += "<td>" + value[0] + " " + value[1] +  "</td>";
          html += "<td>" + value[2] + "</td>";
          html += "<td>" + value[3] + "</td>";
          html += "<td>" + value[4] + "</td>";
          html += "<td>" + value[5] + "</td>";
          html += "<td>#" + value[6] + " "+ value['userIdentite'] +"</td>";
          html += "<td></td>";
          html += "</tr>";
        });
        $('#accessLogShow tbody').html(html);
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  });

  $('body').on("change", "select[name='preformatedPatterns']", function(e) {
    modele = $("select[name='preformatedPatterns'] option:selected").val();
    $("input[name='urlPattern']").val(modele);
  });


});
