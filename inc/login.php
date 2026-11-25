<?php
/**
 * 登录页美化 + 头部登录入口
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'login_enqueue_scripts', function () {
	$font = moji_font_url();
	if ( $font ) {
		wp_enqueue_style( 'moji-fonts', $font, array(), null );
	}
	wp_enqueue_style( 'moji-login', MOJI_URI . '/assets/css/login.css', array( 'login' ), MOJI_VER );
	$initial = moji_initial( get_bloginfo( 'name' ) );
	wp_add_inline_style( 'moji-login', '.login h1 a{--moji-initial:"' . esc_attr( addcslashes( $initial, '"\\' ) ) . '"}' );
} );

// 跟随前台的深色模式选择
add_action( 'login_head', function () {
	echo "<script>(function(){try{var t=localStorage.getItem('moji-theme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t)}}catch(e){}})();</script>\n";
} );

add_filter( 'login_headerurl', function () {
	return home_url( '/' );
} );
add_filter( 'login_headertext', function () {
	return get_bloginfo( 'name' );
} );

/** 头部右上角：没登录显示“登录”，登录后显示“后台” */
function moji_account_button() {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		printf(
			'<a class="icon-btn acct-btn is-in" href="%s" aria-label="%s" title="%s"><span>%s</span></a>',
			esc_url( current_user_can( 'edit_posts' ) ? admin_url() : get_edit_profile_url() ),
			esc_attr( '进入后台（' . $user->display_name . '）' ),
			esc_attr( $user->display_name . ' · 进入后台' ),
			esc_html( moji_initial( $user->display_name ) )
		);
		return;
	}
	$here = is_singular() ? get_permalink() : home_url( '/' );
	printf(
		'<a class="icon-btn acct-btn" href="%s" aria-label="登录" title="登录"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8.5" r="3.6"/><path d="M4.8 20c.9-3.6 3.8-5.6 7.2-5.6s6.3 2 7.2 5.6"/></svg></a>',
		esc_url( wp_login_url( $here ) )
	);
}

// 只用中文，不显示登录页底部的语言切换
add_filter( 'login_display_language_dropdown', '__return_false' );
