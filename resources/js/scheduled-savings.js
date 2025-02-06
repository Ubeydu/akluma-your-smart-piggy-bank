async function handleCheckboxChange(checkbox) {
    const savingId = checkbox.dataset.savingId;
    const piggyBankId = checkbox.dataset.piggyBankId;
    const amount = parseFloat(checkbox.dataset.amount);
    const newStatus = checkbox.checked ? 'saved' : 'pending';

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
            piggyBankStatusElement.value = data.piggy_bank_status;
            piggyBankStatusElement.dataset.initialStatus = data.piggy_bank_status;
        }

        // Update status text in UI
        const statusCell = checkbox.closest('tr').querySelector('td:last-child');
        statusCell.textContent = data.translated_status;

        // If piggy bank status becomes "done", show a flash message dynamically
        if (data.piggy_bank_status === 'done') {
            showFlashMessage(window.piggyBankTranslations['goal_completed'] || 'Congratulations! You have successfully completed your savings goal.');
        }

    } catch (error) {
        console.error('Error:', error);
        checkbox.checked = !checkbox.checked;
        alert('Failed to update saving status. Please try again.');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('Scheduled Savings JS Loaded');

    console.log('Translation check:', {
        'paused_message': window.piggyBankTranslations['paused_message'],
        'piggy_bank_paused_message': window.piggyBankTranslations['piggy_bank_paused_message']
    });


    // Add status handler
    const statusSelects = document.querySelectorAll('select[id^="piggy-bank-status-"]');

    statusSelects.forEach(select => {
        const piggyBankId = select.id.replace('piggy-bank-status-', '');
        const initialStatus = select.dataset.initialStatus;

        select.addEventListener('change', async function() {
            const newStatus = this.value;
            console.log("Status change triggered", {
                piggyBankId,
                newStatus,
                initialStatus
            });

            if (newStatus === 'paused') {
                console.log("Attempting to pause piggy bank", piggyBankId);
                await updatePiggyBankStatus(piggyBankId, `/piggy-banks/${piggyBankId}/pause`, initialStatus);
            } else if (newStatus === 'active') {
                console.log("Attempting to resume piggy bank", piggyBankId);
                await updatePiggyBankStatus(piggyBankId, `/piggy-banks/${piggyBankId}/resume`, initialStatus);
            }
        });
    });

    const checkboxes = document.querySelectorAll('input[data-saving-id]');


    // Handle initial checkbox state based on piggy bank status
    const container = document.querySelector('[data-piggy-bank-status]');
    if (container) {
        const status = container.dataset.piggyBankStatus;
        checkboxes.forEach(checkbox => {
            checkbox.disabled = status === 'paused';


        });
    }






    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', async function () {
            await handleCheckboxChange(this);
        });
    });


    // Update checkbox state when piggy bank status changes
    document.addEventListener('piggyBankStatusChanged', function(e) {
        if (container) {
            container.dataset.piggyBankStatus = e.detail.status;
            checkboxes.forEach(checkbox => {
                checkbox.disabled = e.detail.status === 'paused';
            });
        }
    });

});


async function updatePiggyBankStatus(piggyBankId, url, initialStatus) {
    const startTime = performance.now();
    console.log("updatePiggyBankStatus called with:", {
        startTime,
        piggyBankId,
        url,
        initialStatus
    });

    try {
        console.log("Making PATCH request to:", url);

        let response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        });

        const endTime = performance.now();
        console.log("Fetch completed", {
            duration: endTime - startTime,
            status: response.status
        });

        console.log("Response received:", response.status);

        let data = await response.json();

        console.log("Response data:", data);

        if (response.ok) {
            // Update status text
            const statusTextElement = document.getElementById(`status-text-${piggyBankId}`);
            if (statusTextElement) {
                statusTextElement.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
            }
            showFlashMessage(data.message);


            // Fetch updated schedule using the new route
            fetch(`/piggy-banks/${piggyBankId}/schedule`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('schedule-container').innerHTML = html;


                    // Reinitialize checkboxes after updating the schedule
                    const newCheckboxes = document.querySelectorAll('input[data-saving-id]');
                    const container = document.querySelector('[data-piggy-bank-status]');
                    if (container) {
                        const status = container.dataset.piggyBankStatus;
                        newCheckboxes.forEach(checkbox => {
                            checkbox.disabled = status === 'paused';
                            // Reattach the change event listener
                            checkbox.addEventListener('change', async function () {
                                await handleCheckboxChange(this);
                            });
                        });
                    }

                })



        } else {
            alert(data.error || 'Something went wrong.');
            // Reset select to initial status
            const select = document.getElementById(`piggy-bank-status-${piggyBankId}`);
            if (select) {
                select.value = initialStatus;
            }
        }
    } catch (error) {
        console.error('Error in updatePiggyBankStatus:', error);
        alert('Failed to update piggy bank status.');
        // Reset select to initial status
        const select = document.getElementById(`piggy-bank-status-${piggyBankId}`);
        if (select) {
            select.value = initialStatus;
        }
    }
}


/**
 * Function to display a flash message dynamically
 */
function showFlashMessage(message) {
    // Remove existing flash messages
    const existingFlash = document.getElementById('flash-message');
    if (existingFlash) {
        existingFlash.remove();
    }

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
        <strong class="font-bold">${window.piggyBankTranslations['info']}</strong>
        <span class="block sm:inline">${message}</span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
            &times;
        </button>
    `;

    document.body.prepend(flashMessageContainer);

    setTimeout(() => {
        if (flashMessageContainer) {
            flashMessageContainer.style.opacity = "0";
            setTimeout(() => flashMessageContainer.remove(), 500);
        }
    }, 5000);
}


