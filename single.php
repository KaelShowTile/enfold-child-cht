<?php
	if( ! defined( 'ABSPATH' ) )	{ die(); }

	global $avia_config;

	/**
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	get_header();


	$title = __( 'Blog - Latest News', 'avia_framework' ); //default blog title
	$t_link = home_url( '/' );
	$t_sub = '';

	if( avia_get_option( 'frontpage' ) && $new = avia_get_option( 'blogpage' ) ){
		$title = get_the_title( $new ); //if the blog is attached to a page use this title
		$t_link = get_permalink( $new );
		$t_sub = avia_post_meta( $new, 'subtitle' );
	}

	if( get_post_meta( get_the_ID(), 'header', true ) != 'no' ){
		echo avia_title( array( 'heading' => 'strong', 'title' => $title, 'link' => $t_link, 'subtitle' => $t_sub ) );
	}

	do_action( 'ava_after_main_title' );

	/**
	 * @since 5.6.7
	 * @param string $main_class
	 * @param string $context					file name
	 * @return string
	 */
	$main_class = apply_filters( 'avf_custom_main_classes', 'av-main-' . basename( __FILE__, '.php' ), basename( __FILE__ ) );

	?>

		<div class='container_wrap container_wrap_first main_color <?php avia_layout_class( 'main' ); ?>'>

			<div class='container template-blog template-single-blog '>

				<main class='content units <?php avia_layout_class( 'content' ); ?> <?php echo avia_blog_class_string(); ?> <?php echo $main_class; ?>' <?php avia_markup_helper( array( 'context' => 'content', 'post_type' => 'post' ) );?>>

					<?php
					/* Run the loop to output the posts.
					* If you want to overload this in a child theme then include a file
					* called loop-index.php and that will be used instead.
					*
					*/
					get_template_part( 'includes/loop', 'index' );

					$blog_disabled = ( avia_get_option('disable_blog') == 'disable_blog' ) ? true : false;

					if( ! $blog_disabled )
					{
						//show related posts based on tags if there are any
						get_template_part( 'includes/related-posts' );
					}
					?>

					<div class="blog-author-container">
						<div class="author-avatar">
							<img src="/wp-content/uploads/2025/08/Paul-Eyers.jpg">
						</div>

						<div class="author-description">
							<h2>Paul Eyers</h2>
							<span>Tile Expert </span>
							<p>Paul Eyers is an experienced content writer and journalist with a background spanning broadcast, editorial and digital media. His portfolio includes work for some of Australia's largest media brands including 7News Network, News Corp Australia, Channel Ten. He was part of the foundation team for construction and home renovation brand Build-it making him the perfect fit for our team of tile experts. </p>
							<p>Paul now brings his editorial expertise to the world of tiles, crafting renovation advice and design-focused content for Cheapestiles. His writing blends practical insight with architectural awareness, helping readers explore tile design through a refined and accessible lens.</p>
						</div>
					</div>

				<!--end content-->
				</main>

				<?php

				$avia_config['currently_viewing'] = 'blog';
				//get the sidebar
				get_sidebar();

				?>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php
		get_footer();

