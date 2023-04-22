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
    include get_template_directory().'/assets/css/login.css';
    echo '}';
    echo "</style>";

    echo "<script type=\"text/javascript\">";
    echo "	jQuery(document).ready(function() {";
    echo "		  jQuery('input.input').attr('spellcheck', 'false');";
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
 * 接口解析UA
 */
function clrs_ua_device_parser($useragent)
{
    if(!$useragent) return '';
    $device = json_decode(clrs_curl_get('https://api.hanada.info/ua/?ua='.urlencode($useragent)), true);
    return $device;
}

/**
 * 格式化打印解析后的设备信息
 */
function clrs_print_device($device, $useragent)
{    
    if(!$useragent) {
        return '';
    } else if(!$device) {
        $device = clrs_ua_device_parser($useragent);
    }
    $output = (isset($device['device']['manufacturer']) && $device['device']['manufacturer'])?$device['device']['manufacturer']:'';
    $output .= (isset($device['device']['model']) && $device['device']['model'])?' '.$device['device']['model']:'';
    $output .= (isset($device['os']['name']) && $device['os']['name'])?' '.$device['os']['name']:'';
    $output .= (isset($device['os']['version']['alias']) && $device['os']['name'])?' '.$device['os']['version']['alias']:(isset($device['os']['version']['original']) && $device['os']['version']['original'])?' '.$device['os']['version']['original']:'';
    $output .= (isset($device['browser']['name']) && $device['browser']['name'])?' '.$device['browser']['name']:'';
    $output .= (isset($device['browser']['version']['original']) && $device['browser']['version']['original'])?' '.$device['browser']['version']['original']:'';
    return $output;
}

/**
 * 接口解析IP地理位置
 */
function clrs_ip_location($ip){
    $data = json_decode(clrs_curl_get('https://api.hanada.info/ip/?ip='.$ip), true);
    if ($data['database'] = 'ipip' && $data['ip'] == $ip){
        return $data['location']['country'].$data['location']['province'].$data['location']['city'].$data['location']['company'].$data['location']['isp'];
    }else if($data['database'] = 'chunzhen' && $data['ip'] == $ip){
        return $data['location']['country'].$data['location']['isp'];
    }else{
        return '';
    }
}


/**
 * 记录登录时间、IP地址、地理位置、登录方式
 */
/*function clrs_user_login_message($user_login)
{
    global $user_ID;
    // 纠正8小时时差
    date_default_timezone_set('Asia/Shanghai');
    $current_user = get_user_by( 'login', $user_login );
    //获取当前登录信息
    $login_date = date('Y-m-d H:i:s');
    $login_ip = clrs_get_real_client_ip();
    $login_addr = clrs_ip_location($login_ip);
    $login_ua = $_SERVER['HTTP_USER_AGENT'];
    $login_device = clrs_ua_device_parser($login_ua);
    $login_type = '密码';
    

    //将登录记录写入用户表
    if(empty(get_user_meta($current_user->ID,'login_num', true))) {
        update_user_meta($current_user->ID, 'login_num', 1);
        $login_message_arr = array(
            'login_num_1' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
            'login_num_2' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
            'login_num_3' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
            'login_num_4' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
            'login_num_5' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
            'login_num_6' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
            'login_num_7' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
            'login_num_8' => array( 'date' => '', 'ip' => '', 'addr' => '', 'ua' => '', 'device'=>'', 'type' => '' ),
        );
    }else if(get_user_meta($current_user->ID,'login_num', true) >= 8) {
        update_user_meta($current_user->ID, 'login_num', 1);
        $login_message_arr = get_user_meta($current_user->ID, 'login_message', true);
    }else {
        update_user_meta($current_user->ID, 'login_num', get_user_meta($current_user->ID,'login_num', true) + 1 );
        $login_message_arr = get_user_meta($current_user->ID, 'login_message', true);
    }

    $login_num = get_user_meta($current_user->ID,'login_num', true);

    $login_message_arr['login_num_'.$login_num]['date'] = $login_date;
    $login_message_arr['login_num_'.$login_num]['ip'] = $login_ip;
    $login_message_arr['login_num_'.$login_num]['addr'] = $login_addr;
    $login_message_arr['login_num_'.$login_num]['ua'] = $login_ua;
    $login_message_arr['login_num_'.$login_num]['device'] = $login_device;
    $login_message_arr['login_num_'.$login_num]['type'] = $login_type;
    update_user_meta($current_user->ID, 'login_message', $login_message_arr);
}
add_action('wp_login','clrs_user_login_message');*/


/**
 * 获取用户真实IP
 */
function clrs_get_real_client_ip() {
    if(!empty($_SERVER["HTTP_X_CILENT_IP"])) {
        $ip = $_SERVER["HTTP_X_CILENT_IP"];
    } else if(!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
    } else if(!empty($_SERVER["REMOTE_ADDR"])) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } else {
        $ip = 'N/A';
    }
    return $ip;
}

/**
 * 输出屏蔽处理后的ip地址
 */
