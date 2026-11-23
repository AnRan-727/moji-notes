<?php
/**
 * Template Name: 归档
 *
 * 按年、月列出全部笔记，圆点颜色对应专题。
 */
get_header();
$moji_all = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
$moji_tree = array();
foreach ( $moji_all as $moji_p ) {
	$moji_tree[ get_the_date( 'Y', $moji_p ) ][ get_the_date( 'n', $moji_p ) ][] = $moji_p;
}
$moji_month_cn = array( '', '一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月' );
$moji_cats     = get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 8, 'exclude' => moji_topic_exclude() ) );
?>
<section class="page-hero">
	<h1><?php the_title(); ?><span class="hand"><?php echo count( $moji_all ); ?> 篇</span></h1>
	<?php if ( $moji_cats ) : ?><div class="legend"><?php foreach ( $moji_cats as $moji_c ) { moji_term_chip( $moji_c ); } ?></div><?php endif; ?>
</section>

<div class="archive-list">
	<?php foreach ( $moji_tree as $moji_year => $moji_months ) : ?>
		<section class="year card">
			<h2><?php echo (int) $moji_year; ?><small><?php echo (int) array_sum( array_map( 'count', $moji_months ) ); ?> 篇</small></h2>
			<div>
				<?php foreach ( $moji_months as $moji_m => $moji_list ) : ?>
					<div class="month">
						<h3><?php echo esc_html( $moji_month_cn[ (int) $moji_m ] ); ?></h3>
						<ul>
							<?php foreach ( $moji_list as $moji_p ) : ?>
								<li><a class="c-<?php echo esc_attr( moji_post_color( $moji_p ) ); ?>" href="<?php echo esc_url( get_permalink( $moji_p ) ); ?>"><i></i><span class="d"><?php echo esc_html( get_the_date( 'm/d', $moji_p ) ); ?></span><span class="t"><?php echo esc_html( get_the_title( $moji_p ) ); ?></span><?php moji_stage_badge( $moji_p ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>
	<?php if ( ! $moji_tree ) : ?>
		<div class="card empty-box"><b>还没有笔记</b><p>写下第一篇吧。</p></div>
	<?php endif; ?>
</div>
<?php
get_footer();
