document.addEventListener('DOMContentLoaded', function() {
    // Process all piggy bank cards
    const cards = document.querySelectorAll('.piggy-bank-card');
    // console.log('Found cards:', cards.length);

    cards.forEach(card => {
        const piggyBankId = card.dataset.piggyBankId;
        const newPiggyBankId = card.dataset.newPiggyBankId;
        const newTime = card.dataset.newPiggyBankTime;

        // // Debug: Log data for each card
        // console.log('Card ID:', piggyBankId, 'NewID:', newPiggyBankId, 'NewTime:', newTime);

        // Only proceed if this is the newly created piggy bank
        if (newPiggyBankId && piggyBankId === newPiggyBankId && newTime) {
            // console.log('Found matching card for highlighting:', piggyBankId);

            // Check if we've already highlighted this specific piggy bank creation
            const storageKey = 'highlighted_piggy_bank_' + newPiggyBankId + '_' + newTime;

            const hasBeenHighlighted = localStorage.getItem(storageKey);

            // console.log('Storage key:', storageKey);
            // console.log('Has been highlighted:', hasBeenHighlighted);

            if (!hasBeenHighlighted) {
                // First time seeing this piggy bank creation - apply highlight
                card.classList.add('highlight-new', 'border-indigo-500', 'ring-2', 'ring-indigo-200');

                // Remember that we've highlighted this specific creation
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
