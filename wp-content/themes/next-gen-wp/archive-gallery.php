<?php
/**
 * Gallery page
 */

// Getting gallery layout
if ( function_exists( 'fw_get_db_settings_option' ) ) {

	$aqualine_layout = fw_get_db_post_option( $wp_query->get_queried_object_id(), 'gallery-layout' );
	if ( empty($aqualine_layout) ) $aqualine_layout = fw_get_db_settings_option( 'gallery_layout' );
}
?>
<?php get_header(); ?>

<div class="gallery-page inner-page margin-default gallery-<?php echo esc_attr( $aqualine_layout ); ?>">
	<div class="row ">

		<?php
			if ( get_query_var( 'paged' ) ) {

				$paged = get_query_var( 'paged' );
			} elseif ( get_query_var( 'page' ) ) {

				$paged = get_query_var( 'page' );
			} else {

				$paged = 1;
			}

			$wp_query = new WP_Query( array(
				'post_type' => 'gallery',
				'paged' => (int) $paged,
			) );

			
			if ( ! empty($aqualine_layout) && $aqualine_layout == 'col-3' ) {

				$aqualine_grid = 3;
			}
				elseif ( ! empty($aqualine_layout) && $aqualine_layout == 'col-4' ) {

				$aqualine_grid = 4;
			}
				else {

				$aqualine_grid = 2;
			}

			if ( $wp_query->have_posts() ) :
				while ( $wp_query->have_posts() ) : $wp_query->the_post();

					aqualine_get_template_part( 'tmpl/content', 'ltx-gallery', array(
						'grid' => $aqualine_grid,
					) );

				endwhile;
			else :
				// If no content, include the "No posts found" template.
				get_template_part( 'tmpl/content', 'none' );

			endif;
		?>  
	</div>
	<?php
	if ( have_posts() ) {

		aqualine_paging_nav();
	}
	?>        
</div>            
<?php get_footer(); ?>
