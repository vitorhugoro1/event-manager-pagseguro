<?php
if( ! is_user_logged_in() ){
	header('Location:' . home_url('/login'));
}
$page_slider=eventerra_get_page_slider($post->ID);
eventerra_custom_sidebar_setup($post->ID);
eventerra_wpb_detect($post);
get_header();
?>
	<div class="content">
		<?php if(isset($page_slider) && $page_slider && $page_slider['layout'] != 'below') eventerra_tpl_header_slider($page_slider) ?>
		<?php eventerra_tpl_page_title($post->ID, the_title('','',false)) ?>
		<?php if(isset($page_slider) && $page_slider && $page_slider['layout'] == 'below') eventerra_tpl_header_slider($page_slider) ?>
		<div class="content-columns-wrapper clearfix-a">
			<div class="content-column-content">
				<div class="content-columns-inner">
					<?php while (have_posts()) : the_post(); ?>
						<article>
					    <?php

								$format = get_post_format();
								if( false === $format )
									$format = 'standard';
								get_template_part( 'includes/post-single', $format );

					    ?>
				    </article>
					<?php endwhile; ?>

					<?php eventerra_wp_link_pages();	?>

					<?php
						$prev=get_previous_post_link('%link');
						$next=get_next_post_link('%link');
						if( get_option('eventerra_show_prev_next_post') == 'true' && ( $prev || $next ) ) {
							echo eventerra_prev_next_nav($prev,$next);
						}
					?>

					<?php get_template_part( 'includes/comments' ); ?>
				</div>
			</div>

			<?php get_sidebar(); ?>
		</div>
	</div>
<?php get_footer(); ?>
