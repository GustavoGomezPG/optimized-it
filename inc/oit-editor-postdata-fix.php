<?php
/**
 * Fix: block editor intermittently loads/"redirects" to the wrong post.
 *
 * Root cause (confirmed via stack capture):
 *   post.php -> edit-form-blocks.php -> block_editor_rest_api_preload() preloads
 *   REST responses for the editor. One of them is the post's autosaves endpoint.
 *   WP_REST_Autosaves_Controller -> WP_REST_Revisions_Controller::prepare_item_for_response()
 *   calls setup_postdata() on the autosave *revision* and never restores the
 *   global $post. The leaked revision (or its parent) then stands in as the
 *   "current post" for the rest of the page render, so the inline
 *   wp.editPost.initializeEditor( ..., <postId>, ... ) call (edit-form-blocks.php)
 *   is handed the wrong id and the editor jumps to that post (rewriting the URL
 *   via the editor's history sync). It only triggers when the post has a pending
 *   autosave, which is why it looked intermittent.
 *
 * Fix: during a post.php editor load, after every preloaded REST request,
 * restore the global $post (and the related loop globals via setup_postdata) to
 * the post actually being edited. Scoped tightly so it only acts on the editor
 * preload (admin + a ?post= request id) and never touches real front-end or
 * standalone REST traffic.
 *
 * This is a host-side workaround for a WordPress core leak in
 * WP_REST_Revisions_Controller; remove if/when core resets postdata there.
 */

defined('ABSPATH') || exit;

add_filter('rest_post_dispatch', function ($result, $server, $request) {
    // Only during an admin editor page load (block_editor_rest_api_preload runs
    // inside the post.php request, where ?post=<id> is present). Standalone REST
    // requests and the front end have no ?post= and are skipped.
    if (!is_admin()) {
        return $result;
    }
    $edited_id = isset($_GET['post']) ? (int) $_GET['post'] : 0;
    if (!$edited_id) {
        return $result;
    }

    $current = isset($GLOBALS['post']) && is_object($GLOBALS['post']) ? (int) $GLOBALS['post']->ID : 0;
    if ($current === $edited_id) {
        return $result; // postdata is correct -- nothing leaked on this request.
    }

    $edited = get_post($edited_id);
    if ($edited instanceof WP_Post) {
        $GLOBALS['post'] = $edited;
        setup_postdata($edited);
    }

    return $result;
}, 10, 3);
