<?php
/**
 * Single Product tabs
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/tabs.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$product_id = get_the_ID();
$attribute_finish = get_the_terms($product_id, 'pa_finish');

$terms = get_the_terms($product_id, 'product_cat');
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

<ul class="nav nav-tabs" id="cht-single-product-tab" role="tablist">

	<li class="nav-item" role="presentation">
		<button class="nav-link active" id="cht-product-des" data-bs-toggle="tab" data-bs-target="#cht-product-des-panel" type="button" role="tab" aria-controls="cht-product-des-panel" aria-selected="true">Description</button>
	</li>

	<li class="nav-item" role="presentation">
		<button class="nav-link" id="cht-product-suitability" data-bs-toggle="tab" data-bs-target="#cht-product-suitability-panel" type="button" role="tab" aria-controls="cht-product-suitability-panel" aria-selected="false">Suitability</button>
	</li>

	<li class="nav-item" role="presentation">
		<button class="nav-link" id="cht-product-delivery" data-bs-toggle="tab" data-bs-target="#cht-product-delivery-panel" type="button" role="tab" aria-controls="cht-product-delivery-panel" aria-selected="false">Delivery</button>
	</li>

	<li class="nav-item" role="presentation">
		<button class="nav-link" id="cht-product-return" data-bs-toggle="tab" data-bs-target="#cht-product-return-panel" type="button" role="tab" aria-controls="cht-product-return-panel" aria-selected="false">Return</button>
	</li>

	<li class="nav-item" role="presentation">
		<button class="nav-link" id="cht-calculator" data-bs-toggle="tab" data-bs-target="#cht-calculator-panel" type="button" role="tab" aria-controls="cht-calculator-panel" aria-selected="false">Tile Calculator</button>
	</li>
</ul>

<div class="tab-content" id="cht-single-product-content">

	<div class="tab-pane fade show active" id="cht-product-des-panel" role="tabpanel" aria-labelledby="cht-product-des">
		<?php  the_content(); ?>

		<div class="cht-product-tags">
			<ul>

			<?php if (isset($categories[0]))
			{
				foreach ($categories[0] as $parent_category) 
				{						
					$parent_category_name = $parent_category->name;
					$parent_category_id = $parent_category->term_id;
					$parent_category_url = get_term_link($parent_category->term_id, 'product_cat');

					if ($parent_category_name == "Area") 
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
	</div>

	<div class="tab-pane fade" id="cht-product-suitability-panel" role="tabpanel" aria-labelledby="cht-product-suitability">

		<?php if($attribute_finish)
		{
			foreach($attribute_finish as $finish)
			{
				$finish_id = $finish->term_id;
				$suitablity_id= get_field('suitablity_for_finish', 'pa_finish_' . $finish_id);

				if ($suitablity_id) 
				{
					$suitability_post = get_post($suitablity_id);
					echo apply_filters('the_content', $suitability_post->post_content);
				}
				
			}
		} ?>

	</div>

	<div class="tab-pane fade" id="cht-product-delivery-panel" role="tabpanel" aria-labelledby="cht-product-delivery">
		<?php
			$delivery_page_id = 73080; 
			$page = get_post($delivery_page_id);

			if ($page) 
			{
			    echo apply_filters('the_content', $page->post_content);
			}
		?>
	</div>

	<div class="tab-pane fade" id="cht-product-return-panel" role="tabpanel" aria-labelledby="cht-product-return">
		<?php 
			$return_page_id = 73081; 
			$page = get_post($return_page_id);

			if ($page) 
			{
			    echo apply_filters('the_content', $page->post_content);
			} 
		?>
	</div>

	<div class="tab-pane fade" id="cht-calculator-panel" role="tabpanel" aria-labelledby="cht-calculator-return">
		<?php 
			$return_page_id = 76208; 
			$page = get_post($return_page_id);

			if ($page) 
			{
			    echo apply_filters('the_content', $page->post_content);
			} 
		?>
	</div>

</div>

