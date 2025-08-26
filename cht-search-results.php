<?php
get_header();

// Modify search query parameters
global $wp_query;

$plugin_main_path = wp_normalize_path(WP_PLUGIN_DIR) . '/gto-ajax-search/includes/main.php';

if (file_exists($plugin_main_path)) {
    require_once $plugin_main_path;
}

$args = array_merge($wp_query->query_vars, [
    'post_type' => 'product',
    'posts_per_page' => 20
]);

function get_all_searchable_item(){
    //setup cache
    $transient_key = 'gto_searchable_data';
    $data = get_transient($transient_key);

    $data = array(
        'products' => array(),
        'categories' => array()
    );

    //load class external class 
    $gto_ajax_search = new GTO_AJAX_Search();
    $reflector = new ReflectionClass($gto_ajax_search);

    //excluded products
    $excluded_method = $reflector->getMethod('get_expanded_excluded_items');
    $excluded_method->setAccessible(true); // Make private method accessible
    $excluded = $excluded_method->invoke($gto_ajax_search);
    
    //priority settings
    $setting_method = $reflector->getMethod('get_expanded_priority_items');
    $setting_method->setAccessible(true); // Make private method accessible
    $priority = $setting_method->invoke($gto_ajax_search);
    $highest_priority = $priority['highest'];
    $lowest_priority = $priority['lowest'];
    
    //get custom post type
    global $wpdb;
    $plugin_table_name = $wpdb->prefix . 'glint_wc_ajax_search';
    $custom_types = $wpdb->get_var($wpdb->prepare(
            "SELECT option_value FROM {$plugin_table_name} WHERE option_name = %s",
            "custom_post_type"
    ));

    //get product post type
    $post_types = ['product'];

    //merge together
    if ($custom_types) {
        $additional_types = array_map('trim', explode("\n", $custom_types));
        $post_types = array_merge($post_types, $additional_types);
    }

    // Query for products and custom post types
    $args = array(
        'post_type' => $post_types,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    );

    $product_query = new WP_Query($args);
    $product_ids = $product_query->posts;

    //get products
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product) continue;
        
        $product_key = 'product:' . $product_id;
        // Skip explicitly excluded products
        if (in_array($product_key, $excluded)) continue;

        $search_components = [$product->get_name()];

        //attributes
        $product_attributes = $product->get_attributes();
        $attribute_data = [];

        foreach ($product_attributes as $attribute_key => $product_attributes) {
            // Get attribute name (label)
            $attribute_name = wc_attribute_label($attribute_key);
            
            if ($product_attributes->is_taxonomy()) {
                // For global attributes (taxonomy-based)
                $terms = wc_get_product_terms(
                    $product_id, 
                    $attribute_key, 
                    ['fields' => 'names']
                );
                $attribute_value = implode(', ', $terms);
            } else {
                // For custom attributes
                $attribute_value = implode(', ', $product_attributes->get_options());
            }
            
            $attribute_data[$attribute_name] = $attribute_value;
            $search_components[] = $attribute_value;
        }

        //get category name
        $category_ids = $product->get_category_ids();
        $product_categories = [];

        foreach ($category_ids as $category_id) {
            $term = get_term($category_id, 'product_cat');
            
            if ($term && !is_wp_error($term)) {
                $search_components[] = $term->name;
            } 
        }
        
        // Add priority flags
        $is_high_priority = in_array($product_key, $highest_priority);
        $is_low_priority = in_array($product_key, $lowest_priority);

        $search_string = implode(' ', $search_components);
        
        $data['products'][] = array(
            'title' => $product->get_name(),
            'url' => $product->get_permalink(),
            'image_url' => wp_get_attachment_image_url($product->get_image_id(), 'large') ?: wc_placeholder_img_src('large'),
            'id' => $product_id,
            'priority' => $is_high_priority ? 'high' : ($is_low_priority ? 'low' : 'normal'),
            'search_string' => $search_string
        );
    }

    // Get all categories
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'number' => 0
    ));

    if (!empty($categories) && !is_wp_error($categories)) {
        foreach ($categories as $category) {
            $category_key = 'category:' . $category->term_id;
            
            // Skip excluded categories
            if (in_array($category_key, $excluded)) continue;
            
            // Add priority flags
            $is_high_priority = in_array($category_key, $highest_priority);
            $is_low_priority = in_array($category_key, $lowest_priority);
            
            $data['categories'][] = array(
                'title' => $category->name,
                'url' => get_term_link($category),
                'count' => $category->count,
                'id' => $category->term_id,
                'priority' => $is_high_priority ? 'high' : ($is_low_priority ? 'low' : 'normal')
            );
        }
    }

    // Cache for 12 hours
    set_transient($transient_key, $data, 12 * HOUR_IN_SECONDS);

    return $data;

}

