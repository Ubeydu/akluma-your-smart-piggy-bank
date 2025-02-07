// Status transition configuration
const STATUS_TRANSITIONS = {
    'active': {
        'done': {
            type: 'A',
            endpoint: null,
            message: window.piggyBankTranslations['goal_completed']
        },
        'paused': {
            type: 'PWUC',
            endpoint: '/piggy-banks/{id}/pause',
            confirmMessage: window.piggyBankTranslations['confirm_pause'],
            successMessage: window.piggyBankTranslations['piggy_bank_paused_info']
        },
        'cancelled': {
            type: 'PWUC',
            endpoint: '/piggy-banks/{id}/update-status-cancelled',
            method: 'PATCH',
            confirmMessage: window.piggyBankTranslations['confirm_cancel'],
            successMessage: window.piggyBankTranslations['piggy_bank_cancelled']
        }
    },
    'paused': {
        'active': {
            type: 'PWUC',
            endpoint: '/piggy-banks/{id}/resume',
            confirmMessage: window.piggyBankTranslations['confirm_resume'],
            successMessage: window.piggyBankTranslations['piggy_bank_resumed_schedule_not_updated_info']
        },
        'done': {
            type: 'NPM',
        },
        'cancelled': {
            type: 'PWUC',
            endpoint: '/piggy-banks/{id}/update-status-cancelled',
            method: 'PATCH',
            confirmMessage: window.piggyBankTranslations['confirm_cancel_paused'],
            successMessage: window.piggyBankTranslations['piggy_bank_cancelled']
        }
    },
    'done': {
        'active': {
            type: 'NPM',
        },
        'paused': {
            type: 'NPM',
        },
        'cancelled': {
            type: 'NPM',
        }
    },
    'cancelled': {
        'active': {
            type: 'NPM',
        },
        'paused': {
            type: 'NPM',
        },
        'done': {
            type: 'NPM',
        }
    }
};

