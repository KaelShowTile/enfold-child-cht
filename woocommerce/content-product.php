<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	/**
	 * Hook: woocommerce_before_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_open - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item' );

	$product_id = $product->get_id();
	$product_url = $product->get_permalink();
	$product_suffix = get_product_qty_suffix($product_id);
	$product_display_suffix = " ";
	$product_display_suffix = $product_suffix;
	$step_value = round(get_product_qty_data($product_id), 2);

	if($product_suffix){
		$product_display_suffix = $product_suffix;
	}

	//other sticker
	$sticker_url = get_field('product_icon_image', $product_id);
	if($sticker_url){
		echo '<span class="product-sticker">';
		echo '<img src=' . $sticker_url . ' alt="cheapestile-current-promotion">';
		echo '</span>';
	}else if($product->is_on_sale())
	{//on sale sticker
		$regular_price = $product->get_regular_price();
		$sale_price = $product->get_sale_price();
		$percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
		echo '<span class="product-discount-rate">-'. $percentage .'%</span>'; 
	}

	/**
	 * Hook: woocommerce_before_shop_loop_item_title.
	 *
	 * @hooked woocommerce_show_product_loop_sale_flash - 10
	 * @hooked woocommerce_template_loop_product_thumbnail - 10
	 */
	do_action( 'woocommerce_before_shop_loop_item_title' );

	echo '</a>';

	echo '<a href="' . esc_url( $product_url ) . '">';

	$full_title = get_the_title();

	echo '<h2 class="woocommerce-loop-product__title">' . get_the_title() . '</h2>';

	if ($product->is_type('simple')){
		$regular_price = $product->get_regular_price();

		if($product_suffix == "m2"){
			if($regular_price > 0 && $step_value > 0){
				$regular_price = round(($regular_price/$step_value), 2);
			}
			$product_display_suffix = "m<sup>2</sup>";
		}

		if ($product->is_on_sale()){
			$sale_price = $product->get_sale_price(); 
			
			if($product_suffix == "m2"){
				$sale_price = round(($sale_price/$step_value), 2);
			}

			echo '<span class="price"><del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $regular_price . '</bdi></span></del> <span class="screen-reader-text">Original price was: $' . $regular_price . '.</span>';
			echo '<ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $sale_price . '/' . $product_display_suffix . '</bdi></span></ins><span class="screen-reader-text">Current price is: ' . $sale_price . '.</span></span>';

		}else{
			echo '<span class="price"><span class="woocommerce-Price-amount amount no_sales_regular_price"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $regular_price . '/' . $product_display_suffix .'</bdi></span></span>';
		}

	} 

	echo '</a>';

	echo '<button id="view-cart-btn" data-product_url="' . $product_url . '">View Tile</button>';
	//echo '<button id="cht-add-cart-btn" data-product_id="' . $product_id . '" data-product_name="' . $full_title . '">Add to cart</button>';


	/**
	 * Hook: woocommerce_after_shop_loop_item.
	 *
	 * @hooked woocommerce_template_loop_product_link_close - 5
	 * @hooked woocommerce_template_loop_add_to_cart - 10
	 */
	//do_action( 'woocommerce_after_shop_loop_item' );
	?>
</li>
