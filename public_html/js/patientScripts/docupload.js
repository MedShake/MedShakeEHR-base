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
 * Js pour le module upload document du dossier patient
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  $("#dropZoneDoc").dmUploader({
    url: urlBase+'/patient/ajax/uploadNewDoc/',
    extraData: {
      patientID: $('#identitePatient').attr("data-patientID"),
    },
    dataType: 'html',
    maxFiles: 1,
    onUploadSuccess: function(id, data) {
      $(".progress-bar").css('width', '0%');
      $(this).closest(".toclear").html("");
      getHistorique();
      getHistoriqueToday();
    },
    onUploadProgress: function(id, percent) {
      $(".progress-bar").css('width', percent + '%');
    }
  });

});
