document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("food_image_input");
    const preview = document.getElementById("image_preview");
    const removeBtn = document.getElementById("remove-image-btn");

    if (!(input && preview)) return;

    input.addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;

                if (removeBtn) {
                    removeBtn.classList.remove("hidden");
                }
            };
            reader.readAsDataURL(file);
        }
    });

    if (removeBtn) {
        removeBtn.addEventListener("click", function () {
            input.value = "";

            preview.src = preview.dataset.defaultImage;

            removeBtn.classList.add("hidden");
        });
    }
});