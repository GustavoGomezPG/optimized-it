<?php
/**
 * Shared header partials used by oit-page-header and oit-two-col-header.
 *
 * Both blocks render the same auto-derived breadcrumb and the same
 * giant background wordmark. Extracting those two snippets here keeps
 * the proto-block templates focused on layout and lets a future header
 * variant opt into either piece without copy-pasting markup.
 *
 * Important constraint: Proto-Blocks only scans
 * `wp-content/themes/<active>/proto-blocks/` for Tailwind utility class
 * usage. Anything emitted from this file is invisible to the compiler,
 * so Tailwind utility classes baked into the markup below would simply
 * not exist in the compiled CSS.
 *
 * For that reason this partial emits ONLY BEM hook class names. All
 * visual styling for these hooks lives in the theme's main style.css
 * (loaded everywhere via enqueue_block_assets) so the shared markup
 * paints correctly regardless of which block is active on the page.
 *
 * Class names stay `oit-page-header__*` for historical compatibility:
 * the existing oit-page-header style.css and view.js key off these
 * hooks; renaming would force a three-file edit for zero visual gain.
 * Sibling blocks reuse the same selectors.
 */

if (!function_exists('oit_render_breadcrumb')) {
    /**
     * Print a Home -> ancestors -> current-page breadcrumb derived from
     * the current post's hierarchy. Current crumb is unlinked and
     * painted brand red; ancestor crumbs and the chevron separators
     * inherit text-color from the parent surface (white on a dark
     * card, black on a light section). Callers wanting an explicit
     * override can pass `--idle` as `text-white` or `text-black`, but
     * that class only takes effect if Tailwind compiled it -- normal
     * behavior here is color: inherit via the cascade.
     *
     * Falls back to a "Home -> Current" stub when no current post is
     * resolvable (editor preview / template insertion).
     */
    function oit_render_breadcrumb(string $idle_color_class = ''): void
    {
        $chevron = '<svg class="oit-page-header__crumb-chevron" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';

        $breadcrumb = [
            ['label' => 'Home', 'url' => home_url('/')],
        ];

        $current_id = get_the_ID();
        if ($current_id) {
            $ancestor_ids = array_reverse(get_post_ancestors($current_id));
            foreach ($ancestor_ids as $ancestor_id) {
                $breadcrumb[] = [
                    'label' => get_the_title($ancestor_id),
                    'url'   => get_permalink($ancestor_id),
                ];
            }
            $breadcrumb[] = [
                'label' => get_the_title($current_id) ?: 'Current',
                'url'   => '',
            ];
        } else {
            $breadcrumb[] = ['label' => 'Current', 'url' => ''];
        }

        $idle = $idle_color_class !== '' ? ' ' . esc_attr($idle_color_class) : '';
        ?>
        <nav class="oit-page-header__breadcrumb<?php echo $idle; ?>" aria-label="Breadcrumb">
            <?php foreach ($breadcrumb as $i => $crumb):
                $is_last = ($i === count($breadcrumb) - 1);
                $label   = $crumb['label'] ?? '';
                $url     = $crumb['url'] ?? '';
            ?>
            <span class="oit-page-header__crumb<?php echo $is_last ? ' is-current' : ''; ?>">
                <?php if ($url && !$is_last): ?>
                <a href="<?php echo esc_url($url); ?>" class="oit-page-header__crumb-link">
                    <?php echo esc_html($label); ?>
                </a>
                <?php else: ?>
                <span class="oit-page-header__crumb-label"><?php echo esc_html($label); ?></span>
                <?php endif; ?>
                <?php if (!$is_last): ?>
                <span class="oit-page-header__crumb-sep"><?php echo $chevron; ?></span>
                <?php endif; ?>
            </span>
            <?php endforeach; ?>
        </nav>
        <?php
    }
}

if (!function_exists('oit_render_wordmark')) {
    /**
     * Print the giant uppercase background wordmark that sits behind
     * header content. Empty string -> render nothing (caller doesn't
     * need to wrap the call in a conditional).
     *
     * Sizing, color, opacity and position all live in the theme's
     * main style.css under .oit-page-header__wordmark[-text] -- see
     * the "Shared header partial sizing" section there.
     */
    function oit_render_wordmark(string $text): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }
        ?>
        <div class="oit-page-header__wordmark" aria-hidden="true">
            <span class="oit-page-header__wordmark-text">
                <?php echo esc_html(strtoupper($text)); ?>
            </span>
        </div>
        <?php
    }
}
