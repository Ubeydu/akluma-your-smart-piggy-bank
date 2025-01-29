// resources/js/scheduled-savings.js


document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[data-saving-id]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            const savingId = this.dataset.savingId;
            const piggyBankId = this.dataset.piggyBankId;
            const amount = parseFloat(this.dataset.amount);
            const newStatus = this.checked ? 'saved' : 'pending';

            try {
                const response = await fetch(`/scheduled-savings/${savingId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        piggy_bank_id: piggyBankId,
                        amount: amount
                    })
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error('Failed to update status');
                }

                console.log("Piggy Bank Status Returned:", data.piggy_bank_status);

                // Format number before updating UI using the correct currency & locale
                function formatCurrency(value, currency, locale) {
                    return new Intl.NumberFormat(locale, {
                        style: 'currency',
                        currency: currency
                    }).format(value);
                }

                // Update balance with formatted value
                const balanceElement = document.getElementById(`current-balance-${piggyBankId}`);
                if (balanceElement) {
                    const currency = balanceElement.dataset.currency;
                    const locale = balanceElement.dataset.locale;
                    balanceElement.textContent = formatCurrency(data.new_balance, currency, locale);
                }

                // Update remaining amount with formatted value
                const remainingAmountElement = document.getElementById(`remaining-amount-${piggyBankId}`);
                if (remainingAmountElement) {
                    const currency = remainingAmountElement.dataset.currency;
                    const locale = remainingAmountElement.dataset.locale;
                    remainingAmountElement.textContent = formatCurrency(data.remaining_amount, currency, locale);
                }

                // Update Piggy Bank Status in UI (with translation)
                const piggyBankStatusElement = document.getElementById(`piggy-bank-status-${piggyBankId}`);
                if (piggyBankStatusElement) {
                    const translatedStatus = window.piggyBankTranslations[data.piggy_bank_status] || data.piggy_bank_status;
                    piggyBankStatusElement.textContent = translatedStatus;
                }

                // Update status text in UI
                const statusCell = this.closest('tr').querySelector('td:last-child');
                statusCell.textContent = data.translated_status;


                // If piggy bank status becomes "done", show a flash message dynamically
                if (data.piggy_bank_status === 'done') {
                    showFlashMessage(window.piggyBankTranslations['goal_completed'] || 'Congratulations! You have successfully completed your savings goal.');
                }

            } catch (error) {
                console.error('Error:', error);
                this.checked = !this.checked;
                alert('Failed to update saving status. Please try again.');
            }
        });
    });
});


/**
 * Function to display a flash message dynamically
 */
function showFlashMessage(message) {
    // Remove existing flash messages
    const existingFlash = document.getElementById('flash-message');
    if (existingFlash) {
        existingFlash.remove();
    }

    // Create the flash message container
    const flashMessageContainer = document.createElement('div');
    flashMessageContainer.id = "flash-message";
    flashMessageContainer.className = "bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg";
    flashMessageContainer.style.position = "fixed";
    flashMessageContainer.style.top = "20px";
    flashMessageContainer.style.left = "50%";
    flashMessageContainer.style.transform = "translateX(-50%)";
    flashMessageContainer.style.width = "calc(min(384px, 90vw))";
    flashMessageContainer.style.zIndex = "50";

    flashMessageContainer.innerHTML = `
        <strong class="font-bold">${window.piggyBankTranslations['success'] || 'Success!'}</strong>
        <span class="block sm:inline">${message}</span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
            &times;
        </button>
    `;

    // Insert message at the top of the page
    document.body.prepend(flashMessageContainer);


    setTimeout(() => {
        if (flashMessageContainer) {
            flashMessageContainer.style.opacity = "0";
            setTimeout(() => flashMessageContainer.remove(), 500);
        }
    }, 5000);
}


// function showFlashMessage(message) {
//     // Remove existing flash messages
//     const existingFlash = document.getElementById('flash-message');
//     if (existingFlash) {
//         existingFlash.remove();
//     }
//
//     // Create the flash message container
//     const flashMessageContainer = document.createElement('div');
//     flashMessageContainer.id = "flash-message";
//     flashMessageContainer.className = "w-96 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 max-w-md shadow-lg";
//     flashMessageContainer.setAttribute("role", "alert");
//
//     flashMessageContainer.innerHTML = `
//         <strong class="font-bold">${window.piggyBankTranslations['success'] || 'Success!'}</strong>
//         <span class="block sm:inline">${message}</span>
//         <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
//             &times;
//         </button>
//     `;
//
//     // Insert message at the top of the page
//     document.body.prepend(flashMessageContainer);
//
//     // Auto-hide after 5 seconds
//     setTimeout(() => {
//         if (flashMessageContainer) {
//             flashMessageContainer.style.opacity = "0";
//             setTimeout(() => flashMessageContainer.remove(), 500);
//         }
//     }, 5000);
// }


