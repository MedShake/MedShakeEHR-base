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
 * @edited fr33z00 <https://www.github.com/fr33z00>
 */
$(document).ready(function() {

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

  // datepicker bootstrap
  $('.datepick').datetimepicker({
    locale: 'fr',
    viewMode: 'years',
    format: 'L',
    showClear: true

  });

  $("#nouvelleCs").delegate('div.datepick', "focusin click", function() {
    $(this).datetimepicker({
      locale: 'fr',
      viewMode: 'years',
      format: 'L'

    });
  });

  //age
  $(".datepick[data-typeid='8']").on("dp.change", function(e) {
    bd = moment(e.date);
    age = moment().diff(bd, 'years');
    if (age > 0) $(this).prev('label').append(' - ' + age + ' ans');
  });

  // autocomplete
  $("body").delegate('input.jqautocomplete', "focusin", function() {
    $(this).autocomplete({
      source: urlBase+'/ajax/getAutocompleteFormValues/' + $(this).closest('form').attr('data-dataset') + '/' + parseInt($(this).attr('data-typeid')) + '/' + $(this).attr('data-acTypeID') + '/',
      autoFocus: false
    });
  });

  //prevent form submit by enter key
  $('body').on('keyup keypress', 'input', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
      e.preventDefault();
      return false;
    }
  });

  //alerte confirmation
  $('body').on('click', '.confirmBefore', function(e) {
    if (confirm("Confirmez-vous cette action ?")) {

    } else {
      e.preventDefault();
    }
  });

  // checkboxes dans les formulaires
  $("input").on("click", function(e) {
    chkboxClick(this);
  });

});

// checkboxes dans les formulaires
function chkboxClick(el) {
  if (el.type != "checkbox")
    return;
  var hid = document.getElementById("cloned" + el.id);
  if (hid == undefined) {
    hid = el.cloneNode(true);
    hid.id = "cloned" + this.id;
    hid.style.display = "none";
    el.parentNode.appendChild(hid);
    hid.checked = true;
  }
  hid.value = el.checked.toString();
  el.value = el.checked.toString();
}

// scroller vers un élément
function scrollTo(element) {
  $('html, body').animate({
    scrollTop: $(element).offset().top
  }, 2);
}

//agrandir un élément
function auto_grow(element) {
  element.style.height = (element.scrollHeight) + "px";
}

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
