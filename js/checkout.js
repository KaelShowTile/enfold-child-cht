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

// Listen to broadcast
let reloadTimeout;
const channel = new BroadcastChannel('cart_updates');

channel.onmessage = function(event) {
    if (event.data.action === 'cart_updated') {
        console.log('Cart updated broadcast received');
        
        // Clear any pending reload
        if (reloadTimeout) clearTimeout(reloadTimeout);
        
        // Wait a moment to ensure server is ready, then reload
        reloadTimeout = setTimeout(() => {
            // Use cache-busting reload
            window.location.reload(true);
        }, 1000);
    }
};