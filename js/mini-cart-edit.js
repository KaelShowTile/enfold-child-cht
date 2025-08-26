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