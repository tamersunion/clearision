<?php

//屏蔽赵家人
/*
if( (isset($_SERVER['HTTP_REFERER']) &&
    strpos($_SERVER['HTTP_REFERER'],'gov.cn')) !== false ||
    isset($_COOKIE["MCHECKTOKEN"])
){
    setcookie("MCHECKTOKEN",uniqid());
    include('nginx.html');
    exit();
}
*/


/**
 * 判断字符串是否以某个子字符串开头
 *
 * @param string $str 原字符串
 * @param string $needle 子字符串
 * @return bool
 */
function clrs_startWith(string $str, string $needle) : bool
{
    return strpos($str, $needle) === 0;
}

/**
 * 从字符串左侧删除空格或其他预定义字符
 *
 * @param string $str 原字符串
 * @param string $neddle 预定义字符(可多个)
 * @return string
 */
function clrs_ltrimstr(string $str, string $neddle) : string
{
    $result = mb_ereg_replace('^(' . addslashes($neddle) . ')+', '', $str);
    return $result === false ? $str : $result;
}

/**
 * 从字符串右侧删除空格或其他预定义字符
 *
 * @param string $str 原字符串
 * @param string $neddle 预定义字符(可多个)
 * @return string
 */
function clrs_rtrimstr(string $str, string $neddle) : string
{
    $result = mb_ereg_replace('(' . addslashes($neddle) . ')+$', '', $str);
    return $result === false ? $str : $result;
}

/**
 * Wordpress 中设定的时区的时间
 *
 * @return integer
 */
function clrs_time() : int
{
    static $timezone = null;
    if (is_null($timezone)) {
        $timezone = get_option('gmt_offset');
    }
    return time() + $timezone * 3600;
}

function clrs_menu_function()
{
    add_theme_page(
        __('设置', 'clrs'),
        __('设置', 'clrs'),
        'administrator',
        'clrs_menu',
        'clrs_dashboard'
    );
}
add_action('admin_menu', 'clrs_menu_function');


/**
 * 管理员菜单栏显示主题设置
 */
/*function clrs_admin_bar()
{
    global $wp_admin_bar;
    $wp_admin_bar->add_menu(array(
        'parent' => false,
        'id' => 'theme_setting',
        'title' => __('主题设置', 'dpt'),
        'href' => admin_url('themes.php?page=clrs_menu'),
    ));
}
add_action('wp_before_admin_bar_render', 'clrs_admin_bar');*/


/**
 * 本地化emoji
 */
/*
add_filter('emoji_svg_url', function ($url) use ($clrs_siteURL, $clrs_resURL) {
    return str_replace($clrs_siteURL, $clrs_resURL, get_template_directory_uri() . '/assets/common/emoji/svg/');
});
add_filter('emoji_url', function ($url) use ($clrs_siteURL, $clrs_resURL) {
    return str_replace($clrs_siteURL, $clrs_resURL, get_template_directory_uri() . '/assets/common/emoji/72x72/');
});
add_filter('script_loader_src', function ($url) use ($clrs_siteURL, $clrs_resURL) {
    if (is_numeric(strpos($url, 'wp-emoji')) || is_numeric(strpos($url, 'twemoji'))) {
        $url = str_replace($clrs_siteURL, $clrs_resURL, $url);
    }
    return $url;
});
*/

/**
 * 禁用WordPress自动转义emoji，emoji交由系统处理
 */
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'print_emoji_detection_script',	7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('embed_head',	'print_emoji_detection_script');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
add_filter( 'emoji_svg_url', '__return_false' );

/**
 * 获取博客标题
 *
 * @param string $title 标题
 * @param string $sep 分隔符
 * @return string
 */
function clrs_title(string $title, string $sep) : string
{
    global $paged, $page;

    if (is_feed()) {
        return $title;
    }

    $title .= get_bloginfo('name');

    $site_description = get_bloginfo('description', 'display');
    if ($site_description && (is_home() || is_front_page())) {
        $title = "$title $sep $site_description";
    }

    if ($paged >= 2 || $page >= 2) {
        $title = "$title $sep " . sprintf(__('页面 %s', 'clrs'), max($paged, $page));
    }

    return $title;
}

add_filter('wp_title', 'clrs_title', 10, 2);


/**
 * 首页 SNS 输出
 *
 * @return void
 */
function clrs_sns()
{
    // 修改此顺序可以改变输出顺序，记得修改对应的注释
    $clrs_sns = array(
        "profile" => "个人页",
        "gplus" => "Google+",
        "twitter" => "Twitter",
        "fb" => "Facebook",
        "weibo" => "SinaWeibo",
        "qqw" => "QQ",
        "github" => "Github"
    );
    foreach ($clrs_sns as $name => $title) {
        $clrs_sopt = 'clrs_s_' . $name;
        if (get_option($clrs_sopt) != null) {
            echo '<a href="' . get_option($clrs_sopt) . '" title="' . $title . '" target="_blank" class="tr_' . $name . '_a"><button class="tr_' . $name . '"></button></a>';
        }
    }
}

/**
 * 定义页面导航
 *
 * @return void
 */
