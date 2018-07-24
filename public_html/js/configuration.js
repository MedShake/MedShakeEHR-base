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
 * Fonctions JS pour les pages de configuration
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 * @contrib fr33z00 <https://www.github.com/fr33z00>
 */

$(document).ready(function() {

  if (document.URL.indexOf("#ca") >= 0) {
    $($("ul.nav-tabs li")[1]).children("a")[0].click();
  }
  else if (document.URL.indexOf("#ap") >= 0) {
    $($("ul.nav-tabs li")[2]).children("a")[0].click();
  }

  // extract by primary key
  $("button.edit-by-prim-key").on("click", function(e) {

    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    var id = $(this).attr("data-id");
    var table = $(this).attr("data-table");

    $.ajax({
      url: urlBase + '/configuration/ajax/configExtractByPrimaryKey/',
      type: 'post',
      data: {
        id: id,
        table: table
      },
      dataType: "json",
      success: function(data) {
        $(form).append('<input type="hidden" value="' + data.id + '" name="id" />');
        $(modal + ' form select option').removeProp('selected');
        $(modal + ' form textarea').val('');
        $.each(data, function(index, value) {
          if ($(form + ' input[name="' + index + '"]').length) {
            $(form + ' input[name="' + index + '"]').val(value);
          } else if ($(form + ' select[name="' + index + '"]').length) {
            $(form + ' select[name="' + index + '"]').find('option[value="' + value + '"]').prop("selected", "selected");
          } else if ($(form + ' textarea[name="' + index + '"]').length) {
            $(form + ' textarea[name="' + index + '"]').val(value);
          }
        });
        $(modal).modal('show');

      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });

  });

  // duplicate
  $("button.duplicate").on("click", function(e) {
    var id = $(this).attr("data-id");
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    var table = $(this).attr("data-table");

    $.ajax({
      url: urlBase + '/configuration/ajax/configExtractByPrimaryKey/',
      type: 'post',
      data: {
        id: id,
        table: table
      },
      dataType: "json",
      success: function(data) {
        $(modal + ' form select option').removeProp('selected');
        $(modal + ' form textarea').val('');
        $.each(data, function(index, value) {
          if ($(form + ' input[name="' + index + '"]').length) {
            $(form + ' input[name="' + index + '"]').val(value);
          } else if ($(form + ' select[name="' + index + '"]').length) {
            $(form + ' select[name="' + index + '"]').find('option[value="' + value + '"]').prop("selected", "selected");
          } else if ($(form + ' textarea[name="' + index + '"]').length) {
            $(form + ' textarea[name="' + index + '"]').val(value);
          }
        });
        $(modal + ' form input[name="id"]').remove();
        $(modal).modal('show');

      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });

  });

  //ajax save form in modal
  $("button.modal-save").on("click", function(e) {
    var modal = '#' + $(this).attr("data-modal");
    var form = '#' + $(this).attr("data-form");
    ajaxModalFormSave(form, modal);

  });

  //delete by primary key
  $("button.delete-by-prim-key").on("click", function(e) {

    var id = $(this).attr("data-id");
    var table = $(this).attr("data-table");

    if (confirm("Êtes-vous certain ?")) {


      $.ajax({
        url: urlBase + '/configuration/ajax/configDelByPrimaryKey/',
        type: 'post',
        data: {
          id: id,
          table: table
        },
        dataType: "json",
        success: function(data) {
          location.reload();
        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });
    }
  });


  // reset modal form
  $("button.reset-modal").on("click", function(e) {
    var modal = $(this).attr("data-target");
    $(modal + ' form input[name="id"]').remove();

    $(modal + ' form input').val('');
    $(modal + ' form textarea').val('');
    $(modal + ' form select option').removeProp('selected');
    $(modal + ' form select option:eq(0)').prop('selected', 'selected');

  });

  // ajout à la modale pour dicom
  $("button.addtomodaldicom").on("click", function(e) {
    var modal = $(this).attr("data-target");
    $(modal + ' form input[name="dicomTag"]').val($(this).attr('data-dicomtag'));
    $(modal + ' form input[name="dicomCodeMeaning"]').val($(this).attr('data-dicomcodemeaning'));
  });


  $("button.typeToggleVisibility").on("click", function(e) {

    classToToggle = $(this).attr('id');
    $('.' + classToToggle).toggle();

  });

  // Upload fichier par drag@drop
  $("#dropZoneFichierZoneConfig").dmUploader({
    url: urlBase + '/configuration/ajax/configUploadFichierZoneConfig/',
    extraData: {
      destination: $('#dropZoneFichierZoneConfig').attr("data-destination"),
    },
    dataType: 'html',
    maxFiles: 1,
    onUploadSuccess: function(id, data) {
      $(".progress-bar").css('width', '0%');
      location.reload();
    },
    onUploadProgress: function(id, percent) {
      $(".progress-bar").css('width', percent + '%');
    }
  });

  //delete clef apicrypt
  $(".delApicryptClef").on("click", function(e) {
    e.preventDefault();
    var userID = $(this).attr("data-user");
    var file = $(this).attr("data-file");

    if (confirm("Êtes-vous certain ?")) {


      $.ajax({
        url: urlBase + '/configuration/ajax/configDeleteApicryptClef/',
        type: 'post',
        data: {
          userID: userID,
          file: file
        },
        dataType: "json",
        success: function(data) {
          location.reload();
        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });
    }
  });

  //delete template PDF
  $(".delTemplatePDF").on("click", function(e) {
    e.preventDefault();
    var userID = $(this).attr("data-user");
    var file = $(this).attr("data-file");

    if (confirm("Êtes-vous certain ?")) {


      $.ajax({
        url: urlBase + '/configuration/ajax/configTemplatePDFDelete/',
        type: 'post',
        data: {
          userID: userID,
          file: file
        },
        dataType: "json",
        success: function(data) {
          location.reload();
        },
        error: function() {
          alert_popup("danger", 'Problème, rechargez la page !');

        }
      });
    }
  });

  //activation de codemirror pour édition templates
  if ($("#templateEditor").length ) {
    var editor = CodeMirror.fromTextArea(document.getElementById("templateEditor"), {
      lineNumbers: true,
      mode: "twig",
      lineWrapping: true
    });
    editor.setSize(null, 400);
  }

  //auto_grow pour edition de formulaires
  $("#formParamsEdit textarea").on("keyup, focus", function() {
    $(this).css("overflow", "hidden");
    auto_grow(this);
  });

  // voir les mots de passe dans les paramètres par défaut
  $(".viewPassword").removeClass('viewPassword').parent()
    .css('cursor', 'pointer')
    .addClass('viewPassword')
    .on("mousedown", function(){
      $(this).closest('.input-group').find('input').attr('type','text');
    })
    .on("mouseup", function(){
      $(this).closest('.input-group').find('input').attr('type','password');
    });

  //Suppression d'un paramètre dans la page paramètres spécifiques
  $('body').on('click', '.removeParam', function(){
    var $tr = $(this).closest('tr');
    $.ajax({
      url: urlBase+"/configuration/ajax/configUserParamDelete/",
      type: 'post',
      data: {
        userID: $('input[name=userID]').val(),
        paramName: $tr.attr('data-name')
      },
      dataType: "json",
      success: function(data) {
        $tr.remove();
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  //droits admin dans la page liste des utilisateurs
  $(".changeAdmin").on("click", function(e){
    e.preventDefault();
    var $ca = $(this);
    $.ajax({
      url: urlBase+"/configuration/ajax/configGiveAdmin/",
      type: 'post',
      data: {
        id: $ca.attr('data-userid')
      },
      dataType: "json",
      success: function(data) {
        $ca.children(".fa-square").removeClass("fa-square").addClass("check-square");
        $ca.children(".fa-check-square").removeClass("fa-check-square").addClass("fa-square");
        $ca.children(".check-square").removeClass("check-square").addClass("fa-check-square");
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  //choix du module utilisateur dans la page liste des utilisateurs
  $(".changeModule").on("change", function(){
    var $cm = $(this);
    $.ajax({
      url: urlBase+"/configuration/ajax/configChangeModule/",
      type: 'post',
      data: {
        id: $cm.attr('data-userid'),
        module: $cm.val()
      },
      dataType: "json",
      success: function(data) {
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  //changement de mot de passe d'un utilisateur dans la page liste des utilisateurs
  $(".changePassword").on("click", function(e){
    e.preventDefault();
    var $cp = $(this);
    $.ajax({
      url: urlBase+"/configuration/ajax/configChangePassword/",
      type: 'post',
      data: {
        id: $cp.attr('data-userid'),
        password: $("input[data-userid="+$cp.attr('data-userid')+"]").val()
      },
      dataType: "json",
      success: function(data) {
        alert_popup("success", 'le mot de passe de l\'utilisateur "'+ $cp.attr('data-name') + '" a été changé avec succès');

      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  //forcer le rechargement de la page sur modif du yaml agenda
  $(".reload-on-mod").on("keyup", function(){
    $(this).closest("form").addClass('reload');
  });

  //Révoquer un utilisateur dans la page liste des utilisateurs
  $(".revokeUser").on("click", function(e){
    e.preventDefault();
    var $ru = $(this);
    if (!confirm('Etes vous sûr de vouloir supprimer l\'utilisateur "'+$ru.attr('data-name')+'" ?'))
      return;
    $.ajax({
      url: urlBase+"/configuration/ajax/configRevokeUser/",
      type: 'post',
      data: {
        id: $ru.attr('data-userid')
      },
      dataType: "json",
      success: function(data) {
          $ru.closest("tr").remove();
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  // selecteur de catégorie page SpecificUserParam
  $('select[name=paramCat]').on('click', function(){
    $('.paramselect').hide();
    $('.paramselect[name=paramNameInCat' + $(this).val() + ']').show();
    $('#description').html('description : ' + $('.paramselect:visible').find('option:selected').attr('data-desc'));
    $('#type').html('type : ' + $('.paramselect:visible').find('option:selected').attr('data-type'));
  });

  // selecteur de paramètre page SpecificUserParam
  $('.paramselect').on('click', function(){
    $('#description').html('description : ' + $(this).find('option:selected').attr('data-desc'));
    $('#type').html('type : ' + $(this).find('option:selected').attr('data-type'));
  });

  $("body.uploader").dmUploader({
    url: urlBase + '/configuration/ajax/configInstallModule/',
    extFilter: ["zip"],
    maxFiles: 1,
    allowedTypes: "application/(zip|x-zip-compressed)",
    dataType: 'html',
    onUploadSuccess: function() {
      console.log('fichier envoyé');
    },
    onDragEnter: function(){
      $(".mask").css("display", "block");
      $(".mask").animate({opacity: 0.4}, 500);
    },
    onDragLeave: function(){
      $(".mask").animate({opacity: 0}, 500, "linear", function(){$(".mask").css("display", "none")});
    },
    onFileTypeError: function(){
      alert_popup("danger", "Le format de fichier déposé n'est pas correct. Il faut que ce soit un zip (.zip)");

    },
    onBeforeUpload: function(id) {
      if (!confirm("Confirmez l'installation du fichier"))
        $(this).dmUploader("cancel", id);
    },
    onUploadSuccess: function(id, data) {
        if (data.indexOf("Erreur:")==0) {
            $("#errormessage").html(data);
            $(".submit-error").animate({top: "50px"},300,"easeInOutCubic", function(){setTimeout((function(){$(".submit-error").animate({top:"0"},300)}), 4000)});
        } else if (data.toLowerCase().indexOf("ok")==0) {
          alert_popup("success", "La première phase d'installation a été réalisée avec succès! Deloguez puis reloguez vous pour accomplir la suite");

        } else {
          alert_popup("danger", "La première phase d'installation a été réalisée, mais il y a eu les messages suivants : " + data.substr(0,data.length-2));

        }
    },
    onUploadError: function(id, xhr, status, errorThrown) {
      $("#errormessage").html(errorThrown);
      $(".submit-error").animate({top: "50px"},300,"easeInOutCubic", function(){setTimeout((function(){$(".submit-error").animate({top:"0"},300)}), 4000)});
    }
  });

  // importer les datas d'un acte CCAM
  $(".importFromAmeliCCAM").on("click", function(e){
    e.preventDefault();
    codeActe=$('.modal input[name="code"]').val();

    if(codeActe.length != 7) {
      alert('Le code inséré n\'est pas un code d\'acte CCAM valide !');
      return;
    }

    $.ajax({
      url: urlBase+"/configuration/ajax/extractCcamActeData/",
      type: 'post',
      data: {
        codeActe: codeActe
      },
      dataType: "json",
      success: function(data) {
        $('.modal input[name="label"]').val(data.label);
        $('.modal input[name="tarifs1"]').val(data.tarifs1);
        $('.modal input[name="tarifs2"]').val(data.tarifs2);
        $('.modal select[name="type"]').find('option[value="CCAM"]').prop("selected", "selected");
        console.log(data.modificateurs);
        $.each(['F', 'P', 'S', 'M', 'R', 'D', 'E', 'C', 'U'], function( index, value ) {
          if(data.modificateurs.indexOf(value) != -1) {
            console.log(value + ' applicable');
            $('.modal select[name="'+ value +'"]').find('option[value="true"]').prop("selected", "selected");
          } else {
            console.log(value + ' non applicable');
            $('.modal select[name="'+ value +'"]').find('option[value="false"]').prop("selected", "selected");
          }
        });
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

});

function ajaxModalFormSave(form, modal) {
  var data = {};
  $(form + ' input, ' + form + ' select, ' + form + ' textarea').each(function(index) {
    var input = $(this);
    data[input.attr('name')] = input.val();
  });

  var url = $(form).attr('action');
  data["groupe"] = $(form).attr('data-groupe');

  $.ajax({
    url: url,
    type: 'post',
    data: data,
    dataType: "json",
    success: function(data) {
      if (data.status == 'ok') {
        $(modal).modal('hide');
        location.reload();
      } else {
        $(modal + ' div.alert').show();
        $(modal + ' div.alert ul').html('');
        $.each(data.msg, function(index, value) {
          $(modal + ' div.alert ul').append('<li>' + index + ': ' + value + '</li>');
          $('#' + index + 'ID').parent('div').addClass('has-error');

        });
      }
    },
    error: function() {
      alert_popup("danger", 'Problème, rechargez la page !');

    }
  });
}
