<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

$product_tile = $product->get_title();

//load lightbox assets
?>
<!-- meta pixil event code 
<script type="text/javascript" >fbq('track', 'ViewContent', {content_ids: ['<?php the_ID(); ?>'], content_type: 'product', });</script>-->

<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/cht-lightbox.js" id="cht-lightbox"></script>
<link rel="stylesheet" id="lightbox-css" href="<?php echo get_stylesheet_directory_uri(); ?>/css/lightbox.css" type="text/css" media="all">

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<?php
	/**
	 * Hook: woocommerce_before_single_product_summary.
	 *
	 * @hooked woocommerce_show_product_sale_flash - 10
	 * @hooked woocommerce_show_product_images - 20
	 */
	// disable woocommerce deflaut action for slider
	// do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="row product-img-and-des">

		<div class="col-lg-8 col-12 col-md-8 product-images">
	
			<div class="saferi-product-gallery-container" id="mix-video-product-gallery">

				<?php // Get the gallery image IDs
				$attachment_ids = $product->get_gallery_image_ids();
				
				// Get the main image ID
				$main_image_id = $product->get_image_id();

				//get video id
				$product_id = get_the_ID();
				$vwg_video_url = get_post_meta($product_id, 'vwg_video_url', true);

				$lightbox_string = '<div id="cht-lightbox-modal" class="modal"><span class="close cursor" onclick="closeModal()">&times;</span><div class="modal-content">';
				$lightbox_order = 1;

				// Add the main image to the gallery array if it exists
				if ( $main_image_id ) 
				{
					array_unshift( $attachment_ids, $main_image_id );
				}

				if ( $attachment_ids || $vwg_video_url) 
				{
					echo '<div class="cht-product-gallery">';
					echo '<figure class="woocommerce-product-gallery-container">';

					
					if ($vwg_video_url) 
					{
						$video_data = maybe_unserialize($vwg_video_url);
						
						if (is_array($video_data)) 
						{
							foreach ($video_data as $video) 
							{
								if (isset($video['video_url'])) 
								{
									$video_url = $video['video_url']; ?>
													
									<div data-woocommerce_gallery_thumbnail_url="" data-woocommerce_thumbnail_url="" data-thumb-alt="" data-vwg-video="1" class="woocommerce-product-gallery-image vwg_show_first">

										<a href="<?php echo $video_url ?>" class="woocommerce-product-gallery__vwg_video lightbox-added">

											<div data-setup="{}" playsinline="true" muted="true" loop="true" autoplay="true" preload="auto" class="video-js vwg_video_js vwg_video_js_1-dimensions vjs-controls-enabled vjs-workinghover vjs-v7 vjs-has-started vjs-user-inactive vjs-paused" id="vwg_video_js_1" tabindex="-1" role="region" lang="en-au" aria-label="Video Player">
											
												<video id="vwg_video_js_1_html5_api" class="vjs-tech" preload="auto" autoplay="" loop="" muted="muted" playsinline="playsinline" data-setup="{}" tabindex="-1" role="application">
													<source src="<?php echo $video_url ?>" type="video/mp4">
												</video>

												<img id="img" autoplay="autoplay" preload="metadata" playsinline muted="on" class="fullscreen-video" loop="loop" src="<?php echo $video_url ?>">
																
											</div>

										</a>
									
									</div>
									
									<?php }
							}
						} ?>

						<script>
							var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
											
							if (isSafari) 
							{
								document.getElementById("vwg_video_js_1_html5_api").style.display = "none";
							} else {
								document.getElementById("img").style.display = "none";
							}
						</script>

					<?php }
					
					if($attachment_ids)
					{
						foreach ( $attachment_ids as $attachment_id ) 
						{
							$image_medium = wp_get_attachment_image_src($attachment_id, 'large');
							$image_full = wp_get_attachment_image_src($attachment_id, 'full');

							if($image_medium)
							{
								echo '<div class="woocommerce-product-gallery-image">';
								echo '<a class="lightbox-added">';
								echo '<img decoding="async" src="'. esc_url( $image_medium[0] ) .'"  alt="'. $product_tile .'" onclick="openModal();currentSlide(' . $lightbox_order . ')">';
								echo '</a>';
								echo '</div>';

								$lightbox_string = $lightbox_string . '<div class="cht-product-slider"><img src="' . esc_url( $image_full[0] ) . '" style="width:100%"></div>' ;
								$lightbox_order = $lightbox_order + 1;
							}
						}
					}
					echo '</figure>';
					echo '</div>';
				} else {
					// Fallback if no images are found
					echo '<div class="woocommerce-product-gallery__image--placeholder">';
					echo '<img src="' . wc_placeholder_img_src() . '" alt="' . esc_attr__( 'Placeholder', 'woocommerce' ) . '">';
					echo '</div>';
				} 
				
				$lightbox_string = $lightbox_string . '<a class="prev" onclick="plusSlides(-1)">&#10094;</a><a class="next" onclick="plusSlides(1)">&#10095;</a></div></div>';
				?>	
			</div>

			<?php echo $lightbox_string; ?>

		</div>

		<div class="col-lg-4 col-12 col-md-4 text-left">
			<?php
			/**
			 * Hook: woocommerce_single_product_summary.
			 *
			 * @hooked woocommerce_template_single_title - 5
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_price - 10
			 * @hooked woocommerce_template_single_excerpt - 20
			 * @hooked woocommerce_template_single_add_to_cart - 30
			 * @hooked woocommerce_template_single_meta - 40
			 * @hooked woocommerce_template_single_sharing - 50
			 * @hooked WC_Structured_Data::generate_product_data() - 60
			 */
			//do_action( 'woocommerce_single_product_summary' );
			?>
			<div class = "cht-product-description-container">

				<?php

				echo '<div class="product-summary">';

				if ($product->is_on_sale()) 
				{
					echo '<div class="cht-on-sale-container">';
					echo '<span>Sale!</span>';
					echo '</div>';
				}
				the_title( '<h1 class="product_title entry-title">', '</h1>' );

				wc_get_template_part( 'single-product/price' );

				$sticker_url = get_field('product_icon_image', $product_id);
				if($sticker_url){
					echo '<span class="product-page-sticker">';
					echo '<img src=' . $sticker_url . ' alt="cheapestile-current-promotion">';
					echo '</span>';
				}

				wc_get_template_part( 'single-product/short-description' );

				if (function_exists('display_linked_product'))
				{
					echo display_linked_product(get_the_ID());
				}
				
				woocommerce_template_single_add_to_cart();

				echo '</div>'; 

				?>

			</div>

		</div>

	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	
	$category_ids = $product->get_category_ids();
	$has_qa = false;
	$shortcode_string = "[av_toggle_container faq_markup='faq_markup' initial='0' mode='accordion' sort='' styling='' colors='' font_color='' background_color='' border_color='' toggle_icon_color='' colors_current='' font_color_current='' toggle_icon_color_current='' background_current='' background_color_current='' background_gradient_current_direction='vertical' background_gradient_current_color1='#000000' background_gradient_current_color2='#ffffff' background_gradient_current_color3='' hover_colors='' hover_font_color='' hover_background_color='' hover_toggle_icon_color='' size-toggle='' av-desktop-font-size-toggle='' av-medium-font-size-toggle='' av-small-font-size-toggle='' av-mini-font-size-toggle='' size-content='' av-desktop-font-size-content='' av-medium-font-size-content='' av-small-font-size-content='' av-mini-font-size-content='' heading_tag='' heading_class='' alb_description='' id='' custom_class='' template_class='' element_template='' one_element_template='' av_uid='av-md2dbkdv' sc_version='1.0' admin_preview_bg='']";

	echo '<div class="single-product-qna">';
	echo '<h2>FAQ</h2>';
	
	foreach ($category_ids as $cat_id) {
		$qa_cate = get_field('product_q&a', 'product_cat_' . $cat_id);	

		if( $qa_cate ){
			foreach ($qa_cate as $row){
				$has_qa = true;
				$question = esc_html($row['product_qna_question']);
				$answer = esc_html($row['product_qna_answer']);
				$shortcode_string .= "[av_toggle title='" . $question . "' title_open='' tags='' title_pos='' slide_speed='' custom_id='' aria_collapsed='' aria_expanded='' element_template='' one_element_template='' av_uid='' sc_version='1.0' ]" . $answer . "[/av_toggle]";
			}
		}
	}

	$shortcode_string .= "[/av_toggle_container]";
	
	if($has_qa == true){
		echo do_shortcode($shortcode_string);
	}

	
	echo '</div>';

	?>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>