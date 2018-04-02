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
 * Fonctions JS pour la gestion de la roulette obstétricale
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  $("#DDRId.datepick").on("dp.change", function(e) {
    var ddr = $('#DDRID').val();
    if (ddr.length == 10) {
      var ddr = $('#DDRID').val();
      ddg = moment(ddr, "DD-MM-YYYY").add(14, 'days').format('DD/MM/YYYY');
      $('#DDGID').val(ddg);

      ddt = moment(ddr, "DD-MM-YYYY").add(41, 'weeks').format('DD/MM/YYYY');
      $('#DDTID').val(ddt);

      calculer();
    }
  });

  $("#DDGId.datepick").on("dp.change", function(e) {
    var ddg = $('#DDGID').val();
    if (ddg.length == 10) {
      var ddg = $('#DDGID').val();
      ddr = moment(ddg, "DD-MM-YYYY").subtract(14, 'days').format('DD/MM/YYYY');
      $('#DDRID').val(ddr);

      ddt = moment(ddg, "DD-MM-YYYY").add(39, 'weeks').format('DD/MM/YYYY');
      $('#DDTID').val(ddt);

      calculer();
    }
  });

  $("#DDTId.datepick").on("dp.change", function(e) {
    var ddt = $('#DDTID').val();
    if (ddt.length == 10) {
      var ddt = $('#DDTID').val();
      ddr = moment(ddt, "DD-MM-YYYY").subtract(41, 'weeks').format('DD/MM/YYYY');
      $('#DDRID').val(ddr);
      ddg = moment(ddt, "DD-MM-YYYY").subtract(39, 'weeks').format('DD/MM/YYYY');
      $('#DDGID').val(ddg);
      calculer();
    }
  });

  $("#DDTID, #DDRID, #DDGID").on("click", function(e) {
    $('#DDTID').val('');
    $('#DDRID').val('');
    $('#DDGID').val('');
    cleanRoulette();
  });

	$("#DDTID, #DDRID, #DDGID").on("keyup", function(e) {
		if(e.which == 13) {
        $(this).change();
    }
  });

  $("#TDDId.datepick").on("dp.change", function(e) {
    var tdd = $('#TDDID').val();
    var ddr = $('#DDRID').val();
    if (tdd.length == 10) {
      var tddm = moment($('#TDDID').val(), "DD-MM-YYYY");
      var ddrm = moment($('#DDRID').val(), "DD-MM-YYYY");
      if (ddrm.isValid() && tddm > ddrm) {
        var diffweeks = tddm.diff(ddrm, 'weeks');
        var diffdays = tddm.diff(ddrm, 'days');
        var plusdays = diffdays - (7 * diffweeks);
        var resultat = diffweeks + 'SA';
        if (plusdays > 0) resultat += ' + ' + plusdays + 'J';
        resultat = "Terme au " + tddm.format('dddd D MMMM YYYY') + " : " + resultat;
        $('#TDDRes').html(resultat);
        $('#TDDRes').show();
      } else {
        $('#TDDRes').hide();
        $('#TDDRes').html('');
      }
    }
  });


  $('body').on("click", '#calComplet', function(event) {
    event.preventDefault();
    $('.trHide').toggle();
  });

  $('body').on("click", '#calMensuComplet', function(event) {
    event.preventDefault();
    $('.trMensuHide').toggle();
  });


  $('body').on("change", '#calculSpeID', function() {
    calculSpe($('#calculSpeID').val());
  });

});


