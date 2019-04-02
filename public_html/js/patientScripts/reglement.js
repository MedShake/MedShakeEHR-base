/**
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
 * Js pour le module règlement du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

var factureActuelle = [];

$(document).ready(function() {

  //close button zone newReglement
  $('body').on("click", "#cleanNewReglement", function(e) {
    $(window).unbind("beforeunload");
    $('#newReglement').html('');
  });


  //sortir les data d'une facture type
  $("#newReglement").on("change", ".selectActeStarter", function(e) {
    e.preventDefault();
    searchAndInsertActeData($(this));
  });

  //observer la situation Type de réglement
  $("#newReglement").on("change", ".regleSituationPatient", function(e) {
    e.preventDefault();
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //observer le champ de recherche d'acte
  $("body").delegate('#acteSearch', "focusin", function() {
    $(this).autocomplete({
      source: function(request, response) {
        $.ajax({
          url: urlBase + '/ajax/getAutocompleteCodeNgapOrCcamData/',
          dataType: "json",
          data: {
            term: request.term,
            regleSecteurGeoTarifaire: $("#newReglement input[name='regleSecteurGeoTarifaire']").val(),
            regleSecteurHonoraires: $("#newReglement input[name='regleSecteurHonoraires']").val(),
            regleSecteurHonorairesNgap: $("#newReglement input[name='regleSecteurHonorairesNgap']").val(),
          },
          success: function(data) {
            response(data);
          }
        });
      },
      autoFocus: false,
      select: function(event, ui) {
        ui.item.label = ui.item.labelo;
        $('#detFacturation tbody').append(construireLigneTableauActes(ui.item.code, ui.item));
        $('#detFacturation').show();
        setDefautTarifEtDepa();
        calcResteDu();
        $(this).val('');
        return false;
      }
    });
    $(this).autocomplete("option", "appendTo", "#" + $(this).closest('form').attr('id'));
  });

  //observer le changement sur dépassement
  $("#newReglement").on("change, keyup", ".regleDepaCejour", function(e) {
    e.preventDefault();
    $(this).val($(this).val().replace(' ', ''));
    calcResteDu();
  });

  //observer la sup d'un acte dans le tableau
  $("#newReglement").on("click", ".removeActe", function(e) {
    e.preventDefault();
    factureActuelle.splice($("tr").index(this), 1);
    $(this).closest('tr').remove();
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //observer le dépassement acte par acte dans le tableau
  $("#newReglement").on("change, keyup", "input.add2DepaSum", function(e) {
    calculerTotalLigneTabActes($(this));
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //observer le menu de qualif de l'acte
  $("#newReglement").on("change", "select.codeQualif", function(e) {
    calculerTotalLigneTabActes($(this));
    getFinalTarifTableauActes();
    setDefautTarifEtDepa();
    calcResteDu();
  });

  //observer les modificateur CCAM acte par acte dans le tableau
  $("#newReglement").on("change, keyup", "input.modifsCCAM", function(e) {
    calculerTotalIntermedLigneTabActes($(this));
    calculerTotalLigneTabActes($(this));
    setDefautTarifEtDepa();
    calcResteDu();
  });

  // observer la modulation des IK dans le tableau
  $("#newReglement").on("change, keyup", "input.ikNombre", function(e) {
    nombreIK = parseInt($(this).val());
    valeurIK = parseFloat($(this).closest('tr').find('.baseActeValue').text());
    valeurFinalIK = nombreIK * valeurIK;
    $(this).closest('tr').find('.add2TarifSum').text(valeurFinalIK);
    calculerTotalLigneTabActes($(this));
    setDefautTarifEtDepa();
    calcResteDu();
  });

  // observer la modulation en % ou quantité d'un acte dans le tableau
  $("#newReglement").on("change, keyup", "input.modulationActe, input.quantiteActe", function(e) {
    calculerTotalIntermedLigneTabActes($(this))
    calculerTotalLigneTabActes($(this));
    setDefautTarifEtDepa();
    calcResteDu();
  });

  // observer le double clic sur une case règlement
  $("#newReglement").on("dblclick", "input.regleCB, input.regleCheque, input.regleEspeces, input.regleTiersPayeur", function(e) {
    $(this).val($("input.regleFacture").val());
  });

  //le style du champ Reste du
  $("#newReglement").on("change", ".regleFacture", function(e) {
    val = $(".regleFacture").val();
    if (val > 0 || val < 0) {
      $(".regleFacture").closest("div.form-group").removeClass('has-success');
      $(".regleFacture").closest("div.form-group").addClass('has-error');
    } else {
      $(".regleFacture").closest("div.form-group").removeClass('has-error');
      $(".regleFacture").closest("div.form-group").addClass('has-success');
    }
  });

  //reinjection pour édition
  $(".regleTarifCejour").attr('data-tarifdefaut', $(".regleTarifCejour").val());
  $(".regleDepaCejour").attr('data-tarifdefaut', $(".regleDepaCejour").val());

});



/**
 * Obtenir les infos règlements à partir d'un menu présentant factures types
 * @param  {object} selecteur objet jquery du select source
 * @return {void}
 */
