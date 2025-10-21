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
                quantity: newQty,
                security: window.miniCartParams.update_nonce
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
                    alert(response.data.error || 'Error updating quantity');
                }
            },
            error: function(xhr) {
                console.error('Update error:', xhr.responseText);
                $input.val($input.data('old-value'));
                alert('Error updating quantity. Please try again.');
            },
            complete: function() {
                $('.woocommerce-mini-cart').removeClass('updating');
            }
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
        // Extract nonce correctly from the URL
        const urlParams = new URLSearchParams(e.target.search);
        const nonce = urlParams.get('_wpnonce');
        
        // AJAX call to remove item
        fetch(wc_add_to_cart_params.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'cht_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: nonce
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