<?php /* Template Name: 测试模板 */ ?>
<?php
get_header();
$onlyMembersCanView = get_post_meta(get_the_ID(), 'onlyMembersCanView', true)=== 'yes' ? true : false;
?>

<div class="page-warp">
<div class="content">


    
	<hgroup class="post_header">
		<h2 class="post_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="post_tag">
		<?php $adis = get_option('clrs_adis');
		if ($adis == "yes") {
			$c_adis = "";
		}
		else {
			$c_adis = 'style="display:none;"';
		};
		the_tags('', ' ', '');
		?>
		<span class="post_tag_col"><?php echo edit_post_link(__('<i class="fa fa-pencil-square-o"></i> 编辑', 'clrs')); ?></span>
	        </div>
	</hgroup>
	
	<div class="post_content"><?php
    if($onlyMembersCanView && !is_user_logged_in()){
        $_na_msg = get_option('clrs_noaccess');
        $_na_msg = str_replace('{{page_url}}', urlencode(home_url(add_query_arg(array(),$wp->request))),$_na_msg);
        echo empty($_na_msg) ? '本页面仅对登录用户开放':do_shortcode($_na_msg);
    }else{
    for($id=1;$id<=123;$id++)
{
    if(!current_user_can('manage_options')) break;
    
    if(!get_user_meta($id,'nickname', true)) continue;
    
    //输出用户名
    echo '<br>';
    echo get_user_meta($id,'nickname', true).'  '.$id;
    echo '<br>';
    

    //获取登录记录信息
    $current_user = wp_get_current_user();
    $login_num = get_user_meta($id,'login_num', true);
    $login_message_arr = get_user_meta($id,'login_message', true);

    //正则替换隐藏IP地址后两位
    $login_ip_reg = '~(\d+)\.(\d+)\.(\d+)\.(\d+)~';
    
    //设置北京时间
    date_default_timezone_set("PRC");
    
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
            <td class="login_message_op">'.$login_message_arr['login_num_'.$i]['ip'].'</td>
            <td class="login_message_addr">'.$login_message_arr['login_num_'.$i]['addr'].'</td>
            <td class="login_message_ua" title="'.$login_message_arr['login_num_'.$i]['ua'].'">'.clrs_print_device($login_message_arr['login_num_'.$i]['device'], $login_message_arr['login_num_'.$i]['ua']).'</td>
            <td class="login_message_type">'.$login_message_arr['login_num_'.$i]['type'];
            
            if( strtotime($login_message_arr['login_num_'.$i]['date']) == $cookie['expiration'] - 1209600 || strtotime($login_message_arr['login_num_'.$i]['date']) == $cookie['expiration'] - 172800){
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
    for( $i=8; $i>$login_num; $i-- ) {
        if(!empty($login_message_arr['login_num_'.$i]['date'])){
            $login_message_table = $login_message_table.'
        <tr>
            <td class="login_message_date">'.$login_message_arr['login_num_'.$i]['date'].'</td>
            <td class="login_message_ip">'.$login_message_arr['login_num_'.$i]['ip'].'</td>
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

    echo $login_message_table;
}
            $ua_arr = json_decode(@file_get_contents('https://api.hanada.info/ua/?text='.urlencode($_SERVER['HTTP_USER_AGENT'])), true);
        echo '<pre>';
        print_r($ua_arr);
        echo '</pre>';
        $login_message = get_user_meta($current_user->ID,'login_message', true);
        echo '<pre>';
        print_r($login_message);
        echo '</pre>';
    }
        ?>
</div>
  <nav class='post_nav_bds'>
	<a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class='goHome'>
		<i class="fa fa-home" aria-hidden="true">&nbsp; 返回首页</i>
	</a>
	<?php
	$clrs_share = get_option('clrs_share');
	if (!empty($clrs_share)) {
		echo str_replace('{{theme_dir}}',get_template_directory_uri(),$clrs_share);
	}
	?>
	<div style='clear: both;' />
	</nav>



</div>
</div>
	<?php comments_template('', true); ?>



<?php get_sidebar(); ?>
<?php get_footer(); ?>