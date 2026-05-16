<?php
/**
 * OIT Stats
 *
 * Intro (H2 + body) + a grid of large brand-red stat values. Each stat's
 * numeric value rolls up from zero on scroll-into-view (see view.js). The
 * roll preserves the decimal precision of the source value -- "99.5" rolls
 * in tenths, "26" rolls as whole numbers.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$headline = $attributes['headline'] ?? 'The Optimized IT Difference By The Numbers';
$body     = $attributes['body']     ?? '<p>We know that in the world of managed IT services, you have a lot of choices. So we focus on three key numbers.</p>';
$stats    = $attributes['stats']    ?? [];

if (empty($stats)) {
  $stats = [
    [
      'value'       => '99.5',
      'suffix'      => '%',
      'description' => 'We guarantee 99.5% uptime performance for our hosted infrastructure. Translation: you can always rely on us.',
    ],
    [
      'value'       => '26',
      'suffix'      => ' SECONDS',
      'description' => 'Our US-based support team answers the phone in 26 seconds. You won\'t even have time to get comfortable in a chair before we pick up.',
    ],
    [
      'value'       => '65',
      'suffix'      => ' YEARS',
      'description' => 'We\'ve been in this business for over 65 years, but what\'s helped us keep going is our commitment to innovative thinking.',
    ],
  ];
}

$wrapper_attributes = get_block_wrapper_attributes([
  'class' => 'oit-stats',
]);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="max-w-[1440px] mx-auto flex flex-col gap-10 px-6 py-16 lg:px-20 lg:py-20">

    <div class="oit-stats__intro flex flex-col gap-5 max-w-[900px]">
      <h2
        data-proto-field="headline"
        class="oit-stats__headline m-0 font-grotesk font-bold text-[28px] leading-[1.2] text-black lg:text-h4">
        <?php echo esc_html($headline); ?>
      </h2>
      <div
        data-proto-field="body"
        class="oit-stats__body font-dm font-medium text-body-sm leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-4">
        <?php echo wp_kses_post($body); ?>
      </div>
    </div>

    <ul
      data-proto-repeater="stats"
      class="oit-stats__grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-10 m-0 p-0 list-none">
      <?php foreach ($stats as $stat):
        $value       = $stat['value'] ?? '';
        $suffix      = $stat['suffix'] ?? '';
        $description = $stat['description'] ?? '';
      ?>
      <li
        data-proto-repeater-item
        class="oit-stats__item flex flex-col items-start gap-[15px] list-none">
        <p
          class="oit-stats__value m-0 font-grotesk font-bold text-[40px] leading-[1.2] text-brand-red whitespace-nowrap lg:text-[56px]">
          <span
            data-proto-field="value"
            class="oit-stats__value-number"
            data-target="<?php echo esc_attr($value); ?>"><?php echo esc_html($value); ?></span><span
            data-proto-field="suffix"
            class="oit-stats__value-suffix"><?php echo esc_html($suffix); ?></span>
        </p>
        <p
          data-proto-field="description"
          class="oit-stats__description m-0 font-grotesk font-medium text-body-sm leading-[1.4] text-black">
          <?php echo esc_html($description); ?>
        </p>
      </li>
      <?php endforeach; ?>
    </ul>

  </div>
</section>
