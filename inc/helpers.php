<?php
/**
 * 通用工具：配色、成熟度、字数、摘要、统计、热力图
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 专题配色 */
function moji_palette() {
	return array(
		'coral'  => '珊瑚',
		'cobalt' => '钴蓝',
		'mint'   => '薄荷',
		'sun'    => '明黄',
		'lilac'  => '淡紫',
		'sky'    => '天蓝',
		'pink'   => '樱粉',
	);
}

/** 分类/标签的颜色：后台设置优先，否则按 ID 轮换 */
function moji_term_color( $term ) {
	$term = get_term( $term );
	if ( ! $term || is_wp_error( $term ) ) {
		return 'coral';
	}
	$color = get_term_meta( $term->term_id, 'moji_color', true );
	$keys  = array_keys( moji_palette() );
	if ( $color && in_array( $color, $keys, true ) ) {
		return $color;
	}
	return $keys[ $term->term_id % count( $keys ) ];
}

/** 文章所属专题（第一个分类，跳过“未分类”） */
function moji_post_topic( $post = null ) {
	$cats = get_the_category( $post ? get_post( $post )->ID : get_the_ID() );
	if ( ! $cats ) {
		return null;
	}
	$default = (int) get_option( 'default_category' );
	foreach ( $cats as $cat ) {
		if ( (int) $cat->term_id !== $default ) {
			return $cat;
		}
	}
	return $cats[0];
}

function moji_post_color( $post = null ) {
	$topic = moji_post_topic( $post );
	return $topic ? moji_term_color( $topic ) : 'coral';
}

/** 笔记成熟度 */
function moji_stages() {
	return array(
		'seed' => '草稿',
		'grow' => '生长中',
		'ever' => '常青',
	);
}

function moji_default_stage() {
	$d = get_theme_mod( 'moji_default_stage', 'grow' );
	return array_key_exists( $d, moji_stages() ) ? $d : 'grow';
}

function moji_stage( $post = null ) {
	$post  = get_post( $post );
	$stage = $post ? get_post_meta( $post->ID, 'moji_stage', true ) : '';
	return array_key_exists( $stage, moji_stages() ) ? $stage : moji_default_stage();
}

function moji_stage_badge( $post = null ) {
	$s      = moji_stage( $post );
	$labels = moji_stages();
	printf( '<span class="stage %s">%s</span>', esc_attr( $s ), esc_html( $labels[ $s ] ) );
}

/** 按成熟度筛选的 meta_query（未设置的文章按默认成熟度算） */
function moji_stage_meta_query( $stage ) {
	if ( $stage === moji_default_stage() ) {
		return array(
			'relation' => 'OR',
			array( 'key' => 'moji_stage', 'value' => $stage ),
			array( 'key' => 'moji_stage', 'compare' => 'NOT EXISTS' ),
			array( 'key' => 'moji_stage', 'value' => '' ),
		);
	}
	return array( array( 'key' => 'moji_stage', 'value' => $stage ) );
}

/** 字数（中文按字计，去掉空白） */
function moji_word_count( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return 0;
	}
	$cache = get_post_meta( $post->ID, '_moji_words', true );
	if ( '' !== $cache && (int) get_post_meta( $post->ID, '_moji_words_t', true ) === (int) strtotime( $post->post_modified_gmt ) ) {
		return (int) $cache;
	}
	$text  = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$text  = preg_replace( '/\s+/u', '', $text );
	$count = function_exists( 'mb_strlen' ) ? mb_strlen( $text ) : strlen( $text );
	update_post_meta( $post->ID, '_moji_words', $count );
	update_post_meta( $post->ID, '_moji_words_t', (int) strtotime( $post->post_modified_gmt ) );
	return $count;
}

function moji_read_minutes( $post = null ) {
	return max( 1, (int) ceil( moji_word_count( $post ) / 400 ) );
}

