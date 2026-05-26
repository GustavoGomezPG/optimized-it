<?php
/**
 * OIT Featured Blog
 *
 * Spotlights a single blog post in a two-column layout. Left column is
 * the post date, title (H2), excerpt and a "Keep Reading" pill linking
 * to the post. Right column is the post's featured image at a 610x400
 * box with the brand red glow. Mobile stacks to a single column.
 *
 * Post resolution:
 *   - featuredPost empty (default) -> newest published `post`.
 *   - featuredPost set             -> that specific published `post`
 *                                     (falls back to latest if invalid).
 *
 * `featuredPost` is a dynamic select: its options are the published posts,
 * fetched server-side via the `wp:posts` options provider, and its stored
 * value is the chosen post ID (as a string).
 *
 * Only `buttonLabel` is author-editable; the date, title, excerpt and
 * image are pulled live from the resolved post so the block stays in
 * sync as the post changes. The query runs in the editor too ($block
 * is null there) so the preview shows real content; if no posts exist
 * a placeholder keeps the layout meaningful.
 *
 * Light theme only (see global memory: feedback-no-dark-mode).
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$button_label = !empty($attributes['buttonLabel']) ? (string) $attributes['buttonLabel'] : 'Keep Reading';
$post_id      = isset($attributes['featuredPost']) ? max(0, (int) $attributes['featuredPost']) : 0;

// Resolve the post to feature. An explicit, valid, published post wins;
// otherwise fall back to the most recent published post.
$featured = null;
if ($post_id > 0) {
    $candidate = get_post($post_id);
    if ($candidate && $candidate->post_type === 'post' && $candidate->post_status === 'publish') {
        $featured = $candidate;
    }
}
if (!$featured) {
    $latest = new WP_Query([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 1,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ]);
    if (!empty($latest->posts)) {
        $featured = $latest->posts[0];
    }
}

// Derive display values from the resolved post, or fall back to editor
// placeholder content so the layout reads correctly while authoring.
if ($featured) {
    $date      = get_the_date('F j, Y', $featured);
    $title     = get_the_title($featured);
    $excerpt   = get_the_excerpt($featured);
    $permalink = get_permalink($featured);
    $image_id  = get_post_thumbnail_id($featured);
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'large') : '';
    $image_alt = $image_id ? (string) get_post_meta($image_id, '_wp_attachment_image_alt', true) : '';
} else {
    $date      = get_the_date('F j, Y') ?: date_i18n('F j, Y');
    $title     = 'Your latest blog post title appears here';
    $excerpt   = 'Publish a blog post and this section will automatically feature it with its excerpt, date and image. Pick a post in the sidebar to pin a specific one.';
    $permalink = '#';
    $image_url = '';
    $image_alt = '';
}

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-featured-blog']);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-featured-blog__inner max-w-[1280px] mx-auto px-6 lg:px-20 pb-10 lg:pb-16">

    <div class="oit-featured-blog__grid grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16 items-center">

      <div class="oit-featured-blog__content flex flex-col gap-5 lg:gap-6">

        <p class="oit-featured-blog__date m-0 font-dm font-medium text-[18px] leading-[1.5] text-black">
          <?php echo esc_html($date); ?>
        </p>

        <h2 class="oit-featured-blog__title m-0 font-grotesk font-bold text-[28px] lg:text-[40px] leading-[1.2] text-black break-words">
          <?php echo esc_html($title); ?>
        </h2>

        <p class="oit-featured-blog__excerpt m-0 font-dm font-medium text-[18px] leading-[1.5] text-black max-w-[600px] line-clamp-4">
          <?php echo esc_html($excerpt); ?>
        </p>

        <a href="<?php echo esc_url($permalink); ?>"
           class="oit-btn oit-btn--chevron self-start mt-1">
          <span data-proto-field="buttonLabel"><?php echo esc_html($button_label); ?></span>
        </a>

      </div>

      <div class="oit-featured-blog__image-wrap relative w-full aspect-[610/400] rounded-3xl overflow-clip shadow-red-glow bg-light-grey">
        <?php if ($image_url): ?>
        <img src="<?php echo esc_url($image_url); ?>"
             alt="<?php echo esc_attr($image_alt); ?>"
             class="oit-featured-blog__image absolute inset-0 w-full h-full object-cover" />
        <?php else: ?>
        <div class="oit-featured-blog__image absolute inset-0 w-full h-full flex items-center justify-center text-black/40 text-center text-body-sm font-grotesk px-4"
             aria-hidden="true">
          <span>Featured image</span>
        </div>
        <?php endif; ?>
      </div>

    </div>

  </div>
</section>
