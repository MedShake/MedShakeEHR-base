/*
 * This file is part of MedShakeEHR.
 *
 * Copyright (c) 2017
 * fr33z00 <https://github.com/fr33z00>
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
 * Fonctions JS pour la fonction capture images smartphone
 *
 * @author fr33z00 <https://github.com/fr33z00>
 * @contrib Bertrand Boutillier <b.boutillier@gmail.com>
 */

var page = document.querySelector("#page");
document.addEventListener("touchstart", function(e) {
  if (e.targetTouches.length != 2)
    return;
  if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
    if (page.requestFullscreen)
      page.requestFullscreen();
    else if (page.mozRequestFullScreen)
      page.mozRequestFullScreen();
    else if (page.webkitRequestFullscreen)
      page.webkitRequestFullscreen();
    else if (page.msRequestFullscreen)
      page.msRequestFullscreen();
  } else {
    if (document.exitFullscreen)
      document.exitFullscreen();
    else if (document.webkitExitFullscreen)
      document.webkitExitFullscreen();
    else if (document.mozCancelFullScreen)
      document.mozCancelFullScreen();
    else if (document.msExitFullscreen)
      document.msExitFullscreen();
  }
}, false);

$(document).ready(function() {

  if ($('#landscape').length) {

    $('body').on('click', '#logout', function(e) {
      if (confirm("Si vous décidez de continuer, il faudra recommencer tout le processus d'identification pour ce périphérique.")) {

      } else {
        e.preventDefault();
      }
    });

    var video = $(window).width() < $(window).height() ? $("#portrait video")[0] : $("#landscape video")[0];
    var orientation = $(window).width() < $(window).height() ? 'portrait' : 'landscape';
    $(".declencher").show();
    $(".envoyer").hide();
    $(".refaire").hide();
    $(".rafraichir").hide();

    function onTimeout() {
      video.srcObject.getVideoTracks()[0].stop();
      $(".declencher").hide();
      $(".envoyer").hide();
      $(".refaire").hide();
      $(".rafraichir").show();
      videoTO = undefined;
    };
    var videoTO = setTimeout(onTimeout, 30000);

    $(".rafraichir").on("click", function() {
      startVideo();
      $(".declencher").show();
      $(".envoyer").hide();
      $(".refaire").hide();
      $(".rafraichir").hide();
      videoTO = setTimeout(onTimeout, 30000);
    });

    $(".refaire").on("click", function() {
      video.play();
      $(".declencher").show();
      $(".envoyer").hide();
      $(".refaire").hide();
      if (videoTO) {
        clearTimeout(videoTO);
        videoTO = setTimeout(onTimeout, 30000);
      }
    });
    $(".declencher").on("click", function() {
      video.pause();
      $(".declencher").hide();
      $(".envoyer").show();
      $(".refaire").show();
      if (videoTO) {
        clearTimeout(videoTO);
        videoTO = setTimeout(onTimeout, 30000);
      }
    });
    $(".envoyer").on("click", function() {
      var canvas = document.createElement("canvas");
      var context = canvas.getContext('2d');
      var div = document.createElement("div");
      div.appendChild(canvas);
      $(div).addClass("miniature");
      $(div).width(80 * video.videoWidth / video.videoHeight);
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
      context.fillStyle = "rgba(0,0,0,0.3)";
      context.fillRect(0, video.videoHeight - 25, video.videoWidth, video.videoHeight);
      context.fillStyle = "#ffffff";
      context.font = "18px Arial";
      context.fillText(patientIdentite + " - " + moment(Date.now()).format("YYYY-MM-DD HH:mm:SS"), 10, video.videoHeight - 5);

      $(div).css("left", $("#miniatures").width());
      $("#miniatures").append(div);
      envoiImage(canvas);
      $("#miniatures").width("+=" + (80 * video.videoWidth / video.videoHeight + 5));
      video.play();
      $(".declencher").show();
      $(".envoyer").hide();
      $(".refaire").hide();
      if (videoTO) {
        clearTimeout(videoTO);
        videoTO = setTimeout(onTimeout, 30000);
      }
    });

    $(window).on("resize", function() {
      if ((orientation == 'portrait' && $(window).width() < $(window).height()) || (orientation == 'landscape' && $(window).width() > $(window).height()))
        return;
      clearTimeout(videoTO);
      onTimeout();
      video = $(window).width() < $(window).height() ? $("#portrait video")[0] : $("#landscape video")[0];
      orientation = $(window).width() < $(window).height() ? 'portrait' : 'landscape';
    });

    function avancement(canvas, valeur) {
      canvas.width = canvas.width; //efface le canvas
      var context = canvas.getContext('2d');
      context.fillStyle = valeur == 1 ? "rgba(0,255,0,0.2)" : "rgba(255,0,0,0.2)";
      context.fillRect(0, 0, 100 * valeur, 1);
    }

    function envoiImage(canvas) {
      var avance = document.createElement("canvas");
      avance.width = 100;
      avance.height = 1;
      $(canvas).parent().append(avance);
      $(avance).width($(canvas).width())
        .css("position", "absolute")
        .css("z-index", "100")
        .css("left", 0)
        .css("top", 0);
      $.ajax({
        xhr: function() {
          var xhr = new window.XMLHttpRequest();
          xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
              var percentComplete = evt.loaded / evt.total;
              avancement(avance, percentComplete);
            }
          }, false);
          xhr.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
              var percentComplete = evt.loaded / evt.total;
              avancement(avance, percentComplete);
            }
          }, false);
          return xhr;
        },
        url: urlBase + '/phonecapture/ajax/recevoirImages/',
        type: "post",
        data: {
          jpgBase64: canvas.toDataURL('image/jpeg', 0.97),
          dicomPatientID: dicomPatientID
        },
        dataType: "json",
        success: function(data) {
          if (data['status'] == 'badDicomPatientID') {
            alert_popup("danger", "L'image n'a pas été enregistrée car le dossier ouvert sur l'ordinateur a changé entre temps. L'interface va être mise à jour.");

            location.reload();
          }
        },
        error: function() {
          alert_popup("danger", 'Un problème est survenu.');

          location.reload();
        },
      });
    };

    function startVideo() {
      var constraints = {
        audio: false,
        video: {
          width: phonecaptureResolutionWidth,
          height: phonecaptureResolutionWidth,
          frameRate: 10,
          facingMode: {
            exact: 'environment'
          }
        }
      };

      navigator.mediaDevices.getUserMedia(constraints)
        .then(function(mediaStream) {
          video.srcObject = mediaStream;
          video.onloadedmetadata = function(e) {
            video.play();
          };
        })
        .catch(function(err) {
          console.log(err.name + ": " + err.message);
        });
    };
    startVideo();

  }

  //générer QR code sur page accés
  if ($('#QRcode').length) {
    var el = kjua({
      text: urlDestination,

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
      label: 'MedShakeEHR',
      fontname: 'sans',
      fontcolor: '#d9534f',

      // image element
      image: null

    });
    $('#QRcode').html(el);
  }

})
