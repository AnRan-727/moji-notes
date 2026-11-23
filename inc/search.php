<?php
/**
 * 即时搜索接口：GET /wp-json/moji/v1/search?q=关键词
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'moji/v1', '/search', array(
		'methods'             => 'GET',
		'permission_callback' => '__return_true',
		'args'                => array(
			'q' => array(
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
		),
		'callback'            => 'moji_rest_search',
	) );
} );

function moji_snippet( $content, $q, $len = 70 ) {
	$text = moji_plain_wiki( wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $content ) ) ) );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	$pos  = function_exists( 'mb_stripos' ) ? mb_stripos( $text, $q ) : false;
	if ( false === $pos || $pos < 20 ) {
		return moji_mb_cut( $text, $len );
	}
	return '…' . moji_mb_cut( mb_substr( $text, $pos - 20 ), $len );
}

function moji_rest_search( WP_REST_Request $req ) {
	$q = trim( (string) $req['q'] );
	if ( '' === $q ) {
		return array( 'posts' => array(), 'terms' => array(), 'total' => 0 );
	}
	$labels = moji_stages();
	$query  = new WP_Query( array(
		's'                   => $q,
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 8,
		'ignore_sticky_posts' => true,
	) );
	$posts = array();
	foreach ( $query->posts as $p ) {
		$stage   = moji_stage( $p );
		$posts[] = array(
			'title'   => html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ),
			'url'     => get_permalink( $p ),
			'snippet' => moji_snippet( $p->post_content, $q ),
			'color'   => moji_post_color( $p ),
			'stage'   => $labels[ $stage ],
			'date'    => get_the_date( 'm-d', $p ),
		);
	}
	$terms = array();
	$found = get_terms( array(
		'taxonomy'   => array( 'category', 'post_tag' ),
		'search'     => $q,
		'number'     => 6,
		'hide_empty' => true,
	) );
	if ( ! is_wp_error( $found ) ) {
		foreach ( $found as $t ) {
			$terms[] = array(
				'name'  => ( 'post_tag' === $t->taxonomy ? '#' : '' ) . html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' ),
				'url'   => get_term_link( $t ),
				'kind'  => 'category' === $t->taxonomy ? '专题' : '标签',
				'count' => (int) $t->count,
				'color' => moji_term_color( $t ),
				'desc'  => moji_mb_cut( wp_strip_all_tags( $t->description ), 40 ),
			);
		}
	}
	return array(
		'posts'     => $posts,
		'terms'     => $terms,
		'total'     => (int) $query->found_posts,
		'searchUrl' => get_search_link( $q ),
	);
}
