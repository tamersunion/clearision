<?php /* Template Name: 获取用户头像链接 */ ?>


<?php while (have_posts()) : the_post(); ?>


<?php
if(filter_has_var(INPUT_GET, 'u')){
    $username = $_GET['u'];
    $current_user = get_user_by('login', $username);
    $u_id = $current_user->ID;
}elseif(filter_has_var(INPUT_GET, 'e')){
    $useremail = $_GET['e'];
    $current_user = get_user_by('email', $useremail);
    $u_id = $current_user->ID;
}
else {
    header('HTTP/1.1 404 Not Found');
    exit('404');
}
$avatar = get_avatar_data($u_id);
$avatar = $avatar['url'];
$avatar = str_replace("www.tamersunion.org", "static.tamersunion.org", $avatar);
header('Location: ' . $avatar);
?>

<?php endwhile; // end of the loop. ?>

