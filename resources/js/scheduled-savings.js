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
                        amount: amount,
                    })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                // Parse the JSON response
                const data = await response.json();

                // Update the status text with the translated version
                const statusCell = this.closest('tr').querySelector('td:last-child');
                statusCell.textContent = data.translated_status;

            } catch (error) {
                console.error('Error:', error);
                // Revert checkbox state
                this.checked = !this.checked;

                // Show error message to user
                alert('Failed to update saving status. Please try again.');
            }
        });
    });
});
