/* global translations */
/**
 * @typedef {Object} Translations
 * @property {string} formattedPrice
 */

document.addEventListener('DOMContentLoaded', function () {

    const form = document.querySelector('form');
    const nextButton = document.getElementById('nextButton');
    const priceWholeInput = document.getElementById('price_whole');
    const startingAmountWholeInput = document.getElementById('starting_amount_whole');
    const amountWarning = document.getElementById('amount-warning'); // Reference to the warning message
    const differenceAmountWarning = document.getElementById('difference-amount-warning');


    // Move the details toggle functionality here (at the beginning)
    const detailsToggle = document.getElementById('detailsToggle');
    const detailsContainer = document.getElementById('detailsContainer');
    const toggleText = document.getElementById('toggleText');
    const toggleIcon = document.getElementById('toggleIcon');



    if (detailsToggle) {
        detailsToggle.addEventListener('click', function() {
            detailsContainer.classList.toggle('hidden');
            if (detailsContainer.classList.contains('hidden')) {
                toggleText.textContent = translations.showDetails;
                toggleIcon.classList.remove('rotate-180');
            } else {
                toggleText.textContent = translations.hideDetails;
                toggleIcon.classList.add('rotate-180');
            }
        });
    }



    setTimeout(() => validateAmounts(), 0);

    // Required field validation logic
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('input', function() {
            validateAmounts();  // Let validateAmounts handle everything
        });
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




    function validateAmounts() {
        const priceWhole = parseInt(priceWholeInput.value, 10) || 0;
        const startingAmountWhole = parseInt(startingAmountWholeInput.value, 10) || 0;

        // Changed how we check required fields - checking specific fields we know are required
        const priceValid = priceWholeInput.value.trim() !== '';
        const nameValid = document.getElementById('name').value.trim() !== '';

        // If either required field is empty, disable button and hide warnings
        if (!priceValid || !nameValid) {
            nextButton.disabled = true;
            amountWarning.classList.add('hidden');
            differenceAmountWarning.classList.add('hidden');
            return;
        }

        // Now check the amount comparisons only if we have a starting amount
        if (startingAmountWholeInput.value.trim() !== '') {
            if (startingAmountWhole >= priceWhole) {
                nextButton.disabled = true;
                amountWarning.classList.remove('hidden');
                differenceAmountWarning.classList.add('hidden');
            } else if ((priceWhole - startingAmountWhole) < 100) {
                nextButton.disabled = true;
                differenceAmountWarning.classList.remove('hidden');
                amountWarning.classList.add('hidden');
            } else {
                nextButton.disabled = false;
                amountWarning.classList.add('hidden');
                differenceAmountWarning.classList.add('hidden');
            }
        } else {
            // If no starting amount, just enable the button since required fields are valid
            nextButton.disabled = false;
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

    // Initialize the formatted price on page load
    const priceInput = document.getElementById('price_whole');
    if (priceInput && priceInput.value) {
        updateFormattedPrice(priceInput.value, 'formatted_price');
    }


    window.clearFormAndSwitchCurrency = async function(currency) {
        try {
            // First, make request to clear the form
            const response = await fetch('/create-piggy-bank/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            // Then switch currency
            window.location.href = `/currency/switch/${currency}`;

        } catch (error) {
            console.error('Error:', error);
            // Optionally handle the error, maybe show a message to user
        }
    };


    // Dynamic image preview loading
    const linkInput = document.getElementById('link');
    const currentImage = document.getElementById('preview-image-current');
    const nextImage = document.getElementById('preview-image-next');
    const loadingElement = document.getElementById('preview-loading');
    const errorElement = document.getElementById('preview-error');
    let debounceTimer;

// Add debugging to help us understand what's happening
//     console.log('Elements found:', {
//         linkInput,
//         currentImage,
//         nextImage,
//         loadingElement,
//         errorElement
//     });
//
//     console.log('Link Input:', linkInput);
//     console.log('Loading Element:', loadingElement);
//     console.log('Error Element:', errorElement);

    // Helper functions to manage UI states
    function showLoading() {
        // console.log('Showing loading state');
        if (loadingElement) loadingElement.classList.remove('opacity-0', 'invisible');
        if (currentImage) currentImage.classList.add('opacity-50');
        if (nextImage) nextImage.classList.add('opacity-0');
        if (errorElement) errorElement.classList.add('opacity-0', 'invisible');
    }

    function hideLoading() {
        // console.log('Hiding loading state');
        if (loadingElement) loadingElement.classList.add('opacity-0', 'invisible');
        if (currentImage) currentImage.classList.remove('opacity-50');
    }

    function showError() {
        // console.log('Showing error state');
        if (errorElement) errorElement.classList.remove('opacity-0', 'invisible');
        if (currentImage) currentImage.classList.add('opacity-50');
        if (nextImage) nextImage.classList.add('opacity-0');
    }

    function hideError() {
        // console.log('Hiding error state');
        if (errorElement) errorElement.classList.add('opacity-0', 'invisible');
        if (currentImage) currentImage.classList.remove('opacity-50');
    }

    function updatePreviewImage(newImageUrl) {
        // Create a new image object to preload
        const tempImage = new Image();

        tempImage.onload = function() {
            // Once new image is loaded, perform the transition
            nextImage.src = newImageUrl;

            // Wait a tiny bit for the new image to be ready in the DOM
            setTimeout(() => {
                // Fade out current image, fade in next image
                currentImage.classList.add('opacity-0');
                nextImage.classList.remove('opacity-0');

                // After transition completes, swap the images and reset
                setTimeout(() => {
                    // Swap the sources
                    currentImage.src = newImageUrl;
                    currentImage.classList.remove('opacity-0');
                    nextImage.classList.add('opacity-0');
                    hideLoading();
                    hideError();
                }, 500); // This should match the duration in the CSS transition
            }, 50);
        };

        tempImage.onerror = function() {
            showError();
            updatePreviewImage('/images/default_piggy_bank.png');
            hideLoading();
        };

        // Start loading the new image
        tempImage.src = newImageUrl;
    }


    if (linkInput) {
        linkInput.addEventListener('input', function() {
            // console.log('Input event triggered');
            clearTimeout(debounceTimer);

            const url = this.value.trim();
            // console.log('URL:', url);

            if (!url) {
                // console.log('Empty URL, resetting to default');
                hideLoading();
                hideError();
                currentImage.src = '/images/default_piggy_bank.png';
                nextImage.src = '/images/default_piggy_bank.png';
                return;
            }

            showLoading();

            debounceTimer = setTimeout(() => {
                // console.log('Making fetch request');
                fetch('/create-piggy-bank/api/link-preview', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ url: url })
                })
                    .then(response => {
                        // console.log('Response received:', response);
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        // console.log('Preview data:', data);
                        if (data.preview && data.preview.image) {
                            // Use the new function instead of directly setting src
                            updatePreviewImage(data.preview.image);
                        } else {
                            throw new Error('No preview image available');
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        showError();
                        updatePreviewImage('/images/default_piggy_bank.png');
                    })
            }, 500);
        });
    } else {
        console.error('Could not find link input element');
    }

});
