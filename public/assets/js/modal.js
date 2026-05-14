function openModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('bg-black/40') || e.target.closest('[id^="delete-"]') === e.target) {
        const modals = document.querySelectorAll('[id^="delete-"]');
        modals.forEach(modal => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    }
});