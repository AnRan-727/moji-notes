<?php
/**
 * 评论
 */
if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="card comments">
	<?php if ( have_comments() ) : ?>
		<h3><?php echo (int) get_comments_number(); ?> 条讨论</h3>
		<ol class="comment-list">
			<?php wp_list_comments( array( 'style' => 'ol', 'avatar_size' => 36, 'short_ping' => true ) ); ?>
		</ol>
		<?php the_comments_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p>评论已关闭。</p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'title_reply'        => '留个言',
		'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
		'title_reply_after'  => '</h3>',
		'label_submit'       => '发表',
		'comment_notes_before' => '',
	) );
	?>
</section>
