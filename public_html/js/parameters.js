/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://www.github.com/fr33z00>
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
 * Fonctions JS pour les paramètres utilisateur
 *
 * @author fr33z00 <https://www.github.com/fr33z00>
 */

var gotGroups = false;

$(document).ready(function() {

  $("#id_clicRdvGroupId_id").on("click", function(){
    if (!gotGroups) {
      if ($('#id_clicRdvUserId_id').attr('value') != '' && $('#id_clicRdvPassword').attr('value') != '')
        updateGroupList();
      else
        alert("Entrez d'abord les identifiants clicRDV");
    }
  });

  $("#id_clicRdvGroupId_id").on("change", function(){
    $('#id_clicRdvCalId_id').empty();
    if ($(this).val() != "empty")
      updateCalList();
  });

});

function updateGroupList() {
  $.ajax({
    url: urlBase+'/user/ajax/updateGroups/',
    type: 'post',
    data: {
      userid: $('#id_clicRdvUserId_id').val(),
      password: $('#id_clicRdvPassword_id').val()
    },
    dataType: "json",
    success: function(data) {
      gotGroups = true;
      $('#id_clicRdvGroupId_id').empty();
      $('#id_clicRdvGroupId_id').append('<option value="empty"> </option>');
      for (var i in data.records) {
        $('#id_clicRdvGroupId_id').append('<option value="' + i + ':' + data.records[i].name + '">' + data.records[i].name + '</option>');
      }
    },
    error: function() {
      alert('Erreur de connection au compte clicRDV. Vérifiez vos identifiants et votre connection');
    }
  });

}

function updateCalList() {
  $.ajax({
    url: urlBase+'/user/ajax/updateCals/',
    type: 'post',
    data: {
      userid: $('#id_clicRdvUserId_id').val(),
      password: $('#id_clicRdvPassword_id').val(),
      groupid: $('#id_clicRdvGroupId_id').val()
    },
    dataType: "json",
    success: function(data) {
      $('#id_clicRdvCalId_id').empty();
      $('#id_clicRdvCalId_id').append('<option value="empty"> </option>');
      for (var i in data.records) {
        $('#id_clicRdvCalId_id').append('<option value="' + i + ':' + data.records[i].name + '">' + data.records[i].name + '</option>');
      }
    },
    error: function() {
      alert('Erreur de connection au compte clicRDV. Vérifiez vos identifiants et votre connection');
    }
  });

}
