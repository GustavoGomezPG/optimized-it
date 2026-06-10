<?php
/**
 * HubSpot forms loader.
 *
 * Enqueues the small loader script that boots HubSpot's embed for any
 * `.hs-form-frame` on the page. Hooked on `enqueue_block_assets` so it loads
 * in BOTH the front end and the block-editor canvas iframe -- which is what
 * makes the oit-form block render its HubSpot form while editing (the editor
 * canvas is an iframe in WP 6.3+/7.0, and only enqueue_block_assets reaches it).
 *
 * @package OptimizedIT
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('enqueue_block_assets', function () {
    $rel  = '/assets/js/oit-hs-forms.js';
    $path = get_stylesheet_directory() . $rel;
    if (!file_exists($path)) {
        return;
    }
    wp_enqueue_script(
        'oit-hs-forms',
        get_stylesheet_directory_uri() . $rel,
        [],
        (string) filemtime($path),
        true
    );
});
