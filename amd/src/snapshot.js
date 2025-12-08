define(['jquery', 'core/str', 'core/notification'], function ($, str, notification) {

    return {
        init: function (uniqid) {
            var video = document.getElementById(uniqid + '_video');
            var canvas = document.getElementById(uniqid + '_canvas');
            var snapBtn = document.getElementById(uniqid + '_snap_btn');
            var retakeBtn = document.getElementById(uniqid + '_retake_btn');
            var hiddenInput = document.getElementById(uniqid + '_hidden');
            var stream = null;

            if (!video || !canvas || !snapBtn || !retakeBtn || !hiddenInput) {
                console.warn('Snapshot elements not found for id: ' + uniqid);
                return;
            }

            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (s) {
                    stream = s;
                    video.srcObject = stream;
                    video.onloadedmetadata = function (e) {
                        video.play().catch(function (err) {
                            console.error("Error playing video: ", err);
                        });
                    };
                })
                .catch(function (err) {
                    console.log("An error occurred: " + err);
                    str.get_string('cameraaccesserror', 'block_profilepic').then(function (s) {
                        notification.alert('Error', s + ' ' + err.message);
                    }).catch(function () {
                        alert("Could not access webcam: " + err.message);
                    });
                });

            snapBtn.addEventListener("click", function () {
                var context = canvas.getContext("2d");
                context.drawImage(video, 0, 0, 320, 240);
                var dataURL = canvas.toDataURL("image/png");
                hiddenInput.value = dataURL;

                video.style.display = "none";
                canvas.style.display = "inline-block";
                snapBtn.style.display = "none";
                retakeBtn.style.display = "inline-block";
            });

            retakeBtn.addEventListener("click", function () {
                video.style.display = "inline-block";
                canvas.style.display = "none";
                snapBtn.style.display = "inline-block";
                retakeBtn.style.display = "none";
                hiddenInput.value = "";
            });
        }
    };
});
