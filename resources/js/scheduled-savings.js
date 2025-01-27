// resources/js/scheduled-savings.js

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[data-saving-id]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', async function() {
            const savingId = this.dataset.savingId;
            const piggyBankId = this.dataset.piggyBankId;
            const newStatus = this.checked ? 'saved' : 'pending';

            try {
                const response = await fetch(`/scheduled-savings/${savingId}`, { // Updated URL
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: newStatus,
                        piggy_bank_id: piggyBankId
                    })
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                // Update the status text in the same row
                const statusCell = this.closest('tr').querySelector('td:last-child');
                statusCell.textContent = newStatus;

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
