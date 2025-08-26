<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/mini-cart.php.
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

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

    <ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
        <?php
        do_action( 'woocommerce_before_mini_cart_contents' );

        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
            $product_suffix = get_product_qty_suffix($product_id);
            $step_value = round(get_product_qty_data($product_id), 2);

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>
                <li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
                    <?php
                    echo $thumbnail;

                    echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        'woocommerce_cart_item_remove_link',
                        sprintf(
                            '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s">&times;</a>',
                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            /* translators: %s is the product name */
                            esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                            esc_attr( $product_id ),
                            esc_attr( $cart_item_key ),
                            esc_attr( $_product->get_sku() ),
                            /* translators: %s is the product name */
                            esc_attr( sprintf( __( '&ldquo;%s&rdquo; has been removed from your cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) )
                        ),
                        $cart_item_key
                    );

                    if(empty($product_permalink)){
                        echo wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }
                    else{
                        echo '<a href="' . esc_url( $product_permalink ) . '">';
                        echo wp_kses_post( $product_name );
                        echo '</a>';
                    }

                    echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 

                    // Prepare quantity input attributes
                    $step_attr = $step_value > 0 ? $step_value : 'any';
                    $max_value = $_product->get_max_purchase_quantity() > 0 ? $_product->get_max_purchase_quantity() : '';
                    $min_value = 0;

                    if($product_suffix == "m2"){
                        $box_suffix = "box";
                        if($cart_item['quantity'] > 1){
                            $box_suffix = "boxes";
                        }
                        $square_meter = $cart_item['quantity'] * $step_value;
                        ?>
                        <div class="quantity-wrapper">
                            <span class="quantity">
                                <input type="number" 
                                    class="input-text qty text mini-cart-qty" 
                                    data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>" 
                                    value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" 
                                    min="<?php echo esc_attr( $min_value ); ?>" 
                                    max="<?php echo esc_attr( $max_value ); ?>" 
                                    step="<?php echo esc_attr( $step_attr ); ?>"
                                    aria-label="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>"
                                >
                            </span>
                            <span class="price-and-suffix"><?php echo sprintf( '&times; %s/Box', $product_price ); ?></span>
                        </div>
                        <span class="square-meter-span">Total <?php echo $square_meter; ?> m2(<?php echo $cart_item['quantity'] . ' ' . $box_suffix; ?>)</span>
                        <?php
                    } else {
                        ?>
                        <div class="quantity-wrapper">
                            <span class="quantity">
                                <input type="number" 
                                    class="input-text qty text mini-cart-qty" 
                                    data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>" 
                                    value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" 
                                    min="<?php echo esc_attr( $min_value ); ?>" 
                                    max="<?php echo esc_attr( $max_value ); ?>" 
                                    step="<?php echo esc_attr( $step_attr ); ?>"
                                    aria-label="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>"
                                >
                            </span>
							<?php if($product_suffix){ ?>
								<span class="price-and-suffix"><?php echo sprintf( '&times; %s/%s', $product_price, $product_suffix ); ?></span>
							<?php }else{ ?>
								<span class="price-and-suffix"> x <?php echo sprintf( $product_price); ?></span>
							<?php } ?>
                            
                        </div>
                        <?php
                    }
                    ?>
                </li>
                <?php
            }
        }

        do_action( 'woocommerce_mini_cart_contents' );
        ?>
    </ul>

    <!-- Nonce and AJAX URL for JavaScript -->
    <script type="text/javascript">
        window.miniCartParams = {
            ajax_url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
            update_nonce: '<?php echo wp_create_nonce( 'update-mini-cart' ); ?>'
        };
    </script>

    <p class="woocommerce-mini-cart__total total">
        <?php do_action( 'woocommerce_widget_shopping_cart_total' ); ?>
    </p>

    <?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

    <p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

    <?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

    <p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>