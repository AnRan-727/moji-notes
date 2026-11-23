<?php
/**
 * 浏览量：存在文章自定义字段 views 里（和 WP-PostViews、Argon 主题同一个字段，旧数据直接沿用）。
 * 前端页面加载后由 JS 调用接口计数，不受页面缓存影响；同一浏览器 6 小时内重复打开只算一次。
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const MOJI_VIEWS_KEY = 'views';

function moji_views( $post = null ) {
	$post = get_post( $post );
	return $post ? (int) get_post_meta( $post->ID, MOJI_VIEWS_KEY, true ) : 0;
}

/** 1234 → 1,234；12345 → 1.2万 */
function moji_views_label( $post = null ) {
	return moji_format_count( moji_views( $post ) );
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'moji/v1', '/view/(?P<id>\d+)', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $req ) {
			$id   = (int) $req['id'];
			$post = get_post( $id );
			if ( ! $post || 'publish' !== $post->post_status || 'post' !== $post->post_type ) {
				return new WP_Error( 'moji_no_post', '文章不存在', array( 'status' => 404 ) );
			}
			$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
			if ( '' === $ua || preg_match( '/bot|crawl|spider|slurp|curl|wget|python|headless/i', $ua ) ) {
				return array( 'views' => moji_views( $id ), 'counted' => false );
			}
			global $wpdb;
			// 原子加一，避免并发时丢数
			$updated = $wpdb->query( $wpdb->prepare(
				"UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + 1 WHERE post_id = %d AND meta_key = %s",
				$id,
				MOJI_VIEWS_KEY
			) ); // phpcs:ignore WordPress.DB
			if ( ! $updated ) {
				add_post_meta( $id, MOJI_VIEWS_KEY, 1, true );
			}
			wp_cache_delete( $id, 'post_meta' );
			return array( 'views' => moji_views( $id ), 'counted' => true );
		},
	) );
} );

/** 浏览最多的文章（首页侧栏用），缓存 1 小时 */
function moji_hot_posts( $n = 5 ) {
	$key = 'moji_hot_' . $n . '_' . moji_cache_ver();
	$ids = get_transient( $key );
	if ( false === $ids ) {
		$ids = get_posts( array(
			'post_type'           => 'post',
			'posts_per_page'      => $n,
			'meta_key'            => MOJI_VIEWS_KEY,
			'orderby'             => 'meta_value_num',
			'order'               => 'DESC',
			'has_password'        => false,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'fields'              => 'ids',
			'meta_query'          => array( array( 'key' => MOJI_VIEWS_KEY, 'value' => 0, 'compare' => '>', 'type' => 'NUMERIC' ) ),
		) );
		set_transient( $key, $ids, HOUR_IN_SECONDS );
	}
	return $ids;
}

/** 后台文章列表加“浏览”列 */
add_filter( 'manage_post_posts_columns', function ( $cols ) {
	$cols['moji_views'] = '浏览';
	return $cols;
} );
add_action( 'manage_post_posts_custom_column', function ( $col, $id ) {
	if ( 'moji_views' === $col ) {
		echo esc_html( moji_views_label( $id ) );
	}
}, 10, 2 );
add_filter( 'manage_edit-post_sortable_columns', function ( $cols ) {
	$cols['moji_views'] = 'moji_views';
	return $cols;
} );
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() && $q->is_main_query() && 'moji_views' === $q->get( 'orderby' ) ) {
		$q->set( 'meta_key', MOJI_VIEWS_KEY );
		$q->set( 'orderby', 'meta_value_num' );
	}
} );
