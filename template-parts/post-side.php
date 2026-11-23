<?php
/**
 * 没有目录的笔记：右侧显示笔记信息、阅读进度、同专题笔记和标签。
 */
$moji_topic = isset( $args['topic'] ) ? $args['topic'] : null;
$moji_pid   = get_the_ID();
$moji_tags  = get_the_tags();
$moji_rel   = array();
if ( $moji_topic ) {
	$moji_rel = get_posts( array(
		'category__in'        => array( $moji_topic->term_id ),
		'post__not_in'        => array( $moji_pid ),
		'posts_per_page'      => 5,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'has_password'        => false,
	) );
}
if ( count( $moji_rel ) < 3 ) {
	$moji_more = get_posts( array(
		'post__not_in'        => array_merge( array( $moji_pid ), wp_list_pluck( $moji_rel, 'ID' ) ),
		'posts_per_page'      => 5 - count( $moji_rel ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'has_password'        => false,
		'orderby'             => 'rand',
	) );
	$moji_rel  = array_merge( $moji_rel, $moji_more );
}
?>
<aside class="toc side" id="toc" aria-label="笔记信息">
	<section class="card side-info">
		<h4>笔记卡</h4>
		<dl>
			<?php if ( $moji_topic ) : ?><div><dt>专题</dt><dd><?php moji_term_chip( $moji_topic ); ?></dd></div><?php endif; ?>
			<div><dt>成熟度</dt><dd><?php moji_stage_badge(); ?></dd></div>
			<div><dt>浏览</dt><dd><span class="mono" data-views><?php echo esc_html( moji_views_label() ); ?></span> 次</dd></div>
			<div><dt>写于</dt><dd class="mono"><?php echo esc_html( get_the_date( 'Y-m-d' ) ); ?></dd></div>
			<?php if ( ! moji_locked() ) : ?>
				<div><dt>篇幅</dt><dd><span class="mono"><?php echo esc_html( number_format_i18n( moji_word_count() ) ); ?></span> 字 · 约 <?php echo (int) moji_read_minutes(); ?> 分钟</dd></div>
			<?php endif; ?>
		</dl>
		<?php if ( $moji_tags ) : ?>
			<div class="side-tags"><?php foreach ( array_slice( $moji_tags, 0, 6 ) as $moji_t ) { moji_term_chip( $moji_t, '#' ); } ?></div>
		<?php endif; ?>
	</section>

	<div class="side-stick">
		<section class="card side-prog">
			<div class="prog"><span>阅读进度</span><span id="toc-pct">0%</span></div>
			<div class="track"><i id="toc-bar"></i></div>
			<div class="side-acts">
				<button type="button" class="btn sm" data-copy-link="<?php echo esc_url( get_permalink() ); ?>">复制链接</button>
				<button type="button" class="btn sm" data-to-top>回到顶部 ↑</button>
			</div>
		</section>

		<?php if ( $moji_rel ) : ?>
			<section class="card side-rel">
				<h4><?php echo $moji_topic ? '同专题还有' : '再看一篇'; ?></h4>
				<ol>
					<?php foreach ( $moji_rel as $moji_p ) : ?>
						<li class="c-<?php echo esc_attr( moji_post_color( $moji_p ) ); ?>"><a href="<?php echo esc_url( get_permalink( $moji_p ) ); ?>"><b><?php echo esc_html( get_the_title( $moji_p ) ); ?></b><small class="mono"><?php echo esc_html( get_the_date( 'Y-m-d', $moji_p ) ); ?></small></a></li>
					<?php endforeach; ?>
				</ol>
				<?php if ( $moji_topic ) : ?><a class="side-more" href="<?php echo esc_url( get_category_link( $moji_topic ) ); ?>">看「<?php echo esc_html( $moji_topic->name ); ?>」全部 <?php echo (int) $moji_topic->count; ?> 篇 →</a><?php endif; ?>
			</section>
		<?php endif; ?>
	</div>
</aside>
