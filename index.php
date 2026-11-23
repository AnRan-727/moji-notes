<?php
/**
 * 兜底模板
 */
get_header();
?>
<section class="page-hero"><h1><?php echo is_singular() ? esc_html( get_the_title() ) : '笔记'; ?></h1></section>
<?php if ( have_posts() ) : ?>
	<div class="grid three">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/note-card' );
		endwhile;
		?>
	</div>
	<?php moji_pagination(); ?>
<?php else : ?>
	<div class="card empty-box"><b>这里还空着</b><p>暂时没有内容。</p></div>
<?php endif; ?>
<?php
get_footer();