console.log('Full STATUS_TRANSITIONS:', STATUS_TRANSITIONS);


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

        // In handleCheckboxChange, after getting the response:
        console.log("Checkbox change response:", {
            newStatus: data.piggy_bank_status,
            selectElement: document.getElementById(`piggy-bank-status-${piggyBankId}`),
            currentSelectValue: document.getElementById(`piggy-bank-status-${piggyBankId}`)?.value
        });

        console.log("Piggy Bank Status Returned:", data.piggy_bank_status);

        if (data.piggy_bank_status === 'done') {
            showFlashMessage(window.piggyBankTranslations['goal_completed'] || 'Congratulations! You have successfully completed your savings goal.');
            updateSelectAfterStatusChange(piggyBankId, 'done');
        }

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


    // // Add status handler
    // const statusSelects = document.querySelectorAll('select[id^="piggy-bank-status-"]');
    //
    // statusSelects.forEach(select => {
    //     const piggyBankId = select.id.replace('piggy-bank-status-', '');
    //     const initialStatus = select.dataset.initialStatus;
    //
    //     select.addEventListener('change', async function() {
    //         const newStatus = this.value;
    //         console.log("Status change triggered", {
    //             piggyBankId,
    //             newStatus,
    //             initialStatus
    //         });
    //
    //         if (newStatus === 'paused') {
    //             console.log("Attempting to pause piggy bank", piggyBankId);
    //             await updatePiggyBankStatus(piggyBankId, `/piggy-banks/${piggyBankId}/pause`, initialStatus);
    //         } else if (newStatus === 'active') {
    //             console.log("Attempting to resume piggy bank", piggyBankId);
    //             await updatePiggyBankStatus(piggyBankId, `/piggy-banks/${piggyBankId}/resume`, initialStatus);
    //         }
    //     });
    // });


    const statusSelects = document.querySelectorAll('select[id^="piggy-bank-status-"]');

    statusSelects.forEach(select => {
        const piggyBankId = select.id.replace('piggy-bank-status-', '');
        const initialStatus = select.dataset.initialStatus;

        // Disable invalid options based on current status
        function updateSelectOptions() {
            const currentStatus = select.dataset.initialStatus;
            Array.from(select.options).forEach(option => {
                const targetStatus = option.value;
                const transition = STATUS_TRANSITIONS[currentStatus]?.[targetStatus];

                // Disable if transition doesn't exist or is not possible manually
                option.disabled = !transition || transition.type === 'NPM' || transition.type === 'A';
            });
        }

        // Initial setup of options
        updateSelectOptions();

        select.addEventListener('change', async function() {
            const newStatus = this.value;
            const currentStatus = this.dataset.initialStatus;
            const transition = STATUS_TRANSITIONS[currentStatus]?.[newStatus];

            console.log('Current transition config:', transition);

            if (!transition) {
                // Reset to initial status if transition is not defined
                this.value = currentStatus;
                return;
            }

            if (transition.type === 'PWUC') {


                console.log('Attempting PWUC transition:', {
                    from: currentStatus,
                    to: newStatus,
                    endpoint: transition.endpoint,
                    piggyBankId: piggyBankId
                });


                // Show confirmation dialog
                const confirmed = window.confirm(transition.confirmMessage);
                if (!confirmed) {
                    this.value = currentStatus;
                    return;
                }

                try {
                    // Replace {id} in endpoint with actual ID
                    const endpoint = transition.endpoint.replace('{id}', piggyBankId);
                    console.log('Making request to endpoint:', endpoint);

                    await updatePiggyBankStatus(piggyBankId, endpoint, currentStatus, transition.method || 'PATCH');

                    // If successful, update the data-initial-status
                    this.dataset.initialStatus = newStatus;
                    updateSelectOptions();

                    console.log('Request completed');
                } catch (error) {
                    console.error('Transition error:', error);
                    this.value = currentStatus;
                }
            } else if (transition.type === 'NPM') {
                // Show error message and reset
                showFlashMessage(transition.message);
                this.value = currentStatus;
            }
        });
    });

    const checkboxes = document.querySelectorAll('input[data-saving-id]');


    // Handle initial checkbox state based on piggy bank status
    const container = document.querySelector('[data-piggy-bank-status]');
    if (container) {
        const status = container.dataset.piggyBankStatus;
        checkboxes.forEach(checkbox => {
            checkbox.disabled = ['paused', 'cancelled', 'done'].includes(status);
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
                checkbox.disabled = ['paused', 'cancelled', 'done'].includes(e.detail.status);
            });
        }
    });

});


async function updatePiggyBankStatus(piggyBankId, url, initialStatus, method = 'PATCH') {
    const startTime = performance.now();
    console.log("updatePiggyBankStatus called with:", {
        startTime,
        piggyBankId,
        url,
        initialStatus,
        method
    });

    try {
        // Create form data for POST requests
        const isPost = method.toUpperCase() === 'POST';
        let fetchOptions = {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
        };

        // For POST requests, send as form data
        if (isPost) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            fetchOptions.body = formData;
        } else {
            // For PATCH and other requests, use JSON
            fetchOptions.headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, fetchOptions);

        console.log("Response status:", response.status);

        // Get response text first to help with debugging
        const responseText = await response.text();
        console.log("Raw response:", responseText);

        // Try to parse the response as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error("Failed to parse response as JSON:", e);
            throw new Error(responseText.includes('<!DOCTYPE html>') ?
                "Server error - Request not processed correctly" :
                "Server returned invalid JSON");
        }

        if (!response.ok) {
            throw new Error(data.error || 'Server returned an error');
        }

        // Update UI elements
        await updateUIElements(piggyBankId, data);

        // Update schedule if needed
        await updateSchedule(piggyBankId);

        return true;

    } catch (error) {
        console.error('Error in updatePiggyBankStatus:', error);
        handleError(piggyBankId, initialStatus, error);
        return false;
    }
}

// Helper function to update UI elements
async function updateUIElements(piggyBankId, data) {
    // Update status text if it exists
    const statusTextElement = document.getElementById(`status-text-${piggyBankId}`);
    if (statusTextElement && data.status) {
        const translatedStatus = window.piggyBankTranslations[data.status.toLowerCase()] || data.status;
        statusTextElement.textContent = translatedStatus.charAt(0).toUpperCase() + translatedStatus.slice(1);
    }

    // Show flash message if there's a message
    if (data.message) {
        showFlashMessage(data.message);
    }

    // Update select element's disabled state
    updateSelectAfterStatusChange(piggyBankId, data.status);
}

