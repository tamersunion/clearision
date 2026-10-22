<?php

/**
 * 注册前端资源文件
 */
if (!is_admin()) {
    add_action(
        'wp_enqueue_scripts',
        function () {
            $theme_version = wp_get_theme(get_template())->get('Version');
            wp_enqueue_style('clearision-style', get_template_directory_uri() . '/style.css', [], $theme_version);
            wp_enqueue_script('clearision-init', get_template_directory_uri() . '/assets/scripts/script.js', ['jquery'], $theme_version, true);
        }
    );

    //bootstrap-grid
    add_action('wp_enqueue_scripts', function () {
        $btgirdVerDefault = '4.3.1';
        $btgirdVer = get_option('clrs_thrdptComs_btgird_ver', $btgirdVerDefault);
        $btgirdVer = empty($btgirdVer) ? $btgirdVerDefault : $btgirdVer;
        wp_enqueue_style('clearision-bootstrap', 'https://static.tamersunion.net/wp-content/thirdparty/bootstrap/' . $btgirdVer . '/css/bootstrap-grid.min.css');
    });

    //jQuery
    if (get_option('clrs_thrdptComs_jq') === 'yes') {
        add_action('wp_enqueue_scripts', function () {
            $jqVer = get_option('clrs_thrdptComs_jq_ver', '3.4.1');
            if (empty($jqVer)) {
                $jqVer = '3.4.1';
            }
            wp_deregister_script('jquery');
            wp_register_script('jquery', 'https://static.tamersunion.net/wp-content/thirdparty/jquery/' . $jqVer . '/jquery-' . $jqVer . '.min.js', [], $jqVer);
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
            wp_enqueue_style('clearision-font-awesome', 'https://static.tamersunion.net/wp-content/thirdparty/font-awesome/' . $faVer . '/css/font-awesome.min.css', [], $faVer);
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
