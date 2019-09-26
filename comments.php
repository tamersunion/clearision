<?php
if ( post_password_required() ) return;
if ( ! have_comments() && ! comments_open() ) return;
$onlyMembersCanComment = get_post_meta(get_the_ID(), 'onlyMembersCanComment', true) === 'yes' ? true : false;
if($onlyMembersCanComment && !is_user_logged_in()) return;
?>

<div class="page-warp">
<div class="content">
<h2 class="comment-title">评论</h2>
<div id="comments" class="comments">
    

<?php global $current_user; 
    wp_get_current_user();
    $commenter = wp_get_current_commenter();
    ?>

<?php $comments_args = array(
  'id_form'           => 'comment_form',
  'id_submit'         => 'comment_submit',
  'title_reply'       => '',
  'title_reply_to'    => __('评论 %s','clrs'),
  'cancel_reply_link' => __('撤销评论','clrs'),
  'label_submit'      => __('提交','clrs'),

  'comment_field' =>  '<div class="row"><div class="col-12" ><p class="comment-form-comment"><textarea style="max-width: none; width: 100%;" id="comment" name="comment" rows="8" required aria-required="true"></textarea></p></div></div>',
  'must_log_in' => '<p class="must-log-in">' .
    sprintf(
      __( '你必须 <a href="%s">登录</a> 才能发表评论。' , 'clrs' ),
      wp_login_url( apply_filters( 'the_permalink', get_permalink() ) )
    ) . '</p>',
    /*'logged_in_as' => '<p class="logged-in-as">' .
        sprintf(
    __('您当前使用的账号是 <a href="%1$s">%2$s</a> 。 <a href="%3$s" style="float: right;" title="登出并返回登录界面">登出</a>','clrs'),
    admin_url( 'profile.php' ),
      $user_identity,
      wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ),
      get_avatar( $current_user->user_email, 44)
    ) . '</p>',*/
   'logged_in_as' => '<div class="row"><table class="logged-in-as no_border comment-meta"><tbody>' .
    sprintf(
    __('
    <tr>
         <td style="width: 20px; height: 50px; vertical-align: middle;" ><a style="font-weight: 600;" href="%1$s">%4$s</a></td>
         <td style="vertical-align: middle;"><a style="font-weight: 600;" href="%1$s">%2$s</a></td>
         <td style="text-align: right; vertical-align: middle;"><a href="%3$s" title="登出并返回登录界面">登出</a></td>
    </tr>
    ','clrs'),
      admin_url( 'profile.php' ),
      $user_identity,
      wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ),
      get_avatar( $current_user->user_email, 44)
    ) . '</tbody></table></div>',

  //'comment_notes_before' => '',
  //'comment_notes_after' => '',
  'submit_field'         => '<div class="row"><div class="col-12">%1$s %2$s</div></div>',
  'fields' => apply_filters( 'comment_form_default_fields', array(

    'author' =>
      '<div class="col-12 col-sm-6"><input style="width: 100%;" placeholder="'.__('昵称','clrs').'" id="author" name="author" type="text" required="required" value="' . esc_attr( $commenter['comment_author'] ) .
      '" /></div>',

    'email' =>
      '<div class="col-12 col-sm-6" ><input style="width: 100%;" placeholder="'.__('邮箱','clrs').'" id="email" name="email" type="email" required="required" value="' . esc_attr(  $commenter['comment_author_email'] ) .
      '" /></div>',

    'url' =>
      '<div class="col-12 col-sm-6"><input placeholder="'.__('站点','clrs').'" id="url" name="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .
      '" /></div>'
    )
  ),
);

add_action('comment_form_top', function(){
  echo '<div class="container-fluid" style="margin: unset;">';
});

add_action('comment_form_before_fields', function(){
  echo '<div class="row">';
});

add_action('comment_form_after_fields', function(){
  echo '</div>';
});

add_action('comment_form', function(){
  echo '</div>';
});
?>

	<?php comment_form($comments_args); ?>
<br>	
		<?php if ( have_comments() ) : ?>

		<ol class="comment_list">
			<?php wp_list_comments( array( 'callback' => 'clrs_comment', 'style' => 'ol' ) ); ?>
		</ol>
        
        <div class="comment_page_nav">
		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : 
			paginate_comments_links( array('prev_text' => '上一页', 'next_text' => '下一页') );
		endif; ?>
        </div>

		<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p class="nocomments" style="display:none; "><?php __('抱歉，此处不开放评论。','clrs'); ?></p>
		<?php endif; ?>

	<?php endif; ?>


</div>
</div>
</div>