function searchAndInsertActeData(selecteur) {
  factureActuelle = [];
  id = selecteur.attr('id');
  acteID = $('#' + id + ' option:selected').val();

  if (acteID == '') {
    resetModesReglement();
    $('#detFacturation').hide();
    $('.regleFacture').val('');
    $('.regleTarifCejour').val('');
    $('.regleDepaCejour').val('');
    return;
  }

  $(".selectActeStarter option[value='']").prop('selected', 'selected');
  $("#" + id + " option[value='" + acteID + "']").prop('selected', 'selected');

  $.ajax({
    url: urlBase + '/patient/ajax/getReglementData/',
    type: 'post',
    data: {
      acteID: acteID,
      reglementForm: $('#newReglement input[name=reglementForm]').val(),
      regleSecteurGeoTarifaire: $("#newReglement input[name='regleSecteurGeoTarifaire']").val(),
      regleSecteurHonoraires: $("#newReglement input[name='regleSecteurHonoraires']").val(),
      regleSecteurHonorairesNgap: $("#newReglement input[name='regleSecteurHonorairesNgap']").val(),
    },
    dataType: "json",
    success: function(data) {
      // fixer l'ID de la facture type
      $('input[name="acteID"]').val(acteID);

      // fixer les data de paiement par défaut
      fixerDataPaiementDefaut(data);

      // construire le tableau des actes
      construireTableauActes(data);
      // montrer le tableau des actes
      $('#detFacturation').show();

      // ajuster l'affichage final
      setDefautTarifEtDepa();
      calcResteDu();
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}

/**
 * Fixer les data paiement par défaut (tarif, dépassement ...) en sortie de retour json
 * @param  {object} data objet contenant les data de la facturation
 * @return {[type]}      [description]
 */
function fixerDataPaiementDefaut(data) {
  $(".regleTarifCejour").attr('data-tarifdefaut', data['tarif']);
  $(".regleDepaCejour").attr('data-tarifdefaut', data['depassement']);


  if (data['flagCmu'] == "1") {
    $(".regleSituationPatient").val('CMU');
  } else {
    $(".regleSituationPatient").val('G');
  }
}

/**
 * construire le tableau des actes facturés
 * @param  {object} data objet contenant les data de la facturation
 * @return {void}
 */
function construireTableauActes(data) {
  $('#detFacturation tbody').html('');
  var pourcents = $('.pourcents').length;
  $.each(data['details'], function(index, value) {
    tabLigne = construireLigneTableauActes(index, value);
    $('#detFacturation tbody').append(tabLigne);

  });
}

/**
 * Construire une ligne du tableau des actes facturés
 * @param  {string} index index de la ligne
 * @param  {array} value data
 * @return {string}       ligne HTML
 */
function construireLigneTableauActes(index, value) {

  if (!value['quantite']) value['quantite'] = '1';
  if (!value['pourcents']) value['pourcents'] = '100';
  if (!value['modifsCCAM']) value['modifsCCAM'] = '';
  if (!value['codeQualif']) value['codeQualif'] = '';

  if (RegExp('[A-Z]{4}[0-9]{3}').test(index)) {
    value['type'] = 'CCAM';
  } else {
    value['type'] = 'NGAP'
  }

  reglementForm = $('#newReglement input[name=reglementForm]').val();
  if (reglementForm == 'baseReglementLibre') {
    tabColHide = ' style="display : none"';
  } else {
    tabColHide = '';
  }

  //acte
  tabLigne = '<tr><td class="font-weight-bold row' + index + '" >' + index + '</td>';
  // label
  tabLigne += '<td class="text-left text-secondary">' + (value['label'] == null ? '' : '<span title="' + value['label'] + '"><i class="far fa-question-circle"></i></span>') + '</td>';

  // code asso
  tabLigne += '<td' + tabColHide + '>' + (value['type'] == 'NGAP' ? '' : '<input class="form-control form-control-sm text-right codeAsso" value="' + value['codeAsso'] + '">') + '</td>';
  // valeur de base
  tabLigne += '<td class="text-center"><span class="baseActeValue">' + value['base'] + '</span>€</td>';
  // pourcents & quantité
  if (value['code'] == 'IK' || index == 'IK') {
    tabLigne += '<td class="text-right"' + tabColHide + '><div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">x</span></div><input class="form-control text-right ikNombre" value="' + value['ikNombre'] + '"></div></td>")';
  } else if(value['type'] == 'NGAP') {
    tabLigne += '<td class="text-right"' + tabColHide + '> \
      <div class="input-group input-group-sm"> \
        <div class="input-group-prepend"> \
          <span class="input-group-text">x</span> \
        </div>\
        <input class="form-control text-right modulationActe d-none" value="' + value['pourcents'] + '">\
        <input class="form-control text-right quantiteActe" value="' + value['quantite'] + '"> \
      </div> \
      </td>")';
  } else {
    tabLigne += '<td class="text-right"' + tabColHide + '> \
      <div class="input-group input-group-sm"> \
        <input class="form-control text-right modulationActe" value="' + value['pourcents'] + '"> \
        <input class="form-control text-right quantiteActe d-none" value="' + value['quantite'] + '"> \
        <div class="input-group-append"> \
          <span class="input-group-text">%</span> \
        </div> \
      </div></td>")';
  }
  // modifs ccam
  tabLigne += '<td class="text-right"' + tabColHide + '>' + (value['type'] == 'NGAP' ? '' : '<input class="form-control form-control-sm text-right modifsCCAM" maxlength="4" value="' + value['modifsCCAM'] + '">') + '</td>")';
  // total sécu
  tabLigne += '<td class="text-center"' + tabColHide + '><span class="add2TarifSum">' + value['tarif'] + '</span>€</td>';
  // colonne vide
  tabLigne += '<td></td>';
  // dépassement
  tabLigne += '<td class="text-right"><div class="input-group input-group-sm"><input class="form-control text-right add2DepaSum" value="' + value['depassement'] + '"><div class="input-group-append"><span class="input-group-text">€</span></div></div></td>';
  // code qualif
  tabLigne += '<td class="text-right"' + tabColHide + '><select class="custom-select custom-select-sm codeQualif" autocomplete="off">';
  tabLigne += '<option ' + (value['codeQualif'] == '' ? 'selected' : '') + ' title="Aucun Qualificatif" value=""></option>';
  tabLigne += '<option ' + (value['codeQualif'] == 'A' ? 'selected' : '') + ' title="Depassement Autorise" value="A">DA</option>';
  tabLigne += '<option ' + (value['codeQualif'] == 'E' ? 'selected' : '') + ' title="Exigence particuliere du malade" value="E">DE</option>';
  tabLigne += '<option ' + (value['codeQualif'] == 'G' ? 'selected' : '') + ' title="Acte gratuit" value="G">AG</option>';
  tabLigne += '<option ' + (value['codeQualif'] == 'L' ? 'selected' : '') + ' title="Prise en charge SMG" value="L">SMG</option>';
  tabLigne += '<option ' + (value['codeQualif'] == 'N' ? 'selected' : '') + ' title="Acte a ne pas rembourser en AMO" value="N">NR</option>';
  tabLigne += '</select></td>';
  //total ligne
  tabLigne += '<td class="text-right font-weight-bold total"><span class="totalLigne">' + value['total'] + '</span>€</td>';
  // sup ligne
  tabLigne += '<td class="removeActe text-right"><button class="btn btn-sm btn-light"><i class="far fa-trash-alt"></i></button></td>';
  tabLigne += '</tr>';
  return tabLigne;
}

/**
 * Calculer le montant total intermédiaire basé sur le % et modifs CCAM
 * @param  {object} source objet source jquery
 * @return {void}
 */
function calculerTotalIntermedLigneTabActes(source) {

  // ajustement en fonction %
  base = parseFloat(source.closest('tr').find('.baseActeValue').text());
  pourcents = parseFloat(source.closest('tr').find('.modulationActe').val());
  quantite = parseFloat(source.closest('tr').find('.quantiteActe').val());
  add2TarifSum = Math.round(base * pourcents) / 100;

  // ajustement en fonction modificateurs CCAM
  modifsCcamSum = 0;

  if (modifs = source.closest('tr').find('.modifsCCAM').val()) {} else {
    modifs = {};
  }
  if (modifs.length > 0) {
    for (var i = 0; i < modifs.length; i++) {
      modif = modifs.charAt(i);
      if (modificateursCcamUnit[modif] == 'euro') {
        modifsCcamSum = parseFloat(modifsCcamSum) + parseFloat(modificateursCcamValue[modif]);
      } else if (modificateursCcamUnit[modif] == 'pourcent') {
        modifsCcamSum = parseFloat(modifsCcamSum) + parseFloat(base * parseFloat(modificateursCcamValue[modif]) / 100);
      } else {
        alert("Le modificateur " + modif + " n'est pas connu du système et ne sera pas pris en compte");
      }
    }
  }

  if (source.closest('tr').find('.ikNombre').val()) {
    ikNombre = source.closest('tr').find('.ikNombre').val();
    total = parseFloat(base) * parseFloat(ikNombre);
  } else {
    total = (parseFloat(add2TarifSum) * quantite) + parseFloat(modifsCcamSum);
  }
  total = Math.round(total * 100) / 100;
  source.closest('tr').find('.add2TarifSum').html(total);
}

/**
 * calculer le total à facturer d'une ligne du tableau des actes
 * @param  {object} source objet jquery source de l'event
 * @return {void}
 */
function calculerTotalLigneTabActes(source) {
  if (source.closest('tr').find('.codeQualif').val() == 'G') {
    totalLigne = 0;
  } else {
    totalLigne = parseFloat(source.closest('tr').find('.add2TarifSum').text()) + parseFloat(source.closest('tr').find('.add2DepaSum').val());
  }
  source.closest('tr').find('.totalLigne').html(totalLigne);
}

/**
 * obtenir les montants finaux total et dépassement
 * @return {object} total et depassement
 */
function getFinalTarifTableauActes() {
  var sumTot = 0;
  var sumDep = 0;
  // somme tarifs
  $("#detFacturation span.add2TarifSum").each(function() {
    var value = $(this).text();
    if ($(this).closest('tr').find('.codeQualif').val() == 'G') value = 0;
    if (!isNaN(value) && value.length != 0) {
      sumTot += parseFloat(value);
    }
  });
  // somme des dépassements
  $("#detFacturation input.add2DepaSum").each(function() {
    var value = $(this).val();
    if ($(this).closest('tr').find('.codeQualif').val() == 'G') value = 0;
    if (!isNaN(value) && value.length != 0) {
      sumDep += parseFloat(value);
    }
  });

  sumTot = Math.round(sumTot * 100) / 100;
  sumDep = Math.round(sumDep * 100) / 100;

  // construction d'un objet recap des actes
  $("#detFacturation tbody tr").each(function(index, val) {

    factureActuelle[index] = {
      'acte': $(this).children('td:first').text(),
      'codeAsso': $(this).find('.codeAsso').val(),
      'base': $(this).find('.baseActeValue').text(),
      'ikNombre': $(this).find('.ikNombre').val(),
      'pourcents': $(this).find('.modulationActe').val(),
      'quantite': $(this).find('.quantiteActe').val(),
      'modifsCCAM': $(this).find('.modifsCCAM').val(),
      'depassement': $(this).find('.add2DepaSum').val(),
      'codeQualif': $(this).find('.codeQualif').val(),
    };
    console.log(factureActuelle);
  });

  $('input[name="regleDetailsActes"]').val(JSON.stringify(factureActuelle, null, 2));

  return sums = {
    tarif: sumTot,
    depassement: sumDep
  };
}

/**
 * Construire le tableau des actes à l'édition
 * @return {void}
 */
function construireTabActesEdition() {
  if (actes = tryParseJSON($('input[name="regleDetailsActes"]').val())) {
    $.each(actes, function(index, value) {
      tabLigne = construireLigneTableauActes(value['acte'], value);
      $('#detFacturation tbody').append(tabLigne);
      calculerTotalIntermedLigneTabActes($('#detFacturation td.row' + value['acte']));
      calculerTotalLigneTabActes($('#detFacturation td.row' + value['acte']))
    });
    $('#detFacturation').show();
  }
}

/**
 * Régler affichage des tarifs et des champs du paiement en fonction des cas
 */
function setDefautTarifEtDepa() {
  //resetModesReglement();

  $(".regleTarifCejour").val(getFinalTarifTableauActes().tarif);
  $(".regleDepaCejour").val(getFinalTarifTableauActes().depassement);
  //$(".regleTarifCejour").val($(".regleTarifCejour").attr('data-tarifdefaut'));
  //$(".regleDepaCejour").val($(".regleDepaCejour").attr('data-tarifdefaut'));

  cas = $(".regleSituationPatient" + ' option:selected').val() || 'G';

  tarif = parseFloat($(".regleTarifCejour").val());
  depassement = parseFloat($(".regleDepaCejour").val());

  //tout venant
  if (cas == 'G') {
    $(".regleDepaCejour").removeAttr('readonly');
    //CMU
  } else if (cas == 'CMU') {
    $(".regleDepaCejour").attr('readonly', 'readonly');
    $(".regleDepaCejour").val('0');
    // TP
  } else if (cas == 'TP') {
    $(".regleDepaCejour").removeAttr('readonly');
    // TP ALD
  } else if (cas == 'TP ALD') {
    $(".regleDepaCejour").attr('readonly', 'readonly');
    $(".regleDepaCejour").val('0');
    // TP ALD DEP
  } else if (cas == 'TP ALD DEP') {
    $(".regleDepaCejour").removeAttr('readonly');
  }
}

/**
 * Calculer le reste du
 * @return {void}
 */
function calcResteDu() {
  $(".regleTiersPayeur").parents(".form-group").children("label").html('Tiers');
  cas = $(".regleSituationPatient" + " option:selected").val() || 'G';
  tarif = parseFloat($(".regleTarifCejour").val());
  depassement = parseFloat($(".regleDepaCejour").val());

  //tout venant
  if (cas == 'G') {
    total = parseFloat(tarif) + parseFloat(depassement);
    $(".regleFacture").val(total).change();
    $(".regleTiersPayeur").val('');
    //CMU
  } else if (cas == 'CMU') {
    total = parseFloat(tarif);
    $(".regleTiersPayeur").val(total);
    $(".regleFacture").val(total).change();
  } else if (cas == 'TP') {
    total = parseFloat(tarif) + parseFloat(depassement);
    tiers = Math.round((tarif * 70 / 100) * 100) / 100;
    reste = Math.round((total - tiers) * 100) / 100;
    $(".regleTiersPayeur").val(tiers);
    $(".regleFacture").val(total).change();
    $(".regleTiersPayeur").parents(".form-group").children("label").html('Tiers (reste à payer : ' + reste + '€)');
  } else if (cas == 'TP ALD') {
    total = parseFloat(tarif);
    $(".regleTiersPayeur").val(total);
    $(".regleFacture").val(total).change();
  } else if (cas == 'TP ALD DEP') {
    total = parseFloat(tarif) + parseFloat(depassement);
    tiers = parseFloat(tarif);
    reste = Math.round((total - tiers) * 100) / 100;
    $(".regleTiersPayeur").val(tiers);
    $(".regleFacture").val(total).change();
    $(".regleTiersPayeur").parents(".form-group").children("label").html('Tiers (reste à payer : ' + reste + '€)');
  }

}

/**
 * Reset des champs de règlement
 */
function resetModesReglement() {
  $(".regleCheque").val('');
  $(".regleCB").val('');
  $(".regleEspeces").val('');
  $(".regleFacture").val('').change();
  $(".regleTiersPayeur").parents(".form-group").children("label").html('Tiers');
}
