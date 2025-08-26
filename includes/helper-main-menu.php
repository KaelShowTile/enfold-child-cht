<?php
/**
 * Included for non blank templates in header.php
 *
 *
 * @since ????
 */
if( ! defined( 'ABSPATH' ) ) {  exit;  }    // Exit if accessed directly


global $avia_config;

$responsive = avia_get_option( 'responsive_active' ) != 'disabled' ? 'responsive' : 'fixed_layout';
$headerS = avia_header_setting();

$social_args = array(
					'outside' => 'ul',
					'inside' => 'li',
					'append' => ''
				);

$icons = ! empty( $headerS['header_social'] ) ? avia_social_media_icons( $social_args, false ) : '';
$alternate_menu_id = ! empty( $headerS['alternate_menu'] ) && is_numeric( $headerS['alternate_menu'] ) && empty( $headerS['menu_display'] ) ? $headerS['alternate_menu'] : false;

/**
 * For sidebar menus this filter allows to activate alternate menus - are disabled by default
 *
 * @since 4.5
 * @param int|false $alternate_menu_id
 * @param array $headerS
 * @return int|false
 */
$alternate_menu_id = apply_filters( 'avf_alternate_mobile_menu_id', $alternate_menu_id, $headerS );

if( isset( $headerS['disabled'] ) )
{
	return;
}

$shrink_factor = avia_get_option( 'header_shrinking_factor' );
if( empty( $shrink_factor ) )
{
	$shrink_factor = '50';
}

/**
 *
 * @since 5.5
 * @param int $shrink_factor
 * @param array $headerS
 * @return int
 */
$shrink_factor = apply_filters( 'avf_header_shrink_factor', $shrink_factor, $headerS );

$header_data = "data-av_shrink_factor='{$shrink_factor}'";

$aria_label = avia_get_option( 'header_aria_label', '' );

if( $aria_label != '' )
{
	$aria_label = 'aria-label="' . esc_attr( $aria_label ) . '"';
}

/**
 * @since 6.0.3
 * @param string $aria_label
 * @param string $context
 * @param WP_Post|null $current_post
 * @return string
 */
$aria_label = apply_filters( 'avf_aria_label_for_header', $aria_label, __FILE__, get_post() );

?>

<header id='header' class='all_colors header_color <?php avia_is_dark_bg('header_color'); echo " {$headerS['header_class']}";?>' <?php echo "{$aria_label} {$header_data}"; avia_markup_helper( array( 'context' => 'header' ) );?>>

<?php

//subheader, only display when the user chooses a social header
if( $headerS['header_topbar'] == true )
{
?>
		<div id='header_meta' class='container_wrap container_wrap_meta <?php echo avia_header_class_string( array( 'header_social', 'header_secondary_menu', 'header_phone_active' ), 'av_' ); ?>'>

			      <div class='container'>
			      <?php
			            /*
			            *	display the themes social media icons, defined in the wordpress backend
			            *   the avia_social_media_icons function is located in includes/helper-social-media-php
			            */
						$nav = '';

						//display icons
			            if( strpos( $headerS['header_social'], 'extra_header_active' ) !== false )
						{
							echo $icons;
						}

						//display navigation
						if( strpos( $headerS['header_secondary_menu'], 'extra_header_active' ) !== false )
						{
			            	//display the small submenu
			                $avia_theme_location = 'avia2';
			                $avia_menu_class = $avia_theme_location . '-menu';
			                $args = array(
										'theme_location'	=> $avia_theme_location,
										'menu_id'			=> $avia_menu_class,
										'container_class'	=> $avia_menu_class,
										'items_wrap'		=> '<ul role="menu" class="%2$s" id="%1$s">%3$s</ul>',
										'fallback_cb'		=> '',
										'container'			=> '',
										'echo'				=> false
			                );

			                $nav = wp_nav_menu( $args );
						}

						if( ! empty( $nav ) || apply_filters( 'avf_execute_avia_meta_header', false ) )
						{
							//	add screenreader rules
							$nav = str_replace('<li ', '<li role="menuitem" ', $nav );

							echo "<nav class='sub_menu' " . avia_markup_helper( array( 'context' => 'nav', 'echo' => false ) ) . '>';
							echo	$nav;
									do_action( 'avia_meta_header' ); // Hook that can be used for plugins and theme extensions (currently: the wpml language selector)
							echo '</nav>';

						}

						//phone/info text
						$phone = $headerS['header_phone_active'] != '' ? $headerS['phone'] : '';
						$phone_class = ! empty( $nav ) ? 'with_nav' : '';
						if( $phone )
						{
							echo "<div class='phone-info {$phone_class}'><div>" . do_shortcode( $phone ) . '</div></div>';
						}
			        ?>
			      </div>
		</div>

<?php }



	$output = '';
	$temp_output = '';
	$icon_beside = '';

	if( $headerS['header_social'] == 'icon_active_main' && empty( $headerS['bottom_menu'] ) )
	{
		$icon_beside = ' av_menu_icon_beside';
	}