function calculSpe(c) {
  ddr = $('#DDRID').val();
  ddt = $('#DDTID').val();
  if (c == 'IVGMedVille') {
    dc = moment(ddr, "DD-MM-YYYY").add(4, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddr, "DD-MM-YYYY").add(7, 'weeks').format('dddd D MMMM YYYY');
  } else if (c == 'IVGMedCH') {
    dc = moment(ddr, "DD-MM-YYYY").add(4, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddr, "DD-MM-YYYY").add(9, 'weeks').format('dddd D MMMM YYYY');
  } else if (c == 'IVGChir') {
    dc = moment(ddr, "DD-MM-YYYY").add(4, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddr, "DD-MM-YYYY").add(14, 'weeks').format('dddd D MMMM YYYY');
  } else if (c == 'T21COMBI') {
    dc = moment(ddr, "DD-MM-YYYY").add(11, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddr, "DD-MM-YYYY").add(13, 'weeks').add(6, 'days').format('dddd D MMMM YYYY');
  } else if (c == 'T212TRI') {
    dc = moment(ddr, "DD-MM-YYYY").add(14, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddr, "DD-MM-YYYY").add(17, 'weeks').add(6, 'days').format('dddd D MMMM YYYY');
  } else if (c == 'G1') {
    dc = moment(ddt, "DD-MM-YYYY").subtract(6, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddt, "DD-MM-YYYY").add(10, 'weeks').format('dddd D MMMM YYYY') + ' (jour de reprise)';
  } else if (c == 'G1E') {
    dc = moment(ddt, "DD-MM-YYYY").subtract(8, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddt, "DD-MM-YYYY").add(18, 'weeks').format('dddd D MMMM YYYY') + ' (jour de reprise)';
  } else if (c == 'G2') {
    dc = moment(ddt, "DD-MM-YYYY").subtract(12, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddt, "DD-MM-YYYY").add(22, 'weeks').format('dddd D MMMM YYYY') + ' (jour de reprise)';
  } else if (c == 'G3') {
    dc = moment(ddt, "DD-MM-YYYY").subtract(24, 'weeks').format('dddd D MMMM YYYY');
    fc = moment(ddt, "DD-MM-YYYY").add(22, 'weeks').format('dddd D MMMM YYYY') + ' (jour de reprise)';
  }


  $('#calculSpeRes').html(dc + ' &rarr; ' + fc);
  $('#calculSpeRes').show();
}

function calculer() {

  var ddr = $('#DDRID').val();
  var ddg = $('#DDGID').val();

  cleanRoulette();

  t9m = moment(ddg, "DD-MM-YYYY").add(9, 'months').format('dddd D MMMM YYYY');
  $('.Terme9M').html(t9m);


  for (var i = 0; i <= 41; i++) {
    $('.SA' + i).html(moment(ddr, "DD-MM-YYYY").add(i, 'weeks').format('dddd D MMMM YYYY'));
  }

  for (var i = 1; i <= 9; i++) {
    $('.MOIS' + i).html(moment(ddg, "DD-MM-YYYY").add(i, 'months').format('dddd D MMMM YYYY'));
  }

  //terme du jour
  var tdjm = moment();
  $('.TDJ').html(tdjm.format('dddd D MMMM YYYY'));
  var ddrm = moment($('#DDRID').val(), "DD-MM-YYYY");
  if (ddrm.isValid() && tdjm > ddrm) {

    $('#calculSpeID').removeAttr('disabled');
    $('#TDDID').removeAttr('disabled');

    var diffmonths = tdjm.diff(ddrm, 'months');
    var diffweeks = tdjm.diff(ddrm, 'weeks');
    var diffdays = tdjm.diff(ddrm, 'days');
    var plusdays = diffdays - (7 * diffweeks);
    var resultat = diffweeks + 'SA';
    if (plusdays > 0) resultat += ' + ' + plusdays + 'J';
    if (diffmonths <= 10) {
      $('.TDJRes').html(resultat);
      $('#tdjPanel').show();
    } else {
      $('.TDJRes').html('');
      $('#tdjPanel').hide();
    }
  } else {
    cleanRoulette();

  }

}

function cleanRoulette() {


  // calculs spécifiques
  $('#calculSpeRes').html('');
  $('#calculSpeRes').hide('');
  $('#calculSpeID').val($("#calculSpeID option:first").val());
  $('#calculSpeID').attr('disabled', 'disabled');

  // terme à une date données
  $('#TDDRes').html('');
  $('#TDDID').val('');
  $('#TDDID').attr('disabled', 'disabled');

  // calendrier général
  $('#tdjPanel').hide();
	$('.cleanThis').html('');
}
