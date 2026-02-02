import { Ziggy } from './ziggy';
import { route } from 'ziggy-js';

// Add this function at the top of your file for better debugging
// function debugLog(label, data) {
//     console.log(`DEBUG [${label}]:`, data);
// }

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
            endpoint: 'localized.piggy-banks.pause',
            confirmMessage: window.piggyBankTranslations['confirm_pause'],
            successMessage: window.piggyBankTranslations['piggy_bank_paused_info']
        },
        'cancelled': {
            type: 'PWUC',
            endpoint: 'localized.piggy-banks.update-status-cancelled',
            method: 'PATCH',
            confirmMessage: window.piggyBankTranslations['confirm_cancel'],
            successMessage: window.piggyBankTranslations['piggy_bank_cancelled']
        }
    },
    'paused': {
        'active': {
            type: 'PWUC',
            endpoint: 'localized.piggy-banks.resume',
            confirmMessage: window.piggyBankTranslations['confirm_resume'],
            successMessage: window.piggyBankTranslations['piggy_bank_resumed_schedule_not_updated_info']
        },
        'done': {
            type: 'NPM',
        },
        'cancelled': {
            type: 'PWUC',
            endpoint: 'localized.piggy-banks.update-status-cancelled',
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

// console.log('Full STATUS_TRANSITIONS:', STATUS_TRANSITIONS);

function getCurrentLocale() {
    return document.documentElement.lang || 'en';
}

function buildRouteUrl(routeName, params = {}) {
    // Get the route from Ziggy routes
    const routeConfig = Ziggy.routes[routeName];
    if (!routeConfig) {
        console.error('Route not found:', routeName);
        return null;
    }

    let url = routeConfig.uri;

    // Replace parameters in the URL
    Object.keys(params).forEach(param => {
        url = url.replace(`{${param}}`, params[param]);
    });

    // Use current origin instead of Ziggy.url to avoid CORS issues
    const baseUrl = window.location.origin;
    if (!url.startsWith('http')) {
        url = baseUrl + '/' + url;
    }

    return url;
}


async function handleCheckboxChange(checkbox) {
    const savingId = checkbox.dataset.savingId;
    const piggyBankId = checkbox.dataset.piggyBankId;
    const newStatus = checkbox.checked ? 'saved' : 'pending';

    if (checkbox.checked) {
        // Saving: show confirmation dialog instead of saving directly
        checkbox.checked = false; // Reset until confirmed

        const scheduledAmount = parseFloat(checkbox.dataset.scheduledAmount);
        const currencyHasDecimals = checkbox.dataset.currencyHasDecimals === '1';

        // Dispatch event to open the save dialog
        window.dispatchEvent(new CustomEvent('open-save-dialog', {
            detail: {
                savingId,
                piggyBankId,
                scheduledAmount,
                currencyHasDecimals
            }
        }));
        return;
    }

    // Undoing: proceed with immediate API call
    const amount = parseFloat(checkbox.dataset.amount);
    await performScheduledSave(checkbox, savingId, piggyBankId, 'pending', amount);
}

// Perform the actual save API call
// checkbox can be null when called from modal - will be found by savingId
async function performScheduledSave(checkbox, savingId, piggyBankId, newStatus, amount) {
    // Find checkbox if not provided (when called from modal)
    if (!checkbox) {
        checkbox = document.querySelector(`.scheduled-saving-checkbox[data-saving-id="${savingId}"]`);
    }

    try {
        const locale = getCurrentLocale();
        const response = await fetch(`/${locale}/scheduled-savings/${savingId}`, {
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
        // console.log('Fetch response object:', response);

        let data;
        try {
            data = await response.json();
            // console.log('Parsed JSON:', data);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            const text = await response.text();
            console.error('Raw response text:', text);
            throw new Error('Response not valid JSON');
        }

        if (!data || typeof data !== 'object') {
            throw new Error('Invalid server response');
        }
        if (!['saved', 'pending', 'active', 'done'].includes(data.status)) {
            throw new Error(data.message || 'Failed to update saving status.');
        }

        // In handleCheckboxChange, after getting the response:
        // console.log("Checkbox change response:", {
        //     newStatus: data.piggy_bank_status,
        //     selectElement: document.getElementById(`piggy-bank-status-${piggyBankId}`),
        //     currentSelectValue: document.getElementById(`piggy-bank-status-${piggyBankId}`)?.value
        // });

        // console.log("Piggy Bank Status Returned:", data.piggy_bank_status);

        // Show the appropriate message from the response
        if (data.message) {
            // Use your existing showFlashMessage function
            showFlashMessage(data.message, 'info');
        }

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

        // DEBUG: Check manual money section disabling
        // console.log('About to check if manual section should be disabled, status:', data.piggy_bank_status);
        if (['done', 'paused', 'cancelled'].includes(data.piggy_bank_status)) {
            // console.log('Status requires disabling manual section');
            const manualSection = document.getElementById('manual-money-section');
            // console.log('Manual section found:', !!manualSection);
            setManualMoneySectionDisabled(true);
            // console.log('setManualMoneySectionDisabled(true) called');
        } else {
            // console.log('Status allows manual section to be enabled');
            setManualMoneySectionDisabled(false);
        }

        // Update status text in UI
        const statusCell = checkbox.closest('tr').querySelector('td:last-child');
        statusCell.textContent = data.translated_status;

        // DEBUG: Check what happens when status becomes done
        // console.log('Piggy bank status from response:', data.piggy_bank_status);
        if (data.piggy_bank_status === 'done') {
            // console.log('Status is done - about to disable checkboxes');
            const checkboxes = document.querySelectorAll('input[data-saving-id]');
            // console.log('Found checkboxes before disabling:', checkboxes.length);
            checkboxes.forEach((cb, index) => {
                // console.log(`Checkbox ${index} - disabled before: ${cb.disabled}`);
                cb.disabled = true;
                // console.log(`Checkbox ${index} - disabled after: ${cb.disabled}`);
            });
        }
        disableScheduledSavingCheckboxesIfDone(piggyBankId);

        // If piggy bank status becomes "done", show a flash message dynamically
        if (data.piggy_bank_status === 'done') {
            showFlashMessage(window.piggyBankTranslations['goal_completed'] || 'Congratulations! You have successfully completed your savings goal.');
        }

        // Add cute scale-down + fade animation to show item is moving
        const row = checkbox.closest('tr');
        if (row) {
            row.style.transition = 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s ease';
            row.style.transform = 'scale(0.9)';
            row.style.opacity = '0.3';
        }

        // Wait for animation to complete, then reload
        setTimeout(() => {
            try {
                reloadFinancialSummary(piggyBankId);
                reloadSchedulePartial(piggyBankId);

            } catch (err) {
                console.error('Error reloading financial summary:', err);
            }
        }, 400);

    } catch (error) {
        console.error('Error:', error);
        checkbox.checked = !checkbox.checked;
        alert('Failed to update saving status. Please try again.');
    }
}

// Expose globally for Alpine component access
window.performScheduledSave = performScheduledSave;

document.addEventListener('DOMContentLoaded', function () {
    // Force cache refresh indicator
    // debugLog('Script loaded at', new Date().toISOString());

    // Debug Ziggy routes
    // debugLog('Ziggy available?', typeof Ziggy !== 'undefined');
    // debugLog('buildRouteUrl function available?', typeof buildRouteUrl !== 'undefined');

    // if (typeof Ziggy !== 'undefined') {
    //     debugLog('Available routes', {
    //         routeCount: Object.keys(Ziggy.routes).length,
    //         sampleRoutes: Object.keys(Ziggy.routes).slice(0, 5),
    //         hasLocalizedRoutes: Object.keys(Ziggy.routes).some(r => r.startsWith('localized.'))
    //     });
    // }

    // Check if our specific routes exist (with locale suffix)
    const currentLocale = getCurrentLocale();
    const routesToCheck = [
        `localized.piggy-banks.pause.${currentLocale}`,
        `localized.piggy-banks.resume.${currentLocale}`,
        `localized.piggy-banks.update-status-cancelled.${currentLocale}`
    ];

    // routesToCheck.forEach(routeName => {
    //     if (typeof Ziggy !== 'undefined') {
    //         debugLog(`Route check: ${routeName}`, {
    //             exists: Ziggy.routes[routeName] !== undefined,
    //             details: Ziggy.routes[routeName]
    //         });
    //     }
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
            const piggyBankId = this.id.replace('piggy-bank-status-', '');

            // debugLog('Status Change Attempted', {
            //     element: this,
            //     piggyBankId: piggyBankId,
            //     from: currentStatus,
            //     to: newStatus
            // });

            // Skip if no change
            if (newStatus === currentStatus) {
                // debugLog('No status change', { currentStatus, newStatus });
                return;
            }

            // Get transition configuration
            const transition = STATUS_TRANSITIONS[currentStatus]?.[newStatus];
            // debugLog('Transition Config', transition);

            if (!transition) {
                // debugLog('No transition found', { currentStatus, newStatus });
                this.value = currentStatus;
                return;
            }

            if (transition.type === 'PWUC') {
                // debugLog('PWUC transition', {
                //     from: currentStatus,
                //     to: newStatus,
                //     endpoint: transition.endpoint,
                //     piggyBankId: piggyBankId
                // });

                // Reset select to current status while waiting for confirmation
                this.value = currentStatus;

                // Find the Alpine container and trigger dialog
                const dialogContainer = this.closest('[x-data]');
                // debugLog('Dialog container', dialogContainer);

                if (dialogContainer) {
                    // Store current select element and status info
                    const selectElement = this;
                    const targetStatus = newStatus;

                    // Update Alpine.js state directly on the element
                    dialogContainer._x_dataStack[0].showConfirmCancel = true;
                    dialogContainer._x_dataStack[0].statusChangeMessage = transition.confirmMessage;
                    dialogContainer._x_dataStack[0].statusChangeAction = async function() {
                        try {
                            const locale = getCurrentLocale();
                            // debugLog('Current locale', locale);

                            // Log the route name and parameters
                            // debugLog('Route info', {
                            //     name: transition.endpoint,
                            //     params: {
                            //         locale: locale,
                            //         piggy_id: piggyBankId
                            //     }
                            // });

                            // Build the localized route name
                            const localizedRouteName = `${transition.endpoint}.${locale}`;
                            // debugLog('Looking for route', localizedRouteName);

                            // Check if route exists in Ziggy
                            if (!Ziggy.routes[localizedRouteName]) {
                                // debugLog('Route not found in Ziggy', {
                                //     routeName: localizedRouteName,
                                //     availableRoutes: Object.keys(Ziggy.routes).filter(r => r.includes('piggy-banks'))
                                // });
                                throw new Error(`Route "${localizedRouteName}" not found in Ziggy routes`);
                            }

                            // Use buildRouteUrl to generate the endpoint
                            const endpoint = buildRouteUrl(localizedRouteName, {
                                locale: locale,
                                piggy_id: piggyBankId
                            });

                            if (!endpoint) {
                                throw new Error(`Could not generate endpoint for route "${localizedRouteName}"`);
                            }

                            // debugLog('Generated endpoint', endpoint);

                            const result = await updatePiggyBankStatus(
                                piggyBankId,
                                endpoint,
                                targetStatus,
                                transition.method || 'PATCH'
                            );
                            // debugLog('Update result', result);

                            // If successful, update the data-initial-status
                            selectElement.dataset.initialStatus = targetStatus;
                            selectElement.value = targetStatus;
                            updateSelectOptions();

                            if (['done', 'paused', 'cancelled'].includes(targetStatus)) {
                                setManualMoneySectionDisabled(true);
                            } else {
                                setManualMoneySectionDisabled(false);
                            }

                            reloadSchedulePartial(piggyBankId);

                            if (transition.successMessage) {
                                showFlashMessage(transition.successMessage, 'success');
                            }

                            return true;
                        } catch (error) {
                            console.error('Transition error:', error);
                            // debugLog('Error details', {
                            //     message: error.message,
                            //     stack: error.stack
                            // });
                            selectElement.value = currentStatus;
                            showFlashMessage('Failed to update piggy bank status. Please try again.', 'error');
                            return false;
                        }
                    };
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

        // NEW: Disable manual add/remove money section if status is done, paused, cancelled
        setManualMoneySectionDisabled(['done', 'paused', 'cancelled'].includes(status));
    }

    // checkboxes.forEach(checkbox => {
    //     checkbox.addEventListener('change', async function () {
    //         await handleCheckboxChange(this);
    //     });
    // });
    attachCheckboxListeners();


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


async function updatePiggyBankStatus(piggyBankId, endpoint, newStatus, method = 'PATCH') {
    // debugLog('updatePiggyBankStatus called', {
    //     piggyBankId,
    //     endpoint,
    //     newStatus,
    //     method
    // });

    try {
        const response = await fetch(endpoint, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus
            })
        });

        // debugLog('Response status', {
        //     status: response.status,
        //     statusText: response.statusText
        // });

        const data = await response.json();
        // debugLog('Response data', data);

        if (!response.ok) {
            throw new Error(data.message || 'Failed to update status');
        }

        // Update UI elements
        await updateUIElements(piggyBankId, data);

        // Update schedule if needed
        await updateSchedule(piggyBankId, data);

        return data;
    } catch (error) {
        debugLog('Fetch error', {
            message: error.message,
            stack: error.stack
        });
        throw error;
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
async function updateSchedule(piggyBankId, statusData) {
    try {
        // Extract locale and localized slug from current URL
        const segments = window.location.pathname.split('/').filter(s => s.length > 0);
        // console.log('URL segments:', segments);

        const locale = segments[0];
        const localizedSlug = segments[1];
        // console.log('Extracted locale:', locale);
        // console.log('Extracted localizedSlug:', localizedSlug);
        // console.log('PiggyBank ID passed:', piggyBankId);

        // Construct the schedule URL using locale and localized slug
        const scheduleUrl = `/${locale}/${localizedSlug}/${piggyBankId}/schedule`;
        // console.log('Constructed Schedule URL:', scheduleUrl);

        const response = await fetch(scheduleUrl, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });

        // console.log('Fetch response status:', response.status);

        if (!response.ok) {
            console.error('Failed to fetch schedule partial:', response.statusText);
            return;
        }

        const html = await response.text();
        const scheduleContainer = document.getElementById('schedule-container');
        if (scheduleContainer) {
            scheduleContainer.innerHTML = html;
            // console.log('Schedule container updated');

            if (statusData && statusData.scheduleUpdated) {
                // console.log('HIGHLIGHT DEBUG:', { statusData, scheduleUpdated: statusData.scheduleUpdated });
                scheduleContainer.classList.add('highlight-new');
                scheduleContainer.addEventListener('animationend', () => {
                    scheduleContainer.classList.remove('highlight-new');
                    // console.log('Highlight animation ended');
                }, { once: true });
                // console.log('Highlight animation started');
            }

            reinitializeCheckboxes();
            // console.log('Checkboxes reinitialized');
        } else {
            console.warn('Schedule container element not found');
        }
    } catch (error) {
        console.error('Error updating schedule:', error);
    }
}


// Helper function to reinitialize checkboxes
function reinitializeCheckboxes() {
    const newCheckboxes = document.querySelectorAll('.scheduled-saving-checkbox');
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
    // console.log('updateSelectAfterStatusChange called with:', {
    //     piggyBankId,
    //     newStatus,
    // });

    const selectElement = document.getElementById(`piggy-bank-status-${piggyBankId}`);
    // console.log('Select element found:', {
    //     element: selectElement,
    //     currentDisabledState: selectElement?.disabled,
    //     currentClasses: selectElement?.classList.toString()
    // });

    if (selectElement) {
        // Update the select value and dataset
        selectElement.value = newStatus;
        selectElement.dataset.initialStatus = newStatus;

        // console.log('Should disable?', {
        //     newStatus,
        //     shouldDisable: ['done', 'cancelled'].includes(newStatus)
        // });

        // Disable select and add visual feedback if status is done or cancelled
        if (['done', 'cancelled'].includes(newStatus)) {
            selectElement.disabled = true;
            selectElement.classList.add('opacity-50', 'cursor-not-allowed');
            // console.log('After applying disabled state:', {
            //     isDisabled: selectElement.disabled,
            //     classes: selectElement.classList.toString()
            // });
        } else {
            selectElement.disabled = false;
            selectElement.classList.remove('opacity-50', 'cursor-not-allowed');
            // console.log('After removing disabled state:', {
            //     isDisabled: selectElement.disabled,
            //     classes: selectElement.classList.toString()
            // });
        }

        // Update options' disabled state based on STATUS_TRANSITIONS
        Array.from(selectElement.options).forEach(option => {
            const targetStatus = option.value;
            const transition = STATUS_TRANSITIONS[newStatus]?.[targetStatus];
            option.disabled = !transition || transition.type === 'NPM' || transition.type === 'A';
        });
    }

    // Update status text and color
    const statusTextElement = document.getElementById(`status-text-${piggyBankId}`);
    if (statusTextElement) {
        const translatedStatus = window.piggyBankTranslations[newStatus.toLowerCase()] || newStatus;
        statusTextElement.textContent = translatedStatus.charAt(0).toUpperCase() + translatedStatus.slice(1);

        // Get the parent span element that has the background color
        const statusContainer = statusTextElement.closest('span.inline-flex');
        if (statusContainer) {
            // Remove all existing status-related background classes
            statusContainer.classList.remove(
                'bg-green-100', 'text-green-800',
                'bg-yellow-100', 'text-yellow-800',
                'bg-red-100', 'text-red-800',
                'bg-blue-100', 'text-blue-800'
            );

            // Add new class based on status
            switch(newStatus) {
                case 'active':
                    statusContainer.classList.add('bg-green-100', 'text-green-800');
                    break;
                case 'paused':
                    statusContainer.classList.add('bg-yellow-100', 'text-yellow-800');
                    break;
                case 'cancelled':
                    statusContainer.classList.add('bg-red-100', 'text-red-800');
                    break;
                case 'done':
                    statusContainer.classList.add('bg-blue-100', 'text-blue-800');
                    break;
            }
        }
    }

    // Update schedule table and checkboxes
    const scheduleContainer = document.getElementById('schedule-container');
    if (scheduleContainer) {
        const scheduleTable = scheduleContainer.querySelector('table');
        if (scheduleTable) {
            if (['done', 'paused', 'cancelled'].includes(newStatus)) {
                console.log('SHOULD DISABLE CHECKBOXES:', { newStatus, checkboxes: scheduleContainer.querySelectorAll('input[data-saving-id]').length });
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

// Fetch and replace the financial summary for the current piggy bank
function reloadFinancialSummary(piggyBankId) {
    const container = document.getElementById('financial-summary-container');
    if (!container) return;

    // Get the correct localized URL from the data attribute
    const url = container.dataset.financialSummaryUrl;
    if (!url) {
        console.error('No financial summary URL found on container!');
        return;
    }

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.text())
        .then(html => {
            // Create a dummy div to parse the HTML
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const newContent = temp.firstElementChild;
            if (newContent) {
                container.replaceWith(newContent);
            }
        })
        .catch(err => {
            console.error('Error reloading financial summary:', err);
        });
}


document.addEventListener('DOMContentLoaded', function () {
    const manualForm = document.getElementById('manual-money-form');
    if (!manualForm) return;

    manualForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const match = manualForm.action.match(/\/(\d+)\/add-remove-money/);
        if (!match) {
            console.error('Could not extract piggyBankId from form action:', manualForm.action);
            showFlashMessage('Internal error: Could not determine piggy bank. Please reload.', 'error');
            return;
        }
        const piggyBankId = match[1];

        const formData = new FormData(manualForm);

        fetch(manualForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                // console.log('Add/remove money SUCCESS - about to call reloadSchedulePartial');
                // console.log('AJAX response:', data);

                if (!data || typeof data !== 'object') {
                    throw new Error('Invalid server response');
                }

                // If server-side status is not "success", handle as error
                if (data.status !== 'success') {
                    if (data.status === 'error') {
                        showFlashMessage(data.message, 'error');
                        return; // Don't throw, just show the message and stop
                    }
                    throw new Error(data.message || 'Unknown server error');
                }

                reloadFinancialSummary(piggyBankId);
                reloadSchedulePartial(piggyBankId);

                // Update status badge text and select value
                const statusBadge = document.querySelector(`#status-text-${piggyBankId}`);
                if (statusBadge && data.translated_status) {
                    statusBadge.textContent = data.translated_status.charAt(0).toUpperCase() + data.translated_status.slice(1);
                }
                const statusSelect = document.getElementById(`piggy-bank-status-${piggyBankId}`);
                if (statusSelect && data.piggy_bank_status) {
                    statusSelect.value = data.piggy_bank_status;
                    statusSelect.dataset.initialStatus = data.piggy_bank_status;
                    disableStatusSelectIfDone(piggyBankId);

                    // NEW: Disable/enable manual add/remove section based on status
                    if (['done', 'paused', 'cancelled'].includes(data.piggy_bank_status)) {
                        setManualMoneySectionDisabled(true);
                    } else {
                        setManualMoneySectionDisabled(false);
                    }
                }

                // NEW: Disable checkboxes if piggy bank is done
                disableScheduledSavingCheckboxesIfDone(piggyBankId);

                // Show message
                showFlashMessage(data.message, 'success');
                manualForm.reset();
            })
            .catch((err) => {
                console.error('AJAX error in add/remove money:', err); // <-- ADD THIS LINE
                console.error('Error details:', err.message, err.stack);
                const type = formData.get('type');
                if (type === 'manual_add') {
                    showFlashMessage("Something went wrong and you weren't able to add money to your piggy bank", 'error');
                } else if (type === 'manual_withdraw') {
                    showFlashMessage("Something went wrong and you weren't able to take out any money from your piggy bank", 'error');
                } else {
                    showFlashMessage("Something went wrong. Please try again.", 'error');
                }
            });
    });
});

function disableScheduledSavingCheckboxesIfDone(piggyBankId) {
    // Look for a container or data attribute that tells you the status
    // (Adjust selector if you use something else to store the status)
    const statusBadge = document.querySelector(`#status-text-${piggyBankId}`);
    const statusValue = statusBadge ? statusBadge.textContent.trim().toLowerCase() : null;

    if (statusValue === 'done') {
        document.querySelectorAll('.scheduled-saving-checkbox').forEach(cb => {
            cb.disabled = true;
            cb.classList.remove('cursor-pointer');
            cb.classList.add('cursor-not-allowed');
        });
    }
}

function disableStatusSelectIfDone(piggyBankId) {
    const statusSelect = document.getElementById(`piggy-bank-status-${piggyBankId}`);
    if (!statusSelect) return;
    statusSelect.disabled = statusSelect.value === 'done';
}

// Helper to disable/enable the manual money form section
function setManualMoneySectionDisabled(disabled) {
    // console.log('setManualMoneySectionDisabled called with:', disabled);

    const section = document.getElementById('manual-money-section');
    const form = document.getElementById('manual-money-form');

    // console.log('Section found:', !!section);
    // console.log('Form found:', !!form);

    if (!section || !form) return;

    // All inputs and buttons inside the form
    const elements = form.querySelectorAll('input, button, textarea, select');
    // console.log('Found form elements to disable:', elements.length);

    elements.forEach((el, index) => {
        // console.log(`Element ${index} (${el.tagName}) - disabled before: ${el.disabled}`);
        el.disabled = disabled;
        // console.log(`Element ${index} (${el.tagName}) - disabled after: ${el.disabled}`);

        if (disabled) {
            el.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            el.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

    // Set overall opacity for section
    section.style.opacity = disabled ? '0.5' : '1';
    section.style.pointerEvents = disabled ? 'none' : '';

    // console.log('Section opacity set to:', section.style.opacity);
    // console.log('Section pointer-events set to:', section.style.pointerEvents);
}

// Fetch and replace the schedule partial for the current piggy bank
function reloadSchedulePartial(piggyBankId) {
    const locale = document.documentElement.lang || 'en';
    const generatedUrl = route('localized.piggy-banks.schedule.' + locale, { locale: locale, piggy_id: piggyBankId });

    fetch(generatedUrl, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(response => response.text())
        .then(html => {
            console.log('ABOUT TO REPLACE ENTIRE CONTAINER WITH outerHTML');
            // console.log('Fresh HTML from server contains disabled checkboxes?', html.includes('disabled'));
            const container = document.getElementById('schedule-container');
            if (container) {
                container.outerHTML = html;
                // Re-attach checkbox listeners after DOM replacement
                attachCheckboxListeners();
            }
        })
        .catch(console.error);
}

function attachCheckboxListeners() {
    const checkboxes = document.querySelectorAll('.scheduled-saving-checkbox');

    // Check if piggy bank is done and disable checkboxes accordingly
    const statusSelect = document.querySelector('select[id^="piggy-bank-status-"]');
    const isDone = statusSelect && statusSelect.value === 'done';

    checkboxes.forEach(checkbox => {
        // Disable if piggy bank is done
        if (isDone) {
            checkbox.disabled = true;
        }

        // Attach event listener
        checkbox.addEventListener('change', async function () {
            await handleCheckboxChange(this);
        });
    });

}


/**
 * Function to display a flash message dynamically
 */
function showFlashMessage(message, type = 'success') {
    // Remove existing flash messages
    const existingFlash = document.getElementById('flash-message');
    if (existingFlash) {
        existingFlash.remove();
    }

    // Color and label map
    const config = {
        success: {
            bg: "bg-green-100 border-green-400 text-green-700",
            label: window.piggyBankTranslations['success'] || "Success"
        },
        error: {
            bg: "bg-red-100 border-red-400 text-red-700",
            label: window.piggyBankTranslations['error'] || "Error"
        }
    };

    const c = config[type] || config.success;

    const flashMessageContainer = document.createElement('div');
    flashMessageContainer.id = "flash-message";
    flashMessageContainer.className = `${c.bg} border px-4 py-3 rounded shadow-lg`;
    flashMessageContainer.style.position = "fixed";
    flashMessageContainer.style.top = "20px";
    flashMessageContainer.style.left = "50%";
    flashMessageContainer.style.transform = "translateX(-50%)";
    flashMessageContainer.style.width = "calc(min(384px, 90vw))";
    flashMessageContainer.style.zIndex = "50";
    flashMessageContainer.style.paddingRight = "3rem"; // Make room for close button

    flashMessageContainer.innerHTML = `
        <strong class="font-bold">${c.label}</strong>
        <span class="block sm:inline">${message}</span>
        <button class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer text-2xl leading-none" onclick="this.parentElement.remove();">
            &times;
        </button>
    `;

    document.body.prepend(flashMessageContainer);

    setTimeout(() => {
        if (flashMessageContainer) {
            flashMessageContainer.style.opacity = "0";
            setTimeout(() => flashMessageContainer.remove(), 500);
        }
    }, 8000);
}

// Expose globally for Alpine component access
window.showFlashMessage = showFlashMessage;