?>
		<div  id='header_main' class='container_wrap container_wrap_logo'>

			<div class="header-icons-container">

				<ul class="header-icons-list">

					<li id="cht-member">
						<a href="<?php echo get_site_url();?>/my-account/">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>
						</a>
					</li>

					<li id="cht-wishlist">
					<a href="<?php echo get_site_url();?>/wishlist/">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16"><path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/></svg>
						</a>
					</li>

					<li id="cht-mini-cart">
						<a href="/#" class="cht-mini-cart-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l.84 4.479 9.144-.459L13.89 4zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/></svg>

							<span class="cht-cart-total-amount">
					         <?php echo WC()->cart->get_cart_subtotal(); ?>
					      </span>
						</a>
					</li>

				</ul>

			</div>

        <?php
        /*
        * Hook that can be used for plugins and theme extensions (currently:  the woocommerce shopping cart)
        */
        do_action( 'ava_main_header' );

        if( $headerS['header_position'] != 'header_top' )
		{
			do_action( 'ava_main_header_sidebar' );
		}

				 $output .= "<div class='container av-logo-container'>";

					$output .= "<div class='inner-container'>";

						/*
						*	display the theme logo by checking if the default logo was overwritten in the backend.
						*   the function is located at framework/php/function-set-avia-frontend-functions.php in case you need to edit the output
						*/
						$addition = false;
						if( ! empty( $headerS['header_transparency'] ) && ! empty( $headerS['header_replacement_logo'] ) )
						{
							if( ! avia_SVG()->exists_svg_file( $headerS['header_replacement_logo'], $headerS['header_replacement_logo_id'] ) )
							{
								$resp = array(
											0			=> $headerS['header_replacement_logo'],
											'srcset'	=> $headerS['header_replacement_logo_srcset'],
											'sizes'		=> $headerS['header_replacement_logo_sizes']
										);

								$resp = Av_Responsive_Images()->html_attr_image_src( $resp, true );

								$class = avia_SVG()->is_svg( $headerS['header_replacement_logo'] ) ? 'alternate avia-img-svg-logo' : 'alternate avia-standard-logo';

								$addition = "<img {$resp} class='{$class}' alt='{$headerS['header_replacement_logo_alt']}' title='{$headerS['header_replacement_logo_title']}' />";
							}
							else
							{
								$fallback_title = __( 'Logo', 'avia_framework' );
								$addition = avia_SVG()->get_logo_html( $headerS['header_replacement_logo_id'], $headerS['header_replacement_logo'], avia_SVG()->get_header_logo_aspect_ratio(), $fallback_title );
							}
						}

						//glint mobile mega menu
						$output .= do_shortcode('[glint_mobile_menu_button]');

						$output .= avia_logo( AVIA_BASE_URL . 'images/layout/logo.png', $addition, 'span', true );

						$output .= do_shortcode('[gto_ajax_search]');

						$output .= "<div class='header-contact-container'>";
						
						$output .= "<a href='tel:0297904273'><p class='phone-no'>(02) 9790 4273</p></a>";

						$output .= "<a href='https://maps.app.goo.gl/QNMhF8dF1MLamAqbA' target='_blank' rel='nofollow noopener'><p class='header-address'>65 Canterbury Rd, Bankstown NSW 2200</p></a>";

						$output .= "</div>";

						if( ! empty( $headerS['bottom_menu'] ) )
						{
							ob_start();
							do_action( 'ava_before_bottom_main_menu' ); // todo: replace action with filter, might break user customizations
							$output .= ob_get_clean();
						}

						if( $headerS['header_social'] == 'icon_active_main' && ! empty( $headerS['bottom_menu'] ) )
						{
							$output .= $icons;
						}

						/*
						*	display the main navigation menu
						*   modify the output in your wordpress admin backend at appearance->menus
						*/
						    if( $headerS['bottom_menu'] )
						    {
							    $output .= '</div>';
								$output .= '</div>';

								if( ! empty( $headerS['header_menu_above'] ) )
								{
									$avia_config['temp_logo_container'] = "<div class='av-section-bottom-logo header_color'>{$output}</div>";
									$output = '';
								}

								$output .= "<div id='header_main_alternate' class='container_wrap'>";
								$output .= "<div class='container'>";
							}

							$avia_theme_location = 'avia';
							$avia_menu_class = $avia_theme_location . '-menu';

						    $main_nav = "<nav class='main_menu' data-selectname='" . __( 'Select a page', 'avia_framework' ) . "' " . avia_markup_helper( array( 'context' => 'nav', 'echo' => false ) ) . '>';

							$args = array(
										'theme_location'	=> $avia_theme_location,
										'menu_id' 			=> $avia_menu_class,
										'menu_class'		=> 'menu av-main-nav',
										'container_class'	=> $avia_menu_class.' av-main-nav-wrap'.$icon_beside,
										'items_wrap'        => '<ul role="menu" class="%2$s" id="%1$s">%3$s</ul>',
										'fallback_cb' 		=> 'avia_fallback_menu',
										'echo' 				=>	false,
										'walker' 			=> new avia_responsive_mega_menu()
									);

						        $wp_main_nav = wp_nav_menu( $args );
						        $main_nav .= $wp_main_nav;


						    /*
						    * Hook that can be used for plugins and theme extensions
						    */
						    ob_start();
						    do_action( 'ava_inside_main_menu' ); // todo: replace action with filter, might break user customizations
						    $main_nav .= ob_get_clean();

						    if( $icon_beside )
						    {
							    $main_nav .= $icons;
						    }

						    $main_nav .= '</nav>';

							/**
							 * Allow to modify or remove main menu for special pages
							 *
							 * @since 4.1.3
							 */

							//Enfold Default mobile menu
							//$output .= apply_filters( 'avf_main_menu_nav', $main_nav );

						    /*
						    * Hook that can be used for plugins and theme extensions
						    */
						    ob_start();
						    do_action( 'ava_after_main_menu' ); // todo: replace action with filter, might break user customizations
							$output .= ob_get_clean();

					/* inner-container */		
			        $output .= '</div>';
					
		        /* end container */
		        $output .= ' </div> ';

		   		//output the whole menu
		        echo $output;


		   ?>

		<!-- end container_wrap-->
		</div>