function clrs_pagenavi()
{
    function http_build_url($url_arr){
        $new_url = $url_arr['scheme'] . "://".$url_arr['host'];
        if(!empty($url_arr['port']))
            $new_url = $new_url.":".$url_arr['port'];
        $new_url = $new_url . $url_arr['path'];
        if(!empty($url_arr['query']))
            $new_url = $new_url . "?" . $url_arr['query'];
        if(!empty($url_arr['fragment']))
            $new_url = $new_url . "#" . $url_arr['fragment'];
        return $new_url;
    }
    global $wp_query, $wp_rewrite;
    $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

    $pagination = array(
        'base' => @add_query_arg('paged', '%#%'),
        'format' => '',
        'total' => $wp_query->max_num_pages,
        'current' => $current,
        'show_all' => false,
        'type' => 'plain',
        'end_size' => '0',
        'mid_size' => '5',
        'prev_text' => __('<span>上一页</span>', 'clrs'),
        'next_text' => __('<span>下一页</span>', 'clrs'),
        'before_page_number' => '<span>',
        'after_page_number' => '</span>'
    );

    if ($wp_rewrite->using_permalinks()) {
        $url = parse_url(remove_query_arg('s', get_pagenum_link(1)));
        $url['path'] = trailingslashit($url['path']);
        $url['path'] .= 'page/%#%/';
        $pagination['base'] = http_build_url($url);
    }

    if (!empty($wp_query->query_vars['s'])) {
        $pagination['add_args'] = array('s' => get_query_var('s'));
    }

    echo paginate_links($pagination);
}

// 加载文章格式支持
add_theme_support('post-formats', array('quote', 'status'));
// add_theme_support( 'post-formats', array( 'image', 'video' ) );


// 加载菜单设置
register_nav_menus(array(
    'main' => __('主菜单', 'clrs'),
    'next' => __('辅助链接', 'clrs')
));

// 加载小工具设置
if (function_exists('register_sidebar')) {
    $clrs_sidebars = [
        [
            'name' => '404页面_1',
            'class' => 'p404_col p404_col_1',
            'id' => 'clra_404_1',
            'description' => '404页面内容 #1',
        ], [
            'name' => '404页面_2',
            'class' => 'p404_col p404_col_2',
            'id' => 'clra_404_2',
            'description' => '404页面内容 #2',
        ], [
            'name' => '404页面_3',
            'class' => 'p404_col p404_col_3',
            'id' => 'clra_404_3',
            'description' => '404页面内容 #3',
        ], [
            'name' => '底部栏1',
            'class' => 'side_col',
            'id' => 'clra_footer_1',
            'description' => '底部的工具栏',
        ], [
            'name' => '底部栏2',
            'class' => 'side_col',
            'id' => 'clra_footer_2',
            'description' => '底部的工具栏',
        ]
    ];
    foreach ($clrs_sidebars as $sItem) {
        register_sidebar([
            'name' => $sItem['name'],
            'id' => $sItem['id'],
            'description' => $sItem['description'],
            'class' => $sItem['class'],
            'before_widget' => '',
            'after_widget' => '',
            'before_title' => '<h2>',
            'after_title' => '</h2>',
        ]);
    }
}
if (!function_exists('is_login_page')) {
    function is_login_page()
    {
        return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-signup.php'));
    }
}

$clrs_siteURL = trim(site_url(), "\t\n\r\0\x0B");
if (is_callable('CDN_Enabler::get_options')) {
    $clrs_resURL = clrs_rtrimstr(trim(CDN_Enabler::get_options()['url'], "\t\n\r\0\x0B"), '/');
    $clrs_themeDir = str_replace($clrs_siteURL, $clrs_resURL, get_template_directory_uri());
} else {
    $clrs_resURL = $clrs_siteURL;
    $clrs_themeDir = get_template_directory_uri();
}

function clrs_getResURL(){
    global $clrs_resURL;
    return $clrs_resURL;
}

function clrs_getSiteURL(){
    global $clrs_siteURL;
    return $clrs_siteURL;
}

/**
 * 将链接中的随机参数写入cookie
 *
 * @param [type] $randURI
 * @param [type] $numberName
 * @return void
 */
