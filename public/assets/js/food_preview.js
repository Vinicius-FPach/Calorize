document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("food_image_input");
    const preview = document.getElementById("image_preview");

    if (!(input && preview)) return;

    input.addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
});