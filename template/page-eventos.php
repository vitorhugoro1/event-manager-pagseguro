<?php
/**
 * Template Name: Eventos
 */

$settings_page=get_option('eventerra_archive_category_page_settings');
if($settings_page && function_exists('icl_object_id'))
	$settings_page=icl_object_id($settings_page, 'page', true);

$page_slider=eventerra_get_page_slider($settings_page);
eventerra_custom_sidebar_setup($settings_page);
get_header();

$blog_layout=false;
if($settings_page) {
	$blog_layout=get_post_meta($settings_page, 'eventerra_blog_layout', true);
}
if(!$blog_layout) {
	$blog_layout='small';
}
$blog_small_cut=false;
if($blog_layout=='small') {
	if(get_post_meta($settings_page, 'eventerra_blog_grid_cut', true)) {
		$blog_small_cut=true;
	}
}
?>
	<div class="content">
		<?php if(isset($page_slider) && $page_slider && $page_slider['layout'] != 'below') eventerra_tpl_header_slider($page_slider) ?>
		<?php eventerra_tpl_page_title(false, eventerra_get_archive_page_title()) ?>
		<?php if(isset($page_slider) && $page_slider && $page_slider['layout'] == 'below') eventerra_tpl_header_slider($page_slider) ?>
		<div class="content-columns-wrapper clearfix-a">
			<div class="content-column-content">
				<div class="content-columns-inner">
					<?php
		      	if(is_category())
		      		echo category_description();
		      ?>
					<?php query_posts(array('post_type' => 'eventos')) ?>
					<?php if (have_posts()) { ?>

						<div class="blog-posts layout-<?php echo esc_attr($blog_layout) ?><?php echo ($blog_small_cut ? ' sublayout-cut' : '' )?>">
						<section>

							<?php while (have_posts()) : the_post(); ?>

						    <?php

									$format = get_post_format();
									if( false === $format )
										$format = 'standard';
									eventerra_tpl_blog_post($blog_layout, $format);

						    ?>

							<?php endwhile; ?>

						</section>
						</div>

						<?php
							if(get_option('eventerra_blog_pagination') == 'pages') {

								echo eventerra_wrap_paginate_links ( paginate_links( eventerra_paginate_links_args() ) );

							} else {

								$nav_newer=get_previous_posts_link(esc_html__('Newer Entries', 'eventerra'));
								$nav_older=get_next_posts_link(esc_html__('Older Entries', 'eventerra'));
								if( $nav_newer || $nav_older ) {
									echo eventerra_prev_next_nav ($nav_older, $nav_newer);
								}

							}
						?>

					<?php } else {

							echo '<b>';
							if ( is_category() ) {
								printf(esc_html__('Sorry, but there aren\'t any posts in the %s category yet.', 'eventerra'), single_cat_title('',false));
							} elseif ( is_tag() ) {
							    printf(esc_html__('Sorry, but there aren\'t any posts tagged %s yet.', 'eventerra'), single_tag_title('',false));
							} elseif ( is_date() ) {
								echo(esc_html__('Sorry, but there aren\'t any posts with this date.', 'eventerra'));
							} else {
								echo(esc_html__('No posts found.', 'eventerra'));
							}
							echo '</b>';

					} ?>
				</div>
			</div>

			<?php
				if($settings_page)
					$post=get_post($settings_page);
				else
					$post=false;
				get_sidebar();
			?>
		</div>
	</div>
<?php get_footer(); ?>
