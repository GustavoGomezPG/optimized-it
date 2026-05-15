<?php
/**
 * OptimizedIT theme functions.
 */

add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary' => __('Primary Navigation', 'optimizedit'),
    ]);

    // Load the theme stylesheet inside the block editor too -- it has the
    // Google Font @import, the html/body brand font, and the .oit-font-*
    // helpers. Without this, blocks render with the browser default serif
    // in the editor (or Gutenberg iframe) and look nothing like the front.
    add_editor_style('style.css');
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
