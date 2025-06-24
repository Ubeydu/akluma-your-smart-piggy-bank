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
// console.log('Page starting to load');

/**
 * Main initialization function that sets up all event listeners and handlers
 * for the piggy bank date and frequency selection functionality
 */
document.addEventListener("DOMContentLoaded", function () {
    // Log initial state
    // console.log('DOMContentLoaded fired');
    // console.log('Initial date input value:', dateInput?.value);

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
        if (!this.value) {
            return;
        }

        try {
            // Use the localized route with the current locale
            const url = `/${window.Laravel.locale}/create-piggy-bank/pick-date/calculate-frequencies`;

            // console.log('Making AJAX request with locale info:', {
            //     url: url,
            //     currentLocale: window.Laravel.locale,
            //     documentLang: document.documentElement.lang,
            //     htmlLang: document.querySelector('html').getAttribute('lang')
            // });

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept-Language': window.Laravel.locale // Add locale as a header
                },
                body: JSON.stringify({purchase_date: this.value})
            });

            if (!response.ok) {
                // console.error('Server responded with error:', response.status);
                return;
            }

            const data = await response.json();

            // console.log('Parsed response data:', data);
            // console.log('Data structure:', JSON.stringify(data, null, 2));

            // console.log('Received frequency data:', data);
            // console.log('Data type:', typeof data);
            // console.log('Data keys:', Object.keys(data));


            // console.log('Response received - checking session data:', {
            //     timestamp: new Date().toISOString(),
            //     responseData: data
            // });

            // const flashResponse = await fetch('/create-piggy-bank/check-flash-messages');
            // const flashHtml = await flashResponse.text();
            //
            // // Find existing flash message container
            // let flashContainer = document.querySelector('div.fixed.inset-x-0.top-4');
            //
            // if (flashContainer) {
            //     // If container exists, update it
            //     flashContainer.outerHTML = flashHtml;
            // } else {
            //     // If no container exists, create a new one at the top of the body
            //     document.body.insertAdjacentHTML('afterbegin', flashHtml);
            // }

            /**
             * Fetches flash messages via AJAX and displays them safely without triggering unwanted side effects.
             * This function uses a JSON endpoint to avoid loading HTML that could contain scripts or components.
             * @async
             * @returns {Promise<void>}
             */
            const fetchFlashMessages = async () => {
                try {
                    const response = await fetch(`/${window.Laravel.locale}/create-piggy-bank/get-flash-messages`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) return;

                    const flashData = await response.json();

                    // Check if we have any messages
                    if (!flashData.success && !flashData.error && !flashData.warning && !flashData.info) {
                        return;
                    }

                    const container = document.createElement('div');
                    container.className = 'fixed inset-x-0 top-4 z-50 mx-4 sm:mx-auto sm:max-w-md';

                    // Helper function to create message
                    const createMessage = (type, text) => {
                        if (!text) return null;

                        const colors = {
                            success: { bg: 'bg-green-100', border: 'border-green-200', text: 'text-green-800', button: 'text-green-600 hover:text-green-800' },
                            error: { bg: 'bg-red-100', border: 'border-red-200', text: 'text-red-800', button: 'text-red-600 hover:text-red-800' },
                            warning: { bg: 'bg-yellow-100', border: 'border-yellow-200', text: 'text-yellow-800', button: 'text-yellow-600 hover:text-yellow-800' },
                            info: { bg: 'bg-blue-100', border: 'border-blue-200', text: 'text-blue-800', button: 'text-blue-600 hover:text-blue-800' }
                        };

                        const color = colors[type];
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `relative rounded-md ${color.bg} border ${color.border} p-4 shadow-md mb-2`;

                        const closeBtn = document.createElement('button');
                        closeBtn.className = `absolute top-2 right-2 ${color.button} cursor-pointer`;
                        closeBtn.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            `;
                        closeBtn.addEventListener('click', () => messageDiv.remove());

                        const textP = document.createElement('p');
                        textP.className = `${color.text} text-sm font-medium pr-6`;
                        textP.textContent = text;

                        messageDiv.appendChild(closeBtn);
                        messageDiv.appendChild(textP);

                        return messageDiv;
                    };

                    // Add all message types
                    const successMsg = createMessage('success', flashData.success);
                    const errorMsg = createMessage('error', flashData.error);
                    const warningMsg = createMessage('warning', flashData.warning);
                    const infoMsg = createMessage('info', flashData.info);

                    // Add messages to container
                    [successMsg, errorMsg, warningMsg, infoMsg].forEach(msg => {
                        if (msg) container.appendChild(msg);
                    });

                    // Find existing message and replace, or add to body
                    const existingContainer = document.querySelector('div.fixed.inset-x-0.top-4');
                    if (existingContainer) {
                        existingContainer.replaceWith(container);
                    } else {
                        document.body.prepend(container);
                    }

                    // Auto-hide after 5 seconds
                    setTimeout(() => {
                        container.style.opacity = '0';
                        container.style.transition = 'opacity 0.3s';
                        setTimeout(() => container.remove(), 300);
                    }, 5000);
                } catch (error) {
                    // console.error('Error fetching flash messages:', error);
                }
            };

            // Instead of the old flash message code:
            // const flashResponse = await fetch('/create-piggy-bank/check-flash-messages')...
            // Use our new safer implementation:
            await fetchFlashMessages();

            const container = document.querySelector('#frequencyOptions .space-y-6');

            // console.log('Frequency options container:', container);

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
             * @param {string} type - Base period type (day, week etc.)
             * @returns {string} Translation key for the period
             */
            const periodToTranslationKey = (type) => {
                const translationMap = {
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
            const shortTermPeriods = ['days', 'weeks'];
            const longTermPeriods = ['months', 'years'];

            const hasShortTermOptions = shortTermPeriods.some(period => data[period]?.amount);
            const hasLongTermOptions = longTermPeriods.some(period => data[period]?.amount);

            // console.log('hasShortTermOptions:', hasShortTermOptions);
            // console.log('hasLongTermOptions:', hasLongTermOptions);


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
                // console.log(`Processing option for period type: ${type}`);
                // console.log('Processing option for currency:', type);
                // console.log('Option data:', option);


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
                <div class="relative flex items-start p-4 border rounded-lg hover:bg-gray-50 mb-2 cursor-pointer"
                    onclick="this.querySelector('input[type=\\'radio\\']').click()">
                    <div class="flex items-center h-5">
                        <input type="radio"
                               name="frequency"
                               value="${type}"
                               class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                               onclick="event.stopPropagation()">
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
                    <span class="flex items-center gap-1">
                    <span data-translate="extra-savings">${window.Laravel.translations['Extra']}:</span>
                    <span x-data="{ showTooltip: false }" class="relative cursor-help">
                        <svg @mouseenter="showTooltip = true"
                            @mouseleave="showTooltip = false"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="w-4 h-4 text-green-600 hover:text-gray-800 transition-colors duration-200">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <div x-show="showTooltip"
                            x-cloak
                            class="absolute z-10 w-48 px-3 py-2 mt-2 text-sm bg-gray-900 text-white rounded-lg shadow-lg"
                            :class="{
                                'left-1/2 -translate-x-1/2': window.innerWidth > 640, /* Center it on larger screens */
                                'left-0 ml-2': window.innerWidth <= 640 && $el.getBoundingClientRect().left < 150, /* Shift right if near left edge */
                                'right-0 mr-2': window.innerWidth <= 640 && $el.getBoundingClientRect().right > window.innerWidth - 150 /* Shift left if near right edge */
                            }"
                            role="tooltip">

                            ${window.Laravel.translations['Extra Savings Tooltip Info']}
                        </div>
                    </span>
                    </span>
                    <span>+${formatAmount(option.extra_savings.formatted_value)}</span>
                    </div>


                <div class="flex justify-between font-semibold">
                    <span data-translate="total-savings">${window.Laravel.translations['Total']}:</span>
                    <span>${formatAmount(option.total_savings.formatted_value)}</span>
                </div>
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
            // console.error('Error calculating frequencies:', error);
        }
    });


    // Then, after the listener is set up, check for and trigger existing date
    if (dateInput instanceof HTMLInputElement && dateInput.value) {
        // console.log('Found existing date:', dateInput.value);
        // console.log('About to dispatch change event');
        dateInput.dispatchEvent(new Event('change'));
        // console.log('Change event dispatched');
    } else {
        // console.log('No existing date found or dateInput invalid');
        // console.log('dateInput type:', dateInput?.constructor.name);
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

            const responseData = await response.json();

            if (response.ok) {
                const nextButton = document.getElementById('nextButton');
                nextButton.disabled = false;
                // console.log('Success:', responseData);
            }
        } catch (error) {
            // console.error('Error storing frequency:', error);
            // console.error('Response Body:', responseData);
        }
    });

});

window.addEventListener('pageshow', function(event) {
    // This will run even if the page is loaded from bfcache
    // console.log('Page shown:', event.persisted ? 'from bfcache' : 'fresh load');

    if (dateInput instanceof HTMLInputElement && dateInput.value) {
        // console.log('Found date input with value:', dateInput.value);
        dateInput.dispatchEvent(new Event('change'));
    }
});
