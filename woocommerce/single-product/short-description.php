<?php
/**
 * Single product short description
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/short-description.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

?>

<div class="woocommerce-product-details__short-description">

	<?php $short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );

	if ( $short_description ) {
		echo $short_description; 
	}

	$get_product_id = $post->ID;
	$current_url = get_permalink($get_product_id);

	$terms = get_the_terms($get_product_id, 'product_cat');
	$categories = array();

	if ($terms && !is_wp_error($terms)) 
	{		
		// Organize categories by parent
		foreach ($terms as $term) 
		{
			$categories[$term->parent][] = $term;
		}
	}

	?>

	<div class = "add-to-wishlist-container">

		<div class="cht-product-tags">
			<ul>
				<?php 

				$attribute_size = get_the_terms($get_product_id, 'pa_size');

				if($attribute_size)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Size: </a>';
					echo '<p>';
					
					foreach($attribute_size as $size)
					{
						echo '<a class="child-categories-item"> ' . $size->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}
				
				$attribute_color = get_the_terms($get_product_id, 'pa_colour');

				if($attribute_color)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Colour: </a>';
					echo '<p>';
					
					foreach($attribute_color as $color)
					{
						echo '<a class="child-categories-item"> ' . $color->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}
				
				$attribute_finish = get_the_terms($get_product_id, 'pa_finish');

				if($attribute_finish)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Finish: </a>';
					echo '<p>';
					
					foreach($attribute_finish as $finish)
					{
						echo '<a class="child-categories-item"> ' . $finish->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}	

				$attribute_thickness = get_the_terms($get_product_id, 'pa_thickness');

				if($attribute_thickness)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Thickness: </a>';
					echo '<p>';
					
					foreach($attribute_thickness as $thickness)
					{
						echo '<a class="child-categories-item"> ' . $thickness->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}	

				$attribute_edge = get_the_terms($get_product_id, 'pa_edge');

				if($attribute_edge)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Edge: </a>';
					echo '<p>';
					
					foreach($attribute_edge as $edge)
					{
						echo '<a class="child-categories-item"> ' . $edge->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}	

				$attribute_slip_rating = get_the_terms($get_product_id, 'pa_slip-rating');

				if($attribute_slip_rating)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Slip Rating: </a>';
					echo '<p>';
					
					foreach($attribute_slip_rating as $slip_rating)
					{
						echo '<a class="child-categories-item"> ' . $slip_rating->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}

				$attribute_grout_colour = get_the_terms($get_product_id, 'pa_grout-colour');

				if($attribute_grout_colour)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Grout: </a>';
					echo '<p>';
					
					foreach($attribute_grout_colour as $grout_colour)
					{
						echo '<a class="child-categories-item"> ' . $grout_colour->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}	

				$attribute_coverage = get_the_terms($get_product_id, 'pa_coverage');

				if($attribute_coverage)
				{
					echo '<li>';
					echo '<a class="parent-categories-item">Coverage: </a>';
					echo '<p>';
					
					foreach($attribute_coverage as $coverage)
					{
						echo '<a class="child-categories-item"> ' . $coverage->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}

				$attribute_tile_variation = get_the_terms($get_product_id, 'pa_tile-variation');

				if($attribute_tile_variation)
				{
					echo '<li class="hide-this-area">';
					echo '<a class="parent-categories-item">Tile Variation: </a>';
					echo '<p>';
					
					foreach($attribute_tile_variation as $tile_variation)
					{
						echo '<a class="child-categories-item"> ' . $tile_variation->name . ' </a>';
					}

					echo '</p>';
					echo '</li>';
				}

				if (isset($categories[0]))
				{
					foreach ($categories[0] as $parent_category) 
					{						
						$parent_category_name = $parent_category->name;
						$parent_category_id = $parent_category->term_id;
						$parent_category_url = get_term_link($parent_category->term_id, 'product_cat');

						if ($parent_category_name == "Material") 
						{
							
				        	echo '<li>';

			        		if (isset($categories[$parent_category_id])) 
			        		{
			        			echo '<a class="parent-categories-item" href="' . $parent_category_url . '">';
				        		echo esc_html($parent_category_name) . ':'; 
				        		echo '</a>';

				        		echo '<p>';
			        			
			        			$cat_count = 0;

			        			foreach ($categories[$parent_category_id] as $child_category)
			        			{ 
			        				$child_category_url = get_term_link($child_category->term_id, 'product_cat');

			        				if ($cat_count !== 0)
			        				{
			        					echo ', ';
			        				}  

			        				echo '<a class="child-categories-item" href="' . $child_category_url . '">';

			        				echo esc_html($child_category->name);

			        				echo '</a>';

			        				$cat_count = $cat_count + 1;
			        			}

			        			echo '</p>';

			        		} 
				      
				        	echo'</li>';
						}
					}
				} 
				
				
				?>

			</ul>
		</div>

		<?php echo do_shortcode('[gto_wishlist_button]'); ?>
	</div>
</div>
