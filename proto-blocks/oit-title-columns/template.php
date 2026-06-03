<?php
/**
 * OIT Title + 3 Columns
 *
 * Light-grey -> white gradient section. One big H2 sits in the first
 * grid column; up to three "scannable subhead + body" columns occupy
 * the remaining columns. On mobile the grid collapses to a single
 * column so everything stacks vertically.
 *
    * Layout is a two-level grid:
 *   - Outer 4-column track puts the title in column 1 and the
 *     repeater <ul> spanning columns 2-4 (col-span-3).
 *   - The <ul> itself is a 3-column grid with the same gap, so its
 *     <li> children land in tracks 2-4 of the outer grid visually.
 *
 * We used to use `display: contents` on the <ul> so the <li>s acted as
 * direct grid items, but the Proto-Blocks editor wraps repeater items
 * in a UI shell that breaks that trick. The nested-grid approach below
 * matches the same visual track widths (each cell ends up the same
 * width as one outer track) without relying on `contents`.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$title         = !empty($attributes['title']) ? $attributes['title'] : 'The IT Partner For Your Business';
$intro         = !empty($attributes['intro']) ? $attributes['intro'] : 'Short supporting paragraph that introduces the columns below.';
$columns       = $attributes['columns'] ?? [];
$show_gradient = $attributes['showGradient'] ?? true;
$show_red_line = $attributes['showRedLine'] ?? true;
$stack_layout  = !empty($attributes['stackLayout']);

// Intro paragraph only renders alongside the stacked layout -- it's the
// supporting line that sits between the title and the three columns.
// In the inline layout the title occupies a narrow left column and there
// is no room for a paragraph there, so the field is intentionally hidden.
//
// We render it unconditionally when stack_layout is on (even if the
// author hasn't filled it in yet) so the Proto-Blocks editor has an
// element to mount its inline WYSIWYG UI on. Until the author types
// into it, the placeholder default above is shown -- same pattern
// `title`/`subtitle` use in oit-two-col-header.
$show_intro = $stack_layout;

if (empty($columns)) {
  $columns = [
    [
      'subhead' => 'SCANNABLE SUBHEAD',
      'body'    => '<p>Support your entire organization with a team of experts, comprehensive resources, and a strategic approach to your IT needs.</p>',
    ],
    [
      'subhead' => 'SCANNABLE SUBHEAD',
      'body'    => '<p>Optimized IT is your partner in managing your IT network infrastructure and supporting your employees at their end-point devices. You get continual data analysis, defined service processes, and a deep bench of qualified IT network engineers.</p>',
    ],
    [
      'subhead' => 'SCANNABLE SUBHEAD',
      'body'    => '<p>You don\'t have to rely on a single IT employee to manage and support your IT functions. Our team provides 24/7/365 monitoring and support.</p>',
    ],
  ];
}

$is_preview = !isset($block) || $block === null;

$bg_class = $show_gradient
  ? 'bg-gradient-to-b from-light-grey to-white'
  : 'bg-white';

$wrapper_classes = 'oit-title-columns ' . $bg_class;
if ($stack_layout) {
  $wrapper_classes .= ' oit-title-columns--stacked';
}
$wrapper_args = ['class' => $wrapper_classes];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'manual';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Desktop track count adapts to the number of items the author has
// actually added (clamped 1-4 to match block.json). The grid track
// count is driven by a `--cN` modifier class on the inner `__cols`
// element so the editor's repeater render (which strips inline `style`
// but preserves `className`) still picks up the right layout. Mobile
// stays single column either way.
$cols_count = max(1, min(4, count($columns)));
$cols_count_class = 'oit-title-columns__cols--c' . $cols_count;
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-title-columns__inner max-w-[1440px] mx-auto px-6 lg:px-20 pt-12 lg:pt-20 pb-0">

    <div class="oit-title-columns__grid">

      <h2
        data-proto-field="title"
        class="oit-title-columns__title m-0 font-grotesk font-bold text-[32px] leading-[1.2] text-black lg:text-[40px]">
        <?php echo esc_html(wp_strip_all_tags($title)); ?>
      </h2>

      <?php if ($show_intro): ?>
      <div
        data-proto-field="intro"
        class="oit-title-columns__intro mt-5 font-dm font-medium text-body-sm leading-[1.5] text-black max-w-[820px] [&_p]:m-0 [&_p+p]:mt-3">
        <?php echo wp_kses_post(wpautop($intro)); ?>
      </div>
      <?php endif; ?>

      <ul
        data-proto-repeater="columns"
        class="oit-title-columns__cols <?php echo esc_attr($cols_count_class); ?> m-0 p-0 list-none<?php echo $stack_layout ? ' mt-10' : ''; ?>">
        <?php foreach ($columns as $column):
          $subhead = wp_strip_all_tags($column['subhead'] ?? '');
          $body    = $column['body']    ?? '';
        ?>
        <li
          data-proto-repeater-item
          class="oit-title-columns__col list-none flex flex-col gap-2.5">

          <p
            data-proto-field="subhead"
            class="oit-title-columns__subhead m-0 font-grotesk font-medium text-body-sm leading-[1.4] text-brand-red uppercase">
            <?php echo esc_html($subhead); ?>
          </p>

          <div
            data-proto-field="body"
            class="oit-title-columns__body font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-3">
            <?php echo wp_kses_post(wpautop($body)); ?>
          </div>

        </li>
        <?php endforeach; ?>
      </ul>

    </div>

    <?php if ($show_red_line): ?>
    <hr class="oit-title-columns__rule mt-10 lg:mt-16 mb-0 border-0 h-0.5 bg-brand-red" aria-hidden="true">
    <?php endif; ?>

  </div>
</section>
