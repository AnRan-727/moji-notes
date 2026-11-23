</main>

<footer class="site-foot"><div class="wrap"><div class="foot">
	<b><?php bloginfo( 'name' ); ?><?php $moji_ft = moji_mod( 'moji_footer_text' ); if ( $moji_ft ) { echo ' · ' . esc_html( $moji_ft ); } ?></b>
	<div class="fmeta">
		<?php
		wp_nav_menu( array(
			'theme_location' => 'footer',
			'container'      => false,
			'depth'          => 1,
			'fallback_cb'    => false,
		) );
		?>
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?></span>
		<a href="<?php echo esc_url( get_bloginfo( 'rss2_url' ) ); ?>">RSS</a>
		<span>Powered by WordPress</span>
	</div>
</div></div></footer>

<div class="modal" id="search-modal" hidden>
	<form class="card sbox" role="dialog" aria-modal="true" aria-label="搜索笔记" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
		<div class="q">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
			<input id="search-input" type="search" name="s" placeholder="搜索标题、正文、标签…" autocomplete="off" aria-label="搜索关键词">
			<kbd>Esc</kbd>
		</div>
		<div class="sres" id="search-results">
			<div class="tip"><b>想找哪篇？</b>输入关键词，标题、正文、标签和专题都会搜。</div>
		</div>
		<div class="sfoot"><span><kbd>↑</kbd> <kbd>↓</kbd> 选择</span><span><kbd>↵</kbd> 打开</span><span><kbd>Esc</kbd> 关闭</span><a id="search-all" href="#" hidden>查看全部结果 →</a></div>
	</form>
</div>

<?php wp_footer(); ?>
</body>
</html>