function gto_perform_search($query){

    $data = get_all_searchable_item();
    $keywords = array_filter(array_map('trim', explode(' ', sanitize_text_field($query))));
    
    $results = [
        'products' => [],
        'categories' => []
    ];

    if (empty($keywords)) return $results;

    // Search products (AND logic)
    foreach ($data['products'] as $product) {
        $search_string = strtolower($product['search_string']);
        $all_keywords_match = true;

        foreach ($keywords as $keyword) {
            $normalized_keyword = strtolower($keyword);
            if (strpos($search_string, $normalized_keyword) === false) {
                $all_keywords_match = false;
                break;
            }
        }

        if ($all_keywords_match) {
            unset($product['search_string']); // Remove helper field
            $results['products'][] = $product;
        }
    }

    foreach ($data['categories'] as $category) {
        $category_name = strtolower($category['title']);
        $matched = false;
        
        foreach ($keywords as $keyword) {
            $normalized_keyword = strtolower($keyword);
            if (strpos($category_name, $normalized_keyword) !== false) {
                $matched = true;
                break;
            }
        }
        
        if ($matched) {
            $results['categories'][] = $category;
        }
    }

    return $results;

}

function sort_search_results(&$results) {
    // Define priority order
    $priority_order = ['high' => 0, 'normal' => 1, 'low' => 2];
    
    // Sort products
    usort($results['products'], function($a, $b) use ($priority_order) {
        return $priority_order[$a['priority']] - $priority_order[$b['priority']];
    });
    
    // Sort categories
    usort($results['categories'], function($a, $b) use ($priority_order) {
        return $priority_order[$a['priority']] - $priority_order[$b['priority']];
    });
}


// Create custom query for pagination
$search_query = get_search_query();

//remove "tile(s)" from search string
$remove_words = ['tile', 'tiles'];
$pattern = '/\b(' . implode('|', array_map('preg_quote', $remove_words)) . ')\b/i';
$clean_query = preg_replace($pattern, '', $search_query);
$clean_query = preg_replace('/\s+/', ' ', trim($clean_query));

//if string return null after removing, return to the original
if ($clean_query === '') {
    $clean_query = $search_query;
}

$results = gto_perform_search($clean_query);
sort_search_results($results);

echo '<div class="stretch_full container_wrap alternate_color light_bg_color empty_title  title_container">';
echo '<div class="container">';

if ($results){
    echo '<div class="cht-custom-results">';
    echo '<h1 class="search-result-title">Tile Search Results: "' . $search_query . '"</h1>';

    if (!empty($results['categories'])) {
        echo '<div class="search-result-cates">';
        // Display categories
        foreach ($results['categories'] as $category) {
            echo '<a href="' . esc_url($category['url']) . '"><div class="category-result priority-' . esc_attr($category['priority']) . '">';
            echo '<h3>' . esc_html($category['title']) . '</h3>';
            echo '<span>(' . esc_html($category['count']) . ' products)</span>';
            echo '</div></a>';
        }
        echo '</div>';
    }

    //display products
    if (!empty($results['products'])){
        $product_ids = wp_list_pluck($results['products'], 'id');
        
        // Create query with custom ordering
        $args = array(
            'post_type'      => 'product',
            'post__in'       => $product_ids,
            'orderby'        => 'post__in', // Maintain our priority order
            'posts_per_page' => count($product_ids),
            'meta_query'     => [
                [
                    'key'     => '_stock_status',
                    'value'   => 'instock',
                    'compare' => '=',
                ]
            ]
        );
        
        global $wp_query;
        $wp_query = new WP_Query($args); 
        
        // Display products using WooCommerce templates
        woocommerce_product_loop_start();
        
        while (have_posts()) : the_post();
            global $product;
            
            // Get current product's priority
            $current_id = $product->get_id();
            $priority = 'normal';
            
            foreach ($results['products'] as $result_product) {
                if ($result_product['id'] == $current_id) {
                    $priority = $result_product['priority'];
                    break;
                }
            }
            
            // Add priority class to product
            add_filter('woocommerce_post_class', function($classes) use ($priority) {
                $classes[] = 'priority-' . $priority;
                return $classes;
            });
            
            // Use standard product template
            wc_get_template_part('content', 'product');
            
            // Remove filter after each product
            remove_all_filters('woocommerce_post_class');
            
        endwhile;
        
        woocommerce_product_loop_end();
        
        // Reset main query
        wp_reset_query();
    }
    

    /* Add pagination
    echo '<nav class="pagination">';
    echo paginate_links([
        'base'    => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format'  => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total'   => $search_query->max_num_pages
    ]);
    echo '</nav>'; */
    
    echo '</div>'; // .cht-custom-results
}else{
    echo '<p class="no-results">No products found</p>';
}


// Reset post data
wp_reset_postdata();

echo '</div>'; // .container
echo '</div>'; // .stretch_full

get_footer();