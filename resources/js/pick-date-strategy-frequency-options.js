/**
 * @typedef {Object} DatePickerElements
 * @property {HTMLInputElement} dateInput - The date input field
 * @property {HTMLElement} dateLabel - Label for the date input
 * @property {HTMLElement} dateDisplay - Element to display formatted date
 */

/**
 * DOM elements used for date picking functionality
 * @type {HTMLInputElement | null } dateInput - Input element for selecting date
 * @type {HTMLElement} dateLabel - Label element for the date input
 * @type {HTMLElement} dateDisplay - Element that shows the formatted date
 */
const dateInput = document.getElementById("saving_date");
// const dateLabel = document.getElementById("saving_date_label");
// const dateDisplay = document.getElementById("dateDisplay");

// Log when page starts loading
console.log('Page starting to load');

/**
 * Main initialization function that sets up all event listeners and handlers
 * for the piggy bank date and frequency selection functionality
 */
document.addEventListener("DOMContentLoaded", function () {
    // Log initial state
    console.log('DOMContentLoaded fired');
    console.log('Initial date input value:', dateInput?.value);

    /**
     * Handles changes to the date input field, formatting and displaying the selected date
     * @async
     * @listens input
     */


    /**
     * @typedef {Object} Amount
     * @property {Object} amount - The Money object
     * @property {string} formatted_value - Pre-formatted string with amount and currency
     */

    /**
     * @typedef {Object} FrequencyOption
     * @property {number} frequency - Number of periods
     * @property {Amount} [amount] - Amount to save per period
     * @property {Amount} [extra_savings] - Additional savings possible
     * @property {string} [message] - Optional message to display
     */

    /**
     * Handles date selection changes and calculates saving frequency options
     * @async
     * @listens change
     */

    dateInput.addEventListener("change", async function() {
        console.log('Change event handler triggered');
        console.log('Current date value:', this.value);

        if (!this.value) {
            console.log('No date value, returning early');
            return;
        }

        try {
            console.log('About to fetch frequencies for date:', this.value);
            const response = await fetch(window.Laravel.routes.calculateFrequencies, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({purchase_date: this.value})
            });


            console.log('Fetch response status:', response.status);

            if (!response.ok) {
                console.error('Server responded with error:', response.status);
                return;
            }

            const data = await response.json();
            console.log('Received frequency data:', data);
            console.log('Response received - checking session data:', {
                timestamp: new Date().toISOString(),
                responseData: data
            });

            const flashResponse = await fetch('/create-piggy-bank/check-flash-messages');
            const flashHtml = await flashResponse.text();

            // Find existing flash message container
            let flashContainer = document.querySelector('div.fixed.inset-x-0.top-4');

            if (flashContainer) {
                // If container exists, update it
                flashContainer.outerHTML = flashHtml;
            } else {
                // If no container exists, create a new one at the top of the body
                document.body.insertAdjacentHTML('afterbegin', flashHtml);
            }

            const container = document.querySelector('#frequencyOptions .space-y-6');
            container.innerHTML = '';

            /**
             * Formats currency amount with proper styling
             * @param {string} formattedValue -
             *
             * @returns {string} HTML string for displaying amount
             */
            const formatAmount = (formattedValue) => `
    <div class="inline-flex items-center gap-1">
        <div class="bg-gray-50 px-3 py-1.5 rounded font-mono text-lg">${formattedValue}</div>
    </div>
`;

            /**
             * Maps period types to their translation keys
             * Always uses plural form for simplicity across languages
             * @param {string} type - Base period type (hour, day, etc.)
             * @returns {string} Translation key for the period
             */
            const periodToTranslationKey = (type) => {
                const translationMap = {
                    'hour': 'hours',
                    'day': 'days',
                    'week': 'weeks',
                    'month': 'months',
                    'year': 'years'
                };
                return translationMap[type] || type;
            };

            /**
             * Gets translated period label
             * @param {string} type - Type of period (day, week, month, etc.)
             * @returns {string} Translated period label
             */
            const formatPeriodLabel = (type) => {
                const translationKey = periodToTranslationKey(type);
                return window.Laravel.translations[translationKey];
            };

            /**
             * Predefined period types for grouping saving options
             * @type {string[]}
             */
            const shortTermPeriods = ['hours', 'days'];
            const longTermPeriods = ['weeks', 'months', 'years'];

            const hasShortTermOptions = shortTermPeriods.some(period => data[period]?.amount);
            const hasLongTermOptions = longTermPeriods.some(period => data[period]?.amount);

            document.getElementById('frequencyOptions').classList.remove('hidden');

// If no options available, show error message and return
            if (!hasShortTermOptions && !hasLongTermOptions) {
                document.getElementById('frequencyTitle').classList.add('hidden');
                container.innerHTML = `
        <div class="p-4 border rounded-lg bg-gray-50">
            <p class="text-sm text-gray-700">${window.Laravel.translations['Sorry. We weren\'t able to create a saving plan for you. Try with a different price or date.']}</p>
        </div>
    `;
                return;
            }

// Show the title if we have options
            document.getElementById('frequencyTitle').classList.remove('hidden');

            // Create short-term options container
            if (hasShortTermOptions) {  // Using the variable we already calculated
                container.innerHTML += `
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-500 mb-3"
                data-translate="short-term-options">${window.Laravel.translations['Short-term Saving Options']}</h3>
            <div class="space-y-3" id="shortTermOptions"></div>
        </div>
    `;
            }

// Create long-term options container
            if (hasLongTermOptions) {  // Using the variable we already calculated
                container.innerHTML += `
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-500 mb-3"
                data-translate="long-term-options">${window.Laravel.translations['Long-term Saving Options']}</h3>
            <div class="space-y-3" id="longTermOptions"></div>
        </div>
    `;
            }

            // Process each saving option
            Object.entries(data).forEach(([type, option]) => {
                // Simpler check that only looks at frequency
                const isSinglePayment = option.frequency === 1 && option.amount; // ensure we have amount data

                // Skip rendering for single payment options
                if (isSinglePayment) {
                    return; // Skip this iteration
                }

                // Handle message-only options
                if (option.message) {
                    const isShortTerm = shortTermPeriods.includes(type);
                    const container = document.querySelector(
                        isShortTerm ? '#shortTermOptions' : '#longTermOptions'
                    );
                    if (container) {
                        container.innerHTML += `
                                                    <div class="p-4 border rounded-lg bg-gray-50">
                                                        <p class="text-sm text-gray-700">${option.message}</p>
                                                    </div>
                                                `;
                    }
                    return;
                }

                // Handle valid saving options
                if (option.amount?.formatted_value) {
                    const isShortTerm = shortTermPeriods.includes(type);
                    const container = document.querySelector(
                        isShortTerm ? '#shortTermOptions' : '#longTermOptions'
                    );

                    if (container) {
                        const baseType = type.slice(0, -1);
                        const periodLabel = formatPeriodLabel(baseType);

                        container.innerHTML += `
    <div class="relative flex items-start p-4 border rounded-lg hover:bg-gray-50 mb-2">
        <div class="flex items-center h-5">
            <input type="radio"
                   name="frequency"
                   value="${type}"
                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
        </div>
        <div class="ml-3">
            <div class="text-sm font-medium text-gray-700 flex flex-wrap gap-2">
                <span data-translate="savings-plan">${window.Laravel.translations['Savings plan']}:</span>
                <span class="font-semibold">${formatAmount(option.amount.formatted_value)}</span>
                <span>×</span>
                <span>${option.frequency} ${periodLabel}</span>
            </div>
            ${option.extra_savings ? `
                <div class="text-xs text-gray-600 mt-2 space-y-1">
                    <div class="flex justify-between">
            <span data-translate="target-amount">${window.Laravel.translations['Target']}:</span>
            <span>${formatAmount(option.target_amount.formatted_value)}</span>
        </div>
        <div class="flex justify-between text-green-600">
            <span data-translate="extra-savings">${window.Laravel.translations['Extra']}:</span>
            <span>+${formatAmount(option.extra_savings.formatted_value)}</span>
        </div>
        <div class="flex justify-between font-semibold">
            <span data-translate="total-savings">${window.Laravel.translations['Total']}:</span>
            <span>${formatAmount(option.total_savings.formatted_value)}</span>
        </div>
                </div>
            ` : ''}
        </div>
    </div>
`;

                    }
                }
            });

            // Check if containers are empty after rendering options
            const shortTermContainer = document.querySelector('#shortTermOptions');
            const longTermContainer = document.querySelector('#longTermOptions');

// Remove short-term section if no options were rendered
            if (shortTermContainer && !shortTermContainer.hasChildNodes()) {
                shortTermContainer.closest('.mb-4')?.remove();
            }

// Remove long-term section if no options were rendered
            if (longTermContainer && !longTermContainer.hasChildNodes()) {
                longTermContainer.closest('.mt-6')?.remove();
            }



            document.getElementById('frequencyOptions').classList.remove('hidden');
        } catch (error) {
            console.error('Error calculating frequencies:', error);
        }
    });


    // Then, after the listener is set up, check for and trigger existing date
    if (dateInput instanceof HTMLInputElement && dateInput.value) {
        console.log('Found existing date:', dateInput.value);
        console.log('About to dispatch change event');
        dateInput.dispatchEvent(new Event('change'));
        console.log('Change event dispatched');
    } else {
        console.log('No existing date found or dateInput invalid');
        console.log('dateInput type:', dateInput?.constructor.name);
    }

    /**
     * Handles frequency selection changes and stores the selected frequency
     * @async
     * @param {Event} e - Change event from radio button selection
     * @listens change
     */
    document.querySelector('#frequencyOptions').addEventListener('change', async function(e) {
        if (e.target.type !== 'radio') return;

        try {
            const response = await fetch(window.Laravel.routes.storeFrequency, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({frequency_type: e.target.value})
            });

            if (response.ok) {
                const nextButton = document.getElementById('nextButton');
                nextButton.disabled = false;
            }
        } catch (error) {
            console.error('Error storing frequency:', error);
        }
    });

});

document.addEventListener("visibilitychange", function() {
    if (document.visibilityState === 'visible' && dateInput instanceof HTMLInputElement && dateInput.value) {
        dateInput.dispatchEvent(new Event('change'));
    }
});
