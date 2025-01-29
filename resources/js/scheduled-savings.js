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

            } catch (error) {
                console.error('Error:', error);
                this.checked = !this.checked;
                alert('Failed to update saving status. Please try again.');
            }
        });
    });
});


