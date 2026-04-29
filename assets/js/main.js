// ARPMS Main JavaScript - PIXEL PERFECT REPLICATION

document.addEventListener('DOMContentLoaded', () => {
    // Dropdown Toggles
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    const filterBtn = document.getElementById('filterBtn');
    const filterDropdown = document.getElementById('filterDropdown');

    if (notifBtn) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
            if (profileDropdown) profileDropdown.style.display = 'none';
            if (filterDropdown) filterDropdown.style.display = 'none';
        });
    }

    if (profileBtn) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.style.display = profileDropdown.style.display === 'block' ? 'none' : 'block';
            if (notifDropdown) notifDropdown.style.display = 'none';
            if (filterDropdown) filterDropdown.style.display = 'none';
        });
    }

    if (filterBtn) {
        filterBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            filterDropdown.style.display = filterDropdown.style.display === 'block' ? 'none' : 'block';
            if (notifDropdown) notifDropdown.style.display = 'none';
            if (profileDropdown) profileDropdown.style.display = 'none';
        });
    }

    document.addEventListener('click', () => {
        if (notifDropdown) notifDropdown.style.display = 'none';
        if (profileDropdown) profileDropdown.style.display = 'none';
        if (filterDropdown) filterDropdown.style.display = 'none';
    });

    // Prevent closing when clicking inside the filter dropdown
    if (filterDropdown) {
        filterDropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    // Global Search Functionality
    const searchInput = document.querySelector('.search-input-header');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            const query = e.target.value.toLowerCase();
            const projectCards = document.querySelectorAll('.project-card-premium');
            const teamCards = document.querySelectorAll('.view-card');
            const tableRows = document.querySelectorAll('tbody tr');
            const contactItems = document.querySelectorAll('.contact-item');

            projectCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(query) ? 'block' : 'none';
            });

            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });

            contactItems.forEach(item => {
                const name = item.querySelector('span') ? item.querySelector('span').textContent.toLowerCase() : '';
                item.style.display = name.includes(query) ? 'flex' : 'none';
            });

            teamCards.forEach(card => {
                const h3 = card.querySelector('h3');
                if (h3) {
                    const name = h3.textContent.toLowerCase();
                    card.style.display = name.includes(query) ? 'block' : 'none';
                }
            });
        });
    }
});

// Modal Helpers
function openModal(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'flex';
}

function openNewProjectModal() {
    openModal('newProjectModal');
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Form validation and submission enhancement
const newProjectForm = document.getElementById('newProjectForm');
if (newProjectForm) {
    newProjectForm.addEventListener('submit', function(e) {
        // Optional: Add AJAX submission here for immediate update
        // For now, let it submit normally as the user wants it to appear on dashboard.
    });
}
