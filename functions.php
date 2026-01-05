<?php
//Child theme setting
add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_styles', PHP_INT_MAX);
function enqueue_child_theme_styles() 
{
  	wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

//Force collection template
add_filter('single_template', function($template) {
    global $post;
    if ($post->post_type === 'collection') {
        return locate_template(['single-collection.php', 'single.php']);
    }
    return $template;
});

// Images auto-crop quality
add_filter('wp_editor_set_quality', function($quality, $mime_type) {
    if ($mime_type === 'image/jpeg') return 60;     // JPEG
    if ($mime_type === 'image/webp') return 60;     // WebP
    if ($mime_type === 'image/png') return 6;       // PNG compression level
    return $quality; // Default for others
}, 10, 2);

//add no index for following url
add_action('wp_head', 'add_noindex_to_add_to_cart_urls');
function add_noindex_to_add_to_cart_urls() {
    if (isset($_GET['?add-to-cart'])) {
        echo '<meta name="robots" content="noindex, nofollow">';
    }
}

//change robots meta to noindex nofollow if the url has a woocommerce parametere 
add_filter( 'wp_robots', 'noindex_wc_product_pages_with_params', 20 );
function noindex_wc_product_pages_with_params( $robots ) 
{
    // List of WooCommerce parameters to check for
    $wc_params = array(
        'gclid',
        'orderby'
    );
        
    foreach ($wc_params as $param) 
    {
        if ( isset($_GET[$param]) || strpos($_SERVER['QUERY_STRING'], $param) !== false ) {
            $robots['noindex'] = true;
            $robots['nofollow'] = true;
            break;
        }
    }
    return $robots;
}

//change robots meta to noindex nofollow if the url has a woocommerce parametere 
add_filter( 'wp_robots', 'noindex_pages_with_unwanted_params', 20 );
function noindex_pages_with_unwanted_params( $robots ) 
{
    // Check if URL has query parameters
    if ( !empty($_SERVER['QUERY_STRING']) ) 
    {
        // List of parameters that should trigger noindex
        $unwanted_params = array(
            'fbclid', // Facebook click ID
            'add-to-cart',
            'quantity', 
            'variation_id', 
            'attribute_' ,
            'filter_' // fliter
        );
        
        foreach ($unwanted_params as $param) 
        {
            if ( isset($_GET[$param]) || strpos($_SERVER['QUERY_STRING'], $param) !== false ) {
                $robots['noindex'] = true;
                $robots['nofollow'] = true;
                break;
            }
        }
    }
    return $robots;
}

//Add Bootstrap
function add_bootstrap() 
{
  	// Load Bootstrap CSS
    wp_enqueue_style(
        'bootstrap-css', 
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'
    );

    // Load Bootstrap JS (includes Popper.js)
    wp_enqueue_script(
        'bootstrap-js', 
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', 
        array('jquery'), 
        '5.3.2', 
        true 
    );
}
add_action('wp_enqueue_scripts', 'add_bootstrap');

//Sub Menu Actions
function enqueue_sub_menu_script() 
{
  	wp_enqueue_script(
      	'custom-submenu-script',
      	get_stylesheet_directory_uri() . '/js/custom-submenu.js', 
      	array(), 
      	'1.0', 
      	true
  	);
}
add_action('wp_enqueue_scripts', 'enqueue_sub_menu_script');


//edit mini-cart js
function edit_mini_cart_enqueue_scripts() {
    wp_enqueue_script(
        'edit-mini-cart',
        get_stylesheet_directory_uri() . '/js/mini-cart-edit.js',
        array('jquery', 'wc-cart'),
        '1.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'edit_mini_cart_enqueue_scripts');

//Enqueue cart js
function enqueue_ajax_add_to_cart_js() {
    wp_enqueue_script('ajax-add-to-cart', get_stylesheet_directory_uri() . '/js/ajax-add-to-cart.js', array('jquery'), '1.0', true);
    wp_localize_script('ajax-add-to-cart', 'wc_add_to_cart_params', array(
        'ajax_url' => WC()->ajax_url(),
        'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
        'i18n_view_cart' => esc_html__('View cart', 'woocommerce'),
        'cart_url' => wc_get_cart_url()
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_add_to_cart_js');

// Add AJAX handler for mini-cart quantity updates
add_action('wp_ajax_update_mini_cart_quantity', 'update_mini_cart_quantity');
add_action('wp_ajax_nopriv_update_mini_cart_quantity', 'update_mini_cart_quantity');

function update_mini_cart_quantity() {
    // Validate input
    if (!isset($_POST['cart_item_key']) || !isset($_POST['quantity'])) {
        wp_send_json_error(array('error' => 'Missing parameters'));
        wp_die();
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = floatval($_POST['quantity']);

    // Validate quantity
    if ($quantity <= 0) {
        wp_send_json_error(array('error' => 'Invalid quantity'));
        wp_die();
    }

    // Update cart
    $cart = WC()->cart;
    $cart_item = $cart->get_cart_item($cart_item_key);

    if (!$cart_item) {
        wp_send_json_error(array('error' => 'Cart item not found'));
        wp_die();
    }

    // Check max quantity
    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
    $max_quantity = $_product->get_max_purchase_quantity();
    
    if ($max_quantity > 0 && $quantity > $max_quantity) {
        wp_send_json_error(array(
            'error' => sprintf(
                __('Maximum quantity is %s', 'woocommerce'),
                $max_quantity
            )
        ));
        wp_die();
    }

    // Perform update
    $passed_validation = apply_filters('woocommerce_update_cart_validation', true, $cart_item_key, $cart_item, $quantity);
    
    if ($passed_validation) {
        $cart->set_quantity($cart_item_key, $quantity);
        
        // Return updated fragments
        $data = array(
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
        );
        
        wp_send_json_success($data);
    } else {
        wp_send_json_error(array('error' => 'Validation failed'));
    }
    
    wp_die();
}


//ajax add to cart function
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'woocommerce_ajax_add_to_cart');

function woocommerce_ajax_add_to_cart() {
    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity);
    $product_status = get_post_status($product_id);

    //count the action
    $add_to_cart_count = (int)get_field("add_to_cart_count", 73852);
    $add_to_cart_count++;
    update_field("add_to_cart_count", $add_to_cart_count, 73852);

    if ($passed_validation && WC()->cart->add_to_cart($product_id, $quantity) && 'publish' === $product_status) {
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        if ('yes' === get_option('woocommerce_cart_redirect_after_add')) 
        {
            wc_add_to_cart_message(array($product_id => $quantity), false);
        }

        WC_AJAX::get_refreshed_fragments();
        //Update total value under cart icon

    } 
    else 
    {
        $data = array(
            'error' => true,
            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id)
        );
        wp_send_json($data);
    }

    wp_die();
}

// Count AJAX add-to-cart actions
add_action('woocommerce_add_to_cart', 'count_add_to_cart_ajax_calls', 10, 6);
function count_add_to_cart_ajax_calls($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    if (wp_doing_ajax() && isset($_REQUEST['action']) && $_REQUEST['action'] === 'woocommerce_add_to_cart') {
        $post_id = 21; // cart page ID
        $current_count = (int) get_field('add_to_cart_count', $post_id);
        $new_count = $current_count + 1;
        
        update_field('add_to_cart_count', $new_count, $post_id);
    }
}

// Enqueue refresh mini-cart script 
add_action('wp_enqueue_scripts', function() {
    if (!is_admin()) {
        wp_enqueue_script('wc-cart-fragments');
    }
});

// Force hard cropping for large size
add_action('init', function() {
    update_option('large_size_w', 600);
    update_option('large_size_h', 600);
    update_option('large_crop', 1); // 1 enables hard cropping
});


// Add custom image size to Enfold's image selection
add_filter('avf_ajax_preview_image_sizes', 'add_custom_size_to_enfold');
function add_custom_size_to_enfold($sizes) {
    // Add your custom size to the array
    $sizes['square-large'] = array(
        'width' => 600, 
        'height' => 600,
        'crop' => true
    );
    return $sizes;
}

// Make the size selectable in backend
add_filter('avf_ajax_select_image_size', 'register_custom_size_in_enfold');
function register_custom_size_in_enfold($size_select) {
    $size_select['square-large'] = 'Square Large (600x600)';
    return $size_select;
}

// Menu caching function
function get_cached_mega_menu() {
    $cache_key = 'cached_mega_menu_html';
    
    // Try to get cached menu
    $menu_html = get_transient($cache_key);
    
    // If no cache exists, generate it
    if (false === $menu_html) {
        $submenu_page = get_page_by_path('mega-menu');
        
        if ($submenu_page) {
            // Process the content but skip unnecessary filters
            $menu_html = apply_filters('mega_menu_content', $submenu_page->post_content);
            
            // Cache for 12 hours (adjust as needed)
            set_transient($cache_key, $menu_html, 12 * HOUR_IN_SECONDS);
        }
    }
    
    return $menu_html;
}

// Custom filter to process only essential content
add_filter('mega_menu_content', function($content) {
    // Process shortcodes
    $content = do_shortcode($content);
    
    // Process blocks if using Gutenberg
    if (function_exists('do_blocks')) {
        $content = do_blocks($content);
    }
    
    // Add only essential filters
    $content = wptexturize($content);
    $content = convert_smilies($content);
    $content = wpautop($content);
    $content = shortcode_unautop($content);
    
    return $content;
});

// Clear cache when the mega menu page is updated
add_action('save_post', function($post_id) {
    if (get_post_field('post_name', $post_id) === 'mega-menu') {
        delete_transient('cached_mega_menu_html');
    }
});

//Disable comments
add_action('admin_init', function () {
    // Redirect any user trying to access comments page
    global $pagenow;
    
    if ($pagenow === 'edit-comments.php') {
        wp_safe_redirect(admin_url());
        exit;
    }

    // Remove comments metabox from dashboard
    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

    // Disable support for comments and trackbacks in post types
    foreach (get_post_types() as $post_type) 
	{
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

// Close comments on the front-end
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);

// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);

// Remove comments page in menu
add_action('admin_menu', function () 
{
    remove_menu_page('edit-comments.php');
});

// Remove comments links from admin bar
add_action('init', function () 
{
    if (is_admin_bar_showing()) {
        remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
    }
});

//overwrite tab section js
function enfold_child_override_scripts() 
{
	wp_deregister_script('avia-tab-section');
        
	// Re-register with child theme path
	wp_register_script(
		'avia-tab-section',
		get_stylesheet_directory_uri() . '/config-templatebuilder/avia-template-builder/assets/js/avia-tab-section.js',
		array('jquery', 'avia-builder-js'),
		'1.0',
		true
	);
	
	wp_enqueue_script('avia-tab-section');
}
add_action('wp_enqueue_scripts', 'enfold_child_override_scripts', 999); 

//sort product by price on new arrive category
add_filter( 'woocommerce_default_catalog_orderby', 'custom_default_sort_by_category' );

function custom_default_sort_by_category( $sort_by ) {
    // Check if we are on a product category page with the slug 'new-arrivals'
    if ( is_product_category( 'new-arrivals' ) ) {
        return 'date'; // 'date' results in Newness (New to Old)
    }
    
    return $sort_by;
}
/**
 * Shortcode to display single product in archive style
 * Usage: [single_product_archive id="123"]
 */
add_shortcode('single_product_archive', 'display_single_product_as_archive');
function display_single_product_as_archive($atts) {
    // Shortcode attributes
    $atts = shortcode_atts(array(
        'id' => '', 
        'class' => '' 
    ), $atts, 'single_product_archive');
    
    if (empty($atts['id'])) {
        return '<p class="error">Please specify a product ID</p>';
    }
    
    // Start output buffering
    ob_start();
    
    // Setup WooCommerce query
    $args = array(
        'post_type' => 'product',
        'post__in' => array($atts['id']),
        'posts_per_page' => 1
    );
    
    // Custom query
    $products = new WP_Query($args);
    
    // Modify WooCommerce hooks temporarily
    add_action('woocommerce_before_shop_loop_item', function() {
        echo '<div class="product-archive-wrapper">';
    });
    
    add_action('woocommerce_after_shop_loop_item', function() {
        echo '</div>';
    });
    
    // Display products
    if ($products->have_posts()) :
        echo '<div class="single-product-archive-style ' . esc_attr($atts['class']) . '">';
        woocommerce_product_loop_start();
        
        while ($products->have_posts()) : $products->the_post();
            wc_get_template_part('content', 'product');
        endwhile;
        
        woocommerce_product_loop_end();
        echo '</div>';
    else :
        echo '<p>Product not found</p>';
    endif;
    
    wp_reset_postdata();
    return ob_get_clean();
}

//ajax load more button for collecion page
// Register AJAX handlers
add_action('wp_ajax_load_more_collection_products', 'handle_load_more_products');
add_action('wp_ajax_nopriv_load_more_collection_products', 'handle_load_more_products');
function handle_load_more_products() {
    // Get and sanitize parameters
    $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $collection_id = isset($_POST['collection_id']) ? absint($_POST['collection_id']) : 0;
    $query_args = isset($_POST['query_args']) ? json_decode(stripslashes($_POST['query_args']), true) : array();
    
    // Validate inputs
    if ($page < 1 || $collection_id < 1) {
        wp_send_json_error(array('message' => 'Invalid parameters'), 400);
        wp_die();
    }
    
    // Set up query
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 20, // Must match AJAX handler setting
        'paged'          => $page,
        'orderby'        => isset($query_args['orderby']) ? sanitize_text_field($query_args['orderby']) : 'date',
        'meta_key'       => isset($query_args['meta_key']) ? sanitize_text_field($query_args['meta_key']) : '',
        'order'          => isset($query_args['order']) ? sanitize_text_field($query_args['order']) : 'ASC',
        'post_status'    => 'publish',
    );
    
    // Merge tax queries
    if (!empty($query_args['tax_query']) && is_array($query_args['tax_query'])) {
        $args['tax_query'] = $query_args['tax_query'];
    }
    
    // Merge meta queries
    if (!empty($query_args['meta_query']) && is_array($query_args['meta_query'])) {
        $args['meta_query'] = $query_args['meta_query'];
    }
    
    // Run query
    $products = new WP_Query($args);
    
    // Start output buffer
    ob_start();
    
    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            wc_get_template_part('content', 'product');
        }
    }
    
    $html = ob_get_clean();
    
    // Send response
    wp_send_json_success(array(
        'html' => $html,
        'max_pages' => $products->max_num_pages
    ));
    
    // Always die in AJAX functions
    wp_die();
}

// Localize AJAX URL
add_action('wp_enqueue_scripts', function() {
    wp_localize_script('jquery', 'wpAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
});

// Enqueue scripts
add_action('wp_enqueue_scripts', 'localize_cht_scripts');
function localize_cht_scripts() 
{
    //sidebar shopping cart
    wp_enqueue_script('cart-sidebar', get_stylesheet_directory_uri() . '/js/sidebar-menu.js', array('jquery'), '1.0', true);

    //collection page load-more
    if (is_singular('tile-collection')) { 
        wp_enqueue_script(
            'load-more-products',
            get_stylesheet_directory_uri() . '/js/load-more-products.js',
            array(), 
            filemtime(get_stylesheet_directory() . '/js/load-more-products.js'),
            true 
        );
        
        wp_localize_script(
            'load-more-products',
            'collection_ajax_data',
            array(
                'ajaxurl' => admin_url('admin-ajax.php')
            )
        );
    }
}

//email BCC
function add_bcc_to_woocommerce_emails($headers, $email_id, $order) 
{
    $bcc_emails = array( 
        'marketing@showtile.com.au',
        'ken@showtile.com.au',
        'sales@showtile.com.au'
    );

    if (in_array($email_id, array('new_order', 'cancelled_order', 'failed_order'))) 
    {
        $headers .= 'Bcc: ' . implode(', ', $bcc_emails) . "\r\n";
    }

    return $headers;
}
add_filter('woocommerce_email_headers', 'add_bcc_to_woocommerce_emails', 10, 3);

//Add BCC to enqurire form(wpforms)
add_filter( 'wp_mail', function ( $args ) {
    // Check if this is a WPForms email
    if ( isset( $args['wpforms'] ) && $args['wpforms']['form_id'] == 19280 ) {
        // Add BCC email addresses (correct format)
        $args['headers'][] = 'Bcc: sales@showtile.com.au';
        $args['headers'][] = 'Bcc: marketing@showtile.com.au';
    }
    return $args;
}, 10, 1 );

//auto select parent categories
function auto_select_parent_category($post_id, $post, $update) {
    // Only run for products
    if ('product' !== $post->post_type) {
        return;
    }

    // Get all assigned categories
    $terms = wp_get_post_terms($post_id, 'product_cat', array('fields' => 'ids'));
    
    if (empty($terms) || is_wp_error($terms)) {
        return;
    }

    // Find all parent categories for each selected term
    $parent_terms = array();
    foreach ($terms as $term_id) {
        $ancestors = get_ancestors($term_id, 'product_cat');
        if (!empty($ancestors)) {
            $parent_terms = array_merge($parent_terms, $ancestors);
        }
    }

    // Merge with existing terms and remove duplicates
    if (!empty($parent_terms)) {
        $all_terms = array_unique(array_merge($terms, $parent_terms));
        wp_set_post_terms($post_id, $all_terms, 'product_cat');
    }
}

add_action('save_post_product', 'auto_select_parent_category', 10, 3);


// Disable WooCommerce product feeds
add_action( 'init', function() {
    remove_action( 'do_feed_rss2', 'do_feed_rss2', 10, 1 );
    remove_action( 'do_feed_rss2_comments', 'do_feed_rss2_comments', 10, 1 );
    remove_action( 'do_feed_atom', 'do_feed_atom', 10, 1 );
    remove_action( 'do_feed_rdf', 'do_feed_rdf', 10, 1 );
    remove_action( 'do_feed_rss', 'do_feed_rss', 10, 1 );
});


//disable enfold extra divs & sorting function for WooCommerce pages  
add_action( 'init', 'remove_enfold_action' );

function remove_enfold_action() {
    remove_action( 'woocommerce_before_shop_loop_item_title', 'avia_shop_overview_extra_header_div', 20 );
    remove_action( 'woocommerce_after_shop_loop_item_title',  'avia_close_div', 1000 );
    remove_action( 'woocommerce_after_shop_loop_item_title',  'avia_close_div', 1001 );
    remove_action( 'woocommerce_after_shop_loop_item_title',  'avia_close_div', 1002 );
    //remove_action( 'woocommerce_before_shop_loop', 'avia_woocommerce_frontend_search_params', 20);
    remove_action( 'woocommerce_before_single_product_summary', 'avia_add_summary_div', 25 );
    remove_action( 'woocommerce_after_single_product_summary',  'avia_close_div', 3 );
}

//hide out of stock product on arhcive template(except admin user)
function custom_hide_out_of_stock_products( $q ) {
    if ( ! is_admin() && is_product_category() ) {
        $meta_query = $q->get( 'meta_query' ) ?: [];
        $meta_query[] = [
            'key'     => '_stock_status',
            'value'   => 'outofstock',
            'compare' => '!='
        ];
        $q->set( 'meta_query', $meta_query );
    }
}
add_action( 'woocommerce_product_query', 'custom_hide_out_of_stock_products' );

add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
    // Update mini cart in Products tab
    ob_start();
    echo '<div class="widget_shopping_cart_content">';
    woocommerce_mini_cart();
    echo '</div>';
    $fragments['div.cht-min-cart-inner .widget_shopping_cart_content'] = ob_get_clean();
    
    return $fragments;
});

add_filter('woocommerce_get_item_data', 'add_custom_product_meta', 10, 2);
function add_custom_product_meta($item_data, $cart_item) {

    $show_meta = false;
    
    //Detect traditional WooCommerce pages
    if (function_exists('is_cart') && is_cart()) $show_meta = true;
    if (function_exists('is_checkout') && is_checkout()) $show_meta = true;
    
    //Detect WooCommerce Blocks (Cart/Checkout blocks)
    if (did_action('woocommerce_blocks_cart_enqueue_data')) $show_meta = true;
    if (did_action('woocommerce_blocks_checkout_enqueue_data')) $show_meta = true;
    
    if ($show_meta == true) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $product_suffix = get_product_qty_suffix($product_id);
        
        if ($product_suffix == "m2"){
            $product_quantity = $cart_item['quantity'];
            $step_value = round(get_product_qty_data($product_id), 2);
            $Total_m2 = $product_quantity * $step_value;
            $item_data[] = [  
                'display' => '<p id="cart-quantity">' . $step_value . ' m2 per box, total ' . $Total_m2 . 'm2.</p>' 
            ];
        }
    }

    return $item_data;
}


// Remove default added-to-cart notices
add_filter('wc_add_to_cart_message_html', '__return_false');

// Prevent redirect to cart page
add_filter('woocommerce_add_to_cart_redirect', function() {
    return false;
});

add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
    // Mini cart update
    ob_start();
    echo '<div class="widget_shopping_cart_content">';
    woocommerce_mini_cart();
    echo '</div>';
    $fragments['div.cht-min-cart-inner .widget_shopping_cart_content'] = ob_get_clean();
    
    // Cart total update
    ob_start();
    echo '<span class="cht-cart-total">' . WC()->cart->get_cart_total() . '</span>';
    $fragments['.cht-cart-total'] = ob_get_clean();
    
    return $fragments;
});


