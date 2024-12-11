/* global translations */
/**
 * @typedef {Object} Translations
 * @property {string} formattedPrice
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const nextButton = document.getElementById('nextButton');

    // Required field validation logic
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('input', function() {
            const allValid = Array.from(requiredFields).every(f => f.checkValidity());
            nextButton.disabled = !allValid;
        });
    });

    // Initial check for validity
    const isValid = form.checkValidity();
    nextButton.disabled = !isValid;

    // Character count logic
    const fields = [
        { id: 'name', max: 255 },
        { id: 'link', max: 1000 },
        { id: 'details', max: 5000 },
    ];

    fields.forEach(field => {
        const input = document.getElementById(field.id);
        const countDisplay = document.getElementById(`${field.id}-count`);

        input.addEventListener('input', function () {
            const length = input.value.length;
            countDisplay.textContent = `${length} / ${field.max}`;
        });
    });

    // Logic for comparing starting_amount_whole and price_whole
    const priceWholeInput = document.getElementById('price_whole');
    const startingAmountWholeInput = document.getElementById('starting_amount_whole');
    const amountWarning = document.getElementById('amount-warning'); // Reference to the warning message
    const differenceAmountWarning = document.getElementById('difference-amount-warning');



    function validateAmounts() {
        const priceWhole = parseInt(priceWholeInput.value, 10) || 0;
        const startingAmountWhole = parseInt(startingAmountWholeInput.value, 10) || 0;

        // First, check if either field is empty
        if (priceWholeInput.value.trim() === '' || startingAmountWholeInput.value.trim() === '') {
            amountWarning.classList.add('hidden');
            differenceAmountWarning.classList.add('hidden');
            const allValid = Array.from(requiredFields).every(f => f.checkValidity());
            nextButton.disabled = !allValid;
            return; // Exit the function here if either field is empty
        }

        // If we get here, both fields have values, so perform the validation
        if (startingAmountWhole >= priceWhole) {
            nextButton.disabled = true;
            amountWarning.classList.remove('hidden');
            differenceAmountWarning.classList.add('hidden');
        } else if ((priceWhole - startingAmountWhole) < 100) {
            nextButton.disabled = true;
            differenceAmountWarning.classList.remove('hidden');
            amountWarning.classList.add('hidden');
        } else {
            const allValid = Array.from(requiredFields).every(f => f.checkValidity());
            nextButton.disabled = !allValid;
            amountWarning.classList.add('hidden');
            differenceAmountWarning.classList.add('hidden');
        }
    }

    // Add event listeners for price and starting amount inputs
    priceWholeInput.addEventListener('input', validateAmounts);
    startingAmountWholeInput.addEventListener('input', validateAmounts);



    window.Alpine.data('cancelConfirmation', () => ({
        showConfirmCancel: false
    }));


    window.updateFormattedPrice = function(value, elementId) {
        const formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, ','); // Add a thousand separators
        const targetElement = document.getElementById(elementId);

        if (targetElement) {
            targetElement.textContent = formattedValue
                ? translations.formattedPrice.replace(':value', formattedValue)
                : '';
        }
    };


});
