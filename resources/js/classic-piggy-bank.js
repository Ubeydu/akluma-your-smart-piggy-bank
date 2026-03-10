document.addEventListener('DOMContentLoaded', function () {

    const currentLocale = window.location.pathname.split('/')[1];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Status change handling (only present when piggy bank is active)
    const statusSelect = document.querySelector('[data-piggy-bank-id]');

    if (statusSelect) {
        const piggyBankId = statusSelect.dataset.piggyBankId;
        const initialStatus = statusSelect.dataset.initialStatus;

        statusSelect.addEventListener('change', function () {
            const newStatus = this.value;

            if (newStatus === initialStatus) {
                return;
            }

            let confirmMessage = '';
            if (newStatus === 'done') {
                confirmMessage = window.piggyBankTranslations.confirm_done;
            } else if (newStatus === 'cancelled') {
                confirmMessage = window.piggyBankTranslations.confirm_cancel;
            }

            this.value = initialStatus;

            const dialogContainer = this.closest('[x-data]');
            if (dialogContainer && dialogContainer._x_dataStack) {
                const data = dialogContainer._x_dataStack[0];
                data.showConfirmStatus = true;
                data.statusChangeMessage = confirmMessage;
                data.statusChangeAction = async function () {
                    await executeStatusChange(newStatus);
                };
            }
        });

        async function executeStatusChange(newStatus) {
            let endpoint;
            if (newStatus === 'done') {
                endpoint = getRoute('localized.piggy-banks.update-status-done');
            } else if (newStatus === 'cancelled') {
                endpoint = getRoute('localized.piggy-banks.update-status-cancelled');
            }

            if (!endpoint) {
                return;
            }

            try {
                const response = await fetch(endpoint, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to update status');
                }

                // Reload the page so the server re-renders the correct state
                const showUrl = window.location.pathname;
                window.location.href = showUrl + '?status_updated=' + encodeURIComponent(data.status);

            } catch (error) {
                alert('Failed to update piggy bank status.');
                statusSelect.value = initialStatus;
            }
        }

        function getRoute(routeName) {
            const localizedRouteName = `${routeName}.${currentLocale}`;
            if (typeof Ziggy !== 'undefined' && Ziggy.routes[localizedRouteName]) {
                const routeUri = Ziggy.routes[localizedRouteName].uri;
                return '/' + routeUri
                    .replace('{locale}', currentLocale)
                    .replace('{piggy_id}', piggyBankId);
            }

            const routeMap = {
                'localized.piggy-banks.update-status-done': `/${currentLocale}/piggy-banks/${piggyBankId}/update-status-done`,
                'localized.piggy-banks.update-status-cancelled': `/${currentLocale}/piggy-banks/${piggyBankId}/update-status-cancelled`,
            };
            return routeMap[routeName] || null;
        }
    }

    // Financial summary refresh (used by money form)
    function refreshFinancialSummary() {
        const container = document.getElementById('financial-summary-container');
        if (!container) {
            return;
        }

        const url = container.dataset.financialSummaryUrl;
        if (!url) {
            return;
        }

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.outerHTML = html;
        })
        .catch(() => {});
    }

    // Money form AJAX submission
    const moneyForm = document.getElementById('money-form');
    const moneySuccess = document.getElementById('money-success');
    const moneyError = document.getElementById('money-error');

    function hideMessages() {
        if (moneySuccess) moneySuccess.classList.add('hidden');
        if (moneyError) moneyError.classList.add('hidden');
    }

    function showSuccess(message) {
        hideMessages();
        if (moneySuccess) {
            moneySuccess.textContent = message;
            moneySuccess.classList.remove('hidden');
            setTimeout(() => moneySuccess.classList.add('hidden'), 5000);
        }
    }

    function showError(message) {
        hideMessages();
        if (moneyError) {
            moneyError.textContent = message;
            moneyError.classList.remove('hidden');
            setTimeout(() => moneyError.classList.add('hidden'), 8000);
        }
    }

    async function submitMoney(type) {
        const amountInput = document.getElementById('money-amount');
        const noteInput = document.getElementById('money-note');
        const amount = amountInput ? amountInput.value.trim() : '';

        if (!amount || parseFloat(amount) <= 0) {
            return;
        }

        const url = moneyForm.dataset.url;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: type,
                    amount: amount,
                    note: noteInput ? noteInput.value.trim() : ''
                })
            });

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                showError('Unexpected server response. Please refresh and try again.');
                return;
            }

            const data = await response.json();

            if (!response.ok) {
                showError(data.message || data.errors?.amount?.[0] || 'Something went wrong.');
                return;
            }

            showSuccess(data.message);
            if (amountInput) amountInput.value = '';
            if (noteInput) noteInput.value = '';
            refreshFinancialSummary();

        } catch (error) {
            showError('Something went wrong. Please try again.');
        }
    }

    if (moneyForm) {
        moneyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const typeInput = document.getElementById('money-type');
            const type = typeInput ? typeInput.value : 'manual_add';
            submitMoney(type);
        });
    }
});
