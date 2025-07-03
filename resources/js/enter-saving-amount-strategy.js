document.addEventListener('DOMContentLoaded', function () {
    // Store the current target dates data globally
    let currentTargetDatesData = null;


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

    // Function to calculate and display target date options
    function calculateAndDisplayOptions() {
        const savingAmountWhole = document.getElementById('saving_amount_whole');
        const savingAmountCents = document.getElementById('saving_amount_cents');
        const frequencyOptions = document.getElementById('frequencyOptions');

        if (!savingAmountWhole || !savingAmountWhole.value || savingAmountWhole.value < 10) {
            frequencyOptions.classList.add('hidden');
            return;
        }

        // Check maximum amount
        const currentAmount = parseFloat(savingAmountWhole.value + '.' + (savingAmountCents?.value || '00'));
        if (currentAmount >= window.Laravel.maxSavingAmount) {
            // Show temporary error message and hide options
            frequencyOptions.classList.add('hidden');
            const maxErrorDiv = document.getElementById('saving_amount_max_error');
            maxErrorDiv.classList.remove('hidden');
            setTimeout(() => {
                maxErrorDiv.classList.add('hidden');
            }, 5000);
            return;
        }

        const data = {
            saving_amount_whole: savingAmountWhole.value,
            saving_amount_cents: savingAmountCents ? savingAmountCents.value : '00'
        };

        fetch(window.Laravel.routes.calculateTargetDates, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayFrequencyOptions(data.options);
                    frequencyOptions.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function displayFrequencyOptions(options) {
        // Store the current options data for form submission
        currentTargetDatesData = options;

        // Clear existing content first
        const shortTermContainer = document.querySelector('#shortTermOptions');
        const longTermContainer = document.querySelector('#longTermOptions');

        if (shortTermContainer) shortTermContainer.innerHTML = '';
        if (longTermContainer) longTermContainer.innerHTML = '';


        const periodToTranslationKey = (type) => {
            const translationMap = {
                'day': 'days',
                'week': 'weeks',
                'month': 'months',
                'year': 'years'
            };
            return translationMap[type] || type;
        };

        const formatPeriodLabel = (type) => {
            const translationKey = periodToTranslationKey(type);
            return window.Laravel.translations[translationKey];
        };


        // Define which periods are short-term vs long-term (same as pick-date)
        const shortTermPeriods = ['days', 'weeks'];

        // Get the saving amount for display
        const savingAmountWhole = document.getElementById('saving_amount_whole').value;
        const savingAmountCents = document.getElementById('saving_amount_cents')?.value || '00';

        // Process each option using the exact same structure as pick-date
        ['days', 'weeks', 'months', 'years'].forEach(frequency => {
            if (options[frequency]) {
                const option = options[frequency];
                const isShortTerm = shortTermPeriods.includes(frequency);
                const container = document.querySelector(
                    isShortTerm ? '#shortTermOptions' : '#longTermOptions'
                );

                if (container) {
                    // Format period label using translations
                    const periodKey = frequency === 'daily' ? 'day' :
                        frequency === 'weekly' ? 'week' :
                            frequency === 'monthly' ? 'month' : 'year';

                    const baseType = frequency.slice(0, -1); // Remove 's' to get 'day', 'week', etc.
                    const periodLabel = formatPeriodLabel(baseType);


                    container.innerHTML += `
                    <div class="relative flex items-start p-4 border rounded-lg hover:bg-gray-50 mb-2 cursor-pointer sm:p-6"
                        onclick="this.querySelector('input[type=\\'radio\\']').click()">
                        <div class="flex items-center h-5">
                            <input type="radio"
                                   name="frequency"
                                   value="${frequency}"
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                   onclick="event.stopPropagation(); handleFrequencySelection('${frequency}')">
                        </div>
                        <div class="ml-3 flex-1 min-w-0">

                            <div class="text-base text-gray-700 flex items-center gap-x-4 flex-wrap">
                                <span class="min-w-[120px] sm:min-w-[200px]">${translations.savingsPlan}:</span>
                                <span class="font-mono break-words">${option.saving_amount.formatted_value} × ${option.periods} ${periodLabel}</span>
                            </div>

                            <div class="text-base text-gray-600 mt-3 space-y-3">

                                <div class="flex items-center py-2 gap-x-4 flex-wrap">
                                    <span class="min-w-[180px] sm:min-w-[200px]">${translations.total}:</span>
                                    <span class="font-mono break-words bg-gray-50 px-2 py-1 rounded">${option.total_amount.formatted_value}</span>
                                </div>
                                <div class="flex items-center py-2 gap-x-4 text-green-600 flex-wrap">
                                    <span class="min-w-[100px] sm:min-w-[200px]">${translations.targetDate}:</span>
                                    <span class="font-mono break-words bg-gray-50 px-2 py-1 rounded">${option.target_date}</span>
                                </div>

                            </div>

                        </div>
                    </div>
                `;

                }
            }
        });

    }

    const savingAmountInput = document.getElementById('saving_amount_whole');
    if (savingAmountInput) {
        savingAmountInput.addEventListener('input', calculateAndDisplayOptions);
        // Re-generate options when page loads if amount is already filled
        window.addEventListener('load', function() {
            const savingAmountInput = document.getElementById('saving_amount_whole');
            if (savingAmountInput && savingAmountInput.value && savingAmountInput.value >= 10) {
                calculateAndDisplayOptions();
            }
        });
    }

    const savingAmountCentsInput = document.getElementById('saving_amount_cents');
    if (savingAmountCentsInput) {
        savingAmountCentsInput.addEventListener('input', calculateAndDisplayOptions);
    }

    // Function to handle frequency selection
    window.handleFrequencySelection = function(selectedFrequency) {
        const nextButton = document.getElementById('nextButton');
        const selectedFrequencyInput = document.getElementById('selectedFrequencyInput');
        const savingAmountInput = document.getElementById('savingAmountInput');
        const targetDatesInput = document.getElementById('targetDatesInput');

        if (!nextButton || !selectedFrequencyInput || !savingAmountInput || !targetDatesInput) {
            console.error('Required form elements not found');
            return;
        }

        const savingAmountWhole = document.getElementById('saving_amount_whole').value;
        const savingAmountCents = document.getElementById('saving_amount_cents')?.value || '00';
        const fullSavingAmount = savingAmountWhole + '.' + savingAmountCents;

        selectedFrequencyInput.value = selectedFrequency;
        savingAmountInput.value = fullSavingAmount;
        targetDatesInput.value = JSON.stringify(currentTargetDatesData);

        nextButton.disabled = false;
        nextButton.classList.remove('disabled:bg-gray-400', 'disabled:cursor-not-allowed', 'disabled:hover:bg-gray-300');
    }

    // Disable Next button initially
    const nextButton = document.getElementById('nextButton');
    if (nextButton) {
        nextButton.disabled = true;
    }

});
