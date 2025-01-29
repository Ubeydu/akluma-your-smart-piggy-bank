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
                    const locale = balanceElement.dataset.locale; // Read locale from DOM
                    balanceElement.textContent = formatCurrency(data.new_balance, currency, locale);
                }

                // Update remaining amount with formatted value
                const remainingAmountElement = document.getElementById(`remaining-amount-${piggyBankId}`);
                if (remainingAmountElement) {
                    const currency = remainingAmountElement.dataset.currency;
                    const locale = remainingAmountElement.dataset.locale; // Read locale from DOM
                    remainingAmountElement.textContent = formatCurrency(data.remaining_amount, currency, locale);
                }

                // Update status text in UI
                const statusCell = this.closest('tr').querySelector('td:last-child');
                statusCell.textContent = data.translated_status;

            } catch (error) {
                console.error('Error:', error);
                this.checked = !this.checked;
                alert('Failed to update saving status. Please try again.');
            }
        });
    });
});



// document.addEventListener('DOMContentLoaded', function() {
//     const checkboxes = document.querySelectorAll('input[data-saving-id]');
//
//     checkboxes.forEach(checkbox => {
//         checkbox.addEventListener('change', async function() {
//             const savingId = this.dataset.savingId;
//             const piggyBankId = this.dataset.piggyBankId;
//             const amount = parseFloat(this.dataset.amount);
//             const newStatus = this.checked ? 'saved' : 'pending';
//
//             try {
//                 const response = await fetch(`/scheduled-savings/${savingId}`, {
//                     method: 'PATCH',
//                     headers: {
//                         'Content-Type': 'application/json',
//                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//                     },
//                     body: JSON.stringify({
//                         status: newStatus,
//                         piggy_bank_id: piggyBankId,
//                         amount: amount
//                     })
//                 });
//
//                 const data = await response.json();
//                 if (!response.ok) {
//                     throw new Error('Failed to update status');
//                 }
//
//                 // Update the displayed balance dynamically from response
//                 const balanceElement = document.getElementById(`current-balance-${piggyBankId}`);
//                 if (balanceElement) {
//                     balanceElement.textContent = data.new_balance;
//                 }
//
//                 // Update remaining amount dynamically from response
//                 const remainingAmountElement = document.getElementById(`remaining-amount-${piggyBankId}`);
//                 if (remainingAmountElement) {
//                     remainingAmountElement.textContent = data.remaining_amount; // No calculations, just update from API
//                 }
//
//                 // Update status text in UI
//                 const statusCell = this.closest('tr').querySelector('td:last-child');
//                 statusCell.textContent = data.translated_status;
//
//             } catch (error) {
//                 console.error('Error:', error);
//                 this.checked = !this.checked;
//                 alert('Failed to update saving status. Please try again.');
//             }
//         });
//     });
// });



// document.addEventListener('DOMContentLoaded', function() {
//     const checkboxes = document.querySelectorAll('input[data-saving-id]');
//
//     checkboxes.forEach(checkbox => {
//         checkbox.addEventListener('change', async function() {
//             const savingId = this.dataset.savingId;
//             const piggyBankId = this.dataset.piggyBankId;
//             const amount = parseFloat(this.dataset.amount);
//             const newStatus = this.checked ? 'saved' : 'pending';
//
//             try {
//                 const response = await fetch(`/scheduled-savings/${savingId}`, {
//                     method: 'PATCH',
//                     headers: {
//                         'Content-Type': 'application/json',
//                         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
//                     },
//                     body: JSON.stringify({
//                         status: newStatus,
//                         piggy_bank_id: piggyBankId,
//                         amount: amount,
//                     })
//                 });
//
//                 if (!response.ok) {
//                     throw new Error('Network response was not ok');
//                 }
//
//                 // Parse the JSON response
//                 const data = await response.json();
//
//                 // Update the status text with the translated version
//                 const statusCell = this.closest('tr').querySelector('td:last-child');
//                 statusCell.textContent = data.translated_status;
//
//             } catch (error) {
//                 console.error('Error:', error);
//                 // Revert checkbox state
//                 this.checked = !this.checked;
//
//                 // Show error message to user
//                 alert('Failed to update saving status. Please try again.');
//             }
//         });
//     });
// });
