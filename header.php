<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main">跳到正文</a>

<header class="site-head"><div class="wrap"><div class="bar">
	<?php if ( has_custom_logo() ) : ?>
		<div class="logo"><?php the_custom_logo(); ?><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></div>
	<?php else : ?>
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><span class="blob"><?php echo esc_html( moji_initial( get_bloginfo( 'name' ) ) ); ?></span><?php bloginfo( 'name' ); ?></a>
	<?php endif; ?>

	<nav class="nav" id="site-nav" aria-label="主导航">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'depth'          => 1,
			'fallback_cb'    => function () {
				echo '<ul>';
				wp_list_pages( array( 'title_li' => '', 'depth' => 1, 'number' => 5 ) );
				echo '</ul>';
			},
		) );
		?>
	</nav>

	<div class="head-tools">
		<button type="button" class="search-btn" data-open-search aria-label="搜索笔记">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
			<span>搜点什么…</span><kbd>⌘K</kbd>
		</button>
		<button type="button" class="icon-btn" id="theme-toggle" aria-label="切换深色模式">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/></svg>
		</button>
		<?php moji_account_button(); ?>
		<button type="button" class="icon-btn menu-btn" id="menu-toggle" aria-label="打开菜单" aria-controls="site-nav" aria-expanded="false">
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
		</button>
	</div>
</div></div></header>

<main id="main" class="site-main wrap">
