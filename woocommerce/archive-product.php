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

		echo'<span class="hide-this-area" id="find-current-category">' . $base_category . '</span>';
	}
	
	?>
	

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

echo '<div class="container cate-qa-container">';


$qa_cate = get_field('cat_q&a', 'product_cat_' . $current_category->term_id);

if( $qa_cate ){

	$qa_shortcode_string = "[av_toggle_container faq_markup='faq_markup' initial='0' mode='accordion' sort='' styling='' colors='' font_color='' background_color='' border_color='' toggle_icon_color='' colors_current='' font_color_current='' toggle_icon_color_current='' background_current='' background_color_current='' background_gradient_current_direction='vertical' background_gradient_current_color1='#000000' background_gradient_current_color2='#ffffff' background_gradient_current_color3='' hover_colors='' hover_font_color='' hover_background_color='' hover_toggle_icon_color='' size-toggle='' av-desktop-font-size-toggle='' av-medium-font-size-toggle='' av-small-font-size-toggle='' av-mini-font-size-toggle='' size-content='' av-desktop-font-size-content='' av-medium-font-size-content='' av-small-font-size-content='' av-mini-font-size-content='' heading_tag='' heading_class='' alb_description='' id='' custom_class='' template_class='' element_template='' one_element_template='' av_uid='av-md2dbkdv' sc_version='1.0' admin_preview_bg='']";

	echo "<h2>Q&A</h2>";

	foreach ($qa_cate as $row){
		$question = esc_html($row['cate_qna_question']);
		$answer = esc_html($row['cate_qna_answer']);

		$qa_shortcode_string = $qa_shortcode_string . "[av_toggle title='" . $question . "' title_open='' tags='' title_pos='' slide_speed='' custom_id='' aria_collapsed='' aria_expanded='' element_template='' one_element_template='' av_uid='' sc_version='1.0' ]" . $answer . "[/av_toggle]";
	}

	$qa_shortcode_string = $qa_shortcode_string . "[/av_toggle_container]";
	echo do_shortcode($qa_shortcode_string);
}
echo '</div>';


/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
