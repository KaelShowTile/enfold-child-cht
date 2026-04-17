<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

$check_product_type;

if ($product->is_type('simple')) 
{
    $check_product_type = 1;
} 
elseif ($product->is_type('variable')) 
{
    $check_product_type = 2;
} 
else 
{
    $check_product_type = 0;
}

$m2_price = 0;
$product_id = $product->get_id();
$box_price = $product->get_regular_price();
$step_value = get_product_qty_data($product_id);
$product_suffix = get_product_qty_suffix($product_id);
$isTrader = false;

if(function_exists('glint_is_trader')){
	if(glint_is_trader()){
		$isTrader = true;
		$trader_price = get_post_meta( $product_id, '_trader_price', true );
		if ($trader_price) {
			$box_price = $trader_price;
		}
	}
}

if($check_product_type == 1) 
{
	if($product->is_on_sale() && $isTrader == false){
		echo '<div class="box-price-container">';
	}else{
		echo '<div class="box-price-container not-on-sale">';
	}
	
	if($product_suffix == "m2")
	{
		$m2_price = round(($box_price/$step_value),2);

		if($isTrader == true){
			echo '<p class="' . esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ) . '"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $box_price . '</bdi></span><span class="woocommerce-Price-amount amount">/box = </span></p>';
		}else{
			echo '<p class="' . esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ) . '">' . $product->get_price_html() . '<span class="woocommerce-Price-amount amount">/box = </span></p>';
		}
		
		if ($product->is_on_sale() && $isTrader == false) 
		{
			$m2_price_sales =  round((($product->get_sale_price())/$step_value),2);
			echo '<del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $m2_price . '<bdi></span></del>';
			echo '<p class="sales-square-price"><span class="woocommerce-Price-currencySymbol">$</span>' . $m2_price_sales . '<span class="box-suffix">/m<sup>2</sup></span></p>';

		}else{
			echo '<p><span class="woocommerce-Price-currencySymbol">$</span>' . $m2_price . '<span class="box-suffix">/m<sup>2</sup></span></p>';
		}

		echo '</div>';

	}else
	{
		echo '<p class="' . esc_attr( apply_filters( 'woocommerce_product_price_class', 'price' ) ) . '">' . $product->get_price_html() . '<span class="woocommerce-Price-amount amount">/ ' . $product_suffix . ' </span></p>';
		echo '</div>';
	}

	

}
