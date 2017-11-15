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
 * @edited fr33z00 <https://www.github.com/fr33z00>
 */

init.baseATCD = function(){ 

  //calcul IMC
  if ($('#p_43ID').length > 0) {

    imc = imcCalc($('#p_34ID').val(), $('#p_35ID').val());
    if (imc > 0) {
      $('#p_43ID').val(imc);
    }

    $("input[data-typeid='34'] , input[data-typeid='35']").on("keyup", function() {
      poids = $('#p_34ID').val();
      taille = $('#p_35ID').val();
      imc = imcCalc(poids, taille);
      $('#p_43ID').val(imc);
      patientID = $('#identitePatient').attr("data-patientID");
      setPeopleData(imc, patientID, '43', '#p_43ID', '0');

    });
  }

  //ajutement auto des textarea en hauteur
  $("#formNamebaseATCD textarea").each(function(index) {
    $(this).css("overflow", "hidden");
    auto_grow(this);
  });

  $("#formNamebaseATCD textarea").on("keyup", function() {
    $(this).css("overflow", "auto");
  });

};

kill.baseATCD = function(){
  $("input[data-typeid='34'] , input[data-typeid='35']").unbind("keyup");
};
