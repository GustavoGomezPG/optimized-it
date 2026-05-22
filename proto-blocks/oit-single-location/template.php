<?php
/**
 * OIT Single Location
 *
 * Office page header in a two-column layout. Left column carries the
 * shared breadcrumb (when enabled), headline with optional highlight,
 * supporting paragraph, and a stack of contact rows (phone, email,
 * mailing address). Right column is a live Leaflet map tiled by the
 * CARTO basemap network -- no API key required, free, with several
 * pre-baked themes (Dark Matter is the default and matches the dark
 * map look in the design).
 *
 * The map itself is initialized by view.js. The container is rendered
 * server-side with data-* attributes that the JS reads (center, zoom,
 * theme). When JS is absent or Leaflet fails to load, the container
 * still renders -- it just sits empty with the brand-grey fallback.
 *
 * Light-theme surface only (per the global no-dark-mode rule). The
 * dark Map theme is purely the right-column tile aesthetic, not a
 * page-level dark mode.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$title    = !empty($attributes['title'])    ? $attributes['title']    : '<p>Connect With the OptimizedIT Office in Pittsburgh</p>';
$subtitle = !empty($attributes['subtitle']) ? $attributes['subtitle'] : 'Contact The OptimizedIT Office in Pittsburgh, PA for managed or co-managed IT infrastructure and services.';
$phone    = is_array($attributes['phone']   ?? null) ? $attributes['phone']   : [];
$email    = is_array($attributes['email']   ?? null) ? $attributes['email']   : [];
$address  = !empty($attributes['address'])  ? $attributes['address']  : '<p>4000 Town Center Boulevard Suite 250<br>Canonsburg, PA 15317</p>';

$highlight       = trim((string) ($attributes['highlightWord'] ?? ''));
$show_breadcrumb = $attributes['showBreadcrumb'] ?? true;

// Map config -- threaded into the container element as data-* so
// view.js can read them once. Defaults match the Pittsburgh office.
$map_center = trim((string) ($attributes['mapCenter'] ?? '40.4406,-79.9959'));
$map_zoom   = isset($attributes['mapZoom']) ? max(3, min(18, (int) $attributes['mapZoom'])) : 12;
$map_theme  = (string) ($attributes['mapTheme'] ?? 'dark_all');
$allowed_themes = ['dark_all', 'dark_nolabels', 'voyager', 'voyager_nolabels', 'positron', 'positron_nolabels'];
if (!in_array($map_theme, $allowed_themes, true)) {
  $map_theme = 'dark_all';
}

// Highlight pass -- same approach as oit-two-col-header.
if ($highlight !== '') {
  $pos = stripos($title, $highlight);
  if ($pos !== false) {
    $matched = substr($title, $pos, strlen($highlight));
    $title   = substr($title, 0, $pos)
      . '<span class="text-brand-red">' . $matched . '</span>'
      . substr($title, $pos + strlen($highlight));
  }
}

$is_preview = !isset($block) || $block === null;

$wrapper_args = ['class' => 'oit-single-location relative'];
if (!$is_preview) {
  $wrapper_args['data-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Inline SVG icons. Fill is hard-coded to the brand red so the icons
// keep their signature color regardless of the row's text color (which
// flips on hover). xmlns is included so these survive being copied
// out of the rendered HTML into other contexts (email templates, etc).
$icon_phone = '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[17px] shrink-0" viewBox="0 0 18 17" fill="none" aria-hidden="true"><path d="M12.5569 10.906L12.1019 11.359C12.1019 11.359 11.0189 12.435 8.06386 9.49698C5.10886 6.55898 6.19186 5.48298 6.19186 5.48298L6.47786 5.19698C7.18486 4.49498 7.25186 3.36698 6.63486 2.54298L5.37486 0.859979C4.61086 -0.160021 3.13586 -0.29502 2.26086 0.57498L0.690856 2.13498C0.257856 2.56698 -0.032144 3.12498 0.002856 3.74498C0.092856 5.33198 0.810856 8.74498 4.81486 12.727C9.06186 16.949 13.0469 17.117 14.6759 16.965C15.1919 16.917 15.6399 16.655 16.0009 16.295L17.4209 14.883C18.3809 13.93 18.1109 12.295 16.8829 11.628L14.9729 10.589C14.1669 10.152 13.1869 10.28 12.5569 10.906Z" fill="#D1001D"/></svg>';
$icon_email = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-4 shrink-0" viewBox="0 0 20 16" fill="none" aria-hidden="true"><path d="M18 0H2C0.9 0 0.00999999 0.9 0.00999999 2L0 14C0 15.1 0.9 16 2 16H18C19.1 16 20 15.1 20 14V2C20 0.9 19.1 0 18 0ZM18 4L10 9L2 4V2L10 7L18 2V4Z" fill="#D1001D"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-single-location__inner relative max-w-[1440px] mx-auto px-6 lg:px-20 pt-10 lg:pt-16 pb-12 lg:pb-20">

    <?php if ($show_breadcrumb) { oit_render_breadcrumb(); } ?>

    <div class="oit-single-location__grid relative z-10 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">

      <div class="oit-single-location__content flex flex-col gap-5 lg:gap-6">

        <div
          data-proto-field="title"
          class="oit-single-location__title m-0 font-grotesk font-bold text-[32px] leading-[1.2] lg:text-[40px] lg:leading-[1.2] text-black [&_p]:m-0 break-words">
          <?php
          // The WYSIWYG field stores Enter presses as raw `\n` newlines
          // (no <br> or <p> wrap), and those newlines collapse to a
          // single space inside the default white-space rendering. Run
          // nl2br() so every `\n` becomes a literal <br>, which the
          // browser breaks on natively regardless of nesting.
          echo wp_kses_post(nl2br($title));
          ?>
        </div>

        <div
          data-proto-field="subtitle"
          class="oit-single-location__subtitle m-0 font-dm font-medium text-[18px] leading-[1.5] text-black max-w-[820px] [&_p]:m-0 [&_p+p]:mt-1">
          <?php echo wp_kses_post(wpautop($subtitle)); ?>
        </div>

        <?php
        // Phone + email rows render unconditionally with placeholder
        // defaults when the link field is empty -- the rendered <a>
        // gives Proto-Blocks' editor a DOM node to mount its inline
        // LinkField UI on, otherwise the field is invisible to the
        // author. Same pattern oit-call-to-action uses for its CTA.
        $phone_text = !empty($phone['text']) ? $phone['text'] : '888.308.6689';
        $phone_url  = !empty($phone['url'])  ? $phone['url']  : 'tel:+18883086689';
        $email_text = !empty($email['text']) ? $email['text'] : 'info@optimizedit.net';
        $email_url  = !empty($email['url'])  ? $email['url']  : 'mailto:info@optimizedit.net';
        ?>

        <div class="oit-single-location__contact flex flex-col gap-3 mt-2">

          <a
            href="<?php echo esc_url($phone_url); ?>"
            class="oit-single-location__row oit-single-location__row--phone self-start inline-flex items-center gap-3 pb-2 border-b-2 border-brand-red text-black no-underline font-grotesk font-medium text-[18px] leading-[1.4] hover:text-brand-red transition-colors">
            <?php echo $icon_phone; ?>
            <span data-proto-field="phone"><?php echo esc_html($phone_text); ?></span>
          </a>

          <a
            href="<?php echo esc_url($email_url); ?>"
            class="oit-single-location__row oit-single-location__row--email self-start inline-flex items-center gap-3 pb-2 border-b-2 border-brand-red text-black no-underline font-grotesk font-medium text-[18px] leading-[1.4] hover:text-brand-red transition-colors">
            <?php echo $icon_email; ?>
            <span data-proto-field="email"><?php echo esc_html($email_text); ?></span>
          </a>

        </div>

        <div
          data-proto-field="address"
          class="oit-single-location__row oit-single-location__row--address font-dm font-medium text-[18px] leading-[1.5] text-black [&_p]:m-0 [&_p+p]:mt-1">
          <?php echo wp_kses_post(wpautop($address)); ?>
        </div>

      </div>

      <div
        class="oit-single-location__map-wrap relative w-full aspect-[608/445] rounded-3xl overflow-clip shadow-red-glow bg-light-grey">
        <div
          class="oit-single-location__map absolute inset-0 w-full h-full"
          data-oit-map="1"
          data-oit-map-center="<?php echo esc_attr($map_center); ?>"
          data-oit-map-zoom="<?php echo esc_attr((string) $map_zoom); ?>"
          data-oit-map-theme="<?php echo esc_attr($map_theme); ?>"
          aria-label="Map showing office location"
          role="region"></div>
      </div>

    </div>

  </div>
</section>
