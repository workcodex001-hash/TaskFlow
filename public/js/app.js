/**
 * TaskFlow - JavaScript Vanilla
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Fermeture automatique des alertes flash après 5 secondes
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach((alert) => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // 2. Confirmation pour les actions destructives
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const msg = btn.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir effectuer cette action ?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});
