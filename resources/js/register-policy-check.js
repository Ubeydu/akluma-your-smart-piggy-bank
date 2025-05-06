function toggleRegisterButton() {
    const termsChecked = document.getElementById('terms')?.checked;
    const privacyChecked = document.getElementById('privacy')?.checked;
    const button = document.getElementById('register-btn');

    if (termsChecked && privacyChecked) {
        button?.removeAttribute('disabled');
    } else {
        button?.setAttribute('disabled', 'disabled');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('terms')?.addEventListener('change', toggleRegisterButton);
    document.getElementById('privacy')?.addEventListener('change', toggleRegisterButton);
});
