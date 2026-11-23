<?php
/**
 * 墨记 Moji Notes 主题入口
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MOJI_VER', '1.0.7' );
define( 'MOJI_DIR', get_template_directory() );
define( 'MOJI_URI', get_template_directory_uri() );

require MOJI_DIR . '/inc/helpers.php';
require MOJI_DIR . '/inc/meta.php';
require MOJI_DIR . '/inc/content.php';
require MOJI_DIR . '/inc/customizer.php';
require MOJI_DIR . '/inc/search.php';

/* ---------- 主题基础 ---------- */
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'moji', MOJI_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 80, 'width' => 80, 'flex-width' => true, 'flex-height' => true ) );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support( 'editor-color-palette', array_map( function ( $slug, $label ) {
		$hex = array( 'coral' => '#f6a693', 'cobalt' => '#98a8f2', 'mint' => '#8ad8bb', 'sun' => '#f7dc85', 'lilac' => '#c8b6f4', 'sky' => '#95cff0', 'pink' => '#f4b2cf' );
		return array( 'name' => $label, 'slug' => 'moji-' . $slug, 'color' => $hex[ $slug ] );
	}, array_keys( moji_palette() ), moji_palette() ) );

	register_nav_menus( array(
		'primary' => '主导航',
		'footer'  => '页脚链接',
	) );
} );

add_action( 'widgets_init', function () {
	register_sidebar( array(
		'name'          => '首页侧栏（追加在内置卡片下方）',
		'id'            => 'sidebar-1',
		'before_widget' => '<section id="%1$s" class="card widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="widget-title">',
		'after_title'   => '</h4>',
	) );
} );

/* ---------- 区块样式：提示框 / 注意框 / 结论先行 ---------- */
add_action( 'init', function () {
	if ( ! function_exists( 'register_block_style' ) ) {
		return;
	}
	register_block_style( 'core/group', array( 'name' => 'moji-tldr', 'label' => '结论先行' ) );
	register_block_style( 'core/group', array( 'name' => 'moji-tip', 'label' => '提示框' ) );
	register_block_style( 'core/group', array( 'name' => 'moji-warn', 'label' => '注意框' ) );
	register_block_style( 'core/list', array( 'name' => 'moji-tldr', 'label' => '结论先行' ) );
	register_block_style( 'core/paragraph', array( 'name' => 'moji-tip', 'label' => '提示框' ) );
	register_block_style( 'core/paragraph', array( 'name' => 'moji-warn', 'label' => '注意框' ) );
} );

/* ---------- 资源 ---------- */
function moji_font_url() {
	$source = get_theme_mod( 'moji_font_source', 'loli' );
	if ( 'none' === $source ) {
		return '';
	}
	$host = 'google' === $source ? 'https://fonts.googleapis.com' : 'https://fonts.loli.net';
	return $host . '/css2?family=ZCOOL+QingKe+HuangYou&family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=JetBrains+Mono:wght@400;600&family=Long+Cang&display=swap';
}

add_action( 'wp_enqueue_scripts', function () {
	$font = moji_font_url();
	if ( $font ) {
		wp_enqueue_style( 'moji-fonts', $font, array(), null );
	}
	wp_enqueue_style( 'moji', get_stylesheet_uri(), array(), MOJI_VER );
	wp_enqueue_script( 'moji', MOJI_URI . '/assets/js/theme.js', array(), MOJI_VER, true );
	wp_localize_script( 'moji', 'MOJI', array(
		'searchApi' => esc_url_raw( rest_url( 'moji/v1/search' ) ),
		'searchUrl' => esc_url_raw( home_url( '/' ) ),
	) );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

add_action( 'enqueue_block_editor_assets', function () {
	$font = moji_font_url();
	if ( $font ) {
		wp_enqueue_style( 'moji-editor-fonts', $font, array(), null );
	}
} );

/* 在 <head> 最前面恢复深色/浅色选择，避免闪烁 */
add_action( 'wp_head', function () {
	echo "<script>(function(){try{var t=localStorage.getItem('moji-theme');if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t)}}catch(e){}})();</script>\n";
}, 1 );

/* ---------- 查询调整 ---------- */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_home() ) {
		// 置顶文章已经出现在首页便签里，列表中不再重复置顶
		$q->set( 'ignore_sticky_posts', true );
		$stage = isset( $_GET['stage'] ) ? sanitize_key( wp_unslash( $_GET['stage'] ) ) : '';
		if ( $stage && array_key_exists( $stage, moji_stages() ) ) {
			$q->set( 'meta_query', moji_stage_meta_query( $stage ) );
		}
	}
	if ( $q->is_search() ) {
		$q->set( 'post_type', 'post' );
	}
} );

/* 摘要长度与省略号 */
add_filter( 'excerpt_more', function () {
	return '…';
} );

/* “关于我”页标题里的 {花括号} 只用来做贴纸效果，其它地方显示时去掉 */
function moji_strip_braces( $title, $id = 0 ) {
	if ( $id && 'page' === get_post_type( $id ) && 'template-about.php' === get_page_template_slug( $id ) ) {
		return str_replace( array( '{', '}' ), '', $title );
	}
	return $title;
}
add_filter( 'the_title', 'moji_strip_braces', 10, 2 );
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_page() && isset( $parts['title'] ) ) {
		$parts['title'] = moji_strip_braces( $parts['title'], get_queried_object_id() );
	}
	return $parts;
} );
