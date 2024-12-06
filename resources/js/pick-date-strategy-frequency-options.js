// Add this at the top of the file
const dateInput = document.getElementById("saving_date");
const dateLabel = document.getElementById("saving_date_label");
const dateDisplay = document.getElementById("dateDisplay");


    document.addEventListener("DOMContentLoaded", function () {
        // Handle date input changes and display the formatted date
        dateInput.addEventListener("input", async function () {
            if (dateInput instanceof HTMLInputElement && dateLabel && dateInput.value) {
                dateLabel.classList.add("visibility-hidden");
                try {
                    const response = await fetch(`/format-date?date=${dateInput.value}`);
                    if (response.ok) {
                        const data = await response.json();
                        const message = dateDisplay.getAttribute("data-message");
                        dateDisplay.textContent = `${message} ${data.formatted_date}`;
                        dateDisplay.classList.remove("hidden");
                    }
                } catch (error) {
                    console.error("Error fetching formatted date:", error);
                }
            } else if (dateLabel) {
                dateLabel.classList.remove("visibility-hidden");
                dateDisplay.classList.add("hidden");
            }
        });

    // Function to handle date input changes
    dateInput.addEventListener("change", async function() {
    if (!this.value) return;

    try {
    const response = await fetch(window.Laravel.routes.calculateFrequencies, {
    method: 'POST',
    headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
},
    body: JSON.stringify({purchase_date: this.value})
});

    if (!response.ok) {
    console.error('Server responded with error:', response.status);
    return;
}

    const data = await response.json();
    const container = document.querySelector('#frequencyOptions .space-y-6');
    container.innerHTML = '';

    // Helper function to format currency amounts - simple display of Laravel-formatted values
    const formatAmount = (formattedAmount, currency) => `
            <div class="inline-flex items-center gap-1">
                <div class="bg-gray-50 px-3 py-1.5 rounded font-mono text-lg">${formattedAmount}</div>
                <div class="text-gray-600">${currency}</div>
            </div>
        `;

    // Helper function for proper period labels
    const formatPeriodLabel = (frequency, type) =>
    frequency === 1 ? type : type + 's';

    const shortTermPeriods = ['minutes', 'hours', 'days'];
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
    if (option.amount && option.amount.amount !== null) {
    const isShortTerm = shortTermPeriods.includes(type);
    const container = document.querySelector(
    isShortTerm ? '#shortTermOptions' : '#longTermOptions'
    );

    if (container) {
    const baseType = type.slice(0, -1);
    const periodLabel = formatPeriodLabel(option.frequency, baseType);

    container.innerHTML += `
                        <div class="relative flex items-start p-4 border rounded-lg hover:bg-gray-50">
                            <div class="flex items-center h-5">
                                <input type="radio"
                                       name="frequency"
                                       value="${type}"
                                       class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                            </div>
                            <div class="ml-3">
                                <label class="text-sm font-medium text-gray-700">
                                    I want to put aside
                                    ${formatAmount(option.amount.formatted_amount, option.amount.currency)}
                                    each time, for ${option.frequency} ${periodLabel}
                                </label>
                                ${option.extra_savings ? `
                                    <p class="text-xs text-green-600 mt-1">
                                        You'll save an extra
                                        ${formatAmount(option.extra_savings.formatted_amount, option.extra_savings.currency)}
                                        in total
                                    </p>
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

// Frequency selection handler
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
    document.getElementById('nextButton').classList.remove('hidden');
}
} catch (error) {
    console.error('Error storing frequency:', error);
}
});


});
