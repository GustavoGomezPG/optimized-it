<?php
/**
 * OIT Related Resources
 *
 * "Keep Exploring Resources" section. Queries published `oit_resource`
 * posts, excludes the currently-viewed one (so on a single-resource
 * page it suggests siblings), and renders the same card visual used by
 * oit-resources for layout continuity.
 *
 * If `matchType` is enabled and the current post has at least one
 * Resource Type term, the query is restricted to posts sharing one of
 * those terms.
 *
 * In the editor (no current post / no published items) two placeholder
 * cards render so the layout is meaningful while authoring.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$heading    = (string) ($attributes['heading'] ?? 'Keep Exploring Resources');
$limit      = isset($attributes['limit']) ? max(1, (int) $attributes['limit']) : 2;
$match_type = !empty($attributes['matchType']);

$current_id = is_singular('oit_resource') ? (int) get_queried_object_id() : 0;

$resources = [];
if (post_type_exists('oit_resource')) {
  $args = [
    'post_type'              => 'oit_resource',
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
  if ($match_type && $current_id) {
    $terms = wp_get_post_terms($current_id, 'resource_type', ['fields' => 'ids']);
    if (!is_wp_error($terms) && !empty($terms)) {
      $args['tax_query'] = [[
        'taxonomy' => 'resource_type',
        'field'    => 'term_id',
        'terms'    => $terms,
      ]];
    }
  }
  $query = new WP_Query($args);
  foreach ($query->posts as $post_obj) {
    $terms     = get_the_terms($post_obj->ID, 'resource_type');
    $eyebrow   = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
    $cover_id  = get_post_thumbnail_id($post_obj->ID);
    $cover_url = $cover_id ? wp_get_attachment_image_url($cover_id, 'medium') : '';
    $cover_alt = $cover_id ? (string) get_post_meta($cover_id, '_wp_attachment_image_alt', true) : '';
    $resources[] = [
      'cover_url' => $cover_url,
      'cover_alt' => $cover_alt,
      'eyebrow'   => $eyebrow,
      'title'     => get_the_title($post_obj),
      'url'       => get_permalink($post_obj),
    ];
  }
}

if (empty($resources)) {
  $resources = [[
    'cover_url' => '',
    'cover_alt' => '',
    'eyebrow'   => 'FREE GUIDE',
    'title'     => 'What is Managed Data Security?',
    'url'       => '#',
  ]];
}

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-related-resources']);

$chevron = '<svg class="w-[13px] h-[14px] shrink-0" viewBox="0 0 13 14" fill="currentColor" aria-hidden="true"><path d="M0 1.64L1.36689 0.23L7.18345 6.23L1.36689 12.23L0 10.82L4.43997 6.23L0 1.64ZM5.81656 1.64L7.18345 0.23L13 6.23L7.18345 12.23L5.81656 10.82L10.2565 6.23L5.81656 1.64Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-related-resources__inner max-w-[900px] mx-auto px-6 lg:px-0">

    <h3 data-proto-field="heading"
        class="oit-related-resources__heading m-0 mb-5 lg:mb-6 font-grotesk font-bold text-[24px] lg:text-[30px] leading-[1.4] text-brand-black">
      <?php echo esc_html($heading); ?>
    </h3>

    <ul class="oit-related-resources__grid grid grid-cols-1 gap-6 m-0 p-0 list-none">
      <?php foreach ($resources as $resource):
        $cover_url = $resource['cover_url'];
        $cover_alt = $resource['cover_alt'];
        $eyebrow   = $resource['eyebrow'];
        $title     = $resource['title'];
        $url       = $resource['url'];
        $label     = $title ? sprintf('Read %s', $title) : 'Read resource';
      ?>
      <li class="oit-related-resources__card group relative flex items-center gap-4 lg:gap-5 p-4 lg:p-5 rounded-3xl bg-[#e6e6e8] text-brand-black list-none overflow-clip transition-shadow duration-300 hover:shadow-red-glow">

        <?php if ($cover_url): ?>
        <img src="<?php echo esc_url($cover_url); ?>"
             alt="<?php echo esc_attr($cover_alt); ?>"
             class="oit-related-resources__cover block shrink-0 w-[100px] h-[100px] lg:w-[129px] lg:h-[127px] object-contain object-center" />
        <?php else: ?>
        <div class="oit-related-resources__cover shrink-0 w-[100px] h-[100px] lg:w-[129px] lg:h-[127px] rounded-md border border-dashed border-black/20 flex items-center justify-center text-body-xs text-black/40"
             aria-hidden="true">Cover</div>
        <?php endif; ?>

        <div class="oit-related-resources__content flex-1 min-w-0 flex flex-col gap-2">
          <?php if ($eyebrow): ?>
          <p class="oit-related-resources__eyebrow m-0 font-grotesk font-medium text-body-sm leading-[1.4] text-black uppercase tracking-wide">
            <?php echo esc_html($eyebrow); ?>
          </p>
          <?php endif; ?>
          <p class="oit-related-resources__title m-0 font-grotesk font-medium text-[20px] lg:text-[24px] leading-[1.4] text-brand-black">
            <?php echo esc_html($title); ?>
            <span class="inline-flex items-center align-middle ml-2 text-brand-black transition-transform duration-300 group-hover:translate-x-1">
              <?php echo $chevron; ?>
            </span>
          </p>
        </div>

        <?php if ($url): ?>
        <a href="<?php echo esc_url($url); ?>"
           aria-label="<?php echo esc_attr($label); ?>"
           class="oit-related-resources__link absolute inset-0 z-10 rounded-3xl"></a>
        <?php endif; ?>

      </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
