// Activer/désactiver le champ justifié selon l'absence
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('select[name^="absences"]').forEach(sel => {
        sel.addEventListener('change', function() {
            const row = this.closest('tr');
            const justifiedSelect = row.querySelector('select[name^="justified"]');
            justifiedSelect.disabled = (this.value !== 'oui');
        });
    });
});