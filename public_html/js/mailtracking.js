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
 * Fonctions JS pour le tracking des mails et l'affichage du statut
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  $('.trackMyMail').each(function(index) {

    mailID = $(this).attr('data-mailtrackingid');

    $.ajax({
      url: urlBase + '/ajax/mailTracking/',
      type: 'post',
      data: {
        mailID: mailID,
      },
      dataType: "json",
      success: function(data) {

        if (data['lastStatus'] == 'opened') {
          $('#mt' + data['mailTrackingID']).addClass('table-success');
        } else if (data['lastStatus'] == 'blocked' || data['lastStatus'] == 'bounced') {
          $('#mt' + data['mailTrackingID']).addClass('table-danger');
        } else if (data['lastStatus'] == 'spam') {
          $('#mt' + data['mailTrackingID']).addClass('table-warning');
        }
        if ($('.infos' + data['mailTrackingID']).length) $('.infos' + data['mailTrackingID']).html('Statut : ' + data['lastStatus'] + ' - ' + data['lastDate']);
      }
    });
  });
});
