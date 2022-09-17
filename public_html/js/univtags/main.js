/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2021
 * DEMAREST Maxime <b.boutillier@gmail.com>
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
 * Fonctions JS relative aux tags universel
 *
 * @author DEMAREST Maxime <maxime@indelog.fr>
 */

/**
 * Obtenir un modal de création ou de modification d'un tag universel.
 * @param	int		typeID		ID du type de tag.
 * @param	int		tagID		ID du tag, si création de nouveau tag doit
 *								valoir `0`.
 * @param	int		toID		Si la modal est apelé depuis la fiche d'un
 *								élément doit être l'ID de cette élément. Sinon
 *								si la modal est apelé depuis un autre contexte
 *								commem la page de configuration général des
 *								tags universel doit valoir `0`.
 * @param	string	contexte	Le contexte dans le quel est apelé la modal.
 *								Doit corresondre au contexte définit sur
 *								`msUnivTags::getListHtml()`.
 * @return	void
 */
function getModalUnivTag(typeID, tagID, toID, contexte) {
  $('#modalUnivTag').remove();
  $.ajax({
    type: 'GET',
    url: '/univtags/ajax/getModalTag/',
    data: {
      typeID: typeID,
      tagID: tagID,
      toID: toID,
      contexte: contexte,
    },
    dataType: 'json',
    success: function(data) {
      switch (data.status) {
        case 'ok':
          $('body').append(data.data.modalHTML);
          $('#modalUnivTag').modal('show');
          $('#modalUnivTag').on('hidden.bs.modal', function(event) {$('#modalUnivTag').remove();});
          break;
        case 'error':
          alert_popup('danger', data.message);
          break;
      };
    },
    error: function(data) {
      alert_popup('danger', 'Problème, rechargez la page !');
    },
  });
}

/**
 * Déclanche l'opération de cération ou de modificaton d'un tag universel.
 * @param	object	btn		Élément de type `button` du DOM ayant déclancé
 *							l'apel à la function.
 * @param	bool	del		Si l'action vis la supression d'un tag, mette à
 *							`true` autrement, pour la création ou la
 *							modification d'un tag mettre à `false`.
 * @return	void
 */
function formUnivTagValid(btn, del = false) {
  var formDatas = $('#formUnivTag :input').serializeArray();
  var tagID = formDatas.find((o) => {return o['name'] === 'id'}).value;
  // action de supression du tag
  if (del) {
    var ok = confirm('Confirmer vous la demande de supression de cette étiquette (/!\\ elle sera retiré de tout les éléments au quel elle est atachée) ?');
    if (!ok) { return null };
    var action = 'delTag';
  } else {
    // si le tag id est 0 on créer un nouveau tag sinon c'est que l'on modifie un tag existant
    var action = (tagID > 0) ? 'editTag' : 'newTag';
  }
  $.ajax({
    type: 'POST',
    url: '/univtags/ajax/'+action+'/',
    data: formDatas,
    dataType: 'json',
    success: function(data) {
      switch (data.status) {
        case 'ok':
          alert_popup('success', data.message);
          $('#modalUnivTag').modal('hide');
          // Actualise la liste des tags
          var univTagsContainer = $('.univTagsContainer[data-typeID="'+data.data.typeID+'"]');
          univTagsContainer.empty();
          $(data.data.tagsListHtml).appendTo(univTagsContainer);
          // sur la page de configuration des tags universels passe les lignes du tableau
          // relative au type de tag en visible
          var showIndicator = $('.unviTagsTypeShowIndicator[data-typeID="'+data.data.typeID+'"]')
          if (showIndicator.length > 0) {
						showIndicator.data('showed', true);
						showIndicator.find('.fa-plus-square').toggleClass('d-none', true);
						showIndicator.find('.fa-minus-square').toggleClass('d-none', false);
						univTagsContainer.removeClass('d-none');
          }
          break;
        case 'error':
          alert_popup('danger', data.message);
          break;
      };
    },
    error: function(data) {
      alert_popup('danger', 'Problème, rechargez la page !');
      $('#modalUnivTag').modal('hide');
    },
  });
}

/**
 * Obtenir la le HTML pour la liste de tag.
 * @param	int		typID		ID du type pour le quel obtenir la liste.
 * @param	int		toID		ID de l'élément pour la quel obtenir la liste. Si
 *								il n'y a pas d'élément déterminier pour le quel la
 *								liste doit étre obtenus (ex contexte de configuration
 *								général ou de recheche) mettre à `0`.
 * @param	string	contexte	Contexte dans le quel la liste doit être obtenus.
 *								Doit corresondre au contexte définit sur
 *								`msUnivTags::getListHtml()`.
 * @return	void								
 */
function getTagsList(typeID, toID, contexte) {
  $.ajax({
    type: 'GET',
    url: '/univtags/ajax/getTagsList/',
    data: {
      typeID: typeID,
      toID: toID,
      contexte: contexte
    },
    dataType: 'json',
    success: function(data) {
      switch (data.status) {
        case 'ok':
            var univTagsContainer = $('.univTagsContainer[data-typeID="'+data.data.typeID+'"]');
            univTagsContainer.empty();
            $(data.data.tagsListHtml).appendTo(univTagsContainer);
          break;
        case 'error':
          alert_popup('danger', data.message);
          break;
      };
    },
    error: function(data) {
      alert_popup('danger', 'Problème, rechargez la page !');
    },
  });
}

/**
 * Ajoute le tag à l'élément.
 * @param	object		chkbx		Élément du DOM de type "checkbox" indiquant
 *									si le tag est séléctioné ou non.
 * @return	void
 */
function univTagsSetTo(chkbx) {
  var tagID = chkbx.dataset.tagid;
  var toID = chkbx.dataset.toid;
  if (chkbx.checked) { var action = 'setTagTo'; }
  else { var action = 'removeTagTo'; }
  $.ajax({
    type: 'POST',
    url: '/univtags/ajax/'+action+'/',
    data: {tagID: tagID, toID: toID},
    dataType: 'json',
    success: function(data) {
      switch (data.status) {
        case 'ok':
          alert_popup('success', data.message);
          break;
        case 'error':
          chkbx.checked = (!chkbx.checked);
          alert_popup('danger', data.message);
          break;
      };
    },
    error: function(data) {
      chkbx.checked = (!chkbx.checked);
      alert_popup('danger', 'Problème, rechargez la page !');
    },
  });
}
