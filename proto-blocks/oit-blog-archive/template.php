<?php
/**
 * OIT Blog Archive
 *
 * Paginated archive of blog posts: an intro row ("Explore Blog Archive"
 * heading + a "Filter by topic" category dropdown and a "Sort by"
 * dropdown), a 3-column grid of vertical post cards (image, date,
 * category tags, title with chevron), and numbered pagination.
 *
 * Filtering, sorting and paging are all server-driven via URL query
 * args so the block works on any page with no JavaScript:
 *
 *   - ?topic=<category-slug>   filter to one category
 *   - ?sort=newest|oldest|az   ordering (default newest)
 *   - ?blog_page=<n>           page number
 *
 * The dropdowns use native <details>/<summary> so they open without JS;
 * each option is a link that reloads with the matching query arg (and
 * resets the page). Card visual matches oit-related-blogs.
 *
 * Light theme only (see global memory: feedback-no-dark-mode).
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$heading     = (string) ($attributes['heading'] ?? 'Explore Blog Archive');
$per_page    = isset($attributes['perPage']) ? max(1, (int) $attributes['perPage']) : 9;
$show_filter = !isset($attributes['showFilter']) || !empty($attributes['showFilter']);
$show_sort   = !isset($attributes['showSort']) || !empty($attributes['showSort']);

// --- Current state from the URL -----------------------------------------
$active_topic = isset($_GET['topic']) ? sanitize_title(wp_unslash($_GET['topic'])) : '';
$active_sort  = isset($_GET['sort']) ? sanitize_key(wp_unslash($_GET['sort'])) : 'newest';
if (!in_array($active_sort, ['newest', 'oldest', 'az'], true)) {
  $active_sort = 'newest';
}
$blog_page = isset($_GET['blog_page']) ? max(1, (int) $_GET['blog_page']) : 1;

// Resolve the active category (by slug) so the filter button can show its
// name; an unknown slug falls back to no filter.
$active_topic_name = '';
if ($active_topic !== '') {
  $active_term = get_category_by_slug($active_topic);
  if ($active_term) {
    $active_topic_name = $active_term->name;
  } else {
    $active_topic = '';
  }
}

// --- Query --------------------------------------------------------------
$order_map = [
  'newest' => ['orderby' => 'date',  'order' => 'DESC'],
  'oldest' => ['orderby' => 'date',  'order' => 'ASC'],
  'az'     => ['orderby' => 'title', 'order' => 'ASC'],
];
$args = array_merge([
  'post_type'      => 'post',
  'post_status'    => 'publish',
  'posts_per_page' => $per_page,
  'paged'          => $blog_page,
], $order_map[$active_sort]);
if ($active_topic !== '') {
  $args['category_name'] = $active_topic;
}
$query = new WP_Query($args);

// --- URL helpers (preserve other args, reset paging on filter/sort) -----
$build = static function (array $set, array $remove = []) {
  $url = add_query_arg($set);
  $url = remove_query_arg(array_merge(['blog_page'], $remove), $url);
  return esc_url($url);
};

$categories = $show_filter ? get_categories(['hide_empty' => true, 'orderby' => 'name']) : [];

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-blog-archive']);

$caret    = '<svg class="w-3 h-2 shrink-0 transition-transform duration-200" viewBox="0 0 12 8" fill="currentColor" aria-hidden="true"><path d="M6 8 0 1.4 1.4 0 6 4.9 10.6 0 12 1.4z"/></svg>';
$chevron  = '<svg class="inline-block w-[13px] h-3 shrink-0 align-baseline" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';

$sort_labels = ['newest' => 'Newest', 'oldest' => 'Oldest', 'az' => 'A – Z'];
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-blog-archive__band w-full bg-light-grey py-14 lg:py-20">
  <div class="oit-blog-archive__inner max-w-[1440px] mx-auto px-6 lg:px-20">

    <div class="oit-blog-archive__intro flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between mb-8 lg:mb-12">

      <h2 data-proto-field="heading"
          class="oit-blog-archive__heading m-0 font-grotesk font-bold text-[28px] lg:text-[40px] leading-[1.2] text-brand-black">
        <?php echo esc_html($heading); ?>
      </h2>

      <?php if ($show_filter || $show_sort): ?>
      <div class="oit-blog-archive__controls flex flex-wrap items-center gap-4 lg:gap-6">

        <?php if ($show_filter): ?>
        <details class="oit-blog-archive__dropdown group relative">
          <summary class="oit-blog-archive__toggle flex items-center justify-between gap-3 w-[200px] px-5 py-2.5 rounded-full border-2 border-cta-red font-grotesk font-medium uppercase text-[16px] leading-[1.3] text-brand-black cursor-pointer list-none select-none [&::-webkit-details-marker]:hidden">
            <span><?php echo esc_html($active_topic_name !== '' ? $active_topic_name : 'Filter by topic'); ?></span>
            <span class="group-open:rotate-180"><?php echo $caret; ?></span>
          </summary>
          <div class="oit-blog-archive__menu absolute z-20 mt-2 right-0 min-w-[220px] max-h-[320px] overflow-auto rounded-2xl bg-white border border-light-grey shadow-red-glow p-2">
            <a href="<?php echo $build([], ['topic']); ?>"
               class="block px-4 py-2 rounded-xl font-grotesk text-[15px] text-brand-black hover:bg-light-grey <?php echo $active_topic === '' ? 'text-brand-red' : ''; ?>">All topics</a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?php echo $build(['topic' => $cat->slug]); ?>"
               class="block px-4 py-2 rounded-xl font-grotesk text-[15px] text-brand-black hover:bg-light-grey <?php echo $active_topic === $cat->slug ? 'text-brand-red' : ''; ?>">
              <?php echo esc_html($cat->name); ?>
            </a>
            <?php endforeach; ?>
          </div>
        </details>
        <?php endif; ?>

        <?php if ($show_sort): ?>
        <details class="oit-blog-archive__dropdown group relative">
          <summary class="oit-blog-archive__toggle flex items-center justify-between gap-3 w-[200px] px-5 py-2.5 rounded-full border-2 border-cta-red font-grotesk font-medium uppercase text-[16px] leading-[1.3] text-brand-black cursor-pointer list-none select-none [&::-webkit-details-marker]:hidden">
            <span>Sort by</span>
            <span class="group-open:rotate-180"><?php echo $caret; ?></span>
          </summary>
          <div class="oit-blog-archive__menu absolute z-20 mt-2 right-0 min-w-[180px] rounded-2xl bg-white border border-light-grey shadow-red-glow p-2">
            <?php foreach ($sort_labels as $key => $label): ?>
            <a href="<?php echo $build(['sort' => $key]); ?>"
               class="block px-4 py-2 rounded-xl font-grotesk text-[15px] text-brand-black hover:bg-light-grey <?php echo $active_sort === $key ? 'text-brand-red' : ''; ?>">
              <?php echo esc_html($label); ?>
            </a>
            <?php endforeach; ?>
          </div>
        </details>
        <?php endif; ?>

      </div>
      <?php endif; ?>

    </div>

    <?php if ($query->have_posts()): ?>
    <ul class="oit-blog-archive__grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-[44px] m-0 p-0 list-none">
      <?php while ($query->have_posts()): $query->the_post();
        $post_id   = get_the_ID();
        $cats      = get_the_category($post_id);
        $tags      = (!is_wp_error($cats) && !empty($cats)) ? array_slice($cats, 0, 2) : [];
        $cover_id  = get_post_thumbnail_id($post_id);
        $cover_url = $cover_id ? wp_get_attachment_image_url($cover_id, 'medium_large') : '';
        $cover_alt = $cover_id ? (string) get_post_meta($cover_id, '_wp_attachment_image_alt', true) : '';
        $title     = get_the_title();
        $permalink = get_permalink();
        $label     = $title ? sprintf('Read %s', $title) : 'Read post';
      ?>
      <li class="oit-blog-archive__card group relative flex flex-col rounded-3xl bg-white p-6 overflow-clip transition-shadow duration-300 hover:shadow-red-glow">

        <div class="oit-blog-archive__cover relative w-full aspect-[2/1] rounded-3xl overflow-clip bg-light-grey">
          <?php if ($cover_url): ?>
          <img src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr($cover_alt); ?>"
               class="absolute inset-0 w-full h-full object-cover" />
          <?php else: ?>
          <div class="absolute inset-0 flex items-center justify-center text-black/40 text-body-sm font-grotesk" aria-hidden="true">Cover</div>
          <?php endif; ?>
        </div>

        <p class="oit-blog-archive__date m-0 mt-4 font-grotesk font-medium text-[16px] leading-[1.4] text-brand-black">
          <?php echo esc_html(get_the_date('F j, Y')); ?>
        </p>

        <?php if (!empty($tags)): ?>
        <ul class="oit-blog-archive__tags flex flex-wrap gap-2 m-0 mt-4 p-0 list-none">
          <?php foreach ($tags as $tag): ?>
          <li class="inline-flex items-center rounded-full bg-light-grey px-4 py-2 font-dm font-medium uppercase text-[14px] leading-[1.5] tracking-wide text-brand-black">
            <?php echo esc_html($tag->name); ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <h3 class="oit-blog-archive__title m-0 mt-4 font-grotesk font-bold text-[20px] leading-[1.4] text-brand-black">
          <?php echo esc_html($title); ?>
          <span class="inline-flex items-center ml-2 text-brand-black transition-transform duration-300 group-hover:translate-x-1"><?php echo $chevron; ?></span>
        </h3>

        <?php if ($permalink): ?>
        <a href="<?php echo esc_url($permalink); ?>" aria-label="<?php echo esc_attr($label); ?>"
           class="oit-blog-archive__link absolute inset-0 z-10 rounded-3xl"></a>
        <?php endif; ?>

      </li>
      <?php endwhile; ?>
    </ul>

    <?php
    $total_pages = (int) $query->max_num_pages;
    if ($total_pages > 1):
      $links = paginate_links([
        'base'      => esc_url_raw(add_query_arg('blog_page', '%#%')),
        'format'    => '',
        'current'   => $blog_page,
        'total'     => $total_pages,
        'mid_size'  => 1,
        'end_size'  => 1,
        'prev_text' => $chevron === '' ? '&lsaquo;' : str_replace('class="', 'class="rotate-180 ', $chevron),
        'next_text' => $chevron,
        'type'      => 'list',
      ]);
    ?>
    <nav class="oit-blog-archive__pagination mt-12 lg:mt-16 flex justify-center font-dm font-medium text-[20px] text-brand-black
                [&_ul]:flex [&_ul]:items-center [&_ul]:gap-2 [&_ul]:m-0 [&_ul]:p-0 [&_ul]:list-none
                [&_.page-numbers]:inline-flex [&_.page-numbers]:items-center [&_.page-numbers]:justify-center [&_.page-numbers]:min-w-[30px] [&_.page-numbers]:h-[30px] [&_.page-numbers]:px-2 [&_.page-numbers]:rounded-full [&_.page-numbers]:no-underline [&_.page-numbers]:transition-colors hover:[&_a.page-numbers]:text-brand-red
                [&_.current]:text-brand-red [&_.current]:font-bold"
         aria-label="Blog archive pagination">
      <?php echo $links; ?>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <p class="oit-blog-archive__empty m-0 py-10 font-dm text-[18px] text-brand-black/70">
      <?php echo esc_html($active_topic ? 'No posts found for this topic.' : 'No posts published yet.'); ?>
    </p>
    <?php endif; wp_reset_postdata(); ?>

  </div>
  </div>
</section>
