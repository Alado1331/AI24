// assets/js/extra-admin.js

function filterPages() {
    const searchInput = document.getElementById('page-search').value.toLowerCase();
    const switcherItems = document.querySelectorAll('.switcher-item');

    switcherItems.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchInput)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

jQuery(document).ready(function($) {
    // Additional initialization if needed
});
