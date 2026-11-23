<?php
/**
 * 单篇笔记
 */
get_header();

while ( have_posts() ) :
	the_post();
	$moji_topic   = moji_post_topic();
	$moji_color   = moji_post_color();
	$moji_hand    = get_post_meta( get_the_ID(), 'moji_hand', true );
	$moji_content = apply_filters( 'the_content', get_the_content() );
	$moji_content = str_replace( ']]>', ']]&gt;', $moji_content );
	list( $moji_content, $moji_toc ) = moji_toc_process( $moji_content );
	$moji_has_toc = count( $moji_toc ) >= 2;
	$moji_modified = get_the_modified_date( 'Y-m-d' ) !== get_the_date( 'Y-m-d' );
	?>
<div class="c-<?php echo esc_attr( $moji_color ); ?>">
	<header class="post-hero card">
		<span class="deco"></span><span class="deco2"></span>
		<?php if ( $moji_hand ) : ?><span class="note-hand"><?php echo esc_html( $moji_hand ); ?></span><?php endif; ?>
		<nav class="crumb" aria-label="位置">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">笔记</a>
			<?php if ( $moji_topic ) : ?>/<a href="<?php echo esc_url( get_category_link( $moji_topic ) ); ?>"><?php echo esc_html( $moji_topic->name ); ?></a><?php endif; ?>
		</nav>
		<h1><?php the_title(); ?></h1>
		<div class="meta">
			<?php moji_stage_badge(); ?>
			<span class="chip">创建 <time class="mono" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></time></span>
			<?php if ( $moji_modified ) : ?><span class="chip">第 <?php echo (int) moji_version(); ?> 版 · <span class="mono"><?php echo esc_html( get_the_modified_date( 'm-d' ) ); ?></span></span><?php endif; ?>
			<span class="chip"><span class="mono"><?php echo esc_html( number_format_i18n( moji_word_count() ) ); ?></span> 字 · <?php echo (int) moji_read_minutes(); ?> 分钟</span>
		</div>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="post-cover"><?php the_post_thumbnail( 'full' ); ?></figure>
	<?php endif; ?>

	<div class="post-layout<?php echo $moji_has_toc ? '' : ' no-toc'; ?>">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'card article' ); ?>>
			<?php if ( $moji_has_toc ) : ?>
				<details class="toc-mobile"><summary>目录</summary><ol>
					<?php foreach ( $moji_toc as $moji_h ) : ?>
						<li class="<?php echo 3 === $moji_h[0] ? 'sub' : ''; ?>"><a href="#<?php echo esc_attr( $moji_h[1] ); ?>"><?php echo esc_html( $moji_h[2] ); ?></a></li>
					<?php endforeach; ?>
				</ol></details>
			<?php endif; ?>

			<div class="prose" id="prose">
				<?php echo $moji_content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<?php wp_link_pages( array( 'before' => '<nav class="page-links">分页：', 'after' => '</nav>' ) ); ?>

			<div class="post-foot">
				<?php $moji_tags = get_the_tags(); if ( $moji_tags ) : ?>
					<div class="post-tags"><?php foreach ( $moji_tags as $moji_t ) { moji_term_chip( $moji_t, '#' ); } ?></div>
				<?php endif; ?>

				<?php $moji_bl = moji_backlinks( get_the_ID() ); if ( $moji_bl ) : ?>
					<section class="backlinks">
						<h4>谁引用了这篇 <span class="chip c-sun"><?php echo count( $moji_bl ); ?></span></h4>
						<ul>
							<?php foreach ( $moji_bl as $moji_id ) : ?>
								<li><a class="c-<?php echo esc_attr( moji_post_color( $moji_id ) ); ?>" href="<?php echo esc_url( get_permalink( $moji_id ) ); ?>"><b><?php echo esc_html( get_the_title( $moji_id ) ); ?></b><small><?php echo esc_html( moji_excerpt( 50, $moji_id ) ); ?></small></a></li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endif; ?>

				<?php
				$moji_prev = get_previous_post();
				$moji_next = get_next_post();
				if ( $moji_prev || $moji_next ) :
					?>
					<nav class="nextprev" aria-label="上一篇和下一篇">
						<?php if ( $moji_prev ) : ?><a class="prev" href="<?php echo esc_url( get_permalink( $moji_prev ) ); ?>"><small>← 上一篇</small><b><?php echo esc_html( get_the_title( $moji_prev ) ); ?></b></a><?php endif; ?>
						<?php if ( $moji_next ) : ?><a class="next" href="<?php echo esc_url( get_permalink( $moji_next ) ); ?>"><small>下一篇 →</small><b><?php echo esc_html( get_the_title( $moji_next ) ); ?></b></a><?php endif; ?>
					</nav>
				<?php endif; ?>
			</div>
		</article>

		<?php if ( $moji_has_toc ) : ?>
			<aside class="toc card" id="toc" aria-label="目录">
				<h4>目录</h4>
				<ol>
					<?php foreach ( $moji_toc as $moji_h ) : ?>
						<li class="<?php echo 3 === $moji_h[0] ? 'sub' : ''; ?>"><a href="#<?php echo esc_attr( $moji_h[1] ); ?>" data-t="<?php echo esc_attr( $moji_h[1] ); ?>"><?php echo esc_html( $moji_h[2] ); ?></a></li>
					<?php endforeach; ?>
				</ol>
				<div class="prog"><span>阅读进度</span><span id="toc-pct">0%</span></div>
				<div class="track"><i id="toc-bar"></i></div>
			</aside>
		<?php endif; ?>
	</div>

	<?php if ( comments_open() || get_comments_number() ) : ?>
		<?php comments_template(); ?>
	<?php endif; ?>
</div>
	<?php
endwhile;

get_footer();
