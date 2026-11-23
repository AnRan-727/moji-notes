<?php
/**
 * 笔记卡片。$args['big'] 为 true 时输出通栏大卡片。
 */
$moji_big   = ! empty( $args['big'] );
$moji_topic = moji_post_topic();
$moji_color = moji_post_color();
$moji_tags  = get_the_tags();
?>
<a class="note card lift c-<?php echo esc_attr( $moji_color ); ?><?php echo $moji_big ? ' big' : ''; ?>" href="<?php the_permalink(); ?>">
<?php if ( $moji_big ) : ?>
	<div class="art">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( 'large', array( 'alt' => '' ) ); ?>
		<?php else : ?>
			<i class="o1"></i><i class="o2"></i><span><?php echo esc_html( moji_initial( $moji_topic ? $moji_topic->name : get_the_title() ) ); ?></span>
		<?php endif; ?>
	</div>
	<div class="body">
		<div class="badges"><?php moji_stage_badge(); ?><?php if ( $moji_topic ) : ?><span class="chip"><?php echo esc_html( $moji_topic->name ); ?></span><?php endif; ?></div>
		<h3><?php the_title(); ?></h3>
		<p><?php echo esc_html( moji_excerpt( 110 ) ); ?></p>
		<div class="meta">
			<?php if ( $moji_tags ) : foreach ( array_slice( $moji_tags, 0, 3 ) as $moji_t ) : ?><span class="tag"><?php echo esc_html( $moji_t->name ); ?></span><?php endforeach; endif; ?>
			<span class="rt"><?php echo esc_html( get_the_date( 'm-d' ) ); ?> · <?php echo moji_locked() ? '需要密码' : (int) moji_read_minutes() . ' 分钟'; ?></span>
		</div>
	</div>
<?php else : ?>
	<div class="top"><span><?php echo esc_html( $moji_topic ? $moji_topic->name : '笔记' ); ?></span><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'm-d' ) ); ?></time></div>
	<div class="body">
		<h3><?php the_title(); ?></h3>
		<p><?php echo esc_html( moji_excerpt( 80 ) ); ?></p>
		<div class="meta">
			<?php moji_stage_badge(); ?>
			<?php if ( $moji_tags ) : ?><span class="tag"><?php echo esc_html( $moji_tags[0]->name ); ?></span><?php endif; ?>
			<span class="rt"><?php echo moji_locked() ? '需要密码' : (int) moji_read_minutes() . ' 分钟'; ?></span>
		</div>
	</div>
<?php endif; ?>
</a>
