document.addEventListener('DOMContentLoaded', function() 
{
        document.querySelectorAll('.woocommerce-loop-product__link').forEach(link => {
            const title = link.querySelector('.woocommerce-loop-product__title');
            const img = link.querySelector('.thumbnail_container img');
            
            if(title && img) {
                // Set alt text from title
                img.alt = title.textContent.trim();
                
                // Fallback for initial page load (optional)
                img.setAttribute('data-original-alt', img.alt);
            }
        });
});