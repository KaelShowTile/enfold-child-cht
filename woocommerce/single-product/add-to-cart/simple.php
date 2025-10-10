<?php
/**
 * Simple product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/simple.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

$m2_price = 0;
$box_price = 0;
$product_id = $product->get_id();

$thumbnail_id = $product->get_image_id();
$product_thumbnail = "";

if($thumbnail_id){
	$thumbnail_array = wp_get_attachment_image_src($thumbnail_id, 'woocommerce_thumbnail');
	if ($thumbnail_array){
        $product_thumbnail = $thumbnail_array[0];
    } 
	else{
        $product_thumbnail = "/wp-content/uploads/woocommerce-placeholder.png";
    }
}
else{
	$product_thumbnail = "/wp-content/uploads/woocommerce-placeholder.png";
}

//check backorder
$backorder_status = false;
if ( method_exists( $product, 'get_stock_status' ) ) {
	if($product->get_stock_status() === 'onbackorder'){
		$backorder_status = true;
	}
}

$product_name = $product->get_name();
$product_permalink = get_permalink($product_id);
$step_value = round(get_product_qty_data($product_id),2);
$product_suffix = get_product_qty_suffix($product_id);

if ($product->is_on_sale()) 
{
	$box_price = get_post_meta($product->get_id(), '_sale_price', true); 
}
else
{
	$box_price = get_post_meta($product->get_id(), '_regular_price', true); 
}
	
if($product_suffix == "m2")
{
	$m2_price = round(($box_price/$step_value),2);
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>

	<div id="tile-box-calculator-container">

		<div class="tile-box-calculator-inner grid-view">

			<div class="tile-box-calculator-qty">
				<p>Required Quantity:</p>

				<div class="m2-quantity">
					<input type="number" id="square-meter-needed" min="0" max="999" step="1" placeholder="0" />
					<?php if($product_suffix){
						if($product_suffix == "m2"){
							echo '<p id="quantity-suffix-box">box</p>';
						}else{
							echo '<p>' . $product_suffix . '</p>';
						}
					}?>
				</div>

				<?php if($product_suffix == "m2"){
					echo '<p class="boxes-explaination"> (' . $step_value .' m2 per box)</p>'; 
				} ?>

			</div>

			<?php if ( $backorder_status == false ) : ?>
				<div class="tile-box-total-price-container">
					<p id="output-total-price-label">Total Price:</p> 
					<h3 id="output-total-price">0</h3>
				</div>
			<?php else: ?>
				<div class="tile-box-total-price-container backorder-product">
					<h3>Please contact us for backorder</h5>
				</div>
			<?php endif; ?>

		</div>

		<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>

			<?php
			do_action( 'woocommerce_before_add_to_cart_quantity' );

			woocommerce_quantity_input(
				array(
					'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
					'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
					'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
				)
			);

			do_action( 'woocommerce_after_add_to_cart_quantity' );
			?>

			<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

		</form>

	</div>
	
<?php endif; ?>

	<div class="add-sample-container delivery-option-container v-align-middle">
			
		<div class="delivery-option-inner">
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/imgs/sample.svg">
			<div class="delivery-option-inner-right">
				<h5>Free samples</h5>
				<p class="add-sample-explaination">1 set = 3 tile samples (100x100mm)</p>
				<p class="add-sample-explaination">$13.5/set postage Australian wide</p>
			</div>
		</div>

		<?php cht_add_sample_btn(); ?>

	</div>

	<div class="add-sample-container delivery-option-container">
			
		<?php 

		$not_same_day_pickup = get_field('no_same_day_delivery', $product_id);
		if($not_same_day_pickup == true){ 

		echo '<div class="delivery-option-inner mobile-seperator">';
		echo '<img src="' . get_stylesheet_directory_uri() .'/imgs/package-svg.svg">';
		echo '<div class="delivery-option-inner-right">';
		echo '<h5>Pickup</h5>';

		$not_sameday_message = get_field('select_warehouse', $product_id);
		if($not_sameday_message){
			echo '<p class="add-sample-explaination">' . $not_sameday_message . '</p>';
		}else{
			echo '<p class="add-sample-explaination"><b>Available at: </b>Bankstown Warehouse</p>';
			echo '<p class="add-sample-explaination">Ready in 2-4 working days</p>';
		}

		echo '</div></div>';

		}else{ 

		echo '<div class="delivery-option-inner mobile-seperator">';
		echo '<img src="' . get_stylesheet_directory_uri() .'/imgs/package-svg.svg">';
		echo '<div class="delivery-option-inner-right">';
		echo '<h5 class="blue-title">Same-day Pickup</h5>';

		$sameday_message = get_field('select_warehouse_same_day', $product_id);
		if($sameday_message){
			echo '<p class="add-sample-explaination">' . $sameday_message . '</p>';
		}else{
			echo '<p class="add-sample-explaination"><b>Available at: </b>Bankstown Warehouse</p>';
		}
		
			
		echo '</div></div>';

		} ?>

		<div class="delivery-option-inner">
			<img src="<?php echo get_stylesheet_directory_uri(); ?>/imgs/delivery-svg.svg">
			<div class="delivery-option-inner-right">
				<h5>Fast delivery</h5>
				<p class="add-sample-explaination"><b>Sydney Metro: </b>3-5 working days</p>
				<p class="add-sample-explaination"><b>Regional NSW: </b>5-7 working days</p>
				<p class="add-sample-explaination"><b>Interstate: </b> 7-12 working days</p>
			</div>
		</div>

	</div>

	<div class="product-social-share-container">
		<?php echo do_shortcode("[av_social_share title='Share this product' style='' buttons='custom' share_facebook='aviaTBshare_facebook' share_twitter='aviaTBshare_twitter' share_pinterest='aviaTBshare_interest'  share_whatsapp='aviaTBshare_whatsapp' share_mail='aviaTBshare_mail' custom_class='' admin_preview_bg='' av_uid='av-670mc8']"); ?>
	</div>

<script>
	jQuery(document).ready(function($) {
	    var boxSize = <?php echo json_encode($step_value); ?>;
	    var boxprice = <?php echo json_encode($box_price); ?>; 
	    var suffix = <?php echo json_encode($product_suffix); ?>; 
		var unitPrice = <?php echo json_encode($m2_price); ?>;
	    const squareMetersInput = document.getElementById('square-meter-needed');	    

	    $('#square-meter-needed').on('input', function() 
	    {
	        var quantityNeed = parseFloat($(this).val());

	        if(suffix == "m2")
			{
				var squareMeters = (quantityNeed * boxSize).toFixed(2);
				var totalPrice = (quantityNeed * boxprice).toFixed(2);
				$('input[name="quantity"]').val(quantityNeed);

				if(quantityNeed == 0 )
				{
					$('.boxes-explaination').text( boxSize + ' m2 per box');
					$('input[name="quantity"]').val('0');
					$('#output-total-price').text('$0');
					$('#quantity-suffix-box').text('box');
				}
				else if(quantityNeed == 1)
				{
					$('.boxes-explaination').text( 'Equal to ' + squareMeters + ' m2 (' + quantityNeed + ' box)');
					$('#output-total-price').text('$' + totalPrice);
					$('#quantity-suffix-box').text('box');
				}
				else if(quantityNeed > 1)
				{
					$('.boxes-explaination').text( 'Equal to ' + squareMeters + ' m2 (' + quantityNeed + ' boxes)');
					$('#output-total-price').text('$' + totalPrice);
					$('#quantity-suffix-box').text('boxes');
				}
				else
				{
					$('.boxes-explaination').text( boxSize + ' m2 per box');
					$('input[name="quantity"]').val('0');
					$('#output-total-price').text('$0');
					$('#quantity-suffix-box').text('box');
				}
			}
			else
			{
				var totalPrice = quantityNeed * boxprice;
				if(boxSize == 1 )
				{
					$('input[name="quantity"]').val(quantityNeed);
					$('#output-total-price').text('$' + totalPrice.toFixed(2));
				}
			}

	    });

	    const $inputField = $('#square-meter-needed');
	    $inputField.on('blur', function() 
	    {
		    let inputValue = parseFloat($inputField.val());

			if (!isNaN(inputValue))
			{
				$inputField.val(inputValue);
			}
		    
		});

	});
</script>