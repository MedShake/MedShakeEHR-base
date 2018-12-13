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
 * Fonctions JS pour les pages de configuration CDA tools
 *
 */

$(document).ready(function() {
  $("#tabAssociations select").on("change", function() {
    setInput($(this));
    getAssociationsTxt();
  });

  $("#tabAssociations input").on("change, keyup", function() {
    setSelect($(this));
    getAssociationsTxt();
  });

  $('#tabActes').on("click", "button.btnTrash", function() {
    $(this).parents('tr').remove();
    getActesTxt();
  });

  $('body').on("change", "#tabActes select", function() {
    getActesTxt();
  });

  $("#searchCcamGo").on("click", function(e) {
    e.preventDefault();
    actesCodes = $('#searchCcam').val().split("\n");


    $.each(actesCodes, function(index,acteCode) {
      console.log(acteCode);
      if (acteCode.length > 1) {
        $.ajax({
          url: urlBase + "/configuration/ajax/extractCcamActeData/",
          type: 'post',
          data: {
            acteType: 'CCAM',
            acteCode: acteCode,
            activiteCode: '1',
            phaseCode: '0'
          },
          dataType: "json",
          success: function(data) {
            if (!data.yaml) {
              alert_popup("danger", data);
              return;
            }

            ligne = '<tr><td class="code">' + data.acteCode + '</td>';
            ligne += '<td class="codeSystem">CCAM</td>';
            ligne += '<td class="displayName">' + data.acteLabel + '</td>';
            ligne += '<td>' + menuSelect + '</td>';
            ligne += '<td><button class="btn btn-sm btn-light btnTrash"><i class="far fa-trash-alt"></i></button></td>';
            $('#tabActes').append(ligne);
            getActesTxt();
            $('#searchCcam').val('');
          },
          error: function() {
            alert_popup("danger", 'Problème, rechargez la page !');
          }
        });
      }
    });
  });

  getAssociationsTxt();
  getActesTxt();
});


function setInput(select) {
  code = select.val();
  select.parents('tr').find('input').val(code);
}

function setSelect(input) {
  code = input.val();
  input.parents('tr').find('select').val(code);
}

function getAssociationsTxt() {
  finalTxt = '';
  $('#tabAssociations tbody tr').each(function() {
    clef = $(this).attr('data-clef');
    code = $(this).find('select').val();
    ligne = '        ' + clef + ':' + ' \'' + code + "'\n";
    if (code != '') finalTxt += ligne;
  })
  $('#generatedCode').val(finalTxt);
}

function getActesTxt() {
  finalTxt = '';
  $('#tabActes tbody tr').each(function() {
    code = $(this).find('td.code').text();
    codeSystem = $(this).find('td.codeSystem').text();
    displayName = $(this).find('td.displayName').text();
    clinicalDocumentCode = $(this).find('select.clinicalDocumentCode').val();
    ligne = "  " + code + ":\n";
    ligne += "    serviceEventCode:\n";
    ligne += "      codeSystem: '" + codeSystem + "'\n";
    ligne += "      displayName: \"" + displayName + "\"\n";
    ligne += "    clinicalDocumentCode: '" + clinicalDocumentCode + "'\n";
    if (code != '') finalTxt += ligne;
  })
  $('#generatedCodeActes').val(finalTxt);
}
