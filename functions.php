<?php
/**
 * OptimizedIT theme functions.
 */

add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary' => __('Primary Navigation', 'optimizedit'),
    ]);

    // Register the theme style as an editor style too (classic editor +
    // legacy paths). The real heavy lifting for the FSE canvas iframe is
    // done by enqueue_block_assets below.
    add_editor_style('style.css');
});

add_filter('proto_blocks_category_slug', fn() => 'optimizedit');
add_filter('proto_blocks_category_title', fn() => __('OptimizedIT', 'optimizedit'));

/**
 * Enqueue the theme stylesheet for both the front end and the block-editor
 * canvas iframe.
 *
 * `enqueue_block_assets` is the only hook that fires in both contexts:
 *   - On the front end it runs during wp_head, so the stylesheet ships
 *     with the rendered page.
 *   - In the block / site editor it runs inside the canvas iframe boot,
 *     so the styles travel into the iframe alongside Proto-Blocks' own
 *     editor styles.
 *
 * `wp_enqueue_scripts` alone only covers the front end; `add_editor_style()`
 * alone is unreliable for the iframed FSE canvas. Using
 * `enqueue_block_assets` collapses both into one path.
 */
add_action('enqueue_block_assets', function () {
    $style = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'optimizedit-theme',
        get_stylesheet_uri(),
        [],
        file_exists($style) ? filemtime($style) : false
    );
});
