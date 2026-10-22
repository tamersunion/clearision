<?php

/**
 * Build the comment form without relying on guest-only field wrapper hooks.
 */
function clrs_get_comment_form_args(): array
{
    $user = wp_get_current_user();
    $commenter = wp_get_current_commenter();
    $permalink = apply_filters('the_permalink', get_permalink());
    $num1 = rand(0, 9);
    $num2 = rand(0, 9);

    return [
        'id_form' => 'comment_form',
        'class_form' => 'comment-form clrs-comment-form',
        'id_submit' => 'comment_submit',
        'title_reply' => '',
        'title_reply_to' => __('评论 %s', 'clrs'),
        'cancel_reply_link' => __('撤销评论', 'clrs'),
        'label_submit' => __('提交', 'clrs'),
        'comment_field' => '<p class="comment-form-comment"><textarea id="comment" name="comment" rows="8" required aria-required="true" aria-label="' . esc_attr__('评论', 'clrs') . '"></textarea></p>',
        'must_log_in' => '<p class="must-log-in">' . sprintf(
            __('你必须 <a href="%s">登录</a> 才能发表评论。', 'clrs'),
            esc_url(wp_login_url($permalink))
        ) . '</p>',
        'logged_in_as' => '<div class="logged-in-as clrs-comment-account">' . sprintf(
            '<a class="clrs-comment-profile" href="%1$s">%2$s<span>%3$s</span></a><a class="clrs-comment-logout" href="%4$s" title="%5$s">%6$s</a>',
            esc_url(admin_url('profile.php')),
            get_avatar($user->user_email, 44),
            esc_html($user->display_name),
            esc_url(wp_logout_url($permalink)),
            esc_attr__('登出并返回登录界面', 'clrs'),
            esc_html__('登出', 'clrs')
        ) . '</div>',
        'submit_field' => '<div class="clrs-comment-actions">%1$s %2$s</div>',
        'fields' => apply_filters('comment_form_default_fields', [
            'author' => '<div class="clrs-comment-field"><input placeholder="' . esc_attr__('昵称', 'clrs') . '" aria-label="' . esc_attr__('昵称', 'clrs') . '" id="author" name="author" type="text" required="required" value="' . esc_attr($commenter['comment_author']) . '" /></div>',
            'email' => '<div class="clrs-comment-field"><input placeholder="' . esc_attr__('邮箱', 'clrs') . '" aria-label="' . esc_attr__('邮箱', 'clrs') . '" id="email" name="email" type="email" required="required" value="' . esc_attr($commenter['comment_author_email']) . '" /></div>',
            'url' => '<div class="clrs-comment-field"><input placeholder="' . esc_attr__('站点', 'clrs') . '" aria-label="' . esc_attr__('站点', 'clrs') . '" id="url" name="url" type="text" value="' . esc_attr($commenter['comment_author_url']) . '" /></div>',
            'captcha' => '<div class="clrs-comment-field clrs-comment-captcha"><input type="hidden" name="num1" value="' . $num1 . '" /><input type="hidden" name="num2" value="' . $num2 . '" /><input type="text" name="sum" required="required" placeholder="请计算：' . $num1 . ' + ' . $num2 . ' = ?" aria-label="请计算：' . $num1 . ' + ' . $num2 . ' = ?" id="sum" /></div>',
        ]),
    ];
}