// Make phone field required
add_filter('woocommerce_billing_fields', 'make_phone_field_required');
function make_phone_field_required($fields) {
    $fields['billing_phone']['required'] = true;
    return $fields;
}

// Add custom validation
add_action('woocommerce_checkout_process', 'validate_phone_field');
function validate_phone_field() {
    if (isset($_POST['billing_phone']) && empty($_POST['billing_phone'])) {
        wc_add_notice(__('Please enter a valid phone number.', 'woocommerce'), 'error');
    }
}

//sample product shipping method
add_filter('woocommerce_cart_shipping_packages', 'split_cart_by_shipping_class');
function split_cart_by_shipping_class($packages) {
    $new_packages = [];
    $sample_package = $other_package = [
        'contents' => [],
        'contents_cost' => 0,
        'applied_coupons' => WC()->cart->applied_coupons,
        'destination' => [
            'country' => WC()->customer->get_shipping_country(),
            'state' => WC()->customer->get_shipping_state(),
            'postcode' => WC()->customer->get_shipping_postcode(),
            'city' => WC()->customer->get_shipping_city(),
            'address' => WC()->customer->get_shipping_address(),
            'address_2' => WC()->customer->get_shipping_address_2()
        ]
    ];

    foreach (WC()->cart->get_cart() as $item_key => $item) {
        $product = $item['data'];
        $shipping_class = $product->get_shipping_class();
        
        if ($shipping_class === 'sample-product') {
            $sample_package['contents'][$item_key] = $item;
            $sample_package['contents_cost'] += $item['line_total'];
        } else {
            $other_package['contents'][$item_key] = $item;
            $other_package['contents_cost'] += $item['line_total'];
        }
    }

    if (!empty($sample_package['contents'])) {
        $new_packages[] = $sample_package;
    }
    if (!empty($other_package['contents'])) {
        $new_packages[] = $other_package;
    }

    return !empty($new_packages) ? $new_packages : $packages;
}