function moji_format_count( $n ) {
	if ( $n >= 10000 ) {
		return round( $n / 10000, 1 ) . '万';
	}
	return number_format_i18n( $n );
}

/** 修订版本号 */
function moji_version( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || ! wp_revisions_enabled( $post ) ) {
		return 1;
	}
	$revs = wp_get_post_revisions( $post->ID, array( 'fields' => 'ids' ) );
	$n    = 0;
	foreach ( $revs as $rid ) {
		if ( ! wp_is_post_autosave( $rid ) ) {
			$n++;
		}
	}
	return max( 1, $n );
}

/** 去掉 [[双链]] 记号，只留显示文字 */
function moji_plain_wiki( $text ) {
	return preg_replace( '/\[\[([^\[\]|]+?)(?:\|([^\[\]]+?))?\]\]/u', '$2$1', preg_replace( '/\[\[([^\[\]|]+?)\|([^\[\]]+?)\]\]/u', '$2', $text ) );
}

/** 专题查询时排除默认的“未分类” */
function moji_topic_exclude() {
	$id   = (int) get_option( 'default_category' );
	$term = $id ? get_term( $id, 'category' ) : null;
	return ( $term && ! is_wp_error( $term ) && 'uncategorized' === $term->slug ) ? array( $id ) : array();
}

/** 截取文本 */
function moji_mb_cut( $text, $len ) {
	$text = moji_plain_wiki( $text );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $text ) > $len ) {
		return mb_substr( $text, 0, $len ) . '…';
	}
	return $text;
}

/** 有密码且当前访客没输入过密码 */
function moji_locked( $post = null ) {
	$post = get_post( $post );
	return $post && post_password_required( $post );
}

function moji_excerpt( $len = 90, $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	if ( moji_locked( $post ) ) {
		return '这篇笔记设了密码，输入密码后才能查看。';
	}
	if ( has_excerpt( $post ) ) {
		return moji_mb_cut( wp_strip_all_tags( $post->post_excerpt ), $len );
	}
	$text = wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $post->post_content ) ) );
	return moji_mb_cut( $text, $len );
}

function moji_initial( $text ) {
	$text = trim( wp_strip_all_tags( $text ) );
	if ( '' === $text ) {
		return '';
	}
	$c = function_exists( 'mb_substr' ) ? mb_substr( $text, 0, 1 ) : substr( $text, 0, 1 );
	return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $c ) : strtoupper( $c );
}

/** 统计数据缓存版本：任意文章保存后失效 */
function moji_cache_ver() {
	return (int) get_option( 'moji_cache_ver', 1 );
}
add_action( 'save_post', function ( $id, $post ) {
	if ( wp_is_post_revision( $id ) || 'post' !== $post->post_type && 'page' !== $post->post_type ) {
		return;
	}
	update_option( 'moji_cache_ver', moji_cache_ver() + 1, false );
}, 10, 2 );
add_action( 'deleted_post', function () {
	update_option( 'moji_cache_ver', moji_cache_ver() + 1, false );
} );

/** 站点统计 */
function moji_site_stats() {
	$key   = 'moji_stats_' . moji_cache_ver();
	$stats = get_transient( $key );
	if ( false !== $stats ) {
		return $stats;
	}
	$ids   = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids' ) );
	$words = 0;
	foreach ( $ids as $id ) {
		$words += moji_word_count( $id );
	}
	$latest = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1, 'orderby' => 'modified' ) );
	$first  = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'numberposts' => 1, 'order' => 'ASC' ) );
	$stats  = array(
		'posts'    => count( $ids ),
		'topics'   => (int) wp_count_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'exclude' => moji_topic_exclude() ) ),
		'words'    => $words,
		'modified' => $latest ? (int) get_post_modified_time( 'U', true, $latest[0] ) : 0,
		'since'    => $first ? get_the_date( 'Y-m', $first[0] ) : '',
	);
	set_transient( $key, $stats, DAY_IN_SECONDS );
	return $stats;
}

