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
 * Js pour la génration des codes bares automatiques sur la pages des données pro
 *
 * @author Maxime DEMAREST <maxime@indelog.fr>
 */


/**
 * Obtien via ajax la boite que affiche et génère les codes barres adeli et rpps
 * @param boolean genCode    si true déclanche l'action de génération des codes barres
 */
function retrunBareCodeBox(genCode = false) {
    patientID = $('#identitePatient').attr("data-patientID");
    $.ajax({
        url: urlBase + '/ajax/getBareCodeGenerator/',
        type: 'post',
        data: {
            pratID: $('input[name=patientID]').val(),
            genCode: genCode
        },
        dataType: "json",
    }).done(function(data) {
				if (data.is_disabled) {
					alert_popup("warning", 'Le générateur de code barres est déscativé.');
				} else {
					$('#barecodeGenContainer').remove();
					$('#myTabContent').after(data.html);
					if (data.is_generated) {
            alert_popup("success", 'Code bare généré.');
					}
					$('#getCodeBarreButton').click(function() {
            if ($.trim($('input[name=p_rpps]').val()).length > 0 || $.trim($('input[name=p_adeli]').val()) > 0) {
              retrunBareCodeBox(true);
            }
					});
				}
    }).fail(function(data) {
        alert_popup("danger", 'Echec de l\'obtention des codes bares !');
    });
}

$(document).ready(function() {
    // Appel la boite de dialog pour la génération du code rpps et adeli si ils ont une valeur
    if ($.trim($('input[name=p_rpps]').val()).length > 0 || $.trim($('input[name=p_adeli]').val()) > 0) {
        retrunBareCodeBox();
    }
});
