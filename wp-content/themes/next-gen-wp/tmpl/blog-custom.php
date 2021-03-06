<?php
/**
 * The blog template file
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 */

$blog_class = $sidebar_wrap = $aqualine_layout = '';
$aqualine_sidebar_hidden = false;
$aqualine_sidebar = 'right';
$blog_wrap_class = 'col-xl-9 col-lg-8 col-md-12 col-xs-12';

if ( function_exists( 'FW' ) ) {

	$aqualine_layout = fw_get_db_post_option( $wp_query->get_queried_object_id(), 'blog-layout' );
	$aqualine_sidebar = fw_get_db_settings_option( 'blog_list_sidebar' );

	$aqualine_sidebar_custom = fw_get_db_post_option( $wp_query->get_queried_object_id(), 'sidebar-layout' );
	if ( $aqualine_sidebar_custom != 'default') $aqualine_sidebar = $aqualine_sidebar_custom;

	if ( $aqualine_sidebar == 'hidden' OR $aqualine_sidebar == 'disabled' ) $aqualine_sidebar_hidden = true;

	if ( $aqualine_sidebar == 'left' ) $blog_wrap_class = 'col-xl-9 col-xl-push-3 col-lg-8 col-lg-push-4 col-lg-offset-0 col-md-12 col-xs-12 ';

	$blog_class = '';
	if ( $aqualine_layout == 'two-cols' OR $aqualine_layout == 'three-cols' ) {

		$blog_class = 'masonry';
		if ( $aqualine_sidebar_hidden ) $blog_wrap_class = 'col-lg-12 col-xs-12';
	}
		else {

		if ( $aqualine_sidebar_hidden ) $blog_wrap_class = 'col-xl-9 col-lg-10 col-md-12 col-xs-12';	
	}

	if ( $aqualine_layout == 'classic' ) {

		$sidebar_wrap = 'with-sidebar';
	}
		else {

		$sidebar_wrap = '';
	}
}

get_header(); ?>
<div class="inner-page margin-default">
	<div class="row <?php if ( $aqualine_sidebar_hidden ) echo 'centered'; else echo esc_attr($sidebar_wrap); ?>">
        <div class="<?php echo esc_attr( $blog_wrap_class ); ?> ltx-blog-wrap" >
            <div class="blog blog-block layout-<?php echo esc_attr($aqualine_layout); ?>">
				<?php

				if ( get_query_var( 'paged' ) ) {

					$paged = get_query_var( 'paged' );

				} elseif ( get_query_var( 'page' ) ) {

					$paged = get_query_var( 'page' );
					
				} else {

					$paged = 1;
				}

				if (isset($_GET['s'])) {

					$wp_query = new WP_Query( array(
						's'		=> esc_sql( $_GET['s'] ),
						'paged' => (int) $paged,
					) );
				}
					else {

					$wp_query = new WP_Query( array(
						'post_type' => 'post',
						'paged' => (int) $paged,
					) );
				}

            	echo '<div class="row '.esc_attr($blog_class).'">';
				if ( $wp_query->have_posts() ) :

					while ( $wp_query->have_posts() ) : the_post();

						if ( !function_exists( 'FW' ) ) {

							get_template_part( 'tmpl/content-post-one-col', $wp_query->get_post_format() );
						}
							else {

							set_query_var( 'aqualine_layout', $aqualine_layout );

							if ($aqualine_layout == 'three-cols') {

								get_template_part( 'tmpl/content-post-three-cols', $wp_query->get_post_format() );
							}
								else
							if ($aqualine_layout == 'two-cols') {

								get_template_part( 'tmpl/content-post-two-cols', $wp_query->get_post_format() );
							}
								else {

								set_query_var( 'ltx_display_excerpt', true );
								get_template_part( 'tmpl/content-post-one-col', $wp_query->get_post_format() );
							}
						}

						endwhile;

					else :
						// If no content, include the "No posts found" template.
						get_template_part( 'tmpl/content', 'none' );

					endif;
				echo '</div>';
				?>
	        </div>
			<?php
			if ( have_posts() ) {

				aqualine_paging_nav();
			}
            ?>	        
	    </div>
	    <?php
	    if ( !$aqualine_sidebar_hidden ) {

            if ( $aqualine_sidebar == 'left' ) {

            	get_sidebar( 'left' );
            }
            	else  {

            	get_sidebar();
            }
	    }
	    ?>
	</div>
</div>
<?php

get_footer();
