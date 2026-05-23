<?php
/**
 * OIT Resources -- Custom Post Type
 *
 * Registers the `oit_resource` post type used for whitepapers, guides,
 * and other downloadable assets surfaced by the OIT Resources block.
 *
 * The CPT ships with:
 *   - public archives + single views (so URLs like /resources/foo/ work),
 *   - `show_in_rest` true so it's editable in the block editor and
 *     reachable from the REST API for block-side queries later,
 *   - thumbnail support for the cover image the cards render,
 *   - excerpt support for the eyebrow / short description.
 *
 * The empty FSE template lives at templates/single-oit_resource.html and
 * is auto-bound to the CPT via the `template` arg on registration. WP
 * applies it the first time a resource is published; editors can still
 * override per-post via the document sidebar.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    $labels = [
        'name'                  => __('Resources', 'optimizedit'),
        'singular_name'         => __('Resource', 'optimizedit'),
        'menu_name'             => __('Resources', 'optimizedit'),
        'name_admin_bar'        => __('Resource', 'optimizedit'),
        'add_new'               => __('Add New', 'optimizedit'),
        'add_new_item'          => __('Add New Resource', 'optimizedit'),
        'new_item'              => __('New Resource', 'optimizedit'),
        'edit_item'             => __('Edit Resource', 'optimizedit'),
        'view_item'             => __('View Resource', 'optimizedit'),
        'view_items'            => __('View Resources', 'optimizedit'),
        'all_items'             => __('All Resources', 'optimizedit'),
        'search_items'          => __('Search Resources', 'optimizedit'),
        'not_found'             => __('No resources found.', 'optimizedit'),
        'not_found_in_trash'    => __('No resources found in Trash.', 'optimizedit'),
        'featured_image'        => __('Cover Image', 'optimizedit'),
        'set_featured_image'    => __('Set cover image', 'optimizedit'),
        'remove_featured_image' => __('Remove cover image', 'optimizedit'),
        'use_featured_image'    => __('Use as cover image', 'optimizedit'),
        'archives'              => __('Resource Archives', 'optimizedit'),
        'attributes'            => __('Resource Attributes', 'optimizedit'),
    ];

    register_post_type('oit_resource', [
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'show_in_rest'        => true,
        'menu_position'       => 22,
        'menu_icon'           => 'dashicons-portfolio',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'has_archive'         => 'resources',
        'rewrite'             => ['slug' => 'resources', 'with_front' => false],
        'query_var'           => true,
        'supports'            => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields'],
        // Block template that pre-populates every NEW resource post
        // with the standard resource layout: header card, video, text +
        // image content, form, related resources, and the link cards
        // shelf. Authors fill in each block's fields per-post. Not
        // locked, so authors can rearrange / add / remove blocks.
        'template'            => [
            ['proto-blocks/oit-breadcrumbs-eyebrow-title', []],
            ['proto-blocks/oit-video-preview', []],
            ['core/spacer', ['height' => '48px']],
            ['proto-blocks/oit-text-image', []],
            ['core/spacer', ['height' => '48px']],
            ['proto-blocks/oit-form', []],
            ['core/spacer', ['height' => '48px']],
            ['proto-blocks/oit-related-resources', []],
            ['core/spacer', ['height' => '80px']],
            ['proto-blocks/oit-link-cards', []],
        ],
        'template_lock'       => false,
    ]);

    // Resource Type taxonomy -- powers the eyebrow label on resource
    // cards ("FREE GUIDE", "FREE EDUCATIONAL WHITEPAPER", etc). One
    // resource can have one type; the block uses the first assigned
    // term as the eyebrow.
    register_taxonomy('resource_type', ['oit_resource'], [
        'labels' => [
            'name'              => __('Resource Types', 'optimizedit'),
            'singular_name'     => __('Resource Type', 'optimizedit'),
            'search_items'      => __('Search Resource Types', 'optimizedit'),
            'all_items'         => __('All Resource Types', 'optimizedit'),
            'edit_item'         => __('Edit Resource Type', 'optimizedit'),
            'update_item'       => __('Update Resource Type', 'optimizedit'),
            'add_new_item'      => __('Add New Resource Type', 'optimizedit'),
            'new_item_name'     => __('New Resource Type', 'optimizedit'),
            'menu_name'         => __('Types', 'optimizedit'),
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'resource-type'],
    ]);
});
