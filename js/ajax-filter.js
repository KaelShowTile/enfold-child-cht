jQuery(document).ready(function($) 
{
    function filterProducts() {

        $('#fliter-loading-icon').show();

        var selectedPrices = [];
        $('#fliter-price-option:checked').each(function() {
            selectedPrices.push($(this).val());
        });

        var selectedSize = [];
        $('#fliter-size-option:checked').each(function() {
            selectedSize.push($(this).val());
        });

        var selectedcolors = [];
        $('#fliter-color-option:checked').each(function() {
            selectedcolors.push($(this).val());
        });

        var selectedfinish = [];
        $('#fliter-finish-option:checked').each(function() {
            selectedfinish.push($(this).val());
        });

        var currentParentSlug = $('#current-parent-slug').text();
        var currentChildSlug = $('#current-child-slug').text();

        var data = {
            action: 'filter_products',
            price_range: selectedPrices,
            size: selectedSize,
            color: selectedcolors,
            finish: selectedfinish,
            current_parent: currentParentSlug,
            current_child: currentChildSlug,
        };

        //console.log(data);

        $.ajax({
            url: ajaxfilter.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                $('.products.columns-4').html(response);
            },
            complete: function() {
                // Hide loading icon after the AJAX request is complete
                $('#fliter-loading-icon').hide();
            }
        });
    }

    $(document).on('change', '#fliter-price-option, #fliter-size-option, #fliter-color-option, #fliter-finish-option', function() {
        //console.log('Checkbox changed:'); // Debugging line
        filterProducts();
    });

});