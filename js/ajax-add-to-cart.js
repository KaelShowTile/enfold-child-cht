
jQuery(document).ready(function($) {
    // Handle view-cart clicks on archive pages
    $(document).on('click', '#view-cart-btn', function(e) {
        let product_url = $(this).data('product_url') || $(this).closest('form.cart').find('[name="view-cart-btn"]').val();
        $(location).attr('href',product_url);
    });
     
    // Handle add-to-cart clicks on archive pages
    $(document).on('click', '#cht-add-cart-btn', function(e) {
        handleAddToCart(e, $(this));
    });
    
    // Handle add-to-cart clicks on single product pages
    $(document).on('submit', 'form.cart', function(e) {
        e.preventDefault();
        handleAddToCart(e, $(this).find('[type="submit"]'));
    });
    
    // Shared add-to-cart handler
    function handleAddToCart(e, $button) {
        e.preventDefault();
        
        // Get product data
        let product_id = $button.data('product_id') || $button.closest('form.cart').find('[name="add-to-cart"]').val();
        let product_qty = $button.data('quantity') || $button.closest('form.cart').find('[name="quantity"]').val() || 1;

        /*meta pixil
        if (typeof fbq !== 'undefined' && typeof fbq === 'function'){
            fbq('track', 'AddToCart', {
                content_ids: [product_id], 
                content_type: 'product', 
            });
        }*/
        
        // For variable products
        if ($button.closest('form.variations_form').length) {
            const formData = new FormData($button.closest('form.cart')[0]);
            product_id = formData.get('product_id');
            product_qty = formData.get('quantity') || 1;
        }
        
        // Disable button during AJAX
        $button.prop('disabled', true).addClass('loading').text('Adding...');
        
        // AJAX call
        $.ajax({
            type: 'POST',
            url: wc_add_to_cart_params.ajax_url,
            data: {
                action: 'woocommerce_add_to_cart',
                product_id: product_id,
                quantity: product_qty,
                variation_id: $button.closest('form.variations_form').find('[name="variation_id"]').val() || 0
            },
            success: function(response) {
                if (response.error && response.product_url) {
                    window.location = response.product_url;
                    return;
                }
                
                $button.prop('disabled', false).removeClass('loading').text('Added!');
                $button.addClass('cht-added-item');
                
                // Trigger fragment refresh
                $(document.body).trigger('wc_fragment_refresh');

                //Boardcast the update so checkout page knows
                broadcastCartUpdate();
                
                // Show sidebar and switch tab
                setTimeout(function() {
                    $('#cht-cart-sidebar').addClass('open-sidebar');
                    
                    // Scroll to bottom anchor
                    const anchor = document.getElementById('cht-mini-cart-bottom');
                    if (anchor) {
                        anchor.scrollIntoView({
                            behavior: 'smooth',
                            block: 'end',
                            inline: 'nearest'
                        });
                    }
                }, 500);
            },
            error: function() {
                window.location = $button.closest('a').attr('href') || wc_add_to_cart_params.cart_url;
                $button.prop('disabled', false).removeClass('loading');
            }
        });
    }
    
    // Close sidebar handler
    $('.cht-sidebar-close').on('click', function() {
        $('#cht-cart-sidebar').removeClass('active');
    });

    window.handleAddSampleToCart = function() {
        $(document.body).trigger('wc_fragment_refresh');
        broadcastCartUpdate();
        const $sidebar = $('#cht-cart-sidebar');

        setTimeout(function(){
            $sidebar.addClass('open-sidebar');
        }, 500)
        
    };

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


