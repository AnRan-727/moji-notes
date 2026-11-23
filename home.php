<?php
/**
 * 博客首页（最新文章）
 */
get_header();

$moji_paged = max( 1, (int) get_query_var( 'paged' ) );
$moji_stage = isset( $_GET['stage'] ) ? sanitize_key( wp_unslash( $_GET['stage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
if ( ! array_key_exists( $moji_stage, moji_stages() ) ) {
	$moji_stage = '';
}
$moji_stats = moji_site_stats();

if ( 1 === $moji_paged && ! $moji_stage ) :
	$moji_sticky = get_option( 'sticky_posts' );
	$moji_pin    = $moji_sticky ? get_posts( array( 'post__in' => $moji_sticky, 'numberposts' => 1, 'ignore_sticky_posts' => true ) ) : array();
	$moji_week   = moji_mod( 'moji_week_text' );
	?>
	<section class="hero">
		<div>
			<h1><?php echo moji_render_headline( moji_mod( 'moji_hero_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<?php if ( moji_mod( 'moji_hero_desc' ) ) : ?><p><?php echo esc_html( moji_mod( 'moji_hero_desc' ) ); ?></p><?php endif; ?>
			<div class="chips">
				<span class="chip c-mint">常青 · 验证过</span>
				<span class="chip c-sun">生长中 · 在补充</span>
				<span class="chip ghost">草稿 · 刚记下</span>
			</div>
		</div>
		<?php if ( $moji_pin || $moji_week ) : ?>
		<div class="stack">
			<?php if ( $moji_pin ) : ?>
				<a class="sticky-note s1" href="<?php echo esc_url( get_permalink( $moji_pin[0] ) ); ?>"><span class="tape"></span><b>置顶 · <?php echo esc_html( get_the_title( $moji_pin[0] ) ); ?></b><p><?php echo esc_html( moji_excerpt( 50, $moji_pin[0] ) ); ?></p><?php $moji_h = get_post_meta( $moji_pin[0]->ID, 'moji_hand', true ); if ( $moji_h ) : ?><span class="hand"><?php echo esc_html( $moji_h ); ?></span><?php endif; ?></a>
			<?php endif; ?>
			<?php if ( $moji_week ) : ?>
				<div class="sticky-note s2"<?php echo $moji_pin ? '' : ' style="top:40px"'; ?>><span class="tape"></span><b><?php echo esc_html( moji_mod( 'moji_week_title' ) ); ?></b><p><?php echo esc_html( $moji_week ); ?></p><?php if ( moji_mod( 'moji_week_hand' ) ) : ?><span class="hand"><?php echo esc_html( moji_mod( 'moji_week_hand' ) ); ?></span><?php endif; ?></div>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</section>

	<div class="stat-row">
		<div class="stat card c-coral"><b class="mono"><?php echo esc_html( number_format_i18n( $moji_stats['posts'] ) ); ?></b><span>篇笔记</span></div>
		<div class="stat card c-sky"><b class="mono"><?php echo esc_html( $moji_stats['topics'] ); ?></b><span>个专题</span></div>
		<div class="stat card c-lilac"><b class="mono"><?php echo esc_html( moji_format_count( $moji_stats['words'] ) ); ?></b><span>字</span></div>
		<div class="stat card c-mint"><b class="mono"><?php echo $moji_stats['modified'] ? esc_html( human_time_diff( $moji_stats['modified'] ) ) : '—'; ?></b><span>前更新</span></div>
	</div>
<?php endif; ?>

<div class="home-grid">
	<div style="min-width:0">
		<div class="sec-head">
			<h2><?php echo $moji_stage ? esc_html( moji_stages()[ $moji_stage ] ) . '笔记' : '最近笔记'; ?> <span class="hand"><?php echo 1 === $moji_paged ? '新鲜出炉' : '第 ' . (int) $moji_paged . ' 页'; ?></span></h2>
			<div class="filter">
				<?php $moji_base = get_post_type_archive_link( 'post' ) ? get_post_type_archive_link( 'post' ) : home_url( '/' ); ?>
				<a class="<?php echo $moji_stage ? '' : 'on'; ?>" href="<?php echo esc_url( $moji_base ); ?>">全部</a>
				<?php foreach ( array( 'ever', 'grow', 'seed' ) as $moji_k ) : ?>
					<a class="<?php echo $moji_stage === $moji_k ? 'on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'stage', $moji_k, $moji_base ) ); ?>"><?php echo esc_html( moji_stages()[ $moji_k ] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="grid">
				<?php
				$moji_i = 0;
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/note-card', null, array( 'big' => ( 0 === $moji_i && 1 === $moji_paged ) ) );
					$moji_i++;
				endwhile;
				?>
			</div>
			<?php moji_pagination(); ?>
		<?php else : ?>
			<div class="card empty-box"><b>这里还空着</b><p>还没有<?php echo $moji_stage ? esc_html( moji_stages()[ $moji_stage ] ) : ''; ?>笔记。</p></div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>

<?php
get_footer();
