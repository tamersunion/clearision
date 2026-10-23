<!DOCTYPE html>
<html>
<head><?php define('is_frontEnd', true) ?>
<meta charset="<?php bloginfo('charset'); ?>" />
<title><?php wp_title('-', true, 'right'); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="shortcut icon" type="image/x-icon" href="<?= get_option('clrs_favi', 'favicon.ico'); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="application-name" content="<?php bloginfo('name'); ?>"/>
<meta name="keywords" content="<?php echo get_option('clrs_keywd'); ?>" />
<meta name="description" content="<?php echo get_option('clrs_desc'); ?>" />
<link rel="home" href="<?php echo esc_url(home_url('/')); ?>" />
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url') ?>" />
<link rel="alternate" type="application/rdf+xml" title="RSS 1.0" href="<?php bloginfo('rss_url') ?>" />
<link rel="alternate" type="application/atom+xml" title="ATOM 1.0" href="<?php bloginfo('atom_url') ?>" />
<!--[if lt IE 9]>
<script src="<?php echo esc_url(clrs_asset_url('html5.js')); ?>" type="text/javascript"></script>
<![endif]-->
<script>var resourceUIR="<?php echo clrs_getResURL(); ?>";</script>
<?php
wp_head();
$clrs_style = get_option("clrs_style");
$clrs_opbg_des = clrs_checkRandURI(get_option('clrs_opbg_des'),'clrs_opbg_des');
$clrs_opcl_des = get_option('clrs_opcl_des');
$clrs_opbg_mobi = clrs_checkRandURI(get_option('clrs_opbg_mobi'),'clrs_opbg_mobi');
$clrs_opcl_mobi = get_option('clrs_opcl_mobi');
if (!empty($clrs_opcl_mobi)){echo '<meta name="theme-color" content="'.$clrs_opcl_mobi.'" />';}
echo "<style>";
if (is_user_logged_in()) echo '#float { top: 32px; }'; // 适配 WordPress 顶部管理栏
echo '@media screen and (min-width: 620px){';
if (!empty($clrs_opbg_des)) echo "body { background-image: url('" . $clrs_opbg_des . "'); }";
if (!empty($clrs_opcl_des)) echo "body { background-color: " . $clrs_opcl_des . "; }";
echo '}';
echo '@media screen and (max-width: 620px){';
if (!empty($clrs_opbg_mobi)) echo "body { background-image: url('" . $clrs_opbg_mobi . "'); }";
if (!empty($clrs_opcl_mobi)) echo "body { background-color: " . $clrs_opcl_mobi . "; }";
echo '}';
if (!empty($clrs_style)) echo $clrs_style;
echo "</style>";
$clrs_tjt = get_option('clrs_tongji');
if (!empty($clrs_tjt)) {
  echo $clrs_tjt;
};
?>
</head>

<body <?php body_class(); ?>>

<?php function clrs_zhushi_topmenu(){ ?>
    <div id="float1" >
    
    <a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><img id="logo1" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>" src="<?php echo get_option('clrs_logo'); ?>" ></a>
    <nav id="next1" role="sencond_navigation">
        <?php wp_nav_menu(array('theme_location' => 'next')); ?>
    </nav>
    
    <nav id="nav1" role="navigation">
        <?php wp_nav_menu(array('theme_location' => 'main')); ?>
    </nav>
    </div>
    <!-- 请确保注释内容的所有代码均不运行 谢谢! -->
<?php } ?>

<div id="page">
	

<hgroup id="page-header">
    <div id="title">
        <h1 id="site-title"><span><a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><?php bloginfo('name'); ?></a></span></h1>
        <h2 id="site-description"><?php bloginfo('description'); ?></h2>
    </div>
    <div id="title_icons">
        <?php clrs_sns(); ?>
        <a href="<?php bloginfo('rss2_url'); ?>" target="_blank" class="tr_rss_a"><button id="tr_rss"></button></a>
        <a href="<?php echo admin_url(); ?>" target="_blank" class="tr_admin_a"><button id="tr_admin"></button></a>
        <span id="tr_clear"></span>
        <form id="tr_s_f" method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <input id="tr_search" type="text" name="s" placeholder="" size="10" />
        </form>
    </div>
<div class="clearfix"></div>
</hgroup>

<div id="float" >

<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><img id="logo" alt="<?php bloginfo('name'); ?>" title="<?php bloginfo('name'); ?>" src="<?php echo get_option('clrs_logo'); ?>" ></a>

<nav id="nav" role="navigation">
    <?php wp_nav_menu(array('theme_location' => 'main')); ?>
</nav>

<nav id="next" role="sencond_navigation">
    <?php wp_nav_menu(array('theme_location' => 'next')); ?>
</nav>

</div>
