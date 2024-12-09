document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const nextButton = document.getElementById('nextButton');
    const inputs = document.getElementsByTagName('input');

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

    function validateAmounts() {
        const priceWhole = parseInt(priceWholeInput.value, 10) || 0;
        const startingAmountWhole = parseInt(startingAmountWholeInput.value, 10) || 0;

        if (startingAmountWhole >= priceWhole) {
            nextButton.disabled = true;
            amountWarning.classList.remove('hidden'); // Show the warning
        } else {
            const allValid = Array.from(requiredFields).every(f => f.checkValidity());
            nextButton.disabled = !allValid;
            amountWarning.classList.add('hidden'); // Hide the warning
        }
    }

    // Add event listeners for price and starting amount inputs
    priceWholeInput.addEventListener('input', validateAmounts);
    startingAmountWholeInput.addEventListener('input', validateAmounts);



    window.Alpine.data('cancelConfirmation', () => ({
        showConfirmCancel: false
    }));

});
