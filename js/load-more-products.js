document.addEventListener('DOMContentLoaded', function() {
    const loadMoreButton = document.querySelector('.load-more-button');
    if (!loadMoreButton) return;

    loadMoreButton.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const button = this;
        const container = button.closest('.load-more-container');
        const spinner = container.querySelector('.loading-spinner');
        const productsGrid = document.querySelector('.products-grid');
        
        const nextPage = parseInt(productsGrid.getAttribute('data-page')) + 1;
        const maxPages = parseInt(productsGrid.getAttribute('data-max-pages'));
        const collectionId = productsGrid.getAttribute('data-collection-id');
        const queryArgs = JSON.parse(productsGrid.getAttribute('data-query-args'));
        
        // Show loading state
        button.disabled = true;
        button.style.display = 'none';
        spinner.style.display = 'flex';
        
        try {
            const response = await fetch(collection_ajax_data.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'load_more_collection_products',
                    page: nextPage,
                    collection_id: collectionId,
                    query_args: JSON.stringify(queryArgs),
                    nonce: collection_ajax_data.nonce
                })
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.data?.message || 'Server error');
            }
            
            if (data.success) {
                // Append new products
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.data.html;
                while (tempDiv.firstChild) {
                    productsGrid.appendChild(tempDiv.firstChild);
                }
                
                // Update page number and max pages
                productsGrid.setAttribute('data-page', nextPage);
                productsGrid.setAttribute('data-max-pages', data.data.max_pages);
                
                // Remove button if no more pages
                if (nextPage >= data.data.max_pages) {
                    container.remove();
                }
            } else {
                throw new Error(data.data?.message || 'Unknown error');
            }
        } catch (error) {
            console.error('AJAX error:', error);
            alert('Error: ' + error.message);
        } finally {
            button.disabled = false;
            button.style.display = 'initial';
            spinner.style.display = 'none';
        }
    });
});