<?php
		/**
		 * Add a hidden container for alternate mobile menu
		 *
		 * We use the same structure as main menu to be able to use same logic in js to build burger menu
		 *
		 * @added_by Günter
		 * @since 4.5
		 */
		$out_alternate = '';
		$avia_alternate_location = 'avia_alternate';
		$avia_alternate_menu_class = $avia_alternate_location . '_menu';

		if( false !== $alternate_menu_id && is_nav_menu( $alternate_menu_id ) )
		{
			$out_alternate .= '<div id="avia_alternate_menu_container" style="display: none;">';

			$alternate_nav =	"<nav class='main_menu' data-selectname='" . __( 'Select a page', 'avia_framework' ) . "' " . avia_markup_helper( array( 'context' => 'nav', 'echo' => false ) ) . '>';

			$args = array(
							'menu'				=> $alternate_menu_id,
							'menu_id' 			=> $avia_alternate_menu_class,
							'menu_class'		=> 'menu av-main-nav',
							'container_class'	=> $avia_alternate_menu_class.' av-main-nav-wrap',
							'fallback_cb' 		=> 'avia_fallback_menu',
							'echo' 				=> false,
							'walker' 			=> new avia_responsive_mega_menu()
						);

			$wp_nav_alternate = wp_nav_menu( $args );

			/**
			 * Hook that can be used for plugins and theme extensions
			 *
			 * @since 4.5
			 * @return string
			 */
			$alternate_nav .=		apply_filters( 'avf_inside_alternate_main_menu_nav', $wp_nav_alternate, $avia_alternate_location, $avia_alternate_menu_class );

			$alternate_nav .=	'</nav>';

			/**
			 * Allow to modify or remove alternate menu for special pages.
			 *
			 * @since 4.5
			 * @return string
			 */
			$out_alternate .= apply_filters( 'avf_alternate_main_menu_nav', $alternate_nav );

			$out_alternate .= '</div>';
		}

		/**
		 * Hook to remove or modify alternate mobile menu
		 *
		 * @since 4.5
		 * @return string
		 */
		$out_alternate = apply_filters( 'avf_alternate_mobile_menu', $out_alternate );

		if( ! empty ( $out_alternate ) )
		{
			echo $out_alternate;
		}

		echo '<div class="header_bg"></div>';

		$read = avia_get_option( 'reading_progress' );
		if( 'header_top' == avia_get_option( 'header_position' ) && false !== strpos( $read, 'show' ) )
		{
			$data = [
					'color'	=> avia_get_option( 'reading_progress_color' )
				];

			$data_string = "data-settings='" . json_encode( $data ) . "'";
			$read_class = trim( str_replace( 'show', '', $read ) );

			/**
			 * Filter to hide progress bar on certain pages
			 *
			 * @since 5.6
			 * @param boolean $show
			 * @return boolean				anything else except true will not show
			 */
			$show = apply_filters( 'avf_show_reading_progress_bar', true );

			if( true === $show )
			{
				$id = 'header-reading-progress-' . avia_get_the_ID();

				echo "<div id='{$id}' class='header-reading-progress {$read_class}' {$data_string}></div>";
			}
		}

?>

<!-- end header -->
</header>
