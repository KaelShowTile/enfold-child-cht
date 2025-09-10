document.addEventListener('DOMContentLoaded', function() {
    // Target the Stripe payment method label
    const stripeLabel = document.querySelector('#radio-control-wc-payment-method-options-stripe__label span');
    
    if (stripeLabel && stripeLabel.textContent.trim() === 'Stripe') {
        stripeLabel.textContent = 'Credit Cards';
    }
});