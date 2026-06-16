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

if (!function_exists('oit_preview_post_id')) {
    /**
     * Resolve the post being edited during a Proto-Blocks editor preview.
     *
     * The editor preview is a `proto_blocks_preview` admin-ajax render that
     * does NOT receive the post id, so the template otherwise can't tell
     * which post it is describing. The preview fetch IS fired from the block
     * editor though, so the request Referer is the editor URL
     * (post.php?post=ID&action=edit) -- read the id from there. Guarded to
     * the ajax context and to posts the current user may edit, so it only
     * affects the live editor preview. Returns 0 otherwise.
     */
    function oit_preview_post_id(): int
    {
        if (!wp_doing_ajax()) {
            return 0;
        }
        $referer = isset($_SERVER['HTTP_REFERER']) ? (string) $_SERVER['HTTP_REFERER'] : '';
        if ($referer !== '' && preg_match('~[?&]post=(\d+)~', $referer, $m)) {
            $id = (int) $m[1];
            if ($id && current_user_can('edit_post', $id)) {
                return $id;
            }
        }
        return 0;
    }
}

if (!function_exists('oit_resolved_post_id')) {
    /**
     * The post id a header/breadcrumb block should describe: the queried
     * post on the front end, or the edited post during an editor preview
     * render (so the preview shows the real title/date/terms instead of
     * placeholders). 0 when neither resolves (e.g. a brand-new draft).
     */
    function oit_resolved_post_id(): int
    {
        if (is_singular()) {
            return (int) get_queried_object_id();
        }
        return oit_preview_post_id();
    }
}

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

        // Front end: the post in the loop. Editor preview: the edited post
        // (so the trailing crumb shows the real title, not "Current").
        $current_id = get_the_ID() ?: oit_preview_post_id();
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

if (!function_exists('oit_render_wordmark_split')) {
    /**
     * Two-color variant of the giant background wordmark used by
     * oit-about-hero. Renders two adjacent uppercase segments inside the
     * same wrapper so they read as one continuous word; each segment
     * paints in its own color via the --primary / --accent modifiers.
     *
     * If both strings are empty, nothing is printed. If only one is
     * present, the wrapper still uses the split layout but the missing
     * segment is omitted.
     *
     * Visual rules (font, fluid size, position, color, 0.4 opacity) live
     * in the theme's main style.css under .oit-page-header__wordmark--split.
     */
    function oit_render_wordmark_split(string $primary, string $accent): void
    {
        $primary = trim($primary);
        $accent  = trim($accent);
        if ($primary === '' && $accent === '') {
            return;
        }
        ?>
        <div class="oit-page-header__wordmark oit-page-header__wordmark--split" aria-hidden="true">
            <?php if ($primary !== ''): ?>
            <span class="oit-page-header__wordmark-text oit-page-header__wordmark-text--primary">
                <?php echo esc_html(strtoupper($primary)); ?>
            </span>
            <?php endif; ?>
            <?php if ($accent !== ''): ?>
            <span class="oit-page-header__wordmark-text oit-page-header__wordmark-text--accent">
                <?php echo esc_html(strtoupper($accent)); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php
    }
}
