<?php

/**
 * 评论附加函数
 *
 * @param integer $id
 * @return void
 */
function delete_comment_link(int $id)
{
    if (current_user_can('level_5')) {
        echo '<a class="comment-edit-link" href="' . admin_url("comment.php?action=cdc&c=$id") . '">删除</a> ';
    }
}

/**
 * 定义评论显示
 *
 * @param [type] $comment
 * @param [type] $args
 * @param [type] $depth
 * @return void
 */
function clrs_comment($comment, $args, $depth)
{
    $comment_type = $comment->comment_type;
    if ($comment_type === 'pingback' ||
        $comment_type === 'trackback') {
        echo '<li ' . comment_class() . ' id="comment-' . comment_ID() . '"><p>';

        echo 'Pingback ';
        comment_author_link();
        echo '<span class="edit-link"><a class="comment-edit-link" href="'.get_edit_comment_link(get_comment_ID()).'">'.__('编辑', 'clrs').'</a></span>';
        echo '</p></li>';
    } else {
        $comment_attrs = [
            'class="' . implode(' ', get_comment_class('clearfix')) . '"',
            $depth > 2 ? 'style="margin-left:-50px;margin-right:-15px;"' : '',
            'id="li-comment-' . get_comment_ID() . '"'
        ];
        echo '<li ' . implode(' ', $comment_attrs) . '>'; ?>
	        <article id="comment-<?php comment_ID(); ?>" class="comment">
	            <header class="comment-meta comment-author vcard">
	                <?php
                echo get_avatar($comment, 44);
                printf(
                    '<div class="comment_meta_head"><cite class="fn">%1$s',
                    get_comment_author_link()
                );
                printf(
                    '%1$s </cite>',
                    ($comment->user_id === $comment->post_author) ? '<span class="comment_meta_auth"> ' . __('', 'clrs') . '</span>' : ''
                );
                printf(
                    '</div><span class="comment_meta_time"><a href="%1$s"><time datetime="%2$s">%3$s</time></a></span>',
                    esc_url(get_comment_link($comment->comment_ID)),
                    get_comment_time('c'),
                    sprintf('%1$s %2$s', get_comment_date(), get_comment_time())
                );
                ?>
	            </header>
	
	            <?php if ('0' == $comment->comment_approved) : ?>
	            <p class="comment-awaiting-moderation">
	                <?php __('您的评论正在等待审核', 'clrs'); ?>
	            </p>
	            <?php endif; ?>
	
	            <section class="comment-content comment">
	                <?php comment_text(); ?>
                    <?php if (current_user_can('edit_comment', get_comment_ID())) : ?>
                    <span class="edit-link"><a class="comment-edit-link" href="<?php echo get_edit_comment_link(get_comment_ID()); ?>"><?php echo __('编辑', 'clrs') ?></a></span>
                    <?php endif; ?>
	                <?php delete_comment_link(get_comment_ID()); ?>
	                <?php echo str_replace('<a','<a ',get_comment_reply_link(array_merge($args, array('reply_text' => __('回复', 'clrs'), 'after' => '', 'depth' => $depth, 'max_depth' => 100)))); ?>
	            </section>
	        </article>
	    <?php
    //echo '</li>';
    }
}

/**
 * 发表评论时不需要填写“站点”
 *
 * @param array $fields 默认表单域
 * @return array
 */
function alter_comment_form_fields(array $fields)
{
    $fields['url'] = '';
    return $fields;
}
add_filter('comment_form_default_fields', 'alter_comment_form_fields');

/**
 * 格式化评论链接
 */
remove_filter('comment_text', 'make_clickable', 9);
include(get_stylesheet_directory() . '/func/parseURI.php');
add_filter('comment_text', 'kt_make_clickable');

/**
 * 评论添加@功能
 *
 * @param string $comment_text 评论内容
 * @param WP_Comment|null $comment 评论对象；邮件通知等调用可能不提供
 * @return string
 */
function clrs_comment_add_at($comment_text, $comment = null)
{
    if ($comment instanceof WP_Comment && $comment->comment_parent > 0) {
        $comment_text = '回复 <a href="#comment-' . $comment->comment_parent . '" target="_self">@' . get_comment_author($comment->comment_parent) . '</a>： ' . $comment_text;
    }
    return $comment_text;
}
add_filter('comment_text', 'clrs_comment_add_at', 20, 2);

/**
 * 评论区默认记录cookies
 */
add_filter( 'comment_form_default_fields', function( $fields ) {
	$fields['cookies'] = '<input style="display: none;" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" checked="true" value="yes"/>';
	return $fields;
} );


/**
 * 游客评论人机验证
 */
function clrs_protection_pre($commentdata)
{
    if (is_user_logged_in() || !in_array($commentdata['comment_type'] ?? 'comment', ['', 'comment'], true)) {
        return $commentdata;
    }

    $sum = filter_var($_POST['sum'] ?? null, FILTER_VALIDATE_INT);
    $num1 = filter_var($_POST['num1'] ?? null, FILTER_VALIDATE_INT);
    $num2 = filter_var($_POST['num2'] ?? null, FILTER_VALIDATE_INT);
    if ($sum === false || $num1 === false || $num2 === false || $sum !== $num1 + $num2) {
        echo "<script>alert('人机验证失败，请输入正确的计算结果。')</script>";
        echo "<script>window.history.go(-1)</script>";
        exit;
    }
    return $commentdata;
}

add_filter('preprocess_comment', 'clrs_protection_pre');
