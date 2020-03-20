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
  } else if (document.URL.indexOf("#ap") >= 0) {
    $($("ul.nav-tabs li")[3]).children("a")[0].click();
  } else if (document.URL.indexOf("#journaux") >= 0) {
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
    ajaxModalSave(form, modal, function() {
      location.reload();
    });
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

  //delete user template
  $(".delUserTemplate").on("click", function(e) {
    e.preventDefault();
    var file = $(this).attr("data-file");
    if (confirm("Êtes-vous certain ?")) {
      $.ajax({
        url: urlBase + '/configuration/ajax/configUserTemplateDelete/',
        type: 'post',
        data: {
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

  //  activation de codemirror pour édition templates
  if ($("#templateEditor").length) {
    var editor = CodeMirror.fromTextArea(document.getElementById("templateEditor"), {
      lineNumbers: true,
      mode: "twig",
      lineWrapping: true
    });
    editor.setSize(null, 500);
  }

  // codemirror pour les différents éditeurs de la conception de formulaire
  $('#structure-tab').on('shown.bs.tab', function(e) {
    if (yamlEditor instanceof CodeMirror) {
      yamlEditor.refresh();
    } else {
      // Load main editor
      yamlEditor = CodeMirror.fromTextArea(document.getElementById("yamlEditor"), {
        lineNumbers: true,
        mode: "yaml",
      });
      yamlEditor.setSize('100%', 900);
    }
    yamlEditor.on("change", function(yamlEditor, change) {
      $('#yamlEditor').val(yamlEditor.getValue());
    });
  });

  $('#cda-tab').on('shown.bs.tab', function(e) {
    if (cdaEditor instanceof CodeMirror) {
      cdaEditor.refresh();
    } else {
      // Load main editor
      cdaEditor = CodeMirror.fromTextArea(document.getElementById("cdaEditor"), {
        lineNumbers: true,
        mode: "yaml",
      });
      cdaEditor.setSize('100%', 500);
    }
    cdaEditor.on("change", function(cdaEditor, change) {
      $('#cdaEditor').val(cdaEditor.getValue());
    });
  });

  $('#javascript-tab').on('shown.bs.tab', function(e) {
    if (javascriptEditor instanceof CodeMirror) {
      javascriptEditor.refresh();
    } else {
      // Load main editor
      javascriptEditor = CodeMirror.fromTextArea(document.getElementById("javascriptEditor"), {
        lineNumbers: true,
        mode: "javascript",
      });
      javascriptEditor.setSize('100%', 700);
    }
    javascriptEditor.on("change", function(javascriptEditor, change) {
      $('#javascriptEditor').val(javascriptEditor.getValue());
    });

  });

  $('#options-tab').on('shown.bs.tab', function(e) {
    if (optionsEditor instanceof CodeMirror) {
      optionsEditor.refresh();
    } else {
      // Load main editor
      optionsEditor = CodeMirror.fromTextArea(document.getElementById("optionsEditor"), {
        lineNumbers: true,
        mode: "yaml",
      });
      optionsEditor.setSize('100%', 700);
    }
    optionsEditor.on("change", function(optionsEditor, change) {
      $('#optionsEditor').val(optionsEditor.getValue());
    });
  });


  // voir les mots de passe dans les paramètres par défaut
  $(".viewPassword").removeClass('viewPassword').parent()
    .css('cursor', 'pointer')
    .addClass('viewPassword')
    .on("mousedown", function() {
      $(this).closest('.input-group').find('input').attr('type', 'text');
    })
    .on("mouseup", function() {
      $(this).closest('.input-group').find('input').attr('type', 'password');
    });

  //Suppression d'un paramètre dans la page paramètres spécifiques
  $('body').on('click', '.removeParam', function() {
    var $tr = $(this).closest('tr');
    $.ajax({
      url: urlBase + "/configuration/ajax/configUserParamDelete/",
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
  $(".changeAdmin").on("click", function(e) {
    e.preventDefault();
    var $ca = $(this);
    $.ajax({
      url: urlBase + "/configuration/ajax/configGiveAdmin/",
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
  $(".changeModule").on("change", function() {
    var $cm = $(this);
    $.ajax({
      url: urlBase + "/configuration/ajax/configChangeModule/",
      type: 'post',
      data: {
        id: $cm.attr('data-userid'),
        module: $cm.val()
      },
      dataType: "json",
      success: function(data) {},
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  // révoquer la clef 2FA d'un utilisateur
  $(".revoke2faKey").on("click", function(e) {
    e.preventDefault();
    var $cm = $(this);
    $.ajax({
      url: urlBase + "/configuration/ajax/configRevoke2faKey/",
      type: 'post',
      data: {
        uid: $cm.attr('data-userid'),
      },
      dataType: "json",
      success: function(data) {
        alert_popup("success", 'La clef a été supprimée');
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  });

  //changement de mot de passe d'un utilisateur dans la page liste des utilisateurs
  $(".changePassword").on("click", function(e) {
    e.preventDefault();
    var $cp = $(this);
    $.ajax({
      url: urlBase + "/configuration/ajax/configChangePassword/",
      type: 'post',
      data: {
        id: $cp.attr('data-userid'),
        password: $("input[data-userid=" + $cp.attr('data-userid') + "]").val()
      },
      dataType: "json",
      success: function(data) {
        if (data.status == "ok") {
          alert_popup("success", 'le mot de passe de l\'utilisateur "' + $cp.attr('data-name') + '" a été changé avec succès');
          $("input[data-userid=" + $cp.attr('data-userid') + "]").val('');
        } else {
          alert_popup("danger", data.msg);
        }
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  });

  //forcer le rechargement de la page sur modif du yaml agenda
  $(".reload-on-mod").on("keyup", function() {
    $(this).closest("form").addClass('reload');
  });

  //Révoquer un utilisateur dans la page liste des utilisateurs
  $(".revokeUser").on("click", function(e) {
    e.preventDefault();
    var $ru = $(this);
    if (!confirm('Etes vous sûr de vouloir supprimer l\'utilisateur "' + $ru.attr('data-name') + '" ?'))
      return;
    $.ajax({
      url: urlBase + "/configuration/ajax/configRevokeUser/",
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
  $('select[name=paramCat]').on('click', function() {
    $('.paramselect').hide();
    $('.paramselect[name=paramNameInCat' + $(this).val() + ']').show();
    $('#description').html('description : ' + $('.paramselect:visible').find('option:selected').attr('data-desc'));
    $('#type').html('type : ' + $('.paramselect:visible').find('option:selected').attr('data-type'));
  });

  // selecteur de paramètre page SpecificUserParam
  $('.paramselect').on('click', function() {
    $('#description').html('description : ' + $(this).find('option:selected').attr('data-desc'));
    $('#type').html('type : ' + $(this).find('option:selected').attr('data-type'));
  });

  // upload module
  $("body.uploader").dmUploader({
    url: urlBase + '/configuration/ajax/configInstallModule/',
    extFilter: ["zip"],
    maxFiles: 1,
    allowedTypes: "application/(zip|x-zip-compressed)",
    dataType: 'html',
    onDragEnter: function() {
      $(".mask").css("display", "block");
      $(".mask").animate({
        opacity: 0.4
      }, 500);
    },
    onDragLeave: function() {
      $(".mask").animate({
        opacity: 0
      }, 500, "linear", function() {
        $(".mask").css("display", "none")
      });
    },
    onFileTypeError: function() {
      alert_popup("danger", "Le format de fichier déposé n'est pas correct. Il faut que ce soit un zip (.zip)");

    },
    onBeforeUpload: function(id) {
      if (!confirm("Confirmez l'installation du fichier"))
        $(this).dmUploader("cancel", id);
    },
    onUploadSuccess: function(id, data) {
      if (data.indexOf("Erreur:") == 0) {
        $("#errormessage").html(data);
        $(".submit-error").animate({
          top: "50px"
        }, 300, "easeInOutCubic", function() {
          setTimeout((function() {
            $(".submit-error").animate({
              top: "0"
            }, 300)
          }), 4000)
        });
      } else if (data.toLowerCase().indexOf("ok") == 0) {
        alert_popup("success", "La première phase d'installation a été réalisée avec succès! Deloguez puis reloguez vous pour accomplir la suite");

      } else {
        alert_popup("danger", "La première phase d'installation a été réalisée, mais il y a eu les messages suivants : " + data.substr(0, data.length - 2));

      }
    },
    onUploadError: function(id, xhr, status, errorThrown) {
      $("#errormessage").html(errorThrown);
      $(".submit-error").animate({
        top: "50px"
      }, 300, "easeInOutCubic", function() {
        setTimeout((function() {
          $(".submit-error").animate({
            top: "0"
          }, 300)
        }), 4000)
      });
    }
  });

  // upload plugin
  $("body.uploaderPlugin").dmUploader({
    url: urlBase + '/configuration/ajax/configInstallPlugin/',
    extFilter: ["zip"],
    maxFiles: 1,
    allowedTypes: "application/(zip|x-zip-compressed)",
    dataType: 'html',
    onDragEnter: function() {
      $(".mask").css("display", "block");
      $(".mask").animate({
        opacity: 0.4
      }, 500);
    },
    onDragLeave: function() {
      $(".mask").animate({
        opacity: 0
      }, 500, "linear", function() {
        $(".mask").css("display", "none")
      });
    },
    onFileTypeError: function() {
      alert_popup("danger", "Le format de fichier déposé n'est pas correct. Il faut que ce soit un zip (.zip)");

    },
    onBeforeUpload: function(id) {
      if (!confirm("Confirmez l'installation du fichier"))
        $(this).dmUploader("cancel", id);
    },
    onUploadSuccess: function(id, data) {
      if (data.indexOf("Erreur:") == 0) {
        $("#errormessage").html(data);
        $(".submit-error").animate({
          top: "50px"
        }, 300, "easeInOutCubic", function() {
          setTimeout((function() {
            $(".submit-error").animate({
              top: "0"
            }, 300)
          }), 4000)
        });
      } else if (data.toLowerCase().indexOf("ok") == 0) {
        // ok on reload la page
        window.location.reload(true);

      } else {
        alert_popup("danger", "La première phase d'installation a été réalisée, mais il y a eu les messages suivants : " + data.substr(0, data.length - 2));

      }
    },
    onUploadError: function(id, xhr, status, errorThrown) {
      $("#errormessage").html(errorThrown);
      $(".submit-error").animate({
        top: "50px"
      }, 300, "easeInOutCubic", function() {
        setTimeout((function() {
          $(".submit-error").animate({
            top: "0"
          }, 300)
        }), 4000)
      });
    }
  });


  $('.modalGestionActes').on('show.bs.modal', function(e) {
    setModalGestionActes();
  });

  $('.modalGestionActes select[name="type"]').on('change', function(e) {
    setModalGestionActes();
  });

  function setModalGestionActes() {
    if ($('.modal select[name="type"]').val() == 'NGAP') {
      $('.modal input[name="activite"]').parent('div').addClass('d-none');
      $('.modal input[name="phase"]').parent('div').addClass('d-none');
      $('.modal select[name="codeProf"]').parent('div').removeClass('d-none');
    } else if ($('.modal select[name="type"]').val() == 'Libre') {
      $('.modal input[name="activite"]').parent('div').addClass('d-none');
      $('.modal input[name="phase"]').parent('div').addClass('d-none');
      $('.modal select[name="codeProf"]').parent('div').addClass('d-none');
    } else {
      $('.modal input[name="activite"]').parent('div').removeClass('d-none');
      $('.modal input[name="phase"]').parent('div').removeClass('d-none');
      $('.modal select[name="codeProf"]').parent('div').addClass('d-none');
    }
  }

  // importer les datas d'un acte CCAM
  $(".importFromCCAM").on("click", function(e) {
    e.preventDefault();
    acteCode = $('.modal input[name="code"]').val();

    if (acteCode.length < 1) {
      alert('Le code inséré n\'est pas un code d\'acte valide !');
      return;
    }

    $.ajax({
      url: urlBase + "/configuration/ajax/extractCcamActeData/",
      type: 'post',
      data: {
        acteType: $('.modal select[name="type"]').val(),
        acteCode: acteCode,
        activiteCode: $('.modal input[name="activite"]').val(),
        phaseCode: $('.modal input[name="phase"]').val(),
        codeProf: $('.modal select[name="codeProf"]').val(),
      },
      dataType: "json",
      success: function(data) {
        if (!data.yaml) {
          alert_popup("danger", data);
          return;
        }
        $('.modal textarea[name="dataYaml"]').val(data.yaml);
        $('.modal input[name="label"]').val(data.acteLabel);
        $('.modal input[name="code"]').val(data.acteCode);
        $('.modal input[name="activite"]').val(data.activiteCode);
        $('.modal input[name="phase"]').val(data.phaseCode);
        $('.modal select[name="tarifUnit"]').val(data.tarifUnite);
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');

      }
    });
  });

  //Fixer le displayOrder des data type en fonction de l'ordre dans un form
  $("button.fixDisplayOrder").on("click", function(e) {
    e.preventDefault();
    if (!confirm('Etes vous sûr de vouloir réaliser cette action ?'))
      return;
    $.ajax({
      url: urlBase + "/configuration/ajax/fixDisplayOrder/",
      type: 'post',
      data: {
        formid: $(this).attr('data-formid')
      },
      dataType: "json",
      success: function(data) {
        alert_popup("success", 'Action effectuée !');
      },
      error: function() {
        alert_popup("danger", 'Problème, rechargez la page !');
      }
    });
  });

});
