/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2021      DEMAREST Maxime <maxime@indelog.fr>
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
 * Fonctions JS pour la page de configuration des tags universel
 *
 * @author DEMAREST Maxime <maxime@indelog.fr>
 */

/**
 * Active ou désactive un tag en fonction de l'état d'une checkbox
 * @param	int		typeID		ID du type à activer
 * @param	object  chkbx		Element du DOM contenant la checkbox
 *								activant ou désactivant le tag.
 * @return	void
 */
function univTagsConfigSetTypeActif(typeID, chkbx) {
	var confirmOK = confirm('Confirmer vous la '+(chkbx.checked ? 'l\'activation' : 'désactivation')+' du type de tag avec l\'ID #'+typeID+' ?');
	if (confirmOK) {
		$.ajax({
			type: 'POST',
			url: urlBase + '/univtags/ajax/toggleTypeActif/',
			data: {
				typeID: typeID,
				actif: chkbx.checked,
			},
			dataType: 'json',
			success: function(data) {
				if (data.status === 'ok') {
					alert_popup('success', data.message);
				} else {
					alert_popup('danger', data.message);
					// annule le chegement d'état de la checkbox
					chkbx.checked = !chkbx.checked;
				};
			},
			error: function(data) {
				alert_popup('danger', 'Problème, rechargez la page !');
				// annule le chegement d'état de la checkbox
				chkbx.checked = !chkbx.checked;
			},
		});
	};

	if (!confirmOK) {
		// annule le chegement d'état de la checkbox
		chkbx.checked = !chkbx.checked;
	};
}

/**
 * Obtenir la liste HTML des tags pour le type voulut.
 * @param	int		typeID		ID du type.
 * @param	object	elem		Elément ayant déclanché la demande de la
 *								liste.
 * @return	void
 */
function univTagsGetList(typeID, elem) {
	isShowed = elem.dataset.showed;
	$(elem).children().each(function() {$(this).toggleClass('d-none');});
	var univTagsContainer = $('.univTagsContainer[data-typeID="'+typeID+'"]');
	if (isShowed == 'true') {
		univTagsContainer.empty();
		univTagsContainer.addClass('d-none');
		elem.dataset.showed = 'false';
	} else {
		$.ajax({
			type: 'GET',
			url: urlBase + '/univtags/ajax/getTagsList/',
			data: {
				typeID: typeID,
				toID: 0,
				contexte: 'config',
			},
			dataType: 'json',
			success: function(data) {
				if (data.status === 'ok') {
					univTagsContainer.empty();
					$(data.data.tagsListHtml).appendTo(univTagsContainer);
					elem.dataset.showed = 'true';
					console.log(elem);
					univTagsContainer.removeClass('d-none');
				} else {
					alert_popup('danger', data.message);
					$(elem).find('.fa-plus-square').toggleClass('d-none', true);
					$(elem).find('.fa-minus-square').toggleClass('d-none', false);
					univTagsContainer.addClass('d-none');
				};
			},
			error: function(data) {
				alert_popup('danger', 'Problème, rechargez la page !');
				$(elem).find('.fa-plus-square').toggleClass('d-none', true);
				$(elem).find('.fa-minus-square').toggleClass('d-none', false);
				univTagsContainer.addClass('d-none');
			},
		});
	}
}
