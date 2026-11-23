<?php
/**
 * 404
 */
get_header();
?>
<section class="lost">
	<div class="num">404<span class="s s1"><b>咦？</b><span>这页还没写</span></span><span class="s s2"><b>也可能</b><span>被我改名了</span></span></div>
	<h1>这篇笔记不存在</h1>
	<p>链接可能写错了，或者笔记已经合并到别的地方。试试搜一下。</p>
	<div class="acts"><button type="button" class="btn pri" data-open-search>搜索笔记 ⌘K</button><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">回到首页</a></div>
</section>
<?php
$moji_sticky = get_option( 'sticky_posts' );
$moji_sug    = get_posts( array( 'numberposts' => 4, 'post__in' => $moji_sticky ? $moji_sticky : null, 'ignore_sticky_posts' => true ) );
if ( count( $moji_sug ) < 3 ) {
	$moji_sug = get_posts( array( 'numberposts' => 4 ) );
}
if ( $moji_sug ) :
	?>
<div class="lost-sug">
	<h2>也许你在找</h2>
	<ul class="cats">
		<?php foreach ( $moji_sug as $moji_p ) : ?>
			<li><a class="c-<?php echo esc_attr( moji_post_color( $moji_p ) ); ?>" href="<?php echo esc_url( get_permalink( $moji_p ) ); ?>"><i></i><?php echo esc_html( get_the_title( $moji_p ) ); ?><span><?php echo esc_html( get_the_date( 'm-d', $moji_p ) ); ?></span></a></li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
<?php
get_footer();
