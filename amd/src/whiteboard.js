define(['jquery'], function ($) {

    return {
        init: function (uniqid) {
            var canvas = document.getElementById(uniqid + '_wb_canvas');
            var clearBtn = document.getElementById(uniqid + '_clear_btn');
            var hiddenInput = document.getElementById(uniqid + '_hidden');

            if (!canvas || !clearBtn || !hiddenInput) {
                console.warn('Whiteboard elements not found for id: ' + uniqid);
                return;
            }

            var ctx = canvas.getContext("2d");
            var drawing = false;

            // Defaults
            ctx.lineWidth = 7;
            ctx.lineCap = "round";
            ctx.strokeStyle = "black";

            // Color selection
            var colorsDiv = document.getElementById(uniqid + '_colors');
            if (colorsDiv) {
                var colors = colorsDiv.querySelectorAll('.block_profilepic_color');
                colors.forEach(function (color) {
                    color.addEventListener('click', function () {
                        // Update visual selection
                        colors.forEach(function (c) { c.style.border = '1px solid #ccc'; });
                        this.style.border = '2px solid #000';

                        // Update context
                        ctx.strokeStyle = this.getAttribute('data-color');
                    });
                    // Set initial visual selection for default black
                    if (color.getAttribute('data-color') === 'black') {
                        color.style.border = '2px solid #000';
                    }
                });
            }

            // Size selection
            var sizesDiv = document.getElementById(uniqid + '_sizes');
            if (sizesDiv) {
                var sizes = sizesDiv.querySelectorAll('.block_profilepic_size');
                sizes.forEach(function (size) {
                    size.addEventListener('click', function () {
                        // Update visual selection
                        sizes.forEach(function (s) { s.style.border = 'none'; });
                        this.style.border = '2px solid #000';

                        // Update context
                        ctx.lineWidth = parseInt(this.getAttribute('data-size'));
                    });
                    // Set initial visual selection for default size 7
                    if (size.getAttribute('data-size') === '7') {
                        size.style.border = '2px solid #000';
                    }
                });
            }


            // Update hidden input on mouse up
            function updateInput() {
                hiddenInput.value = canvas.toDataURL("image/png");
            }

            // Initial empty whiteboard state
            updateInput();

            function getPos(canvas, evt) {
                var rect = canvas.getBoundingClientRect();
                return {
                    x: (evt.clientX - rect.left) / (rect.right - rect.left) * canvas.width,
                    y: (evt.clientY - rect.top) / (rect.bottom - rect.top) * canvas.height
                };
            }

            function startDraw(e) {
                drawing = true;
                draw(e);
            }

            function endDraw() {
                drawing = false;
                ctx.beginPath();
                updateInput();
            }

            function draw(e) {
                if (!drawing) return;

                var pos;
                if (e.type.includes("touch")) {
                    var touch = e.touches[0];
                    var mouseEvent = new MouseEvent("mousemove", {
                        clientX: touch.clientX,
                        clientY: touch.clientY
                    });
                    pos = getPos(canvas, mouseEvent);
                } else {
                    pos = getPos(canvas, e);
                }

                // ctx properties are set by event listeners, simplified draw here
                // but need to ensure path is correct

                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
            }

            canvas.addEventListener("mousedown", startDraw);
            canvas.addEventListener("mouseup", endDraw);
            canvas.addEventListener("mousemove", draw);
            canvas.addEventListener("mouseout", endDraw); // Stop drawing if leave canvas

            // Touch support
            canvas.addEventListener("touchstart", function (e) {
                e.preventDefault(); // Prevent scrolling while drawing
                startDraw(e.touches[0]);
            });
            canvas.addEventListener("touchend", function (e) {
                e.preventDefault();
                endDraw();
            });
            canvas.addEventListener("touchmove", function (e) {
                e.preventDefault();
                draw(e);
            });

            clearBtn.addEventListener("click", function () {
                ctx.fillStyle = "white";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                // Reset to white fill but keep stroke style
                updateInput();
            });
        }
    };
});
