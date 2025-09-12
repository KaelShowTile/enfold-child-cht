<?php
	if( ! defined( 'ABSPATH' ) )	{ die(); }

	global $avia_config;

	/* */
	get_header();
	// Get the current collection post id
	$collection_id = get_the_ID();

	$title = __( 'Tile Collection', 'avia_framework' ); //default blog title
	$t_link = home_url( '/' );
	$t_sub = '';

	if( avia_get_option( 'frontpage' ) && $new = avia_get_option( 'blogpage' ) )
	{
		$title = get_the_title( $new ); //if the blog is attached to a page use this title
		$t_link = get_permalink( $new );
		$t_sub = avia_post_meta( $new, 'subtitle' );
	}

	if( get_post_meta( get_the_ID(), 'header', true ) != 'no' )
	{
		echo avia_title( array( 'heading' => 'strong', 'title' => $title, 'link' => $t_link, 'subtitle' => $t_sub ) );
	}

	?>

	<div class="collection-product-header">

		<div class="container">

			<div class="collection-product-header-slider">
				<img src="<?php the_field('collection_images', $collection_id) ?>">
			</div>

			<div class="collection-product-header-description">
				<?php the_field('collection_header', $collection_id) ?>
			</div>

		</div>

	</div>

	<?php do_action( 'ava_after_main_title' );

	 /**
	 * @since 5.6.7
	 * @param string $main_class
	 * @param string $context					file name
	 * @return string
	 */
	$main_class = apply_filters( 'avf_custom_main_classes', 'av-main-' . basename( __FILE__, '.php' ), basename( __FILE__ ) );
	
	?>

		<div class='container_wrap no-border-col container_wrap_first main_color <?php avia_layout_class( 'main' ); ?>'>

			<div class='container template-single-collection '>

				<main class='content units <?php avia_layout_class( 'content' ); ?> <?php echo avia_blog_class_string(); ?> <?php echo $main_class; ?>' <?php avia_markup_helper( array( 'context' => 'content', 'post_type' => 'post' ) );?>>

					<?php

					echo '<div class="collection-product-list-container glint-products-container products columns-4">';

					if(get_field('product_list_title', $collection_id)){
						echo '<h3>';
						the_field('product_list_title', $collection_id);
						echo '</h3>';
					}else{
						
						echo'<h3>' . get_the_title( $collection_id ) . ' Products</h3>';
					}	
					
						// Get query values
						$product_categories = get_field('select_product_category', $collection_id); 
						$colors = get_field('select_color', $collection_id); 
						$sizes = get_field('select_size', $collection_id); 
						$finish = get_field('select_finish', $collection_id); 
						$category_rule = get_field('rule_for_categories', $collection_id); 
						$color_rule = get_field('rule_for_color', $collection_id); 
						$size_rule = get_field('rule_for_size', $collection_id); 
						$finish_rule = get_field('rule_for_finish', $collection_id); 

						// Initialize tax query array
						$tax_query = array('relation' => 'AND'); // Top-level relation is always AND

						// 1. Handle Product Categories 
						if (!empty($product_categories)) {
							if (strtoupper($category_rule) === 'AND') {
								// For AND, we need to create separate queries for each category
								foreach ($product_categories as $category) {
									$tax_query[] = array(
										'taxonomy' => 'product_cat',
										'field'    => 'term_id',
										'terms'    => $category, // Single term
										'operator' => 'IN' // Changed from AND to IN for individual terms
									);
								}
							} else {
								// Normal IN operator case
								$tax_query[] = array(
									'taxonomy' => 'product_cat',
									'field'    => 'term_id',
									'terms'    => $product_categories,
									'operator' => strtoupper($category_rule) 
								);
							}
						}

						// 2. Handle Colors 
						if (!empty($colors)) {
							$tax_query[] = array(
								'taxonomy' => 'pa_colour', 
								'field'    => 'term_id',
								'terms'    => $colors,
								'operator' => strtoupper($color_rule) 
							);
						}

						// 3. Handle Sizes 
						if (!empty($sizes)) {
							$tax_query[] = array(
								'taxonomy' => 'pa_size', 
								'field'    => 'term_id',
								'terms'    => $sizes,
								'operator' => strtoupper($size_rule) 
							);
						}

						// 4. Handle Finish 
						if (!empty($finish)) {
							$tax_query[] = array(
								'taxonomy' => 'pa_finish', 
								'field'    => 'term_id',
								'terms'    => $finish,
								'operator' => strtoupper($finish_rule) 
							);
						}

						// Build the product query
						$args = array(
							'post_type'      => 'product',
							'posts_per_page' => 40, 
							'paged'          => 1,
							'tax_query'      => $tax_query,
							'meta_query'     => array(
								array(
									'key'     => '_stock_status',
									'value'   => 'instock',
									'compare' => '='
								)
							),
							'orderby' => 'meta_value_num', // Sort by numeric meta value
							'meta_key' => '_price', // Use the price meta key
							'order' => 'ASC', // Ascending order (low to high)
						);
						$products = new WP_Query($args);
						
						// Store query args for JavaScript
						$query_args = array(
							'tax_query' 	 => $tax_query,
							'meta_query'     => $args['meta_query'],
							'orderby'        => $args['orderby'],
							'meta_key'       => $args['meta_key'],
							'order'          => $args['order']
						);
						?>
						
						<div class="products-grid" 
							data-collection-id="<?php echo get_the_ID(); ?>" 
							data-query-args='<?php echo json_encode($query_args); ?>'
							data-page="1" 
							data-max-pages="<?php echo $products->max_num_pages; ?>">
							
							<?php if ($products->have_posts()) : ?>
								<?php while ($products->have_posts()) : $products->the_post(); 
									wc_get_template_part('content', 'product');
								endwhile; ?>
							<?php endif; ?>
						</div>
						
						<?php if ($products->max_num_pages > 1) : ?>
							<div class="load-more-container">
								<button class="load-more-button">Load More</button>
								<div class="loading-spinner" style="display: none;">
									<div class="spinner"></div>
									<span>Loading...</span>
								</div>
							</div>
						<?php endif;
						
						wp_reset_postdata(); 

					echo '</div>';

					echo '<div class="container google-review-container">';
					echo do_shortcode('[trustindex no-registration=google]');
					echo '</div>';

					echo '<div class="collection-description-container">';
					the_content();
					echo '</div>';

					?>

				<!--end content-->
				</main>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->
<?php
		
		
get_footer();

