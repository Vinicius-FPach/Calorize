document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("food_image_input");
    const preview = document.getElementById("image_preview");
    const removeBtn = document.getElementById("remove-image-btn");
    const removeInput = document.getElementById("remove_image_input");

    if (!(input && preview)) return;

    const defaultImage = preview.dataset.defaultImage;
    const initialImage = preview.src;

    if (initialImage === defaultImage || initialImage.endsWith(defaultImage)) {
        setPreviewDefault();
    }

    function toggleRemoveBtn(visible) {
        if (removeBtn) {
            removeBtn.style.setProperty('display', visible ? 'flex' : 'none', 'important');
        }
    }

    function setPreviewDefault() {
        preview.className = 'w-12 h-12 object-contain opacity-50';
    }

    function setPreviewImage() {
        preview.className = 'w-full h-full object-cover';
    }

    input.addEventListener("change", function (event) {
        const file = event.target.files[0];

        if (file) {
            if (removeInput) removeInput.value = '0';

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                setPreviewImage();
                toggleRemoveBtn(true);
            };
            reader.readAsDataURL(file);
        }
    });

    if (removeBtn) {
        removeBtn.addEventListener("click", function () {
            if (input.value !== "") {
                input.value = "";
                preview.src = initialImage;

                if (initialImage === defaultImage || initialImage.endsWith(defaultImage)) {
                    setPreviewDefault();
                    toggleRemoveBtn(false);
                    if (removeInput) removeInput.value = '1';
                } else {
                    setPreviewImage();
                    toggleRemoveBtn(true);
                    if (removeInput) removeInput.value = '0';
                }
            } else {
                preview.src = defaultImage;
                setPreviewDefault();
                toggleRemoveBtn(false);
                if (removeInput) removeInput.value = '1';
            }
        });
    }
});