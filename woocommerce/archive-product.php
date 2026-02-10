<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

/**
 * Hook: woocommerce_shop_loop_header.
 *
 * @since 8.6.0
 *
 * @hooked woocommerce_product_taxonomy_archive_header - 10
 */
//do_action( 'woocommerce_shop_loop_header' );

if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	do_action( 'woocommerce_before_shop_loop' ); 
	
	$base_category = '';
	if (is_product_category()) {
		$current_category = get_queried_object();
		$base_category = $current_category->slug; // Get slug

		echo'<span class="hide-this-area" id="Zfind-current-category">' . $base_category . '</span>';
	}else{
		echo '<div class="term-description all-tiles"><h1>All Tiles</h1></div>';
	}

	?>

	<!-- Sub Menu -->
	<div class="archive-sub-menu-container">
		<?php 
		$menuCols = get_field('category_sub_menu', 'product_cat_' . $current_category->term_id);
		if( $menuCols ){
			foreach ($menuCols as $menuCols) {
				$menuItems = $menuCols['sub_menu_item_list'];
				echo '<div class="archive-sub-menu-container-col">';
				echo '<h5>' . $menuCols['sub_menu_column_name']. '</h5>';
				echo '<img class="dropdown-icon" src="' . get_stylesheet_directory_uri() . '/imgs/dropdown.svg">';
				echo '<div class="archive-sub-menu-container-dropdown">';
				echo '<ul>';
					if($menuItems){
						foreach ($menuItems as $menuItem) {
							echo '<a href="' . $menuItem['sub_menu_item_link'] . '"><li class="archive-sub-menu-container-item"><p>' . $menuItem['sub_menu_item_name'] . '</p><img src="' . $menuItem['sub_menu_item_icon'] .'"></li></a>'; 
						}
					}
				echo '</ul></div></div>';
			}
		}?>
	</div>
	
	<!-- filter -->
	<div class="archive-list-header">
		<div id="call-glint-filter">
			<img src="<?php echo get_site_url(); ?>/wp-content/uploads/2025/05/fliter.svg">
			<p>Fliters</p>
		</div>
		<div class="cht-product-fliter-title"><?php echo do_shortcode('[glint_product_filters]'); ?></div>
	</div>

	<div class="glint-products-container products columns-4">
		<?php woocommerce_product_loop_start();

		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				/**
				 * Hook: woocommerce_shop_loop.
				 */
				do_action( 'woocommerce_shop_loop' );

				wc_get_template_part( 'content', 'product' );
			}
		} 

		woocommerce_product_loop_end(); ?>

		<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/image-alt.js" id="archive-image-alt"></script>

	</div>

	<div class="glint-load-more-container" style="display: none; text-align: center; margin: 20px 0;">
		<button class="button glint-load-more"><?php _e('Load More', 'glint-product-filters'); ?></button>
	</div>

	<?php 
	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

$current_category = get_queried_object();

echo '<div class="container google-review-container">';
echo do_shortcode('[trustindex no-registration=google]');
echo '</div>';


if($current_category)
{
	$below_category_content = get_field('category_extra_description_text', 'product_cat_' . $current_category->term_id);
	    
	if ($below_category_content) 
	{
		echo '<div class="container">';
			echo '<div class="below-category-content">';
			echo $below_category_content;
			echo '</div>';
		echo '</div>';
	}
}


// Q&A
$qa_cate = get_field('cat_q&a', 'product_cat_' . $current_category->term_id);
$faq_schema = array();

if( $qa_cate ){

	$qa_shortcode_string = '<div class="container cate-qa-container"><h2>FAQ</h2>';

	foreach ($qa_cate as $row){
		$question = esc_html($row['cate_qna_question']);
		$answer = wp_filter_nohtml_kses($row['cate_qna_answer']);

		$qa_shortcode_string .= '<h5 class="cht-qna-question">Q: ' . $question . '</h5>';
		$qa_shortcode_string .= '<p class="cht-qna-answer">A: ' . $answer . '</p>';

		//generate qna schema
		$faq_schema[] = array(
			'@type' => 'Question',
			'name' => $question,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text' => $answer
			)
		);
	}

	$qa_shortcode_string .= '</div>';

	echo $qa_shortcode_string;


	//generate qna schema
	$schema_data = array(
		'@context' => 'https://schema.org',
		'@type' => 'FAQPage',
		'mainEntity' => $faq_schema
	);
	echo '<script type="application/ld+json">' . wp_json_encode($schema_data) . '</script>';
}
echo '</div>';


/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
