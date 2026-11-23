<?php
/**
 * Template Name: 专题
 *
 * 列出所有分类（专题），每个专题显示最近 3 篇和成熟度比例。
 */
get_header();
$moji_cats = get_categories( array( 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'parent' => 0, 'exclude' => moji_topic_exclude() ) );
?>
<section class="page-hero">
	<h1><?php the_title(); ?><span class="hand">按领域翻</span></h1>
	<p><?php echo has_excerpt() ? esc_html( get_the_excerpt() ) : '每个专题一种颜色，标签、卡片和归档里都用同一种颜色，方便一眼认出来。'; ?></p>
</section>

<?php if ( $moji_cats ) : ?>
<div class="topics">
	<?php
	foreach ( $moji_cats as $moji_cat ) :
		$moji_ids = get_posts( array( 'category' => $moji_cat->term_id, 'numberposts' => -1, 'fields' => 'ids' ) );
		update_meta_cache( 'post', $moji_ids );
		$moji_n = array( 'ever' => 0, 'grow' => 0, 'seed' => 0 );
		foreach ( $moji_ids as $moji_id ) {
			$moji_n[ moji_stage( $moji_id ) ]++;
		}
		$moji_recent = array_slice( $moji_ids, 0, 3 );
		?>
		<section class="topic card c-<?php echo esc_attr( moji_term_color( $moji_cat ) ); ?>">
			<span class="big" aria-hidden="true"><?php echo esc_html( moji_initial( $moji_cat->name ) ); ?></span>
			<header><h2><a href="<?php echo esc_url( get_category_link( $moji_cat ) ); ?>"><?php echo esc_html( $moji_cat->name ); ?></a></h2><span><?php echo (int) $moji_cat->count; ?> 篇</span></header>
			<?php if ( $moji_cat->description ) : ?><p><?php echo esc_html( moji_mb_cut( wp_strip_all_tags( $moji_cat->description ), 40 ) ); ?></p><?php endif; ?>
			<?php if ( $moji_recent ) : ?>
				<ul>
					<?php foreach ( $moji_recent as $moji_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $moji_id ) ); ?>"><?php echo esc_html( moji_mb_cut( get_the_title( $moji_id ), 18 ) ); ?><span><?php echo esc_html( get_the_date( 'm-d', $moji_id ) ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
			<div class="mat" aria-hidden="true"><i style="flex:<?php echo (int) $moji_n['ever']; ?>"></i><i style="flex:<?php echo (int) $moji_n['grow']; ?>"></i><i style="flex:<?php echo (int) $moji_n['seed']; ?>"></i></div>
			<div class="mat-l"><span>常青 <?php echo (int) $moji_n['ever']; ?></span><span>生长 <?php echo (int) $moji_n['grow']; ?></span><span>草稿 <?php echo (int) $moji_n['seed']; ?></span></div>
		</section>
	<?php endforeach; ?>
</div>
<?php else : ?>
	<div class="card empty-box"><b>还没有专题</b><p>在“文章 → 分类”里新建分类，就会出现在这里。</p></div>
<?php endif; ?>
<?php
get_footer();
