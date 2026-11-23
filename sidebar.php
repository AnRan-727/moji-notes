<?php
/**
 * 首页侧栏
 */
$moji_name  = moji_mod( 'moji_profile_name' ) ? moji_mod( 'moji_profile_name' ) : get_bloginfo( 'name' );
$moji_bio   = moji_mod( 'moji_profile_bio' ) ? moji_mod( 'moji_profile_bio' ) : get_bloginfo( 'description' );
$moji_heat  = moji_heatmap( 20 );
$moji_about = get_pages( array( 'meta_key' => '_wp_page_template', 'meta_value' => 'template-about.php', 'number' => 1 ) );
?>
<aside class="side" aria-label="侧栏">
	<section class="card profile">
		<?php moji_avatar(); ?>
		<b><?php echo esc_html( $moji_name ); ?></b>
		<?php if ( $moji_bio ) : ?><p><?php echo esc_html( $moji_bio ); ?></p><?php endif; ?>
		<div class="chips">
			<a class="chip c-sky" href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>">RSS</a>
			<?php if ( moji_mod( 'moji_github' ) ) : ?><a class="chip c-pink" href="<?php echo esc_url( moji_mod( 'moji_github' ) ); ?>" rel="me noopener" target="_blank">GitHub</a><?php endif; ?>
			<?php if ( $moji_about ) : ?><a class="chip c-sun" href="<?php echo esc_url( get_permalink( $moji_about[0] ) ); ?>">关于我</a><?php endif; ?>
		</div>
	</section>

	<section class="card widget">
		<h4>写作打卡 <small>近 20 周 · <?php echo (int) $moji_heat['total']; ?> 篇</small></h4>
		<div class="heat">
			<?php foreach ( $moji_heat['cells'] as $moji_c ) : ?>
				<i<?php echo $moji_c['c'] ? ' class="c-' . esc_attr( $moji_c['c'] ) . '"' : ( $moji_c['future'] ? ' class="future"' : '' ); ?> title="<?php echo esc_attr( $moji_c['d'] . ( $moji_c['n'] ? ' · ' . $moji_c['n'] . ' 篇' : '' ) ); ?>"></i>
			<?php endforeach; ?>
		</div>
	</section>

	<?php
	$moji_cats = get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 8, 'hide_empty' => true, 'exclude' => moji_topic_exclude() ) );
	if ( $moji_cats ) :
		$moji_topics_page = get_pages( array( 'meta_key' => '_wp_page_template', 'meta_value' => 'template-topics.php', 'number' => 1 ) );
		?>
	<section class="card widget">
		<h4>专题<?php if ( $moji_topics_page ) : ?> <small><a href="<?php echo esc_url( get_permalink( $moji_topics_page[0] ) ); ?>">全部 →</a></small><?php endif; ?></h4>
		<ul class="cats">
			<?php foreach ( $moji_cats as $moji_cat ) : ?>
				<li><a class="c-<?php echo esc_attr( moji_term_color( $moji_cat ) ); ?>" href="<?php echo esc_url( get_category_link( $moji_cat ) ); ?>"><i></i><?php echo esc_html( $moji_cat->name ); ?><span><?php echo (int) $moji_cat->count; ?></span></a></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php endif; ?>

	<?php if ( moji_mod( 'moji_memo_text' ) ) : ?>
	<section class="card memo">
		<h4><?php echo esc_html( moji_mod( 'moji_memo_title' ) ); ?></h4>
		<p><?php echo esc_html( moji_mod( 'moji_memo_text' ) ); ?></p>
	</section>
	<?php endif; ?>

	<?php
	$moji_tags = get_tags( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 14 ) );
	if ( $moji_tags ) :
		?>
	<section class="card widget">
		<h4>标签</h4>
		<div class="tags"><?php foreach ( $moji_tags as $moji_t ) { moji_term_chip( $moji_t ); } ?></div>
	</section>
	<?php endif; ?>

	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
