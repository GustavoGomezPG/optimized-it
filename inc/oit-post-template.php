<?php
/**
 * Default block-editor template for standard blog posts.
 *
 * Standard `post` is a core post type, so we can't pass a `template` arg
 * to register_post_type(). Instead we set the `template` (and unlock it)
 * directly on the registered post type object late on `init`, by which
 * point core's built-in types definitely exist. The block editor reads
 * this when seeding a NEW post, giving every blog post the standard
 * layout: blog header, a full-width rounded featured image, an open
 * content area, related blogs, and the link-cards shelf.
 *
 * The matching front-end markup lives in templates/single.html (header +
 * full-width post-content + footer). Not locked, so authors can
 * rearrange / add / remove blocks per post.
 *
 * @package optimizedit
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    $post_type = get_post_type_object('post');
    if (!$post_type) {
        return;
    }

    $post_type->template = [
        ['proto-blocks/oit-blog-header', []],
        // Centered 900px content column: the full-width (within the
        // column) rounded featured image, then an open area authors fill
        // with paragraphs, headings, lists, etc.
        ['core/group', [
            'layout' => ['type' => 'constrained', 'contentSize' => '900px'],
        ], [
            // Auto-pulls the post's Featured Image. Renders nothing when
            // none is set, so authors can simply set the featured image
            // in the sidebar instead of inserting a separate image block.
            ['core/post-featured-image', [
                'aspectRatio' => '2/1',
                'isLink'      => false,
                'style'       => ['border' => ['radius' => '24px']],
            ]],
            ['core/spacer', ['height' => '32px']],
            ['core/paragraph', ['placeholder' => 'Write your post content here…']],
        ]],
        ['core/spacer', ['height' => '96px']],
        ['proto-blocks/oit-related-blogs', []],
        ['core/spacer', ['height' => '96px']],
        ['proto-blocks/oit-link-cards', []],
    ];
    $post_type->template_lock = false;
}, 99);
