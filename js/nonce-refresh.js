(function($) {
    'use strict';
    
    let nonceRefresh = {
        init: function() {
            this.refreshInterval = 6 * 60 * 60 * 1000; // 6 hours in milliseconds
            this.refreshOnLoad = true;
            
            if (this.refreshOnLoad) {
                this.refreshAllNonces();
            }
            
            // Set up periodic refresh
            setInterval(() => {
                this.refreshAllNonces();
            }, this.refreshInterval);
            
            // Also refresh when user becomes active after being idle
            this.setupActivityRefresh();
        },
        
        refreshAllNonces: function() {
            // Find all script tags that contain nonces
            const scriptsWithNonces = [];

            $('script').each((index, element) => {
                const scriptContent = $(element).html() || element.textContent || '';

                // Check if this script contains nonce patterns
                if (this.hasNoncePatterns(scriptContent)) {
                    scriptsWithNonces.push({
                        element: element,
                        content: scriptContent
                    });
                }
            });

            // If we found scripts with nonces, make one request and update all
            if (scriptsWithNonces.length > 0) {
                this.requestNewNonce().then(newNonce => {
                    if (newNonce) {
                        scriptsWithNonces.forEach(script => {
                            this.replaceNoncesInScript(script.element, script.content, newNonce);
                        });
                    }
                });
            }
        },

        hasNoncePatterns: function(scriptContent) {
            // Common nonce patterns in WordPress
            const noncePatterns = [
                /nonce['"]?:\s*['"]([a-f0-9]+)['"]/gi,
                /_nonce['"]?:\s*['"]([a-f0-9]+)['"]/gi,
                /nonce['"]?\s*=>\s*['"]([a-f0-9]+)['"]/gi,
                /wp_rest['"]?:\s*['"]([a-f0-9]+)['"]/gi,
                /security['"]?:\s*['"]([a-f0-9]+)['"]/gi
            ];

            return noncePatterns.some(pattern => pattern.test(scriptContent));
        },
        
        replaceNoncesInScript: function(scriptElement, originalContent, newNonce) {
            let updatedContent = originalContent;

            // Replace various nonce patterns
            const replacePatterns = [
                [/nonce['"]?:\s*['"]([a-f0-9]+)['"]/gi, `nonce:'${newNonce}'`],
                [/_nonce['"]?:\s*['"]([a-f0-9]+)['"]/gi, `_nonce:'${newNonce}'`],
                [/nonce['"]?\s*=>\s*['"]([a-f0-9]+)['"]/gi, `nonce=>'${newNonce}'`],
                [/wp_rest['"]?:\s*['"]([a-f0-9]+)['"]/gi, `wp_rest:'${newNonce}'`],
                [/security['"]?:\s*['"]([a-f0-9]+)['"]/gi, `security:'${newNonce}'`]
            ];
            
            replacePatterns.forEach(([pattern, replacement]) => {
                updatedContent = updatedContent.replace(pattern, replacement);
            });
            
            // Update the script content
            if (scriptElement.innerHTML) {
                scriptElement.innerHTML = updatedContent;
            } else {
                scriptElement.textContent = updatedContent;
            }
        },
        
        requestNewNonce: function() {
            return new Promise((resolve) => {
                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'refresh_nonce'
                    },
                    success: function(response) {
                        if (response && response.success && response.data && response.data.nonce) {
                            resolve(response.data.nonce);
                        } else {
                            console.error('Failed to get new nonce - invalid response structure');
                            resolve(null);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error requesting new nonce:', status, error);
                        resolve(null);
                    }
                });
            });
        },
        
        setupActivityRefresh: function() {
            // Refresh nonces when user becomes active after being idle
            let idleTime = 0;
            
            const resetIdleTime = () => {
                idleTime = 0;
            };
            
            const incrementIdleTime = () => {
                idleTime++;
                if (idleTime > 30) { // 30 minutes idle
                    // User was idle, refresh nonces when they return
                    $(document).on('mousemove keydown click', () => {
                        this.refreshAllNonces();
                        $(document).off('mousemove keydown click');
                    });
                }
            };
            
            // Reset idle time on user activity
            $(document).on('mousemove keydown click scroll', resetIdleTime);
            
            // Increment idle time every minute
            setInterval(incrementIdleTime, 60000);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        nonceRefresh.init();
    });
    
})(jQuery);