// Register custom shipping method
add_action('woocommerce_shipping_init', 'sample_product_shipping_init');
function sample_product_shipping_init() {
    if (!class_exists('WC_Shipping_Sample_Product')) {
        class WC_Shipping_Sample_Product extends WC_Shipping_Method {
            public function __construct($instance_id = 0) {
                $this->id = 'sample_product_shipping';
                $this->instance_id = absint($instance_id);
                $this->method_title = __('Sample Product Shipping', 'text-domain');
                $this->method_description = __('Custom shipping for sample products', 'text-domain');
                $this->supports = ['shipping-zones', 'instance-settings'];
                $this->enabled = 'yes';
                
                $this->init();
            }

            public function init() {
                $this->init_form_fields();
                $this->init_settings();
                $this->title = $this->get_option('title', __('Sample Shipping', 'text-domain'));
                add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
            }

            public function init_form_fields() {
                $this->form_fields = [
                    'title' => [
                        'title' => __('Method Title', 'text-domain'),
                        'type' => 'text',
                        'default' => __('Sample Shipping', 'text-domain')
                    ]
                ];
            }

            public function calculate_shipping($package = []) {
                $total_quantity = 0;
                
                foreach ($package['contents'] as $item) {
                    $total_quantity += $item['quantity'];
                }

                $units = ceil($total_quantity / 3);
                $cost = 13.50 + (7 * max(0, ($units - 1)));

                $this->add_rate([
                    'id' => $this->id,
                    'label' => $this->title,
                    'cost' => $cost,
                    'package' => $package,
                ]);
            }
        }
    }
}