// Helper function to update schedule
async function updateSchedule(piggyBankId) {
    try {
        const response = await fetch(`/piggy-banks/${piggyBankId}/schedule`);
        const html = await response.text();

        const scheduleContainer = document.getElementById('schedule-container');
        if (scheduleContainer) {
            scheduleContainer.innerHTML = html;
            reinitializeCheckboxes();
        }
    } catch (error) {
        console.error('Error updating schedule:', error);
    }
}

// Helper function to reinitialize checkboxes
function reinitializeCheckboxes() {
    const newCheckboxes = document.querySelectorAll('input[data-saving-id]');
    const container = document.querySelector('[data-piggy-bank-status]');

    if (container) {
        const status = container.dataset.piggyBankStatus;
        newCheckboxes.forEach(checkbox => {
            checkbox.disabled = ['paused', 'cancelled', 'done'].includes(status);
            checkbox.addEventListener('change', async function () {
                await handleCheckboxChange(this);
            });
        });
    }
}

// Helper function to handle errors
function handleError(piggyBankId, initialStatus, error) {
    alert('Failed to update piggy bank status.');

    // Reset select to initial status
    const select = document.getElementById(`piggy-bank-status-${piggyBankId}`);
    if (select) {
        select.value = initialStatus;
    }
}

function updateSelectAfterStatusChange(piggyBankId, newStatus) {
    console.log('updateSelectAfterStatusChange called with:', {
        piggyBankId,
        newStatus,
    });

    const selectElement = document.getElementById(`piggy-bank-status-${piggyBankId}`);
    console.log('Select element found:', {
        element: selectElement,
        currentDisabledState: selectElement?.disabled,
        currentClasses: selectElement?.classList.toString()
    });

    if (selectElement) {
        // Update the select value and dataset
        selectElement.value = newStatus;
        selectElement.dataset.initialStatus = newStatus;

        console.log('Should disable?', {
            newStatus,
            shouldDisable: ['done', 'cancelled'].includes(newStatus)
        });

        // Disable select and add visual feedback if status is done or cancelled
        if (['done', 'cancelled'].includes(newStatus)) {
            selectElement.disabled = true;
            selectElement.classList.add('opacity-50', 'cursor-not-allowed');
            console.log('After applying disabled state:', {
                isDisabled: selectElement.disabled,
                classes: selectElement.classList.toString()
            });
        } else {
            selectElement.disabled = false;
            selectElement.classList.remove('opacity-50', 'cursor-not-allowed');
            console.log('After removing disabled state:', {
                isDisabled: selectElement.disabled,
                classes: selectElement.classList.toString()
            });
        }

        // Update options' disabled state based on STATUS_TRANSITIONS
        Array.from(selectElement.options).forEach(option => {
            const targetStatus = option.value;
            const transition = STATUS_TRANSITIONS[newStatus]?.[targetStatus];
            option.disabled = !transition || transition.type === 'NPM' || transition.type === 'A';
        });
    }

    // Update status text
    const statusTextElement = document.getElementById(`status-text-${piggyBankId}`);
    if (statusTextElement) {
        const translatedStatus = window.piggyBankTranslations[newStatus.toLowerCase()] || newStatus;
        statusTextElement.textContent = translatedStatus.charAt(0).toUpperCase() + translatedStatus.slice(1);
    }

    // Update schedule table and checkboxes
    const scheduleContainer = document.getElementById('schedule-container');
    if (scheduleContainer) {
        const scheduleTable = scheduleContainer.querySelector('table');
        if (scheduleTable) {
            if (['done', 'paused', 'cancelled'].includes(newStatus)) {
                scheduleTable.classList.add('opacity-50');
                // Disable all checkboxes
                const checkboxes = scheduleContainer.querySelectorAll('input[data-saving-id]');
                checkboxes.forEach(checkbox => {
                    checkbox.disabled = true;
                });
            }
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


