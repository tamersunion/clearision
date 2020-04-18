<?php
/**
 * 短代码： 当前用户属性
 */
function clrs_shortcode_current_user($atts, $content = '')
{
    $atts = shortcode_atts([
        'type' => 'display_name',
        'guest' => '未登录'
    ], $atts, 'current_user');

    if (!is_user_logged_in()) {
        return $atts['guest'];
    }

    $current_user = wp_get_current_user();
    switch ($atts['type']) {
        case 'login':
            return $current_user->user_login;
            break;
        case 'email':
            return $current_user->user_email;
            break;
        case 'avatar':
            $avatar = get_avatar_data($current_user);
            return $avatar['url'];
            break;
        case 'firstname':
            return $current_user->user_firstname;
            break;
        case 'lastname':
            return $current_user->user_lastname;
            break;
		case 'job':
		    return $current_user->job;
		    break;
		case 'desc':
		    return $current_user->description;
		    break;
        case 'id':
            return $current_user->ID;
            break;
        case 'registered':
            $registered = $current_user->user_registered;
            return date('Y年m月d日', strtotime($registered));
            break;
        case 'display_name':
        default:
            return $current_user->display_name;
            break;
    }
}


/**
 * 短代码： 当前时间(已修正时区)
 */
function clrs_shortcode_time($atts, $content = '')
{
    $atts = shortcode_atts([
        'format' => 'Y-m-d H:i:s',
    ], $atts, 'time');
    return date($atts['format'], clrs_time());
}

/**
 * 短代码： 登录可见
 */
function clrs_shortcode_for_members($atts, $content = '')
{
    if (is_user_logged_in()) {
        return $content;
    } else {
        $_ = '<div class="view_after_login" style="text-align:center;border:1px dashed #FF9A9A;padding:8px;margin:10px auto;color: white; background: rgba(255,102,102,0.45);">';
        $_a = '<a href="' . wp_login_url(get_permalink()) . '">' . __('登录', 'clrs') . '</a>';
        $_ .= sprintf(__('此内容需 %s 后方可浏览', 'clrs'), $_a);
        $_ .= '</div>';
        return $_;
    }
}

/**
 * 短代码： 用户资料卡
 */
function clrs_shortcode_pfcard($atts, $content = '')
{
    $atts = shortcode_atts([
        'id' => '',
        'user' => '',
        'job' => '',
        'desc' => '',
        'title' => '',
        'avatar' => '',
    ], $atts, 'pfcard');

    if (is_string($atts['user'])) {
        if ($atts['user'] === '__current_user__') {
            $current_user = wp_get_current_user();
        } else {
            $current_user = get_user_by('login', $atts['user']);
        }
        $u_login = $current_user->user_login;
        $u_id = $current_user->ID;
    } else if (is_numeric($atts['id'])) {
        $current_user = get_userdata($atts['id']);
        $u_login = $current_user->user_login;
        $u_id = $current_user->ID;
    } else {
        $current_user = null;
        $u_login = '';
        $u_id = '';
    }

    if (empty($atts['title'])) {
        if ($current_user) {
            $title = $current_user->display_name;
        } else {
            $title = '(unknow)';
        }
    } else {
        $title = $atts['title'];
    }

    if ($current_user) {
        $job = do_shortcode(get_user_meta($u_id, 'job', true));
        $desc = do_shortcode(get_user_meta($u_id, 'description', true));
    } else {
        $job = '';
        $desc = '';
    }
    
    //根据卡片信息更新用户信息
    //if(isset($_GET['update_job']) && !empty($atts['job']) &&  $job !== $atts['job']){
        //update_user_meta( $u_id, 'job', $atts['job'] );
    //}

    if (!empty($atts['desc']) && empty($desc)) {
        $desc = $atts['desc'];
    }

    if (!empty($atts['job']) && empty($job)) {
        $job = $atts['job'];
    }

    if (empty($atts['avatar'])) {
        $avatar = get_avatar_data($u_id);
        $avatar = $avatar['url'];
    } else {
        $avatar = $atts['avatar'];
    }

    return
        '<div style="cursor: pointer;" class="pfcard_warper col-sm-12 col-md-6 col-lg-4 un_' . $u_login . '" onclick="window.location.href=\''.get_author_posts_url($u_id).'\'">' .
        '    <div class="pfcard__top">' . $job . '</div>' .
        '    <div class="pfcard">' .
        '        <div class=" pfcard_content">' .
        '            <div class="pfcard__avatar_warper">' .
        '                <img src="' . $avatar . '" class="pfcard__avatar" />' .
        '            </div>' .
        '            <div class="pfcard__text_warper">' .
        '                <span class="main"><strong>' . $title . '</strong></span>' .
        '                <span class="desc">' . $desc . '</span>' .
        '            </div>' .
        '        </div>' .
        '    </div>' .
        '</div>';
}

function clrs_shortcode_pfcard_container($atts, $content = '')
{
    return '<div class="container"><div class="row">' . do_shortcode($content) . '</div></div>';
}


/**
 * 短代码注册
 */
function clrs_shortcode_register()
{
    add_shortcode('current_user', 'clrs_shortcode_current_user');
    add_shortcode('time', 'clrs_shortcode_time');
    add_shortcode('for_members', 'clrs_shortcode_for_members');
    add_shortcode('pfcard', 'clrs_shortcode_pfcard');
    add_shortcode('pfcard_container', 'clrs_shortcode_pfcard_container');
    
    //换行
    add_shortcode('brtag', function () {
        return '<br />';
    });
}
add_action('init', 'clrs_shortcode_register');