<?php
/**
 * 正文处理：[[双链]]、目录、反向链接
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- [[双链]] ---------- */
function moji_find_post_by_title( $title ) {
	static $cache = array();
	if ( isset( $cache[ $title ] ) ) {
		return $cache[ $title ];
	}
	$q = new WP_Query( array(
		'post_type'      => array( 'post', 'page' ),
		'post_status'    => 'publish',
		'title'          => $title,
		'posts_per_page' => 1,
		'no_found_rows'  => true,
		'fields'         => 'ids',
	) );
	$cache[ $title ] = $q->posts ? (int) $q->posts[0] : 0;
	return $cache[ $title ];
}

/**
 * [[标题]] 或 [[标题|显示文字]] 转为站内链接；<pre>、<code> 里的内容不处理。
 */
add_filter( 'the_content', function ( $content ) {
	if ( false === strpos( $content, '[[' ) ) {
		return $content;
	}
	$parts = preg_split( '#(<pre\b.*?</pre>|<code\b.*?</code>)#is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	foreach ( $parts as $i => $part ) {
		if ( $i % 2 ) {
			continue;
		}
		$parts[ $i ] = preg_replace_callback( '/\[\[([^\[\]|]+?)(?:\|([^\[\]]+?))?\]\]/u', function ( $m ) {
			$title = trim( html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' ) );
			$label = isset( $m[2] ) && '' !== $m[2] ? $m[2] : $m[1];
			$id    = moji_find_post_by_title( $title );
			if ( $id ) {
				return '<a class="wiki" href="' . esc_url( get_permalink( $id ) ) . '">' . $label . '</a>';
			}
			return '<span class="wiki-missing" title="还没有这篇笔记">' . $label . '</span>';
		}, $part );
	}
	return implode( '', $parts );
}, 8 );

/* ---------- 目录 ---------- */
/**
 * 给 h2/h3 加 id，并返回目录数组。
 *
 * @return array [ 处理后的 HTML, [ [level, id, text], ... ] ]
 */
function moji_toc_process( $html ) {
	$toc  = array();
	$used = array();
	$n    = 0;
	$html = preg_replace_callback( '#<h([23])([^>]*)>(.*?)</h\1>#is', function ( $m ) use ( &$toc, &$used, &$n ) {
		$attrs = $m[2];
		$text  = trim( wp_strip_all_tags( $m[3] ) );
		if ( '' === $text ) {
			return $m[0];
		}
		if ( preg_match( '/\sid=["\']([^"\']+)["\']/', $attrs, $idm ) ) {
			$id = $idm[1];
		} else {
			$id = 'h-' . ( ++$n );
			while ( isset( $used[ $id ] ) ) {
				$id = 'h-' . ( ++$n );
			}
			$attrs .= ' id="' . $id . '"';
		}
		$used[ $id ] = true;
		$toc[]       = array( (int) $m[1], $id, $text );
		return '<h' . $m[1] . $attrs . '>' . $m[3] . '</h' . $m[1] . '>';
	}, $html );
	return array( $html, $toc );
}

/* ---------- 反向链接 ---------- */
function moji_backlinks( $post_id, $limit = 8 ) {
	$key = 'moji_bl_' . $post_id . '_' . moji_cache_ver();
	$ids = get_transient( $key );
	if ( false !== $ids ) {
		return $ids;
	}
	global $wpdb;
	$likes = array();
	$title = get_the_title( $post_id );
	if ( '' !== $title ) {
		$likes[] = '%' . $wpdb->esc_like( '[[' . html_entity_decode( $title, ENT_QUOTES, 'UTF-8' ) ) . '%';
	}
	$likes[] = '%' . $wpdb->esc_like( '?p=' . $post_id . '"' ) . '%';
	$path    = wp_parse_url( get_permalink( $post_id ), PHP_URL_PATH );
	if ( $path && '/' !== $path && strlen( $path ) > 3 ) {
		$likes[] = '%' . $wpdb->esc_like( untrailingslashit( $path ) ) . '%';
	}
	$where = implode( ' OR ', array_fill( 0, count( $likes ), 'post_content LIKE %s' ) );
	$sql   = "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post','page') AND ID <> %d AND ( {$where} ) ORDER BY post_date DESC LIMIT %d";
	$args  = array_merge( array( $post_id ), $likes, array( $limit ) );
	$ids   = array_map( 'intval', $wpdb->get_col( $wpdb->prepare( $sql, $args ) ) ); // phpcs:ignore WordPress.DB
	set_transient( $key, $ids, 12 * HOUR_IN_SECONDS );
	return $ids;
}

/* ---------- 站外图片不发送来源，绕过图床防盗链（如 CSDN） ---------- */
add_filter( 'the_content', function ( $content ) {
	if ( false === strpos( $content, '<img' ) ) {
		return $content;
	}
	$home = wp_parse_url( home_url(), PHP_URL_HOST );
	return preg_replace_callback( '/<img\b[^>]*>/i', function ( $m ) use ( $home ) {
		$tag = $m[0];
		if ( stripos( $tag, 'referrerpolicy=' ) !== false || ! preg_match( '/\ssrc=["\'](https?:)?\/\/([^\/"\']+)/i', $tag, $src ) ) {
			return $tag;
		}
		if ( strcasecmp( $src[2], $home ) === 0 ) {
			return $tag;
		}
		return preg_replace( '/^<img\b/i', '<img referrerpolicy="no-referrer"', $tag );
	}, $content );
}, 20 );

/* ---------- 密码保护：便签风格的输入框 ---------- */
add_filter( 'the_password_form', function ( $output, $post = null ) {
	$post  = get_post( $post );
	$id    = 'pwbox-' . ( $post ? $post->ID : wp_rand() );
	$wrong = $post && isset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ); // 已经输过但还是没通过
	ob_start();
	?>
<form class="moji-lock<?php echo $wrong ? ' is-wrong' : ''; ?>" action="<?php echo esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ); ?>" method="post">
	<span class="tape" aria-hidden="true"></span>
	<span class="lock-badge" aria-hidden="true"><svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10.5" width="16" height="10.5" rx="2.5"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/><circle cx="12" cy="15.6" r="1.3" fill="currentColor"/></svg></span>
	<p class="lock-title">这篇笔记上了锁</p>
	<p class="lock-desc">内容只给知道密码的人看，输入密码后就能打开。</p>
	<div class="lock-row">
		<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>">密码</label>
		<input name="post_password" id="<?php echo esc_attr( $id ); ?>" type="password" placeholder="输入密码" autocomplete="current-password" required spellcheck="false">
		<button type="submit" class="btn pri">解锁</button>
	</div>
	<?php if ( $wrong ) : ?><p class="lock-err" role="alert">密码不对，再试一次</p><?php endif; ?>
	<span class="lock-hand" aria-hidden="true">嘘，悄悄看</span>
</form>
	<?php
	return trim( preg_replace( '/>\s+</', '><', ob_get_clean() ) ); // 去掉换行，免得 wpautop 插入空段落
}, 10, 2 );
