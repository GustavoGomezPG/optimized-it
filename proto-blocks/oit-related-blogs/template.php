<?php
/**
 * OIT Related Blogs
 *
 * "Related Blogs" section. Queries recent published `post`s, excludes the
 * currently-viewed one (so on a single post it suggests siblings), and
 * renders each as a vertical card: featured image, date, category tag
 * chips, then the title with a trailing chevron. A "Related Blogs"
 * heading sits top-left with an optional "View all blogs" pill top-right.
 *
 * If there are no other published posts to suggest, the whole section
 * is skipped (renders nothing) on both the front end and the editor.
 *
 * Light theme only (see global memory: feedback-no-dark-mode).
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$heading  = (string) ($attributes['heading'] ?? 'Related Blogs');
$limit    = isset($attributes['limit']) ? max(1, (int) $attributes['limit']) : 3;
$view_all = is_array($attributes['viewAll'] ?? null) ? $attributes['viewAll'] : [];

$va_url    = !empty($view_all['url'])  ? $view_all['url']  : '';
$va_text   = !empty($view_all['text']) ? $view_all['text'] : 'View all blogs';
$va_target = !empty($view_all['target']) ? ' target="' . esc_attr($view_all['target']) . '"' : '';
$va_rel    = !empty($view_all['rel'])    ? ' rel="'    . esc_attr($view_all['rel'])    . '"' : '';

$current_id = is_singular('post') ? (int) get_queried_object_id() : 0;

$posts = [];
$args = [
  'post_type'              => 'post',
  'post_status'            => 'publish',
  'posts_per_page'         => $limit,
  'orderby'                => 'date',
  'order'                  => 'DESC',
  'no_found_rows'          => true,
  'ignore_sticky_posts'    => true,
  'update_post_meta_cache' => false,
];
if ($current_id) {
  $args['post__not_in'] = [$current_id];
}
$query = new WP_Query($args);
foreach ($query->posts as $post_obj) {
  $cats     = get_the_category($post_obj->ID);
  $tags     = (!is_wp_error($cats) && !empty($cats)) ? array_map(static fn($c) => $c->name, array_slice($cats, 0, 2)) : [];
  $cover_id = get_post_thumbnail_id($post_obj->ID);
  $posts[] = [
    'cover_url' => $cover_id ? wp_get_attachment_image_url($cover_id, 'medium_large') : '',
    'cover_alt' => $cover_id ? (string) get_post_meta($cover_id, '_wp_attachment_image_alt', true) : '',
    'date'      => get_the_date('F j, Y', $post_obj),
    'tags'      => $tags,
    'title'     => get_the_title($post_obj),
    'url'       => get_permalink($post_obj),
  ];
}

// Nothing to suggest (e.g. this is the only published post) -> render
// nothing at all rather than an empty shell or placeholder cards.
if (empty($posts)) {
  return;
}

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-related-blogs']);

$chevron = '<svg class="inline-block w-[13px] h-3 shrink-0 align-baseline" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-related-blogs__band w-full py-14 lg:py-20" style="background-image: linear-gradient(180deg, rgb(230, 230, 232) 0%, rgb(255, 255, 255) 100%);">
  <div class="oit-related-blogs__inner max-w-[1440px] mx-auto px-6 lg:px-20">

    <div class="oit-related-blogs__head flex flex-wrap items-center justify-between gap-4 mb-8 lg:mb-12">
      <h2 data-proto-field="heading"
          class="oit-related-blogs__heading m-0 font-grotesk font-bold text-[28px] lg:text-[40px] leading-[1.2] text-brand-black">
        <?php echo esc_html($heading); ?>
      </h2>
      <a href="<?php echo esc_url($va_url ?: '#'); ?>"<?php echo $va_target; echo $va_rel; ?>
         class="oit-related-blogs__view-all oit-btn oit-btn--chevron shrink-0">
        <span data-proto-field="viewAll"><?php echo esc_html($va_text); ?></span>
      </a>
    </div>

    <ul class="oit-related-blogs__grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-[44px] m-0 p-0 list-none">
      <?php foreach ($posts as $post):
        $label = $post['title'] ? sprintf('Read %s', $post['title']) : 'Read post';
      ?>
      <li class="oit-related-blogs__card group relative flex flex-col rounded-3xl bg-white p-6 overflow-clip transition-shadow duration-300 hover:shadow-red-glow">

        <div class="oit-related-blogs__cover relative w-full aspect-[2/1] rounded-3xl overflow-clip bg-light-grey">
          <?php if ($post['cover_url']): ?>
          <img src="<?php echo esc_url($post['cover_url']); ?>"
               alt="<?php echo esc_attr($post['cover_alt']); ?>"
               class="absolute inset-0 w-full h-full object-cover" />
          <?php else: ?>
          <div class="absolute inset-0 flex items-center justify-center text-black/40 text-body-sm font-grotesk" aria-hidden="true">Cover</div>
          <?php endif; ?>
        </div>

        <p class="oit-related-blogs__date m-0 mt-4 font-grotesk font-medium text-[16px] leading-[1.4] text-brand-black">
          <?php echo esc_html($post['date']); ?>
        </p>

        <?php if (!empty($post['tags'])): ?>
        <ul class="oit-related-blogs__tags flex flex-wrap gap-2 m-0 mt-4 p-0 list-none">
          <?php foreach ($post['tags'] as $tag): ?>
          <li class="inline-flex items-center rounded-full bg-light-grey px-4 py-2 font-dm font-medium uppercase text-[14px] leading-[1.5] tracking-wide text-brand-black">
            <?php echo esc_html($tag); ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <h3 class="oit-related-blogs__title m-0 mt-4 font-grotesk font-bold text-[20px] leading-[1.4] text-brand-black">
          <?php echo esc_html($post['title']); ?>
          <span class="inline-flex items-center ml-2 text-brand-black transition-transform duration-300 group-hover:translate-x-1"><?php echo $chevron; ?></span>
        </h3>

        <?php if ($post['url']): ?>
        <a href="<?php echo esc_url($post['url']); ?>" aria-label="<?php echo esc_attr($label); ?>"
           class="oit-related-blogs__link absolute inset-0 z-10 rounded-3xl"></a>
        <?php endif; ?>

      </li>
      <?php endforeach; ?>
    </ul>

  </div>
  </div>
</section>
