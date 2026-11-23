<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="s-<?php echo esc_attr( wp_unique_id() ); ?>">搜索</label>
	<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="搜索笔记…">
	<button type="submit" class="btn pri">搜索</button>
</form>
