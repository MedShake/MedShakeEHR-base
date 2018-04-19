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
 * Fonctions JS monographie lap
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

var sections = ['administratif', 'formesPharma', 'composition', 'classifications', 'pharmacologie', 'recommandations', 'presentations', 'indications', 'posologies', 'modeAdministration', 'nonindications', 'contreindications', 'noncontreindications', 'mgpe', 'interactions', 'grossesse', 'effetsindesirables', 'conduite', 'mvgeneriques'];

//var sections = ['conduite'];

$(document).ready(function() {
  chargerSectionMonographie(section);
  $.each(sections, function(index, value) {
    if(value != section) chargerSectionMonographie(value);
  });

});


function chargerSectionMonographie(section) {
  $.ajax({
    url: urlBase + '/lap/ajax/lapMonographieSection/',
    type: 'post',
    data: {
      spe: spe,
      section: section,
    },
    dataType: "json",
    success: function(data) {
      $('#' + section).html(data['html']);
    },
    error: function() {
      console.log("chargement section monographie  : PROBLEME");
    }
  });
}
