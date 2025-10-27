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

					<div id="cht-blog-content-container" class="flex_column av_four_fifth  avia-builder-el-0  el_before_av_one_fifth  avia-builder-el-first  first flex_column_div">
					<?php
					/* Run the loop to output the posts.
					* If you want to overload this in a child theme then include a file
					* called loop-index.php and that will be used instead.
					*/
					get_template_part( 'includes/loop', 'index' );

					$blog_disabled = ( avia_get_option('disable_blog') == 'disable_blog' ) ? true : false;

					/*if( ! $blog_disabled )
					{
						//show related posts based on tags if there are any
						get_template_part( 'includes/related-posts' );
					}*/
					?>
					</div>
					
					<div id="cht-blog-sidebar-container" class="flex_column av_one_fifth  avia-builder-el-1  el_after_av_four_fifth  el_before_av_one_full  flex_column_div">
						
						<?php //get related products
						$related_tile_ids = get_field('blog_related_tiles');
						
						if($related_tile_ids){

							echo '<div class="blog-related-tiles">';
							echo '<h2 class="blog-sidebar-title">Related Tiles</h2>';

							foreach($related_tile_ids as $tile_id){
								$related_tiles = wc_get_product( $tile_id );
								$tile_permalink = get_permalink( $tile_id );
								$product_thumbnail_id = $related_tiles->get_image_id();
								$product_thumbnail_url = wp_get_attachment_image_url( $product_thumbnail_id, 'woocommerce_thumbnail' );
								
								$product_suffix = get_product_qty_suffix($tile_id);
								$display_product_suffix = "";
								if($product_suffix){
									$display_product_suffix = '/' . $product_suffix;
								}
								
								echo '<a href="' . $tile_permalink . '">';
								echo '<div class="related_tile">';
								echo '<img src="' . $product_thumbnail_url . '">';
								echo '<p>' . $related_tiles->get_name() . '</p>';
								
								echo '<div class="price">';
								if($product_suffix != 'm2'){
									echo '<span>' . $related_tiles->get_price_html() . $display_product_suffix . '</span>';
								}else{
									$step_value = round(get_product_qty_data($tile_id), 2);
									$regular_price = round((($related_tiles->get_regular_price())/$step_value), 2);

									if ($related_tiles->is_on_sale()){
										$sale_price = round((($related_tiles->get_sale_price())/$step_value), 2);
										echo '<span><del aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $sale_price . '</span></bdi></span></del>';
										echo '<ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $regular_price . '</bdi></span></ins>' . $display_product_suffix . '</span>';
									}else{
										echo '<span><ins aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span>' . $regular_price . '</bdi></span></ins>' . $display_product_suffix . '</span>';	
									}
								}
								echo '</div>';
								
								echo '</div>';
								echo '</a>';
							}
							echo '</div>';
						}

						$category_ids = wp_get_post_categories( get_the_ID() );

						//get related post(post in same category)
						if($category_ids){
							echo '<div class="blog-related-article">';
							echo '<h2 class="blog-sidebar-title">Related Articles</h2>';
							$args = array(
								'category__in' => $category_ids,
								'posts_per_page' => 4,
								'post__not_in' => array( get_the_ID() ),
								'post_status' => 'publish'
							);
							$related_posts = new WP_Query( $args );
							if( $related_posts->have_posts() ) {
								echo '<div class="related-articles">';
								while( $related_posts->have_posts() ) {
									$related_posts->the_post();
									$permalink = get_permalink();
									$thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
									$title = get_the_title();
									echo '<a href="' . $permalink . '">';
									echo '<div class="related_article">';
									if($thumbnail){
										echo '<img src="' . $thumbnail . '">';
									}
									echo '<p>' . $title . '</p>';
									echo '</div>';
									echo '</a>';
								}
								echo '</div>';
								wp_reset_postdata();
							}
							echo '</div>';
						}
						

						?>
					</div>

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
