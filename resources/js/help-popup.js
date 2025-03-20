    document.addEventListener('DOMContentLoaded', function() {
    // Select elements
    const helpPopup = document.getElementById('helpPopup');
    const getHelpBtn = document.getElementById('getHelpBtn');
    const getHelpBtnMobile = document.getElementById('getHelpBtnMobile');
    const closeBtn = document.querySelector('.help-close-btn');
    const copyBtn = document.querySelector('.help-copy-btn');
    const emailElement = document.querySelector('.help-email');

    // Open popup
    if (getHelpBtn) {
    getHelpBtn.addEventListener('click', function(e) {
    e.preventDefault();
    helpPopup.style.display = 'flex';
});
}

    if (getHelpBtnMobile) {
    getHelpBtnMobile.addEventListener('click', function(e) {
    e.preventDefault();
    helpPopup.style.display = 'flex';
});
}

    // Close popup when clicking X
    if (closeBtn) {
    closeBtn.addEventListener('click', function() {
    helpPopup.style.display = 'none';
});
}

    // Close popup when clicking outside
    window.addEventListener('click', function(e) {
    if (e.target === helpPopup) {
    helpPopup.style.display = 'none';
}
});

    // Copy email to clipboard
    if (copyBtn && emailElement) {
    copyBtn.addEventListener('click', function() {
    const email = emailElement.textContent.trim();

    // Create temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = email;
    document.body.appendChild(tempInput);

    // Select and copy
    tempInput.select();
    document.execCommand('copy');

    // Remove temporary element
    document.body.removeChild(tempInput);

    // Show feedback
    copyBtn.classList.add('copied');

    // Reset after animation
    setTimeout(function() {
    copyBtn.classList.remove('copied');
}, 1000);
});
}

        // Add event listener for the Escape key
        document.addEventListener('keydown', function(e) {
            if (helpPopup && helpPopup.style.display === 'flex' && e.key === 'Escape') {
                helpPopup.style.display = 'none';
            }
        });


});