add_filter('woocommerce_shipping_methods', 'add_sample_product_shipping_method');
function add_sample_product_shipping_method($methods) {
    $methods['sample_product_shipping'] = 'WC_Shipping_Sample_Product';
    return $methods;
}

// Force custom shipping method for sample products
add_filter('woocommerce_package_rates', 'force_sample_shipping_method', 100, 2);
function force_sample_shipping_method($rates, $package) {
    $is_sample_package = false;
    
    foreach ($package['contents'] as $item) {
        $shipping_class = $item['data']->get_shipping_class();
        if ($shipping_class === 'sample-product') {
            $is_sample_package = true;
            break;
        }
    }

    if ($is_sample_package) {
        // Remove all other shipping methods
        foreach ($rates as $rate_id => $rate) {
            if ('sample_product_shipping' !== $rate->method_id) {
                unset($rates[$rate_id]);
            }
        }
    } else {
        // Remove sample shipping from non-sample packages
        foreach ($rates as $rate_id => $rate) {
            if ('sample_product_shipping' === $rate->method_id) {
                unset($rates[$rate_id]);
            }
        }
    }

    return $rates;
}

//exclude sample category from archive pages
add_action('pre_get_posts', 'exclude_category_from_shop');
function exclude_category_from_shop($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('product')) {
        $tax_query = array(
            array(
                'taxonomy' => 'product_cat',
                'terms' => array('sample-tiles','tile-products'), 
                'field' => 'slug',
                'operator' => 'NOT IN'
            )
        );
        
        $query->set('tax_query', $tax_query);
    }
}

