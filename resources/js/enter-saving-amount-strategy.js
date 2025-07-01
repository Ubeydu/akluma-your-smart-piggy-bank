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

    // Function to calculate and display target date options
    function calculateAndDisplayOptions() {
        const savingAmountWhole = document.getElementById('saving_amount_whole');
        const savingAmountCents = document.getElementById('saving_amount_cents');
        const frequencyOptions = document.getElementById('frequencyOptions');

        if (!savingAmountWhole || !savingAmountWhole.value || savingAmountWhole.value < 10) {
            frequencyOptions.classList.add('hidden');
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

        // Clear existing content first
        const shortTermContainer = document.querySelector('#shortTermOptions');
        const longTermContainer = document.querySelector('#longTermOptions');

        if (shortTermContainer) shortTermContainer.innerHTML = '';
        if (longTermContainer) longTermContainer.innerHTML = '';

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

                    // Copy exact HTML structure from pick-date
                    container.innerHTML += `
                    <div class="relative flex items-start p-4 border rounded-lg hover:bg-gray-50 mb-2 cursor-pointer"
                        onclick="this.querySelector('input[type=\\'radio\\']').click()">
                        <div class="flex items-center h-5">
                            <input type="radio"
                                   name="frequency"
                                   value="${frequency}"
                                   class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                   onclick="event.stopPropagation()">
                        </div>
                        <div class="ml-3">
                            <div class="text-sm font-medium text-gray-700 flex flex-wrap gap-2">
                                <span>${translations.savingsPlan}:</span>
                                <span class="font-semibold">${savingAmountWhole}</span>
                                <span>×</span>
                                <span>${option.periods} ${periodLabel}</span>
                            </div>
                            <div class="text-xs text-gray-600 mt-2 space-y-1">

                                <div class="flex justify-between">
                                    <span>${translations.periodicSavingAmount}:</span>
                                    <span>${option.saving_amount}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>${translations.targetDate}:</span>
                                    <span>${option.target_date}</span>
                                </div>
                                <div class="flex justify-between font-semibold">
                                    <span>${translations.total}:</span>
                                    <span>${option.total_amount}</span>
                                </div>

                            </div>
                        </div>
                    </div>
                `;
                }
            }
        });
    }


    // Add event listener to trigger calculation when user enters amount
    const savingAmountInput = document.getElementById('saving_amount_whole');
    if (savingAmountInput) {
        savingAmountInput.addEventListener('input', calculateAndDisplayOptions);
        savingAmountInput.addEventListener('blur', calculateAndDisplayOptions);
    }
});
