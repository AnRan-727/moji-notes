<?php
/**
 * 分类（专题）、标签、日期、作者归档
 */
get_header();

$moji_obj   = get_queried_object();
$moji_term  = ( $moji_obj instanceof WP_Term ) ? $moji_obj : null;
$moji_color = $moji_term ? moji_term_color( $moji_term ) : 'sun';

if ( is_tag() ) {
	$moji_title = '<span>#</span>' . esc_html( single_tag_title( '', false ) );
	$moji_kind  = '标签';
} elseif ( is_category() ) {
	$moji_title = esc_html( single_cat_title( '', false ) );
	$moji_kind  = '专题';
} elseif ( is_year() ) {
	$moji_title = esc_html( get_the_date( 'Y' ) ) . ' 年';
	$moji_kind  = '年度归档';
} elseif ( is_month() ) {
	$moji_title = esc_html( get_the_date( 'Y 年 n 月' ) );
	$moji_kind  = '月度归档';
} elseif ( is_day() ) {
	$moji_title = esc_html( get_the_date( 'Y-m-d' ) );
	$moji_kind  = '当日笔记';
} elseif ( is_author() ) {
	$moji_title = esc_html( get_the_author_meta( 'display_name', get_queried_object_id() ) );
	$moji_kind  = '作者';
} else {
	$moji_title = esc_html( wp_strip_all_tags( get_the_archive_title() ) );
	$moji_kind  = '归档';
}
$moji_desc = $moji_term ? term_description( $moji_term ) : '';
global $wp_query;
?>
<section class="term-hero c-<?php echo esc_attr( $moji_color ); ?>">
	<h1 class="big"><?php echo $moji_title; // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
	<div class="desc">
		<p><?php echo $moji_desc ? esc_html( wp_strip_all_tags( $moji_desc ) ) : esc_html( $moji_kind ) . ' · 共 ' . (int) $wp_query->found_posts . ' 篇笔记'; ?></p>
		<?php
		if ( is_tag() && $moji_term ) :
			// 相关标签：本标签下文章最常用的其它标签
			$moji_ids = get_posts( array( 'tag_id' => $moji_term->term_id, 'numberposts' => 50, 'fields' => 'ids' ) );
			$moji_rel = $moji_ids ? wp_get_object_terms( $moji_ids, 'post_tag', array( 'orderby' => 'count', 'order' => 'DESC', 'exclude' => array( $moji_term->term_id ), 'number' => 6 ) ) : array();
			if ( $moji_rel && ! is_wp_error( $moji_rel ) ) :
				?>
				<div class="rel">相关标签 <?php foreach ( $moji_rel as $moji_t ) { moji_term_chip( $moji_t ); } ?></div>
			<?php endif; ?>
		<?php elseif ( is_category() && $moji_term ) : ?>
			<?php $moji_children = get_categories( array( 'parent' => $moji_term->term_id, 'hide_empty' => true ) ); ?>
			<?php if ( $moji_children ) : ?><div class="rel">子专题 <?php foreach ( $moji_children as $moji_t ) { moji_term_chip( $moji_t ); } ?></div><?php endif; ?>
		<?php endif; ?>
	</div>
</section>

<div class="sortbar">
	<span class="cnt">共 <?php echo (int) $wp_query->found_posts; ?> 篇</span>
	<?php $moji_order = isset( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( wp_unslash( $_GET['order'] ) ) ) ? 'asc' : 'desc'; // phpcs:ignore WordPress.Security.NonceVerification ?>
	<div class="filter">
		<a class="<?php echo 'desc' === $moji_order ? 'on' : ''; ?>" href="<?php echo esc_url( remove_query_arg( array( 'order', 'paged' ) ) ); ?>">最近</a>
		<a class="<?php echo 'asc' === $moji_order ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'order', 'asc', remove_query_arg( 'paged' ) ) ); ?>">最早</a>
	</div>
</div>

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
	<div class="card empty-box"><b>这里还空着</b><p>暂时没有笔记。</p></div>
<?php endif; ?>

<?php
get_footer();