function clrs_ip_hidden($ip) {
    $ipver = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? 4 : 6;
    $hidden_ip = '';
    if ($ipver === 4){
        //正则替换隐藏IPV4地址后两位
        $ip_reg = '~(\d+)\.(\d+)\.(\d+)\.(\d+)~';
        $hidden_ip = preg_replace($ip_reg, "$1.$2.*.*", $ip);
    }else{ 
        // 替换隐藏IPV6地址前半部分
        $ip_reg = explode(':',$ip);
        $ip_count = count($ip_reg);
        $i=0;
        while($i<=$ip_count-1){
            if ($i<=$ip_count/2-2) {
                $hidden_ip .= $ip_reg[$i];
            } else if(!$ip_reg[$i]) {
                $hidden_ip .= '';
            } else {
                $hidden_ip .= '*';
            }
            if ($i!=$ip_count-1) {
                $hidden_ip .= ':';
            }
            $i++;
        }
    }
    return $hidden_ip;
}

/**
 * 短代码：输出登录记录信息
 */
function clrs_shotcode_login_message()
{
    //未登录时返回空值
    if (!is_user_logged_in()) {
        return null;
    }

    //获取登录记录信息
    $current_user = wp_get_current_user();
    $login_num = get_user_meta($current_user->ID,'login_num', true);
    $login_message_arr = get_user_meta($current_user->ID,'login_message', true);


    
    //设置北京时间
    date_default_timezone_set('Asia/Shanghai');
    
    //获取当前cookie
    $cookie = wp_parse_auth_cookie( '', 'logged_in' );
    
    //获取当前账号sessions
    $sessions = wp_get_all_sessions();
    
    //格式化输出登录记录信息
    $login_message_table = '
        <table class="tablepress login_message_table"><thead><tr>
            <th>时间</th>
            <th>IP地址</th>
            <th>地理位置</th>
            <th>登录设备</th>
            <th>登录方式</th>
        </tr></thead><tbody class="row-hover">';
    for( $i=$login_num; $i>0; $i-- ) {
        if(!empty($login_message_arr['login_num_'.$i]['date'])) {
            $login_message_table = $login_message_table.'
        <tr>
            <td class="login_message_date">'.$login_message_arr['login_num_'.$i]['date'].'</td>
            <td class="login_message_ip">'.clrs_ip_hidden($login_message_arr['login_num_'.$i]['ip']).'</td>
            <td class="login_message_addr">'.$login_message_arr['login_num_'.$i]['addr'].'</td>
            <td class="login_message_ua" title="'.$login_message_arr['login_num_'.$i]['ua'].'">'.clrs_print_device($login_message_arr['login_num_'.$i]['device'], $login_message_arr['login_num_'.$i]['ua']).'</td>
            <td class="login_message_type">'.$login_message_arr['login_num_'.$i]['type'];
            
            if( strtotime($login_message_arr['login_num_'.$i]['date']) == $cookie['expiration'] - 1209600 || strtotime($login_message_arr['login_num_'.$i]['date']) == $cookie['expiration'] - 172800){
                $login_message_table = $login_message_table.'<span class="current_session">当前登录</span></td></tr>';
            } else {
                $has_seesion = false;
                foreach($sessions as $value){
                    if( strtotime($login_message_arr['login_num_'.$i]['date']) == $value['login'] ){
                        $login_message_table = $login_message_table.'<span class="other_session">尚未登出</span></td></tr>';
                        $has_seesion = true;
                        break;
                    }
                }
                if( $has_seesion == false ) $login_message_table = $login_message_table.'<span class="loggedout_session">已经登出</span></td></tr>';
            }
        }
    }
    for( $i=8; $i>$login_num; $i-- ) {
        if(!empty($login_message_arr['login_num_'.$i]['date'])){
            $login_message_table = $login_message_table.'
        <tr>
            <td class="login_message_date">'.$login_message_arr['login_num_'.$i]['date'].'</td>
            <td class="login_message_ip">'.clrs_ip_hidden($login_message_arr['login_num_'.$i]['ip']).'</td>
            <td class="login_message_addr">'.$login_message_arr['login_num_'.$i]['addr'].'</td>
            <td class="login_message_ua" title="'.$login_message_arr['login_num_'.$i]['ua'].'">'.clrs_print_device($login_message_arr['login_num_'.$i]['device'], $login_message_arr['login_num_'.$i]['ua']).'</td>
            <td class="login_message_type">'.$login_message_arr['login_num_'.$i]['type'];
            if(  strtotime($login_message_arr['login_num_'.$i]['date']) === $cookie['expiration'] - 1209600 || strtotime($login_message_arr['login_num_'.$i]['date']) == $cookie['expiration'] - 172800){
                $login_message_table = $login_message_table.'<span class="current_session">当前登录</span></td></tr>';
            }else{
                $has_seesion = false;
                foreach($sessions as $value){
                    if( strtotime($login_message_arr['login_num_'.$i]['date']) == $value['login'] ){
                        $login_message_table = $login_message_table.'<span class="other_session">尚未登出</span></td></tr>';
                        $has_seesion = true;
                        break;
                    }
                }
                if( $has_seesion == false ) $login_message_table = $login_message_table.'<span class="loggedout_session">已经登出</span></td></tr>';
            }
        }
    }
    $login_message_table = $login_message_table.'</tbody></table>';

    return $login_message_table;
}
add_shortcode('login_message', 'clrs_shotcode_login_message');


/**
 * 注销重定向
 */
function auto_redirect_external_after_logout(){
  wp_redirect( 'https://cas.tamersunion.org/logout?redirect=https%3A%2F%2Fwww.tamersunion.org' );
  exit();
}
add_action('wp_logout','auto_redirect_external_after_logout');

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
