<?php
	if ( ! defined( 'ABSPATH' ) ){ die(); }

	/**
	* Template Name: Test Search Bar
	*/

	global $avia_config, $wp_query;

	/*
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	get_header();

	/**
	 * @used_by				enfold\config-wpml\config.php				10
	 * @since 4.5.1
	 */
	do_action( 'ava_page_template_after_header' );

	if( get_post_meta(get_the_ID(), 'header', true) != 'no')
	{
		echo avia_title();
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

			<div class='container'>

				<main class='template-page content  <?php avia_layout_class( 'content' ); ?> units <?php echo $main_class; ?>' <?php avia_markup_helper(array('context' => 'content','post_type'=>'page'));?>>

					<div class="test-explaination">

						<div class="test-search-bar"><?php echo do_shortcode('[gto_xml_local_ajax_search]'); ?></div>

						<div class="test-search-bar"><?php echo do_shortcode('[gto_db_ajax_search]'); ?></div>

						<div class="test-search-bar"><?php echo do_shortcode('[gto_xml_ajax_search]'); ?></div>

					</div>

					<div class="test-explaination">

						<div class="test-search-bar">
							<h5>Local Storage Search(XML version)</h5>
							<span>Download all necessary information of products and categories to user's device and save it for 7 days, when user search the keywords, the search auctally happen on their device instead of using our server resouce. After 7 days, if customer open our website again, the program will re-download the information.</span>
							<p>PRO:</p>
							<ul>
								<li>Not use resouce of both hosting & database</li>
								<li>Fast response</li>
								<li>Search result is more accurate</li>
							</ul>
							<p>CON:</p>
							<ul>
								<li>Download product/category information takes 2~4 seconds</li>
								<li>Data may not update-to-day</li>
							</ul>
						</div>

						<div class="test-search-bar">
							<h5>On-time database Search</h5>
							<span>Search the database via WordPress default quick search function. This function can't be replaced by our customized search function due to WordPress setting, database rules and program preformance issue. It may miss some products/category in search result</span>
							<p>PRO:</p>
							<ul>
								<li>Data is up-to-date</li>
							</ul>
							<p>CON:</p>
							<ul>
								<li>Use a little resource of hosting, and a lot of resouce of database, may slow the whole website</li>
								<li>Slow response</li>
								<li>Search result is not accurate</li>
							</ul>
						</div>

						<div class="test-search-bar">
							<h5>XML Search</h5>
							<span>Generate an XML file on hosting to store necessary information of all products/categories, user search on this file instead of searching database. This file automatically updates every day.</span>
							<p>PRO:</p>
							<ul>
								<li>Only use acceptable resource of hosting, doesn't use database</li>
								<li>Search result is more accurate</li>
							</ul>
							<p>CON:</p>
							<ul>
								<li>Slow response, but faster than searching database</li>
								<li>Data may not update-to-day</li>
							</ul>
						</div>

					</div>

					

				<!--end content-->
				</main>

			</div><!--end container-->

		</div><!-- close default .container_wrap element -->

<?php
		get_footer();
