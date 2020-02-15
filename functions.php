<?php
//屏蔽赵家人
if( (isset($_SERVER['HTTP_REFERER']) &&
    strpos($_SERVER['HTTP_REFERER'],'gov.cn')) !== false ||
    isset($_COOKIE["MCHECKTOKEN"])
){
    setcookie("MCHECKTOKEN",uniqid());
    include('nginx.html');
    exit();
}

if (is_admin()) {
    // 加载后台设置页面
    include('dashboard.php');
}

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
 * 本地化emoji
 */
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
                    <?php if (current_user_can('edit_comment')) : ?>
                    <span class="edit-link"><a class="comment-edit-link" href="<?php echo get_edit_comment_link(get_comment_ID()); ?>"><?php echo __('编辑', 'clrs') ?></a></span>
                    <?php endif; ?>
	                <?php delete_comment_link(get_comment_ID()); ?>
	                <?php echo str_replace('<a','<a ',get_comment_reply_link(array_merge($args, array('reply_text' => __('回复', 'clrs'), 'after' => '', 'depth' => $depth, 'max_depth' => 100)))); ?>
	            </section>
	        </article>
	    <?php
    echo '</li>';
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
 * 发表评论时不需要填写“站点”
 *
 * @param array $fields 默认表单域
 * @return array
 */
function alter_comment_form_fields(array $fields)
{
    //$fields['author'] = '';
    //$fields['email'] = '';
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
 * @param [type] $comment_text 回复框标题
 * @param string $comment 回复给的comment对象
 * @return void
 */
function clrs_comment_add_at($comment_text, $comment = '')
{
    if ($comment->comment_parent > 0) {
        $comment_text = '回复 <a href="#comment-' . $comment->comment_parent . '" target="_self">@' . get_comment_author($comment->comment_parent) . '</a>： ' . $comment_text;
    }
    return $comment_text;
}
add_filter('comment_text', 'clrs_comment_add_at', 20, 2);

/**
 * 注册前端资源文件
 */
if (!is_admin()) {
    add_action(
        'wp_enqueue_scripts',
        function () {
            wp_enqueue_style('clearision-style', get_template_directory_uri() . '/style.css');
            wp_register_script('clearision-init', get_template_directory_uri() . '/assets/scripts/script.js', ['jquery'], false, true);
            wp_enqueue_script('clearision-init');
        }
    );

    //bootstrap-grid
    add_action('wp_enqueue_scripts', function () {
        $btgirdVerDefault = '4.3.1';
        $btgirdVer = get_option('clrs_thrdptComs_btgird_ver', $btgirdVerDefault);
        $btgirdVer = empty($btgirdVer) ? $btgirdVerDefault : $btgirdVer;
        wp_enqueue_style('clearision-bootstrap', 'https://st-aliyun-4.hanada.info/twitter-bootstrap/' . $btgirdVer . '/css/bootstrap-grid.min.css');
    });
    
    //jQuery
    if (get_option('clrs_thrdptComs_jq') === 'yes') {
        add_action('wp_enqueue_scripts', function () {
            $jqVer = get_option('clrs_thrdptComs_jq_ver', '3.4.1');
            if (empty($jqVer)) {
                $jqVer = '3.4.1';
            }
            wp_deregister_script('jquery');
            wp_register_script('jquery', 'https://st-aliyun-4.hanada.info/jquery/' . $jqVer . '/jquery.min.js', [], $jqVer);
            wp_enqueue_script('jquery');
        }, 99);
    }

    //font-awesome
    if (get_option('clrs_thrdptComs_fa') === 'yes') {
        add_action('wp_enqueue_scripts', function () {
            global $wp_styles;
            $faVer = get_option('clrs_thrdptComs_fa_ver', '4.7.0');
            if (empty($faVer)) {
                $faVer = '4.7.0';
            }
            wp_enqueue_style('clearision-font-awesome', 'https://st-aliyun-4.hanada.info/font-awesome/' . $faVer . '/css/font-awesome.min.css', [], $faVer);
            if (is_array($wp_styles->registered)) {
                foreach ($wp_styles->registered as $script => $details) {
                    $src = isset($details->src) ? $details->src : false;
                    if (false !== strpos($script, 'clearision-font-awesome')) {
                        continue;
                    }
                    if ($src && (false !== strpos($script, 'fontawesome') || false !== strpos($script, 'font-awesome'))) {
                        wp_dequeue_style($script);
                    }
                    if ($src && (false !== strpos($src, 'font-awesome') || false !== strpos($src, 'fontawesome'))) {
                        wp_dequeue_style($script);
                    }
                }
            }
        }, 11);
    }
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
 * 注册登录模块
 */
include "functions.login.php";


/**
 * 头像功能 
 */
include "functions.avatar.php";

/**
 * 新闻简讯功能
 */
include "functions.news.php";


/**
 * 评论区默认记录cookies
 */
add_filter( 'comment_form_default_fields', function( $fields ) {
	$fields['cookies'] = '<input style="display: none;" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" checked="true" value="yes"/>';
	return $fields;
} );

/**
 * 用户列表不显示姓名栏目
 */
add_filter('manage_users_columns', 'remove_name_column');
function remove_name_column($columns) {
	unset($columns['name']);
	return $columns;
}

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
 * 禁用srcset
 */

function clrs_disable_srcset( $sources ) {
	return false;
}
add_filter( 'wp_calculate_image_srcset', 'clrs_disable_srcset' );

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