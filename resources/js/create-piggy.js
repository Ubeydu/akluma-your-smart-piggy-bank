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

    const clearButton = document.querySelector('[data-action="clear-form"]');
    clearButton.addEventListener('click', clearForm);

    function clearForm() {
        // Clear all form fields
        document.getElementById('name').value = '';
        document.getElementById('price_whole').value = '';
        document.getElementById('price_cents').value = '00';
        document.getElementById('link').value = '';
        document.getElementById('details').value = '';
        document.getElementById('starting_amount_whole').value = '';
        document.getElementById('starting_amount_cents').value = '';

        // Reset character counters
        document.getElementById('name-count').textContent = '0 / 255';
        document.getElementById('link-count').textContent = '0 / 1000';
        document.getElementById('details-count').textContent = '0 / 5000';

        // Clear session data via AJAX using the named route
        fetch(route('create-piggy-bank.clear'), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Optionally show a success message
                    console.log('Form and session cleared successfully');
                }
            })
            .catch(error => console.error('Error:', error));
    }

});
