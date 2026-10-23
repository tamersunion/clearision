<?php

/**
 * 登录界面样式
 */
function custom_login()
{
    $clrs_opbg_des = clrs_checkRandURI(get_option('clrs_opbg_des'), 'clrs_opbg_des');
    $clrs_opcl_des = get_option('clrs_opcl_des');
    $clrs_opbg_mobi = clrs_checkRandURI(get_option('clrs_opbg_mobi'), 'clrs_opbg_mobi');
    $clrs_opcl_mobi = get_option('clrs_opcl_mobi');
    if (!empty($clrs_opcl_mobi)) {
        echo '<meta name="theme-color" content="' . $clrs_opcl_mobi . '" />';
    }
    echo "<style>";
    echo '@media screen and (min-width: 600px){';
    if (!empty($clrs_opbg_des)) echo "body { background-image: url('" . $clrs_opbg_des . "'); }";
    if (!empty($clrs_opcl_des)) echo "body { background-color: " . $clrs_opcl_des . "; }";
    include get_template_directory() . clrs_asset_path('login.css');
    echo '}';
    echo "</style>";

    echo "<script type=\"text/javascript\">";
    echo "	document.addEventListener('DOMContentLoaded', function() {";
    echo "		  document.querySelectorAll('input.input').forEach(function(input) { input.spellcheck = false; });";
    echo "	});";
    echo "</script>";
}
add_action('login_head', 'custom_login');


/**
 * 登录重定向
 */
if (!empty(get_option('clrs_login_redirect'))) {
    add_filter('login_redirect', function ($url, $query, $user) {
        if ($url === home_url() . '/wp-admin/') {
            return get_option('clrs_login_redirect');
        } else if (empty($url)) {
            return home_url();
        } else {
            return $url;
        }
    }, 10, 3);
}


/**
 * 更改注册链接文字
 */
add_filter('gettext', 'clrs_translate_text_strings', 20, 3);
function clrs_translate_text_strings($translated_text, $text, $domain)
{
    switch ($translated_text) {
        case '在这个站点注册':
            $translated_text = '账号注册';
            break;
    }
    return $translated_text;
}


/**
 * 注册时填写昵称
 */
add_action('register_form', 'additional_profile_fields', -1);
function additional_profile_fields()
{ ?>
    <p>
        <label><?php _e('昵称') ?><br />
        <input type="text" name="nickname" id="nickname" class="input" size="25" />
        </label>
    </p>
<?php 
}

// 检测表单字段是否为空，如果为空显示提示信息
add_action('register_post', function ($sanitized_user_login, $user_email, $errors) {
    if (!isset($_POST['nickname'])) {
        return $errors->add('nicknameempty', '<strong>ERROR</strong>: 请输入您的昵称.');
    }
}, 10, 3);

// 将用户填写的字段内容保存到数据库中
add_action('user_register', 'insert_register_fields');
function insert_register_fields($user_id)
{
    $nickname = apply_filters('pre_user_nickname', $_POST['nickname']);
    wp_update_user([
        'ID' => $user_id,
        'nickname' => $nickname,
        'display_name' => $nickname,
    ]);
}


/**
 * 注销重定向
 */
if (!empty(get_option('clrs_logout_redirect'))) {
    add_action('wp_logout', function () {
        wp_redirect(get_option('clrs_logout_redirect'));
        exit();
    });
}


/**
 * curl get获取接口返回
 */
function clrs_curl_get($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); //设置URL
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); //设置超时时间10s
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //不直接输出结果
    $r = curl_exec($ch);
    if($r === false ){
        curl_close($ch);
        return '';
    }else {
        curl_close($ch);
        return $r;
    }
}
