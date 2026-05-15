const toggle = document.getElementById('menu-toggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');

toggle.addEventListener('click', () => {
    const isOpen = !sidebar.classList.contains('-translate-x-full');
            
    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        setTimeout(() => sidebar.classList.add('hidden'), 300);
    } else {
        sidebar.classList.remove('hidden');
        setTimeout(() => sidebar.classList.remove('-translate-x-full'), 10);
        overlay.classList.remove('hidden');
    }
});

overlay.addEventListener('click', () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    setTimeout(() => sidebar.classList.add('hidden'), 300);
});