//don't load recaptcha if there is no contact form
function prohibit_google_recaptcha( $prohibited ){
  global $post;
  if( ! $post instanceof WP_Post ){ return $prohibited; }
  
  $content = Avia_Builder()->get_post_content( $post->ID );
  $prohibited = ( false !== strpos( $content, '[contact-form-7 ' ) || false !== strpos( $content, '[av_contact ' ) ) ? false : true;
  
  return $prohibited;
}
add_filter( 'avf_load_google_recaptcha_api_prohibited', 'prohibit_google_recaptcha', 10, 1 );

//don't load map js
function ava_disable_gmap() {
  add_filter('avf_load_google_map_api', function($load_google_map_api) {
    $load_google_map_api = false;
    return $load_google_map_api;
  },10,1);
  add_filter('avia_google_maps_widget_load_api', function($load_google_map_api) {
    $load_google_map_api = false;
    return $load_google_map_api;
  },10,1);
}
add_action('after_setup_theme', 'ava_disable_gmap');

//refresh sub-total
add_filter('woocommerce_add_to_cart_fragments', 'cht_update_cart_fragments');
function cht_update_cart_fragments($fragments) {
    // Update cart subtotal amount
    $fragments['.cht-cart-total-amount'] = '<span class="cht-cart-total-amount">' . WC()->cart->get_cart_subtotal() . '</span>';
    
    // Optional: Update cart count if you have it
    $fragments['.cart-count'] = '<span class="cart-count">' . WC()->cart->get_cart_contents_count() . '</span>';
    
    return $fragments;
}

