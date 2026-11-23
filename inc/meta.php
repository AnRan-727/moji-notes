<?php
/**
 * 后台字段：专题颜色、笔记成熟度、手写批注
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- 分类 / 标签颜色 ---------- */
function moji_color_select( $current = '' ) {
	echo '<select name="moji_color" id="moji_color"><option value="">自动分配</option>';
	foreach ( moji_palette() as $slug => $label ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $slug ), selected( $current, $slug, false ), esc_html( $label ) );
	}
	echo '</select>';
	wp_nonce_field( 'moji_term_color', 'moji_term_nonce' );
}

foreach ( array( 'category', 'post_tag' ) as $moji_tax ) {
	add_action( "{$moji_tax}_add_form_fields", function () {
		echo '<div class="form-field"><label for="moji_color">主题颜色</label>';
		moji_color_select();
		echo '<p>卡片、标签、归档圆点和写作打卡都会用这个颜色。</p></div>';
	} );
	add_action( "{$moji_tax}_edit_form_fields", function ( $term ) {
		echo '<tr class="form-field"><th scope="row"><label for="moji_color">主题颜色</label></th><td>';
		moji_color_select( get_term_meta( $term->term_id, 'moji_color', true ) );
		echo '<p class="description">卡片、标签、归档圆点和写作打卡都会用这个颜色。</p></td></tr>';
	} );
	add_action( "created_{$moji_tax}", 'moji_save_term_color' );
	add_action( "edited_{$moji_tax}", 'moji_save_term_color' );
	add_filter( "manage_edit-{$moji_tax}_columns", function ( $cols ) {
		$cols['moji_color'] = '颜色';
		return $cols;
	} );
	add_filter( "manage_{$moji_tax}_custom_column", function ( $out, $col, $term_id ) {
		if ( 'moji_color' !== $col ) {
			return $out;
		}
		$c      = moji_term_color( $term_id );
		$labels = moji_palette();
		$hex    = array( 'coral' => '#f6a693', 'cobalt' => '#98a8f2', 'mint' => '#8ad8bb', 'sun' => '#f7dc85', 'lilac' => '#c8b6f4', 'sky' => '#95cff0', 'pink' => '#f4b2cf' );
		return sprintf( '<span style="display:inline-block;width:12px;height:12px;border-radius:3px;border:1px solid #333;background:%s;vertical-align:-1px"></span> %s', esc_attr( $hex[ $c ] ), esc_html( $labels[ $c ] ) );
	}, 10, 3 );
}
unset( $moji_tax );

function moji_save_term_color( $term_id ) {
	if ( ! isset( $_POST['moji_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moji_term_nonce'] ) ), 'moji_term_color' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$c = isset( $_POST['moji_color'] ) ? sanitize_key( wp_unslash( $_POST['moji_color'] ) ) : '';
	if ( $c && array_key_exists( $c, moji_palette() ) ) {
		update_term_meta( $term_id, 'moji_color', $c );
	} else {
		delete_term_meta( $term_id, 'moji_color' );
	}
	update_option( 'moji_cache_ver', moji_cache_ver() + 1, false );
}

/* ---------- 文章：成熟度 + 手写批注 ---------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'moji_note', '笔记信息', 'moji_note_box', 'post', 'side', 'high' );
} );

function moji_note_box( $post ) {
	wp_nonce_field( 'moji_note_box', 'moji_note_nonce' );
	$stage = get_post_meta( $post->ID, 'moji_stage', true );
	$hand  = get_post_meta( $post->ID, 'moji_hand', true );
	echo '<p><strong>成熟度</strong></p>';
	foreach ( moji_stages() as $k => $label ) {
		$desc = array( 'seed' => '刚记下，还很乱', 'grow' => '在补充整理', 'ever' => '反复验证过' );
		printf(
			'<label style="display:block;margin:4px 0"><input type="radio" name="moji_stage" value="%s"%s> %s <span style="color:#888">· %s</span></label>',
			esc_attr( $k ),
			checked( $stage ? $stage : moji_default_stage(), $k, false ),
			esc_html( $label ),
			esc_html( $desc[ $k ] )
		);
	}
	echo '<p style="margin-top:14px"><label for="moji_hand"><strong>标题区手写批注</strong></label></p>';
	printf( '<input type="text" id="moji_hand" name="moji_hand" value="%s" placeholder="例如：这个坑踩了两天" style="width:100%%">', esc_attr( $hand ) );
	echo '<p class="description">显示在文章标题区右上角，可留空。</p>';
}

add_action( 'save_post_post', function ( $post_id ) {
	if ( ! isset( $_POST['moji_note_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moji_note_nonce'] ) ), 'moji_note_box' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$stage = isset( $_POST['moji_stage'] ) ? sanitize_key( wp_unslash( $_POST['moji_stage'] ) ) : '';
	if ( array_key_exists( $stage, moji_stages() ) ) {
		update_post_meta( $post_id, 'moji_stage', $stage );
	}
	$hand = isset( $_POST['moji_hand'] ) ? sanitize_text_field( wp_unslash( $_POST['moji_hand'] ) ) : '';
	if ( '' === $hand ) {
		delete_post_meta( $post_id, 'moji_hand' );
	} else {
		update_post_meta( $post_id, 'moji_hand', $hand );
	}
} );

/* 文章列表显示成熟度 */
add_filter( 'manage_post_posts_columns', function ( $cols ) {
	$cols['moji_stage'] = '成熟度';
	return $cols;
} );
add_action( 'manage_post_posts_custom_column', function ( $col, $id ) {
	if ( 'moji_stage' === $col ) {
		$labels = moji_stages();
		echo esc_html( $labels[ moji_stage( $id ) ] );
	}
}, 10, 2 );
