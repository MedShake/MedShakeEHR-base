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

    $("input[name=p_poids] , input[name=p_taillePatient]").on("keyup", function() {
      poids = $('input[name=p_poids]').val();
      taille = $('input[name=p_taillePatient]').val();
      imc = imcCalc(poids, taille);
      $('input[name=p_imc]').val(imc);
      patientID = $('#identitePatient').attr("data-patientID");
      setPeopleData(imc, patientID, '43', 'input[name=p_imc]', '0');

    });
  }

  //ajutement auto des textarea en hauteur
  $("#formName_baseATCD textarea").each(function(index, element) {
    $(element).css("overflow", "hidden");
    if (element.offsetParent && element.value!='')
      auto_grow(element);
  });

  $("#formName_baseATCD textarea").on("keyup", function() {
    $(this).css("overflow", "hidden");
    auto_grow(this);
  });

});
