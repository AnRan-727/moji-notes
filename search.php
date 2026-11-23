<?php
/**
 * 搜索结果
 */
get_header();
global $wp_query;
?>
<section class="term-hero c-sun">
	<h1 class="big"><span>搜</span> <?php echo esc_html( get_search_query() ); ?></h1>
	<div class="desc">
		<p>找到 <?php echo (int) $wp_query->found_posts; ?> 篇相关笔记</p>
		<?php get_search_form(); ?>
	</div>
</section>

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
	<div class="card empty-box"><b>没找到</b><p>换个关键词试试，或者看看下面的专题。</p>
		<div class="tags" style="justify-content:center">
			<?php foreach ( get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 8, 'exclude' => moji_topic_exclude() ) ) as $moji_c ) { moji_term_chip( $moji_c ); } ?>
		</div>
	</div>
<?php endif; ?>
<?php
get_footer();
