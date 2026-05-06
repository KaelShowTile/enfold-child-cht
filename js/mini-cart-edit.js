jQuery(function($) {
    // Update cart quantity
    $(document).on('change', '.mini-cart-qty', function() {
        var $input = $(this);
        var cartItemKey = $input.data('cart_item_key');
        var newQty = $input.val();
        
        // Show loading indicator
        $('.woocommerce-mini-cart').addClass('updating');
        
        $.ajax({
            type: 'POST',
            url: window.miniCartParams.ajax_url,
            data: {
                action: 'update_mini_cart_quantity',
                cart_item_key: cartItemKey,
                quantity: newQty
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update fragments
                    if (response.data.fragments) {
                        $.each(response.data.fragments, function(key, value) {
                            $(key).replaceWith(value);
                        });
                        // Trigger event for other scripts
                        $(document.body).trigger('wc_fragment_refresh');
                        broadcastCartUpdate();
                    }
                } else {
                    // Revert input value on error
                    $input.val($input.data('old-value'));
                    
                    var errMsg = 'Error updating quantity';
                    if (response.data && response.data.error) {
                        errMsg = response.data.error;
                    } else if (response.data && response.data.message) {
                        errMsg = response.data.message;
                    } else if (response.message) {
                        errMsg = response.message;
                    } else if (typeof response.data === 'string') {
                        errMsg = response.data;  wp_send_json_error('Please input right number.')
                    }
                    
                    alert(errMsg);
                }
            },
        });
    });

    // Store old value before change
    $(document).on('focus', '.mini-cart-qty', function() {
        $(this).data('old-value', $(this).val());
    });
});


document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove_from_cart_button')) {
        e.preventDefault();
        
        const cartItemKey = e.target.dataset.cartItemKey;

        // AJAX call to remove item
        fetch(wc_add_to_cart_params.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'cht_remove_cart_item',
                cart_item_key: cartItemKey
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                // Trigger fragment refresh
                jQuery(document.body).trigger('wc_fragment_refresh');
                
                // Broadcast to other tabs
                broadcastCartUpdate();
            } else {
                jQuery(document.body).trigger('wc_fragment_refresh');
                
                // Broadcast to other tabs
                broadcastCartUpdate();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function broadcastCartUpdate() {
        // Wait a bit to ensure server processed the cart update
        setTimeout(() => {
            const channel = new BroadcastChannel('cart_updates');
            channel.postMessage({ 
                action: 'cart_updated',
                timestamp: Date.now()
            });
            setTimeout(() => channel.close(), 1000);
        }, 500); // Wait 500ms before broadcasting
    }
});
