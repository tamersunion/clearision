<?php
if (post_password_required() || (!have_comments() && !comments_open())) {
    return;
}

$onlyMembersCanComment = get_post_meta(get_the_ID(), 'onlyMembersCanComment', true) === 'yes';
if ($onlyMembersCanComment && !is_user_logged_in()) {
    return;
}
?>

<div class="page-warp">
    <div class="content">
        <h2 class="comment-title">评论</h2>
        <div id="comments" class="comments">
            <?php comment_form(clrs_get_comment_form_args()); ?>
            <br>

            <?php if (have_comments()) : ?>
                <ol class="comment_list">
                    <?php wp_list_comments(['callback' => 'clrs_comment', 'style' => 'ol']); ?>
                </ol>

                <div class="comment_page_nav">
                    <?php
                    if (get_comment_pages_count() > 1 && get_option('page_comments')) {
                        paginate_comments_links(['prev_text' => '上一页', 'next_text' => '下一页']);
                    }
                    ?>
                </div>

                <?php if (!comments_open() && get_comments_number()) : ?>
                    <p class="nocomments" style="display:none;"><?php echo esc_html__('抱歉，此处不开放评论。', 'clrs'); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
