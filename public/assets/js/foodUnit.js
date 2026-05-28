document.addEventListener('DOMContentLoaded', function () {
    const unitSelect = document.getElementById('unit-select');

    if (unitSelect) {
        unitSelect.addEventListener('change', function () {
            let unit = this.value === 'ml' ? 'ml' : 'g';

            document.querySelectorAll('.dynamic-unit').forEach(el => el.textContent = unit);

            document.querySelectorAll('.dynamic-badge').forEach(el => el.textContent = unit);
        });
    }
});