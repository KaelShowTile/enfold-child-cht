function changeStripeText() {
    // Try multiple selectors for broader compatibility
    const selectors = [
        '#radio-control-wc-payment-method-options-stripe__label span',
        '.wc-block-components-radio-control__label span', // Alternative selector
        '[data-gateway-id="stripe"] .wc-block-components-radio-control__label' // Data attribute selector
    ];
    
    let stripeLabel;
    for (const selector of selectors) {
        stripeLabel = document.querySelector(selector);
        if (stripeLabel) break;
    }
    
    if (stripeLabel && /Stripe/i.test(stripeLabel.textContent.trim())) {
        stripeLabel.textContent = 'Credit Card/Debit Card';
        return true; // Success indicator
    }
    return false;
}

// Set up MutationObserver to detect when payment methods load
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.addedNodes && mutation.addedNodes.length > 0) {
            changeStripeText();
        }
    });
});

// Start observing when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        // Try immediately, then observe for changes
        if (!changeStripeText()) {
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    });
} else {
    // DOM already loaded, try immediately and observe for changes
    if (!changeStripeText()) {
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
}

// Also try on WooCommerce checkout updates
if (typeof jQuery !== 'undefined') {
    jQuery(document).on('updated_checkout', function() {
        setTimeout(changeStripeText, 100);
    });
}