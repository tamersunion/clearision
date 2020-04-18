<?php

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