/** 写作打卡热力图：最近 N 周，每格取当天第一篇的专题色 */
function moji_heatmap( $weeks = 20 ) {
	$key  = 'moji_heat_' . $weeks . '_' . moji_cache_ver() . '_' . current_time( 'Ymd' );
	$data = get_transient( $key );
	if ( false === $data ) {
		$now      = current_time( 'timestamp' );
		$today    = strtotime( gmdate( 'Y-m-d', $now ) );
		$dow      = (int) gmdate( 'N', $today ); // 1=周一
		$start    = $today - ( ( $weeks - 1 ) * 7 + ( $dow - 1 ) ) * DAY_IN_SECONDS;
		$posts    = get_posts( array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'numberposts' => -1,
			'date_query'  => array( array( 'after' => gmdate( 'Y-m-d 00:00:00', $start ), 'inclusive' => true ) ),
			'order'       => 'ASC',
		) );
		$days = array();
		foreach ( $posts as $p ) {
			$d = get_the_date( 'Y-m-d', $p );
			if ( ! isset( $days[ $d ] ) ) {
				$days[ $d ] = array( 'n' => 0, 'c' => moji_post_color( $p ) );
			}
			$days[ $d ]['n']++;
		}
		$cells = array();
		for ( $t = $start; $t < $start + $weeks * 7 * DAY_IN_SECONDS; $t += DAY_IN_SECONDS ) {
			$d       = gmdate( 'Y-m-d', $t );
			$cells[] = array(
				'd'      => $d,
				'n'      => isset( $days[ $d ] ) ? $days[ $d ]['n'] : 0,
				'c'      => isset( $days[ $d ] ) ? $days[ $d ]['c'] : '',
				'future' => $t > $today,
			);
		}
		$data = array( 'cells' => $cells, 'total' => count( $posts ) );
		set_transient( $key, $data, DAY_IN_SECONDS );
	}
	return $data;
}

/** 分页 */
function moji_pagination( $query = null ) {
	$args = array( 'type' => 'array', 'prev_text' => '←', 'next_text' => '→', 'mid_size' => 1 );
	if ( $query ) {
		$args['total']   = $query->max_num_pages;
		$args['current'] = max( 1, get_query_var( 'paged' ) );
	}
	$links = paginate_links( $args );
	if ( $links ) {
		echo '<nav class="pager" aria-label="分页">' . implode( '', $links ) . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/** 首页标题：{关键词} 变成彩色贴纸，换行变成 <br> */
function moji_render_headline( $text ) {
	$text = esc_html( $text );
	$i    = 0;
	$text = preg_replace_callback( '/\{([^{}]+)\}/u', function ( $m ) use ( &$i ) {
		$cls = ( $i++ % 2 ) ? 'b' : 'a';
		return '<span class="hl ' . $cls . '">' . $m[1] . '</span>';
	}, $text );
	return nl2br( $text );
}

/** 头像：自定义图片 > 单字 */
function moji_avatar() {
	$img = get_theme_mod( 'moji_profile_image' );
	echo '<div class="avatar">';
	if ( $img ) {
		echo '<img src="' . esc_url( $img ) . '" alt="">';
	} else {
		$char = get_theme_mod( 'moji_profile_char', '' );
		if ( '' === $char ) {
			$char = moji_initial( get_bloginfo( 'name' ) );
		}
		echo '<span>' . esc_html( $char ) . '</span>';
	}
	echo '</div>';
}

/** 带颜色的分类/标签 chip */
function moji_term_chip( $term, $prefix = '' ) {
	printf(
		'<a class="chip c-%s" href="%s">%s%s</a>',
		esc_attr( moji_term_color( $term ) ),
		esc_url( get_term_link( $term ) ),
		esc_html( $prefix ),
		esc_html( $term->name )
	);
}
