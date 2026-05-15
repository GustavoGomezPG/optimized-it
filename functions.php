<?php
/**
 * OptimizedIT theme functions.
 */

add_action('after_setup_theme', function () {
    register_nav_menus([
        'primary' => __('Primary Navigation', 'optimizedit'),
        'footer'  => __('Footer Navigation', 'optimizedit'),
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

/**
 * Inline SVG for a brand social icon. Used by oit-navigation and oit-footer.
 * Returns a stroke/fill currentColor SVG so the rendering element controls
 * the icon color through Tailwind text utilities.
 *
 * Supported: linkedin, facebook, youtube, instagram, x (alias: twitter).
 * Anything else returns a neutral circle so the icon area is still visible.
 */
if (!function_exists('oit_social_icon')) {
    function oit_social_icon(string $platform): string
    {
        $platform = strtolower(trim($platform));
        switch ($platform) {
            case 'linkedin':
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.86-3.04-1.86 0-2.14 1.45-2.14 2.95v5.66H9.35V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.11 2.06 2.06 0 0 1 0 4.11Zm1.78 13.02H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0Z"/></svg>';
            case 'facebook':
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12a12 12 0 1 0-13.88 11.85v-8.39H7.08V12h3.04V9.36c0-3 1.79-4.67 4.53-4.67 1.31 0 2.69.24 2.69.24v2.96h-1.52c-1.49 0-1.96.93-1.96 1.88V12h3.33l-.53 3.47h-2.8v8.39A12 12 0 0 0 24 12Z"/></svg>';
            case 'youtube':
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.12-2.13C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.52A3 3 0 0 0 .5 6.2 31.4 31.4 0 0 0 0 12a31.4 31.4 0 0 0 .5 5.8 3 3 0 0 0 2.12 2.13C4.5 20.45 12 20.45 12 20.45s7.5 0 9.38-.52a3 3 0 0 0 2.12-2.13A31.4 31.4 0 0 0 24 12a31.4 31.4 0 0 0-.5-5.8ZM9.55 15.57V8.43L15.82 12l-6.27 3.57Z"/></svg>';
            case 'instagram':
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.16c3.2 0 3.58 0 4.85.07 1.17.05 1.8.25 2.22.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.05.41 2.22.06 1.27.07 1.65.07 4.85s0 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.22a3.72 3.72 0 0 1-.9 1.38c-.42.42-.82.68-1.38.9-.42.16-1.05.36-2.22.41-1.27.06-1.65.07-4.85.07s-3.58 0-4.85-.07c-1.17-.05-1.8-.25-2.22-.41a3.72 3.72 0 0 1-1.38-.9 3.72 3.72 0 0 1-.9-1.38c-.16-.42-.36-1.05-.41-2.22C2.18 15.58 2.16 15.2 2.16 12s0-3.58.07-4.85c.05-1.17.25-1.8.41-2.22.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.05-.36 2.22-.41C8.42 2.18 8.8 2.16 12 2.16ZM12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63a5.88 5.88 0 0 0-2.13 1.38A5.88 5.88 0 0 0 .63 4.14C.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.79.73 1.46 1.38 2.13.67.65 1.34 1.07 2.13 1.38.76.3 1.64.5 2.91.56 1.28.06 1.69.07 4.95.07s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56a5.88 5.88 0 0 0 2.13-1.38 5.88 5.88 0 0 0 1.38-2.13c.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.88 5.88 0 0 0-1.38-2.13A5.88 5.88 0 0 0 19.86.63c-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0Zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.41-10.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88Z"/></svg>';
            case 'x':
            case 'twitter':
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 1.16h3.68l-8.03 9.17L24 22.85h-7.4l-5.79-7.57-6.63 7.57H.5l8.59-9.81L0 1.16h7.58l5.23 6.92 6.09-6.92Zm-1.29 19.5h2.04L6.49 3.24H4.3L17.6 20.65Z"/></svg>';
            default:
                return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="10"/></svg>';
        }
    }
}
