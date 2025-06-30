document.addEventListener('DOMContentLoaded', function () {
    // Formatting function for enter saving amount strategy
    window.updateFormattedPrice = function(value, elementId) {
        const formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const targetElement = document.getElementById(elementId);

        if (targetElement) {
            targetElement.textContent = formattedValue
                ? translations.formattedSavingAmount.replace(':value', formattedValue)
                : '';
        }
    };
});