//remove cart item
add_action('wp_ajax_cht_remove_cart_item', 'cht_remove_cart_item');
add_action('wp_ajax_nopriv_cht_remove_cart_item', 'cht_remove_cart_item');
function cht_remove_cart_item() {
    if (!isset($_POST['cart_item_key'])) {
        wp_send_json_error('Invalid request');
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    // Remove the cart item
    $result = WC()->cart->remove_cart_item($cart_item_key);
    
    if ($result) {
        wp_send_json_success('Item removed');
    } else {
        wp_send_json_error('Failed to remove item');
    }
}

//Update product image alt text to product title
add_filter('wp_get_attachment_image_attributes', 'change_attachment_image_attributes', 20, 2);
function change_attachment_image_attributes($attr, $attachment) {
    // Get the post parent of the attachment
    $parent = get_post_field('post_parent', $attachment);
    // Get the post type of the parent
    $type = get_post_field('post_type', $parent);
    
    // Check if the attachment belongs to a product
    if ($type == 'product') {
        // Get the product title
        $title = get_post_field('post_title', $parent);
        // Set alt and title attributes to the product title
        $attr['alt'] = $title;
        $attr['title'] = $title;
    }
    return $attr;
}

//remove "Free" label when no shipping method available
add_filter( 'woocommerce_cart_shipping_method_full_label', 'change_free_shipping_label_to_empty', 10, 2 );
function change_free_shipping_label_to_empty( $label, $method ) {
    if ( $method->cost == 0 ) {
        // Remove the shipping cost part (including "Free")
        $label = preg_replace( '/:\s*.*$/','', $label );
    }
    return $label;
}



?>
