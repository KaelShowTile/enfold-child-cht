jQuery(document).ready(function($) 
{  
    // Toggle cart sidebar
    $('.cht-mini-cart-icon').on('click', function(e) 
    {
        e.preventDefault();
        $('#cht-cart-sidebar').addClass('open-sidebar');
    });
    
    // Close sidebar when clicking close button
    $('.cht-sidebar-close').on('click', function() 
    {
        $('#cht-cart-sidebar').removeClass('open-sidebar');
    });
    
    // Close sidebar when clicking other place
    $('.cart-sidebar-overlay').on('click', function() 
    {
        $('#cht-cart-sidebar').removeClass('open-sidebar');
    });

});