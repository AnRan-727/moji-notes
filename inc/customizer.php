<?php
/**
 * 外观 → 自定义 → 墨记主题
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function moji_mod( $key ) {
	$defaults = array(
		'moji_hero_title'  => "把{踩过的坑}\n写成{找得到}的笔记",
		'moji_hero_desc'   => '工作中的技术笔记。每篇都会反复修订，用颜色标出它有多靠谱。',
		'moji_week_title'  => '本周在写',
		'moji_week_text'   => '',
		'moji_week_hand'   => '',
		'moji_memo_title'  => '今日一句',
		'moji_memo_text'   => '记下来，才算学会。',
		'moji_profile_name'=> '',
		'moji_profile_bio' => '',
		'moji_github'      => '',
		'moji_email'       => '',
		'moji_footer_text' => '记下来，才算学会',
		'moji_about_stamp' => '记下来，才算学会',
	);
	$val = get_theme_mod( $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
	return is_string( $val ) ? $val : '';
}

add_action( 'customize_register', function ( $wp ) {
	$wp->add_panel( 'moji', array( 'title' => '墨记主题', 'priority' => 30 ) );

	$sections = array(
		'moji_home'    => '首页',
		'moji_profile' => '个人信息',
		'moji_misc'    => '笔记与字体',
	);
	foreach ( $sections as $id => $title ) {
		$wp->add_section( $id, array( 'title' => $title, 'panel' => 'moji' ) );
	}

	$fields = array(
		array( 'moji_hero_title', 'moji_home', '首页大标题', 'textarea', '用 {花括号} 包住的词会变成彩色贴纸，换行即换行。' ),
		array( 'moji_hero_desc', 'moji_home', '大标题下的介绍', 'textarea', '' ),
		array( 'moji_week_title', 'moji_home', '第二张便签：标题', 'text', '第一张便签自动显示最新的置顶文章。' ),
		array( 'moji_week_text', 'moji_home', '第二张便签：内容', 'textarea', '留空则不显示这张便签。' ),
		array( 'moji_week_hand', 'moji_home', '第二张便签：手写批注', 'text', '' ),
		array( 'moji_memo_title', 'moji_home', '侧栏黄色便签：标题', 'text', '' ),
		array( 'moji_memo_text', 'moji_home', '侧栏黄色便签：内容', 'textarea', '留空则不显示。' ),
		array( 'moji_profile_name', 'moji_profile', '显示名称', 'text', '留空使用站点标题。' ),
		array( 'moji_profile_bio', 'moji_profile', '一句话介绍', 'text', '留空使用站点副标题。' ),
		array( 'moji_profile_char', 'moji_profile', '头像文字（单个字）', 'text', '没有上传头像时显示。' ),
		array( 'moji_github', 'moji_profile', 'GitHub 地址', 'url', '' ),
		array( 'moji_email', 'moji_profile', '公开邮箱', 'email', '显示在“关于我”页，可一键复制。' ),
		array( 'moji_about_stamp', 'moji_profile', '“关于我”名片上的手写字', 'text', '' ),
		array( 'moji_footer_text', 'moji_misc', '页脚标语', 'text', '' ),
	);
	foreach ( $fields as $f ) {
		list( $id, $sec, $label, $type, $desc ) = $f;
		$san = 'url' === $type ? 'esc_url_raw' : ( 'email' === $type ? 'sanitize_email' : ( 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field' ) );
		$wp->add_setting( $id, array( 'default' => moji_mod( $id ), 'sanitize_callback' => $san ) );
		$wp->add_control( $id, array( 'label' => $label, 'section' => $sec, 'type' => $type, 'description' => $desc ) );
	}

	$wp->add_setting( 'moji_profile_image', array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
	$wp->add_control( new WP_Customize_Image_Control( $wp, 'moji_profile_image', array( 'label' => '头像图片', 'section' => 'moji_profile' ) ) );

	$wp->add_setting( 'moji_default_stage', array( 'default' => 'grow', 'sanitize_callback' => function ( $v ) {
		return array_key_exists( $v, moji_stages() ) ? $v : 'grow';
	} ) );
	$wp->add_control( 'moji_default_stage', array(
		'label'       => '未标记文章的默认成熟度',
		'section'     => 'moji_misc',
		'type'        => 'select',
		'choices'     => moji_stages(),
		'description' => '旧文章没有设置成熟度时按这个显示。',
	) );

	$wp->add_setting( 'moji_font_source', array( 'default' => 'loli', 'sanitize_callback' => function ( $v ) {
		return in_array( $v, array( 'loli', 'google', 'none' ), true ) ? $v : 'loli';
	} ) );
	$wp->add_control( 'moji_font_source', array(
		'label'       => '字体加载源',
		'section'     => 'moji_misc',
		'type'        => 'select',
		'choices'     => array(
			'loli'   => '国内镜像 fonts.loli.net（推荐）',
			'google' => 'Google Fonts',
			'none'   => '不加载，使用系统字体',
		),
		'description' => '标题用的站酷庆科黄油体、手写体都来自这里。国内访问 Google Fonts 可能很慢。',
	) );
} );
