<?php
/**
 * 普通页面
 */
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'card page-card' ); ?>>
		<h1><?php the_title(); ?></h1>
		<div class="prose"><?php the_content(); ?></div>
		<?php wp_link_pages( array( 'before' => '<nav class="page-links">分页：', 'after' => '</nav>' ) ); ?>
	</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}
endwhile;
get_footer();
