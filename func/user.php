<?php 

/**
 * 获取用户信息
 *
 * @param WP_User|int|string $id_or_email
 * @return WP_User|false
 */
function clrs_userInfo($id_or_email)
{
    $user = $email = false;

    if (is_object($id_or_email) && isset($id_or_email->comment_ID)) {
        $id_or_email = get_comment($id_or_email);
    }
 
    // Process the user identifier.
    if (is_numeric($id_or_email)) {
        $user = get_user_by('id', absint($id_or_email));
    } elseif (is_string($id_or_email) && strpos($id_or_email, '@')) {
        $email = $id_or_email;
    } elseif ($id_or_email instanceof WP_User) {
        // User Object
        $user = $id_or_email;
    } elseif ($id_or_email instanceof WP_Post) {
        // Post Object
        $user = get_user_by('id', (int)$id_or_email->post_author);
    } elseif ($id_or_email instanceof WP_Comment) {
        if (!empty($id_or_email->user_id)) {
            $user = get_user_by('id', (int)$id_or_email->user_id);
        }
        if ((!$user || is_wp_error($user)) && !empty($id_or_email->comment_author_email)) {
            $email = $id_or_email->comment_author_email;
        }
    } else {
        return false;
    }

    if (empty($user) && !empty($email)) {
        $user = get_user_by('email', $email);
    }

    return $user;
}


/* ==================权限控制====================
 *                  Author: Kenta
 * 下方代码块主要功能是限制游客访问/评论特定页面. 
 * 涉及钩子:
 *     add_meta_boxes
 *     save_post
 *     manage_page_posts_columns
 *     manage_post_posts_columns
 *     manage_pages_custom_column
 *     manage_posts_custom_column
 * 对内容的显示控制存在于以下文件中:
 *     content.php
 *     page.php
 * 对评论的显示控制存在于以下文件中:
 *     comments.php
 * =============================================
 */

/* 文章编辑器右侧的"访问权限"框
 *
 * Attention:
 * checkbox未选中时不在$_POST中出现
 * 为防止"快速编辑"提交的数据中不包含
 * 选项内容而导致判断为"非",请务必使用
 * 隐藏域作为选项的数据来源
 */
add_action('add_meta_boxes', function () {
    add_meta_box(
        'clrs_mtbox_postAccess',
        '访问权限',
        function ($post) {
            $onlyMembersCanView = get_post_meta($post->ID, 'onlyMembersCanView', true);
            $onlyMembersCanComment = get_post_meta($post->ID, 'onlyMembersCanComment', true);
            ?>
                <input type="checkbox" <?php echo $onlyMembersCanView === 'yes' ? 'checked' : '' ?> onclick="document.getElementById('input_onlyMembersCanView').value = this.checked?'yes':'no'" />
                <input type="hidden" name="onlyMembersCanView" id="input_onlyMembersCanView" value="<?php echo $onlyMembersCanView ?>" />
                <label for="onlyMembersCanView">仅登录用户可浏览</label>
            <br />
                <input type="checkbox" <?php echo $onlyMembersCanComment === 'yes' ? 'checked' : '' ?> onclick="document.getElementById('input_onlyMembersCanComment').value = this.checked?'yes':'no'" />
                <input type="hidden" name="onlyMembersCanComment" id="input_onlyMembersCanComment" value="<?php echo $onlyMembersCanComment ?>" />
                <label for="onlyMembersCanComment">仅登录用户可评论</label>
            <?php
        },
        null,
        'side',
        'low'
    );
});
//保存文章/页面时更新权限选项
add_action('save_post', function ($post_id) {
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['onlyMembersCanView'])) {
        $onlyMembersCanView = $_POST['onlyMembersCanView'] === 'yes' ? 'yes' : 'no';
        update_post_meta($post_id, 'onlyMembersCanView', $onlyMembersCanView);
    }
    if (isset($_POST['onlyMembersCanComment'])) {
        $onlyMembersCanComment = $_POST['onlyMembersCanComment'] === 'yes' ? 'yes' : 'no';
        update_post_meta($post_id, 'onlyMembersCanComment', $onlyMembersCanComment);
    }
});


//文章/页面列表 创建数据列
add_filter('manage_page_posts_columns', 'clrs_PostsAndPages_columnTitle_access');
add_filter('manage_post_posts_columns', 'clrs_PostsAndPages_columnTitle_access');
function clrs_PostsAndPages_columnTitle_access($columns)
{
    $columns['_clrs_access'] = '访问限制';
    return $columns;
}

//文章/页面列表 填充数据列
add_action('manage_pages_custom_column', 'clrs_PostsAndPages_columnContent_access', 10, 2);
add_action('manage_posts_custom_column', 'clrs_PostsAndPages_columnContent_access', 10, 2);
function clrs_PostsAndPages_columnContent_access($column_name, $id)
{
    switch ($column_name) {
        case '_clrs_access':
            $onlyMembersCanView = get_post_meta($id, 'onlyMembersCanView', true);
            $onlyMembersCanComment = get_post_meta($id, 'onlyMembersCanComment', true);

            if ($onlyMembersCanView !== 'yes' && !$onlyMembersCanComment !== 'yes') {
                echo '—';
            } else {
                $_cantdo = [];
                if ($onlyMembersCanView === 'yes') {
                    array_push($_cantdo, '浏览');
                }

                if ($onlyMembersCanComment === 'yes') {
                    array_push($_cantdo, '评论');
                }
                echo '游客不可 ' . implode($_cantdo, '、');
            }
            break;
    }
}

/**
 * 添加用户字段“职位”
 *
 * @param array $user_contactmethods 默认用户字段
 * @return array
 */
function clrs_user_contact(array $user_contactmethods)
{
    $user_contactmethods['job'] = '职位';
    return $user_contactmethods;
}
add_filter('user_contactmethods', 'clrs_user_contact');


/**
 * 禁止填写姓名，强制显示为昵称
 */
/*if ($pagenow == 'profile.php' && !isset($_COOKIE['admin'])) {
    add_action('admin_footer', function () { ?>
	<script>
	    jQuery(document).ready( function($) {
			$('.user-first-name-wrap input, .user-last-name-wrap input')
			.attr("disabled", "disabled")
			.attr("placeholder", "已禁用")
			.attr("value", "")
			.val('');
	    });
	</script>
	<?php 
});
}*/

//隐藏姓名、显示为选项
add_action('show_user_profile','edit_user_profile');
add_action('edit_user_profile','edit_user_profile');
function edit_user_profile($user){
	?>
	<script>
        jQuery(document).ready(function($) {
                $('#first_name').parent().parent().hide();
                $('#last_name').parent().parent().hide();
                $('#display_name').parent().parent().hide();
                $('.show-admin-bar').hide();
        });
    </script>
<?php 
}

//更新时强制设置显示名称为昵称 
add_action('personal_options_update','edit_user_profile_update');
add_action('edit_user_profile_update','edit_user_profile_update');
function edit_user_profile_update($user_id){ 
        if (!current_user_can('edit_user', $user_id)) return false;
        $user = get_userdata($user_id); 
        $_POST['nickname'] = ($_POST['nickname'])?:$user->user_login;
	    $_POST['display_name']	= $_POST['nickname'];
	    $_POST['first_name']	= '';
	    $_POST['last_name']		= '';
}

/**
 * 用户列表不显示姓名栏目
 */
add_filter('manage_users_columns', 'remove_name_column');
function remove_name_column($columns) {
	unset($columns['name']);
	return $columns;
}