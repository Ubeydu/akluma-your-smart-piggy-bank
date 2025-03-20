document.addEventListener('DOMContentLoaded', function() {

    // Process all piggy bank cards
    const cards = document.querySelectorAll('.piggy-bank-card');

    cards.forEach(card => {
        const piggyBankId = card.dataset.piggyBankId;
        const newPiggyBankId = card.dataset.newPiggyBankId;
        const newTime = card.dataset.newPiggyBankTime;

        // Only proceed if this is the newly created piggy bank
        if (newPiggyBankId && piggyBankId === newPiggyBankId && newTime) {
            // Check if we've already highlighted this piggy bank
            const storageKey = 'highlighted_piggy_bank_' + newPiggyBankId;
            const hasBeenHighlighted = localStorage.getItem(storageKey);

            if (!hasBeenHighlighted) {
                // First time seeing this piggy bank - apply highlight

                card.classList.add('highlight-new', 'border-indigo-500', 'ring-2', 'ring-indigo-200');

                // Remember that we've highlighted this piggy bank
                localStorage.setItem(storageKey, 'true');
            } else {

                // Remove any server-side highlight classes
                card.classList.remove('highlight-new', 'border-indigo-500', 'ring-2', 'ring-indigo-200');
            }
        }
    });

    // Handle the success message with Alpine.js
    const successContainer = document.querySelector('[x-data*="show: true"]');
    if (successContainer) {
        // Check if we're coming from back/forward navigation
        const navigationType = performance.getEntriesByType('navigation')[0].type;
        if (navigationType === 'back_forward') {
            // Get the Alpine.js component instance
            if (window.Alpine && successContainer.__x) {
                // Set show to false using Alpine.js
                successContainer.__x.setData('show', false);
            } else {
                // Fallback
                successContainer.style.display = 'none';
            }
        }
    }
});
