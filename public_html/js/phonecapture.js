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
 * Fonctions JS pour la fonction capture images smartphone
 *
 * @author Bertrand Boutillier <b.boutillier@gmail.com>
 */

$(document).ready(function() {

  $('body').on('click', '#logout', function(e) {
    if (confirm("Si vous décidez de continuer, il faudra recommencer tout le processus d'identification pour ce périphérique.")) {

    } else {
      e.preventDefault();
    }
  });

  $("#declencher").show();
  $("#envoyer").hide();
  $("#refaire").hide();
  $("#rafraichir").hide();

  function onTimeout() {
    $("video")[0].srcObject.getVideoTracks()[0].stop();
    $("#declencher").hide();
    $("#envoyer").hide();
    $("#refaire").hide();
    $("#rafraichir").show();
    videoTO = undefined;
  };
  var videoTO = setTimeout(onTimeout, 30000);

  $("#rafraichir").on("click", function() {
    startVideo();
    $("#declencher").show();
    $("#envoyer").hide();
    $("#refaire").hide();
    $("#rafraichir").hide();
    videoTO = setTimeout(onTimeout, 30000);
  });

  $("#refaire").on("click", function() {
    $("video")[0].play();
    $("#declencher").show();
    $("#envoyer").hide();
    $("#refaire").hide();
    if (videoTO) {
      clearTimeout(videoTO);
      videoTO = setTimeout(onTimeout, 30000);
    }
  });
  $("#declencher").on("click", function() {
    $("video")[0].pause();
    $("#declencher").hide();
    $("#envoyer").show();
    $("#refaire").show();
    if (videoTO) {
      clearTimeout(videoTO);
      videoTO = setTimeout(onTimeout, 30000);
    }
  });
  $("#envoyer").on("click", function() {
    var canvas = document.createElement("canvas");
    var context = canvas.getContext('2d');
    var video = $("video")[0];
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, video.videoWidth, video.videoHeight);
    context.fillStyle = "rgba(0,0,0,0.3)";
    context.fillRect(0, video.videoHeight - 25, video.videoWidth, video.videoHeight);
    context.fillStyle = "#ffffff";
    context.font = "18px Arial";
    context.fillText("{{ page.data.patient.2 }} {{ page.data.patient.3 }} ({{ page.data.patient.dicomPatientID }}) - " + moment(Date.now()).format("YYYY-MM-DD HH:mm:SS"), 10, video.videoHeight - 5);

    $("#miniatures").append(canvas);
    //la valeur à envoyer via ajax
    var a_envoyer = canvas.toDataURL('image/png');
    $("#miniatures").width("+=" + (80 * video.videoWidth / video.videoHeight + 5));
    $("video")[0].play();
    $("#declencher").show();
    $("#envoyer").hide();
    $("#refaire").hide();
    if (videoTO) {
      clearTimeout(videoTO);
      videoTO = setTimeout(onTimeout, 30000);
    }


    $.ajax({
      xhr: function() {
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener("progress", function(evt) {
          if (evt.lengthComputable) {
            var percentComplete = evt.loaded / evt.total;
            console.log(percentComplete);
            $('#avancement').val(
              percentComplete * 100
            );
            if (percentComplete === 1) {
              $('#avancement').val(0);
            }
          }
        }, false);
        xhr.addEventListener("progress", function(evt) {
          if (evt.lengthComputable) {
            var percentComplete = evt.loaded / evt.total;
            console.log(percentComplete);
            $('#avancement').val(
              percentComplete * 100
            );
          }
        }, false);
        return xhr;
      },
      url: urlBase + '/phonecapture/ajax/recevoirImages/',
      type: "post",
      data: {
        pngBase64: a_envoyer
      },
      dataType: "json",
      beforeSend: function() {
        $('#avancement').show();
        $('#avancement').val(0);
      },
      success: function(data) {
        $('#avancement').hide();
        $('#avancement').val(0);
      },
      error: function() {
        alert('Un problème est survenu.');
        location.reload();
      },
    });


  });

  function startVideo() {
    var constraints = {
      audio: false,
      video: {
        width: 1920,
        height: 1080,
        frameRate: 10
      }
    };

    navigator.mediaDevices.getUserMedia(constraints)
      .then(function(mediaStream) {
        var video = $('video')[0];
        video.srcObject = mediaStream;
        video.onloadedmetadata = function(e) {
          video.play();
        };
      })
      .catch(function(err) {
        console.log(err.name + ": " + err.message);
      }); // always check for errors at the end.
  };
  startVideo();

})
