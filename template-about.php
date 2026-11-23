<?php
/**
 * Template Name: 关于我
 *
 * 页面标题里用 {花括号} 包住的词会变成粉色贴纸，例如“你好，我是{小岩}”。
 * 页面“摘要”显示为标题下的介绍；正文写经历、工具等内容。
 */
get_header();
$moji_stats = moji_site_stats();
while ( have_posts() ) :
	the_post();
	$moji_raw_title = get_post_field( 'post_title', get_the_ID() );
	?>
	<section class="about-hero">
		<div>
			<h1><?php echo moji_render_headline( $moji_raw_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<?php foreach ( preg_split( "/\n\s*\n/", get_the_excerpt() ) as $moji_para ) : ?>
					<p class="lead"><?php echo esc_html( $moji_para ); ?></p>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php $moji_cats = get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 4, 'exclude' => moji_topic_exclude() ) ); ?>
			<?php if ( $moji_cats ) : ?><div class="chips"><?php foreach ( $moji_cats as $moji_c ) { moji_term_chip( $moji_c ); } ?></div><?php endif; ?>
		</div>
		<div class="card idcard">
			<div class="top"><span>NOTE-TAKER ID</span><span>No.<?php echo esc_html( str_pad( (string) $moji_stats['posts'], 4, '0', STR_PAD_LEFT ) ); ?></span></div>
			<div class="in">
				<?php moji_avatar(); ?>
				<dl>
					<dt>名字</dt><dd><?php echo esc_html( moji_mod( 'moji_profile_name' ) ? moji_mod( 'moji_profile_name' ) : get_bloginfo( 'name' ) ); ?></dd>
					<dt>在写</dt><dd><?php echo (int) $moji_stats['posts']; ?> 篇笔记</dd>
					<dt>累计</dt><dd><?php echo esc_html( moji_format_count( $moji_stats['words'] ) ); ?> 字</dd>
					<?php if ( $moji_stats['since'] ) : ?><dt>开博</dt><dd class="mono"><?php echo esc_html( $moji_stats['since'] ); ?></dd><?php endif; ?>
				</dl>
			</div>
			<?php if ( moji_mod( 'moji_about_stamp' ) ) : ?><div class="stamp"><?php echo esc_html( moji_mod( 'moji_about_stamp' ) ); ?></div><?php endif; ?>
		</div>
	</section>

	<section class="about-sec">
		<h2>一篇笔记怎么长大 <span class="hand">成熟度说明</span></h2>
		<div class="flow">
			<div class="step card c-pink"><span class="stage seed">草稿</span><b>先记下来</b><p>遇到问题当场记，命令、日志、猜测都先堆上去，不管通不通顺。</p></div>
			<div class="step card c-sun"><span class="stage grow">生长中</span><b>整理补充</b><p>问题解决后补上原因和步骤，删掉走错的路，加上参考链接。</p></div>
			<div class="step card c-mint"><span class="stage ever">常青</span><b>反复验证</b><p>同样的问题又遇到并且照着笔记解决了，才标为常青。</p></div>
		</div>
	</section>

	<?php if ( '' !== trim( get_the_content() ) ) : ?>
		<section class="about-sec">
			<div class="card page-card"><div class="prose"><?php the_content(); ?></div></div>
		</section>
	<?php endif; ?>

	<?php if ( moji_mod( 'moji_email' ) ) : ?>
		<div class="card contact">
			<b>找我聊聊</b><span class="sp"></span>
			<code id="contact-mail"><?php echo esc_html( antispambot( moji_mod( 'moji_email' ) ) ); ?></code>
			<button type="button" data-copy="#contact-mail">复制邮箱</button>
		</div>
	<?php endif; ?>
	<?php
endwhile;
get_footer();
