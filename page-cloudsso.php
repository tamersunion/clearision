<?php /* Template Name: 档案库认证页模板 */ ?>

	<?php
        if(current_user_can( 'edit_posts' )){
            $current_user = wp_get_current_user();
            $user = $current_user->user_login;
            date_default_timezone_set('PRC');
            $login_token = base64_encode($user).'|'.md5($user.get_option('clrs_kod_tonken'));
            $url = 'https://cloud.tamersunion.net/?user/loginSubmit&login_token='.urlencode($login_token);
            header('location: '.$url);
        }else{
            header('location: '.esc_url(home_url()).'/wp-login.php?redirect_to=cloudsso');
        }
	?>
