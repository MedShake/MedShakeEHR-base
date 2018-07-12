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
 * JS général
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */
$(document).ready(function() {


  ////////////////////////////////////////////////////////////////////////
  ///////// Paramètrages pour momentjs

  moment.locale('fr', {
    months: "janvier_février_mars_avril_mai_juin_juillet_août_septembre_octobre_novembre_décembre".split("_"),
    monthsShort: "janv._févr._mars_avr._mai_juin_juil._août_sept._oct._nov._déc.".split("_"),
    weekdays: "dimanche_lundi_mardi_mercredi_jeudi_vendredi_samedi".split("_"),
    weekdaysShort: "dim._lun._mar._mer._jeu._ven._sam.".split("_"),
    weekdaysMin: "Di_Lu_Ma_Me_Je_Ve_Sa".split("_"),
    longDateFormat: {
      LT: "HH:mm",
      L: "DD/MM/YYYY",
      LL: "D MMMM YYYY",
      LLL: "D MMMM YYYY LT",
      LLLL: "dddd D MMMM YYYY LT"
    },
    calendar: {
      sameDay: "[Aujourd'hui à] LT",
      nextDay: '[Demain à] LT',
      nextWeek: 'dddd [à] LT',
      lastDay: '[Hier à] LT',
      lastWeek: 'dddd [dernier à] LT',
      sameElse: 'L'
    },
    relativeTime: {
      future: "dans %s",
      past: "il y a %s",
      s: "quelques secondes",
      m: "une minute",
      mm: "%d minutes",
      h: "une heure",
      hh: "%d heures",
      d: "un jour",
      dd: "%d jours",
      M: "un mois",
      MM: "%d mois",
      y: "un an",
      yy: "%d ans"
    },
    ordinal: function(number) {
      return number + (number === 1 ? 'er' : 'ème');
    },
    week: {
      dow: 1, // Monday is the first day of the week.
      doy: 4 // The week that contains Jan 4th is the first week of the year.
    }
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Obesrvations générales pour formulaires

  //// datepicker bootstrap
  $("body").on("click", 'div.datepick', function() {
    var $div=$(this).closest("div.datepick");
    var viewMode = $div.hasClass("pick-year")?'years':($div.hasClass("pick-month")?'months':'days');
    viewMode = $div.find("input").hasClass("pick-year")?'years':($div.find("input").hasClass("pick-month")?'months':viewMode);
    $(this).datetimepicker({
      locale: 'fr',
      viewMode: viewMode,
      format: 'L',
      useCurrent: false,
      icons: {
        time: 'far fa-clock',
        date: 'fa fa-calendar',
        up: 'fa fa-chevron-up',
        down: 'fa fa-chevron-down',
        previous: 'fa fa-chevron-left',
        next: 'fa fa-chevron-right',
        today: 'fa fa-crosshairs',
        clear: 'fa fa-trash',
        close: 'fa fa-times'
      }
    });
    $div.data("DateTimePicker").toggle();
  });

  // age affiché en label de l'input date de naissance
  $(".datepick[data-typeid='8']").on("dp.change", function(e) {
    bd = moment(e.date);
    age = moment().diff(bd, 'years');
    if (age > 0) $(this).prev('label').append(' - ' + age + ' ans');
  });

  // autocomplete simple
  $("body").delegate('input.jqautocomplete', "focusin", function() {
    $(this).autocomplete({
      source: urlBase + '/ajax/getAutocompleteFormValues/' + $(this).closest('form').attr('data-dataset') + '/' + parseInt($(this).attr('data-typeid')) + '/' + $(this).attr('data-acTypeID') + '/',
      autoFocus: false
    });
    $(this).autocomplete("option", "appendTo", "#" + $(this).closest('form').attr('id'));
  });

  //autocomplete pour la liaison code postal - > ville
  $('body').delegate('#id_postalCodePerso_id, #id_codePostalPro_id', 'focusin', function() {
    type = $(this).attr('data-typeID');
    if (type == 53) dest = 56;
    else if (type == 13) dest = 12;

    if ($(this).is(':data(autocomplete)')) return;
    $(this).autocomplete({
      source: '/ajax/getAutocompleteLinkType/data_types/' + type + '/' + type + '/' + type + ':' + dest + '/',
      autoFocus: true,
      minLength: 3,
      select: function(event, ui) {
        sourceval = eval('ui.item.d' + type);
        destival = eval('ui.item.d' + dest);
        $('input[data-typeid="' + dest + '"]').val(destival);
        $('input[data-typeid="' + type + '"]').val(sourceval);

        //si contexte de mise à jour automatique
        patientID = $('#identitePatient').attr("data-patientID");
        if ($('input[data-typeid="' + dest + '"]').parents('.changeObserv').length) {
          setPeopleData(destival, patientID, dest, 'input[data-typeid="' + dest + '"]', '0');
        }
        if ($('input[data-typeid="' + type + '"]').parents('.changeObserv').length) {
          setPeopleData(sourceval, patientID, type, 'input[data-typeid="' + type + '"]', '0');
        }

      }
    });
    $(this).autocomplete("option", "appendTo", "#" + $(this).closest('form').attr('id'));
  });

  //prévention du form submit sur la touche enter
  $('body').on('keyup keypress', 'input', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
      e.preventDefault();
      return false;
    }
  });

  // checkboxes dans les formulaires
  $('body').on("click", ".checkboxFixValue input[type=checkbox]", function(e) {
    chkboxClick(e.target);
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Obesrvations générales pour éléments divers

  //alerte confirmation
  $('body').on('click', '.confirmBefore', function(e) {
    if (confirm("Confirmez-vous cette action ?")) {

    } else {
      e.preventDefault();
    }
  });

  //enregistrement de forms en ajax
  $('body').on('click', ".ajaxForm input[type=submit],.ajaxForm button[type=submit]", function(e) {
    e.preventDefault();
    var reload = $(this).closest("form").hasClass('reload');
    var stop = false;
    $(this).closest("form").find('input[required],textarea[required]').each(function(idx, el) {
      if (el.value==''){
        glow('danger', $(el));
        stop=true;
      }
    });
    if (stop) {
      alert_popup("warning", "Certains champs requis n'ont pas été remplis.");
      return;
    }
    $.ajax({
      url: $(this).closest("form").attr("action"),
      type: 'post',
      data: $(this).closest("form").serialize(),
      dataType: "json",
      success: function(data) {
        if (reload)
          window.location.reload();
        else
          alert_popup("success", "Opération validée");
      },
      error: function() {
        alert_popup("danger", "Une erreur s'est produite durant l'opération");
      }
    });
  });

  ////////////////////////////////////////////////////////////////////////
  ///////// Générer le QR code  /phonecapture/ pour accès facile

  if ($('.QRcodeAccesPhoneCapture').length) {
    var el = kjua({
      text: phoneCaptureUrlAcces,

      // render method: 'canvas' or 'image'
      render: 'image',

      // render pixel-perfect lines
      crisp: true,

      // minimum version: 1..40
      minVersion: 1,

      // error correction level: 'L', 'M', 'Q' or 'H'
      ecLevel: 'H',

      // size in pixel
      size: 400,

      // pixel-ratio, null for devicePixelRatio
      ratio: null,

      // code color
      fill: '#333',

      // background color
      back: '#fff',

      // roundend corners in pc: 0..100
      rounded: 100,

      // quiet zone in modules
      quiet: 1,

      // modes: 'plain', 'label' or 'image'
      mode: 'label',

      // label/image size and pos in pc: 0..100
      mSize: 10,
      mPosX: 50,
      mPosY: 50,

      // label
      label: 'PhoneCapture',
      fontname: 'sans',
      fontcolor: '#d9534f',

      // image element
      image: null

    });
    $('.QRcodeAccesPhoneCapture').html(el);
  }

});


////////////////////////////////////////////////////////////////////////
///////// Fonctions diverses

// faire flasher le background d'un élément
function flashBackgroundElement(el) {
  attrInitiaux = el.attr('class');
  el.removeClass('bg-light');
  el.css("background", "#efffe8");
  el.delay(700).queue(function() {
    $(this).css("background","").dequeue();
    $(this).attr('class' , attrInitiaux);
  });
}

// checkboxes dans les formulaires
function chkboxClick(el) {
  var hid = document.getElementById("cloned" + el.id);
  if (hid == undefined) {
    hid = el.cloneNode(true);
    hid.id = "cloned" + el.id;
    hid.type = "hidden";
    el.parentNode.appendChild(hid);
  }
  hid.value = el.checked.toString();
  el.value = el.checked.toString();
}

// scroller vers un élément de la page
function scrollTo(element, delai) {
  $('html, body').animate({
    scrollTop: $(element).offset().top
  }, delai == undefined ? 2 : delai);
}

//agrandir un élément de formulaire automatiquement
function auto_grow(element) {
  $(element).css('height', Math.max(16*(parseInt($(element).attr('rows')) || 1), element.scrollHeight) + 2);
}

function glow(type, $el) {
  var colors={success:"#efffe8", danger:"#f8d7da"};
  $el.css("background", colors[type]);
  $el.delay(700).queue(function() {
    $(this).css("background","").dequeue();
  });
}

//fonction pour la sauvegarde automatique de champ de formulaire
function setPeopleData(value, patientID, typeID, source, instance) {
  if (patientID && typeID && source) {
    $.ajax({
      url: urlBase + '/ajax/setPeopleData/',
      type: 'post',
      data: {
        value: value,
        patientID: patientID,
        typeID: typeID,
        instance: instance
      },
      dataType: "json",
      success: function(data) {
        glow('success', $(source));
      },
      error: function() {
        //alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  }
}

// équivalent de nl2br php
function nl2br(str, is_xhtml) {
  var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

//fonction pour la sauvegarde automatique de champ de formulaire via le nom du type de donnée
function setPeopleDataByTypeName(value, patientID, typeName, source, instance) {
  if (patientID && typeName && source) {
    $.ajax({
      url: urlBase + '/ajax/setPeopleDataByTypeName/',
      type: 'post',
      data: {
        value: value,
        patientID: patientID,
        typeName: typeName,
        instance: instance
      },
      dataType: "json",
      success: function(data) {
        glow('success', $(source));
      },
      error: function() {
        //alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  }
}

// affichage de messages d'alerte
function alert_popup(severity, message) {
  var titre = {info: 'Note: ', success: 'Succès: ', warning: 'Message: ', danger: 'Erreur: '}
  $("#alert_section").append('\
    <div class="alert alert-' + severity + ' alert-to-remove fade show col-md-auto pl-4" role="alert">\
      <strong>' + titre[severity] + ' </strong>' + message +
      '<button type="button" class="pl-2 close" data-dismiss="alert" aria-label="Close">\
        <span aria-hidden="true">&times;</span>\
      </button>\
    </div>');
  if (severity == 'info' || severity == 'success') {
    setTimeout((function(){$('.alert-to-remove').remove()}),4000);
  }
}

/**
 * Comparer 2 tableaux
 * @param  {array} arr1 tableau 1
 * @param  {array} arr2 tableau 2
 * @return {boolean}      true / false
 */
function arraysEqual(arr1, arr2) {
    if(arr1.length !== arr2.length)
        return false;
    for(var i = arr1.length; i--;) {
        if(arr1[i] !== arr2[i])
            return false;
    }

    return true;
}


/**
 * download csv file
 * Thanks to codexworld <https://www.codexworld.com/export-html-table-data-to-csv-using-javascript/>
 */
function downloadCSV(csv, filename) {
    var csvFile;
    var downloadLink;

    // CSV file
    csvFile = new Blob([csv], {type: "text/csv"});

    // Download link
    downloadLink = document.createElement("a");

    // File name
    downloadLink.download = filename;

    // Create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);

    // Hide download link
    downloadLink.style.display = "none";

    // Add the link to DOM
    document.body.appendChild(downloadLink);

    // Click download link
    downloadLink.click();
}

/**
 * Parser le JSON si correct, false sinon
 * @param  {string} jsonString json string à parser
 * @return {mixte}            json ou false
 */
function tryParseJSON(jsonString){
    try {
        var o = JSON.parse(jsonString);
        if (o && typeof o === "object") {
            return o;
        }
    }
    catch (e) { }
    return false;
};

/**
 * Make csv file from html table
 * Thanks to codexworld <https://www.codexworld.com/export-html-table-data-to-csv-using-javascript/>
 * @param  {string} table    table selector
 * @param  {string} filename filename
 * @return {file}          file
 */
function exportTableToCSV(table, filename) {
    var csv = [];
    var rows = document.querySelectorAll(table + " tr");

    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");

        for (var j = 0; j < cols.length; j++)
            row.push(cols[j].innerText);

        csv.push(row.join(";"));
    }

    // Download CSV file
    downloadCSV(csv.join("\n"), filename);
}

////////////////////////////////////////////////////////////////////////
///////// Fonctions tierces

/*! jQuery getScriptOnce - v0.1.0 - 2013-11-15
 * http://www.invetek.nl/?p=105
 * https://github.com/invetek/jquery-getscriptonce
 * Copyright (c) 2013 Loran Kloeze | Invetek
 * Licensed MIT
 */
(function($) {
  $.getScriptOnce = function(url, successhandler) {
    if ($.getScriptOnce.loaded.indexOf(url) === -1) {
      $.getScriptOnce.loaded.push(url);
      if (successhandler === undefined) {
        return $.getScript(url);
      } else {
        return $.getScript(url, function(script, textStatus, jqXHR) {
          successhandler(script, textStatus, jqXHR);
        });
      }
    } else {
      return false;
    }

  };

  $.getScriptOnce.loaded = [];

}(jQuery));