function clrs_checkRandURI($randURI, $numberName)
{
    if (!empty($randURI) && preg_match("/\{rand\:([0-9]+)\,([0-9]+)\}/", $randURI, $randURI_mt)) {
        $setCookie = false;
        $randURI_mt[1] = intval($randURI_mt[1]);
        $randURI_mt[2] = intval($randURI_mt[2]);
        //读取随机数
        if (isset($_COOKIE[$numberName]) && //cookie存在
        is_numeric($_COOKIE[$numberName]) //是有效数字
        ) {
            $randURI_rand = intval($_COOKIE[$numberName]);
        } else {
            $randURI_rand = rand($randURI_mt[1], $randURI_mt[2]);
            $setCookie = true;
        }
        
        //校验
        if ($randURI_rand < $randURI_mt[1] ||
            $randURI_rand > $randURI_mt[2]) {
            $randURI_rand = rand($randURI_mt[1], $randURI_mt[2]);
            $setCookie = true;
        }
        $randURI = implode($randURI_rand, explode($randURI_mt[0], $randURI));
        if ($setCookie) {
            $hookName = is_admin() ? 'admin_footer' : is_login_page() ? 'login_footer' : 'wp_footer';
            add_action($hookName, function () use ($numberName, $randURI_rand) {
                echo "\n" . '<script>';
                echo "\n" . 'if(!clrs_setCookie || typeof(clrs_setCookie) !== "function"){';
                echo "\n" . 'var clrs_setCookie = function (name,value){';
                echo "\n" . '    var Days = 3;';
                echo "\n" . '    var exp = new Date();';
                echo "\n" . '    exp.setTime(exp.getTime() + Days*24*60*60*1000);';
                echo "\n" . '    document.cookie = name + "="+ escape (value) + ";path=/;expires=" + exp.toGMTString();';
                echo "\n" . '}}';
                echo "\n" . 'clrs_setCookie("' . $numberName . '", ' . $randURI_rand . ')';
                echo "\n" . '</script>';
            });
        }
    }
    return $randURI;
}

/**
 * 删除多余的换行
 */
function clrs_shortcode_content($content)
{
    $content = preg_replace('/^(\<br \/\>|\<br\>)+/', "", $content);
    $content = preg_replace('/(\<br \/\>|\<br\>)+$/', "", $content);
    $content = trim($content);
    $content = preg_replace('/\](\s|\<br \/\>|\<br\>)+\[/', "][", $content);
    $content = preg_replace('/\>(\s|\<br \/\>|\<br\>)+\</', "><", $content);
    $content = do_shortcode($content);
    $content = preg_replace('/^(\<br \/\>|\<br\>)+/', "", $content);
    $content = preg_replace('/(\<br \/\>|\<br\>)+$/', "", $content);
    $content = trim($content);
    $content = preg_replace('/\](\s|\<br \/\>|\<br\>)+\[/', "][", $content);
    $content = preg_replace('/\>(\s|\<br \/\>|\<br\>)+\</', "><", $content);
    return $content;
}

/**
 * 菜单图标
 */
function clrs_menu_opt($sorted_menu_items)
{
    foreach ($sorted_menu_items as $menu_key => $menu_items) {
        if (!empty($menu_items->description)) {
            $sorted_menu_items[$menu_key]->title = '<i class="fa fa-fw ' . $menu_items->description . '" aria-hidden="true"></i> ' . $menu_items->title;
        }
    }
    return $sorted_menu_items;
}
add_filter('wp_nav_menu_objects', 'clrs_menu_opt');

/**
 * 禁用区块样式表引用
 */
function remove_block_library_css() {
	wp_dequeue_style( 'wp-block-library' );
}
add_action( 'wp_enqueue_scripts', 'remove_block_library_css', 100 );

/**
 * 禁用XML-RPC和远程发布功能
 */
add_filter('xmlrpc_enabled', '__return_false');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

/**
 * 禁用Link响应头
 */
remove_action('template_redirect', 'wp_shortlink_header', 11, 0);
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

/**
 * 禁用srcset
 */

function clrs_disable_srcset( $sources ) {
	return false;
}
add_filter( 'wp_calculate_image_srcset', 'clrs_disable_srcset' );

/**
 * 禁止WordPress缩略图
 */
add_filter( 'add_image_size', create_function( '', 'return 1;' ) );

/**
 * 移除顶部工具栏
 */
add_filter('show_admin_bar', '__return_false');

/**
 * 媒体库上传文件重命名(例：2019031020135421.jpg)
 */
function clrs_upload_rename_filter($file) {
    date_default_timezone_set('PRC');
    $time = date("YmdHis");
    $file['name'] = $time . "" . mt_rand(10, 99) . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'clrs_upload_rename_filter');

/**
 * 移除标题转换
 */
remove_filter('wp_title', 'wptexturize');

/**
 * 作者页面链接重写
 */
add_filter( 'author_link', 'clrs_author_link', 10, 2 );
function clrs_author_link( $link, $author_id) {
    global $wp_rewrite;
    $author_id = (int) $author_id;
    $link = $wp_rewrite->get_author_permastruct();
 
    if ( empty($link) ) {
        $file = home_url( '/' );
        $link = $file . '?author=' . $author_id;
    } else {
        $link = str_replace('%author%', $author_id, $link);
        $link = home_url( user_trailingslashit( $link ) );
    }
    return $link;
}

add_filter( 'request', 'clrs_author_link_request' );
function clrs_author_link_request( $query_vars ) {
    if ( array_key_exists( 'author_name', $query_vars ) ) {
        global $wpdb;
        $author_id=$query_vars['author_name'];
        if ( $author_id ) {
            $query_vars['author'] = $author_id;
            unset( $query_vars['author_name'] );    
        }
    }
    return $query_vars;
}

add_filter(
    'init', 
    create_function(
     '$a', 
     'global $wp_rewrite; 
     $wp_rewrite->author_base = "staff"; 
     $wp_rewrite->flush_rules();' 
    ) 
);