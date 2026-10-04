function handleClientRouting() {
    const hash = window.location.hash || '#shop';

    document.querySelectorAll('.portal-section').forEach(section => {
        section.style.display = 'none';
    });

    document.querySelectorAll('.nav-links a').forEach(link => {
        link.classList.remove('active');
        link.style.color = '#cbd5e1';
    });

    // Show the selected section.
    const targetSection = document.querySelector(hash);

    if (targetSection) {
        targetSection.style.display = 'block';
    }

    // Highlight the selected navigation item.
    const targetLink = document.querySelector(`a[href="${hash}"]`);

    if (targetLink) {
        targetLink.classList.add('active');
        targetLink.style.color = '#ffffff';
    }
}

window.addEventListener('hashchange', handleClientRouting);
window.addEventListener('DOMContentLoaded', handleClientRouting);
