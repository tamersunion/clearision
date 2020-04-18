<?php

/**
 * 核心全局功能
 */
include get_template_directory().'/func/core.php';

/**
 * 用户与权限控制模块
 */
include get_template_directory().'/func/user.php';

/**
 * 评论相关模块
 */
include get_template_directory().'/func/comments.php';

/**
 * 前端资源处理
 */
include get_template_directory().'/func/static.php';

/**
 * 短代码模块
 */
include get_template_directory().'/func/shortcode.php';

/**
 * 注册登录模块
 */
include get_template_directory().'/func/login.php';

/**
 * 头像功能 
 */
include get_template_directory().'/func/avatar.php';

/**
 * 新闻简讯功能
 */
include get_template_directory().'/func/news.php';