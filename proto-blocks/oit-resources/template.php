<?php
/**
 * OIT Resources
 *
 * Resources library section. Three pieces stacked on the light-grey to
 * white background gradient:
 *
 *   1. Section heading ("Explore Resources" by default).
 *   2. Responsive 2-up grid of white rounded cards, ONE PER PUBLISHED
 *      `oit_resource` POST (newest first, count capped by the `limit`
 *      control). Each card pulls:
 *         - cover    : featured image
 *         - eyebrow  : first assigned Resource Type term name (uppercased)
 *         - title    : post title
 *         - link     : permalink (stretched <a> covers the whole card)
 *      In the editor / when no resources exist, two static placeholder
 *      cards render so the layout is still meaningful.
 *   3. Conversion callout bar -- dark gradient pill, headline on the
 *      left, solid white pill button on the right. Same visual recipe
 *      as oit-cta but baked in here so the section ships as one block.
 *      Toggle off via the `showCta` control.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$heading      = !empty($attributes['heading'])     ? $attributes['heading']     : 'Explore Resources';
$cta_headline = !empty($attributes['ctaHeadline']) ? $attributes['ctaHeadline'] : 'Explore more topics with the OptimizedIT Blog.';
$cta_button   = is_array($attributes['ctaButton'] ?? null) ? $attributes['ctaButton'] : [];
$cta_url      = !empty($cta_button['url'])    ? $cta_button['url']    : '#';
$cta_text     = !empty($cta_button['text'])   ? $cta_button['text']   : 'EXPLORE BLOG';
$cta_target   = !empty($cta_button['target']) ? ' target="' . esc_attr($cta_button['target']) . '"' : '';
$cta_rel      = !empty($cta_button['rel'])    ? ' rel="'    . esc_attr($cta_button['rel'])    . '"' : '';

$limit    = isset($attributes['limit']) ? max(1, (int) $attributes['limit']) : 6;
$show_cta = !isset($attributes['showCta']) || !empty($attributes['showCta']);

$is_preview = !isset($block) || $block === null;

// ---- Resource query --------------------------------------------------
// Pull live items from the CPT. In the editor preview we still want
// SOMETHING to show, so fall through to placeholders if no posts exist.
$resources = [];
if (post_type_exists('oit_resource')) {
  $query = new WP_Query([
    'post_type'              => 'oit_resource',
    'post_status'            => 'publish',
    'posts_per_page'         => $limit,
    'orderby'                => 'date',
    'order'                  => 'DESC',
    'no_found_rows'          => true,
    'ignore_sticky_posts'    => true,
    'update_post_meta_cache' => false,
  ]);
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

// Editor preview / empty-state fallback.
if (empty($resources)) {
  $resources = [
    [
      'cover_url' => '',
      'cover_alt' => '',
      'eyebrow'   => 'FREE EDUCATIONAL WHITEPAPER',
      'title'     => 'Co-Managed IT Playbook',
      'url'       => '#',
    ],
    [
      'cover_url' => '',
      'cover_alt' => '',
      'eyebrow'   => 'FREE GUIDE',
      'title'     => 'What is Managed Data Security?',
      'url'       => '#',
    ],
  ];
}

$wrapper_args = ['class' => 'oit-resources'];
if (!$is_preview) {
  $wrapper_args['data-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Same chevron used across CTA buttons for visual consistency.
$chevron_card = '<svg class="w-[13px] h-[14px] shrink-0" viewBox="0 0 13 14" fill="currentColor" aria-hidden="true"><path d="M0 1.64L1.36689 0.23L7.18345 6.23L1.36689 12.23L0 10.82L4.43997 6.23L0 1.64ZM5.81656 1.64L7.18345 0.23L13 6.23L7.18345 12.23L5.81656 10.82L10.2565 6.23L5.81656 1.64Z"/></svg>';
$chevron_cta  = '<svg class="w-[13px] h-3 shrink-0" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-resources__bg bg-linear-to-b from-[#e6e6e8] to-white">
    <div class="oit-resources__inner max-w-[1440px] mx-auto px-6 lg:px-20 pt-12 pb-14 lg:pt-14 lg:pb-20">

      <h2 data-proto-field="heading"
          class="oit-resources__heading m-0 mb-8 lg:mb-9 font-grotesk font-bold text-[28px] leading-[1.2] text-brand-black lg:text-[40px]">
        <?php echo esc_html($heading); ?>
      </h2>

      <ul class="oit-resources__grid grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-11 m-0 p-0 list-none">
        <?php foreach ($resources as $resource):
          $cover_url = $resource['cover_url'];
          $cover_alt = $resource['cover_alt'];
          $eyebrow   = $resource['eyebrow'];
          $title     = $resource['title'];
          $url       = $resource['url'];
          $label     = $title ? sprintf('Read %s', $title) : 'Read resource';
        ?>
        <li class="oit-resources__card group relative flex items-center gap-4 lg:gap-5 p-4 lg:p-5 rounded-3xl bg-white text-brand-black list-none overflow-clip transition-shadow duration-300 hover:shadow-red-glow">

          <?php if ($cover_url): ?>
          <img src="<?php echo esc_url($cover_url); ?>"
               alt="<?php echo esc_attr($cover_alt); ?>"
               class="oit-resources__cover block shrink-0 w-[100px] h-[100px] lg:w-[129px] lg:h-[127px] object-contain object-center" />
          <?php else: ?>
          <div class="oit-resources__cover shrink-0 w-[100px] h-[100px] lg:w-[129px] lg:h-[127px] rounded-md border border-dashed border-black/20 flex items-center justify-center text-body-xs text-black/40"
               aria-hidden="true">Cover</div>
          <?php endif; ?>

          <div class="oit-resources__content flex-1 min-w-0 flex flex-col gap-2">
            <?php if ($eyebrow): ?>
            <p class="oit-resources__eyebrow m-0 font-grotesk font-medium text-body-sm leading-[1.4] text-black uppercase tracking-wide">
              <?php echo esc_html($eyebrow); ?>
            </p>
            <?php endif; ?>
            <p class="oit-resources__title m-0 font-grotesk font-medium text-[20px] lg:text-[24px] leading-[1.4] text-brand-black">
              <?php echo esc_html($title); ?>
              <span class="oit-resources__chevron inline-flex items-center align-middle ml-2 text-brand-black transition-transform duration-300 group-hover:translate-x-1">
                <?php echo $chevron_card; ?>
              </span>
            </p>
          </div>

          <?php if ($url): ?>
          <a href="<?php echo esc_url($url); ?>"
             aria-label="<?php echo esc_attr($label); ?>"
             class="oit-resources__link absolute inset-0 z-10 rounded-3xl"></a>
          <?php endif; ?>

        </li>
        <?php endforeach; ?>
      </ul>

      <?php if ($show_cta): ?>
      <div class="oit-resources__cta mt-12 lg:mt-16">
        <div class="oit-resources__cta-card relative rounded-3xl overflow-hidden shadow-red-glow bg-[#353333] p-6 lg:p-12">
          <div class="oit-resources__cta-row flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between lg:gap-10">

            <p data-proto-field="ctaHeadline"
               class="oit-resources__cta-headline m-0 font-grotesk font-bold text-[22px] leading-[1.3] text-white lg:text-[28px] lg:leading-[1.3] max-w-[700px]">
              <?php echo esc_html($cta_headline); ?>
            </p>

            <a href="<?php echo esc_url($cta_url); ?>"<?php echo $cta_target; echo $cta_rel; ?>
               class="oit-resources__cta-button self-start shrink-0 inline-flex items-center gap-3 px-5 py-2.5 rounded-full bg-white hover:bg-white/90 text-brand-black no-underline font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap transition-colors lg:self-auto">
              <span data-proto-field="ctaButton"><?php echo esc_html($cta_text); ?></span>
              <?php echo $chevron_cta; ?>
            </a>

          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</section>
