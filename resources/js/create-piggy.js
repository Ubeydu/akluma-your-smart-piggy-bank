    document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const nextButton = document.getElementById('nextButton');
    const inputs = document.getElementsByTagName('input');

        // Replace your existing form.addEventListener section with this:
        const requiredFields = form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('input', function() {
                alert('Required field changed: ' + field.id);
                const allValid = Array.from(requiredFields).every(f => f.checkValidity());
                nextButton.disabled = !allValid;
            });
        });

    const isValid = form.checkValidity();

    nextButton.disabled = !isValid;
});

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
