<?php
/**
 * OptimizedIT theme functions.
 */

add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary' => __('Primary Navigation', 'optimizedit'),
    ]);
});

add_filter('proto_blocks_category_slug', fn() => 'optimizedit');
add_filter('proto_blocks_category_title', fn() => __('OptimizedIT', 'optimizedit'));

add_action('wp_enqueue_scripts', function () {
    $style = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'optimizedit-theme',
        get_stylesheet_uri(),
        [],
        file_exists($style) ? filemtime($style) : false
    );
});
