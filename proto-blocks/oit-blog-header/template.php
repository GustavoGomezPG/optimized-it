<?php
/**
 * OIT Blog Header
 *
 * Top-of-page section for single blog posts. On a light-grey -> white
 * gradient background it stacks:
 *
 *   1. Breadcrumb row:  Home  >  Blog  >  [current title]
 *      (left-aligned; Home + parent are brand-black links, the trailing
 *      current crumb is brand-red text).
 *   2. A centered column: the post date, the post title as an h1, and a
 *      centered, wrapping row of category tag chips.
 *
 * Everything is content-driven from the current post + its categories so
 * editors do not have to touch fields on the canvas for the common case.
 * Mirrors oit-breadcrumbs-eyebrow-title (the resource equivalent) for the
 * gradient/breadcrumb treatment, but tailored to the blog mock.
 *
 * Light theme only (see global memory: feedback-no-dark-mode).
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$home_label    = (string) ($attributes['homeLabel'] ?? 'Home');
$parent_label  = (string) ($attributes['parentLabel'] ?? 'Blog');
$parent_url_in = trim((string) ($attributes['parentUrl'] ?? ''));
$title_over    = trim((string) ($attributes['titleOverride'] ?? ''));

// Front end: the queried post. Editor preview: the edited post (resolved
// from the preview-render referer) so the title/date/categories are real.
$current_id = function_exists('oit_resolved_post_id')
  ? oit_resolved_post_id()
  : (is_singular() ? (int) get_queried_object_id() : 0);

// Title resolution: explicit override wins, then the post title, then a
// placeholder so the editor preview reads correctly.
if ($title_over !== '') {
  $title = $title_over;
} elseif ($current_id) {
  $title = get_the_title($current_id);
} else {
  $title = 'Blog Post Title';
}

// Date from the current post; in the editor with no post fall back to today.
$date = $current_id ? get_the_date('F j, Y', $current_id) : date_i18n('F j, Y');

// Category chips: every category assigned to the post, uppercased via CSS.
$tags = [];
if ($current_id) {
  $cats = get_the_category($current_id);
  if (!is_wp_error($cats) && !empty($cats)) {
    foreach ($cats as $cat) {
      if (!empty($cat->name)) {
        $tags[] = $cat->name;
      }
    }
  }
}
if (empty($tags)) {
  // Placeholder chips so the layout is meaningful while authoring.
  $tags = ['Category'];
}

// Breadcrumb parent URL: explicit override, else the Posts page, else home.
if ($parent_url_in !== '') {
  $parent_url = $parent_url_in;
} else {
  $posts_page = (int) get_option('page_for_posts');
  $parent_url = $posts_page ? get_permalink($posts_page) : home_url('/');
}
$home_url   = esc_url(home_url('/'));
$parent_url = esc_url($parent_url ?: home_url('/'));

$is_preview = !isset($block) || $block === null;
$wrapper_args = ['class' => 'oit-blog-header'];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

$chevron = '<svg class="w-[13px] h-3 shrink-0 text-brand-black" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-blog-header__bg relative pt-[160px] lg:pt-[calc(160px_+_var(--oit-header-offset))]" style="background-image: linear-gradient(180deg, rgb(217, 217, 217) 0%, rgb(255, 255, 255) 100%);">

    <div class="oit-blog-header__inner max-w-[1440px] mx-auto px-6 lg:px-20">

      <nav class="oit-blog-header__crumbs flex items-center gap-2 mb-6 lg:mb-8 font-grotesk font-medium text-[16px] leading-[1.3]" aria-label="Breadcrumb">
        <a href="<?php echo $home_url; ?>" class="text-brand-black hover:text-brand-red transition-colors no-underline">
          <?php echo esc_html($home_label); ?>
        </a>
        <?php echo $chevron; ?>
        <a href="<?php echo $parent_url; ?>" class="text-brand-black hover:text-brand-red transition-colors no-underline">
          <?php echo esc_html($parent_label); ?>
        </a>
        <?php echo $chevron; ?>
        <span class="text-brand-red truncate max-w-[50vw]"><?php echo esc_html($title); ?></span>
      </nav>

      <div class="oit-blog-header__card relative rounded-3xl bg-white px-6 py-12 lg:px-15 lg:py-15 overflow-clip">
        <div class="oit-blog-header__card-inner mx-auto max-w-[900px] flex flex-col items-center gap-5 text-center">

          <p class="oit-blog-header__date m-0 font-dm font-medium text-[18px] leading-[1.5] text-brand-black">
            <?php echo esc_html($date); ?>
          </p>

          <h1 class="oit-blog-header__title m-0 font-grotesk font-bold text-[28px] lg:text-[40px] leading-[1.2] text-brand-black">
            <?php echo esc_html($title); ?>
          </h1>

          <ul class="oit-blog-header__tags flex flex-wrap justify-center gap-3 m-0 p-0 list-none">
            <?php foreach ($tags as $tag): ?>
            <li class="oit-blog-header__tag inline-flex items-center rounded-full bg-light-grey px-4 py-2 font-grotesk font-medium uppercase text-[14px] leading-[1.4] tracking-wide text-brand-black">
              <?php echo esc_html($tag); ?>
            </li>
            <?php endforeach; ?>
          </ul>

        </div>
      </div>

    </div>

  </div>
</section>
