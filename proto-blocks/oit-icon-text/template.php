<?php
/**
 * OIT Icon + Text
 *
 * A single icon-with-text row, isolated from oit-single-location's
 * contact details. A brand-red icon (built-in phone / email / location
 * preset, or a custom uploaded image) sits left of a line of text, with
 * an optional brand-red bottom border. If the link field has a URL the
 * whole row is an <a> (tel:/mailto:/URL); otherwise it renders as a
 * static row.
 *
 * Built-in icons keep their hard-coded #D1001D fill (copied from
 * oit-single-location) so they stay brand red regardless of the row's
 * text color, which flips to red on hover when it's a link.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$item   = is_array($attributes['item'] ?? null) ? $attributes['item'] : [];
$icon   = is_array($attributes['icon'] ?? null) ? $attributes['icon'] : [];
$preset = (string) ($attributes['preset'] ?? 'phone');
$underline = !isset($attributes['underline']) || !empty($attributes['underline']);

$text   = !empty($item['text']) ? $item['text'] : '888.308.6689';
$url    = !empty($item['url'])  ? $item['url']  : '';
$target = !empty($item['target']) ? ' target="' . esc_attr($item['target']) . '"' : '';
$rel    = !empty($item['rel'])    ? ' rel="'    . esc_attr($item['rel'])    . '"' : '';

// Built-in brand-red icons (phone/email copied from oit-single-location;
// location is a standard pin). Fill is hard-coded #D1001D.
$icons = [
  'phone'    => '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[17px] shrink-0" viewBox="0 0 18 17" fill="none" aria-hidden="true"><path d="M12.5569 10.906L12.1019 11.359C12.1019 11.359 11.0189 12.435 8.06386 9.49698C5.10886 6.55898 6.19186 5.48298 6.19186 5.48298L6.47786 5.19698C7.18486 4.49498 7.25186 3.36698 6.63486 2.54298L5.37486 0.859979C4.61086 -0.160021 3.13586 -0.29502 2.26086 0.57498L0.690856 2.13498C0.257856 2.56698 -0.032144 3.12498 0.002856 3.74498C0.092856 5.33198 0.810856 8.74498 4.81486 12.727C9.06186 16.949 13.0469 17.117 14.6759 16.965C15.1919 16.917 15.6399 16.655 16.0009 16.295L17.4209 14.883C18.3809 13.93 18.1109 12.295 16.8829 11.628L14.9729 10.589C14.1669 10.152 13.1869 10.28 12.5569 10.906Z" fill="#D1001D"/></svg>',
  'email'    => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-4 shrink-0" viewBox="0 0 20 16" fill="none" aria-hidden="true"><path d="M18 0H2C0.9 0 0.00999999 0.9 0.00999999 2L0 14C0 15.1 0.9 16 2 16H18C19.1 16 20 15.1 20 14V2C20 0.9 19.1 0 18 0ZM18 4L10 9L2 4V2L10 7L18 2V4Z" fill="#D1001D"/></svg>',
  'location' => '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-5 shrink-0" viewBox="0 0 16 20" fill="#D1001D" aria-hidden="true"><path d="M8 0C3.582 0 0 3.582 0 8c0 5.5 8 12 8 12s8-6.5 8-12c0-4.418-3.582-8-8-8Zm0 10.8A2.8 2.8 0 1 1 8 5.2a2.8 2.8 0 0 1 0 5.6Z"/></svg>',
];

// Custom uploaded icon wins; otherwise use the chosen preset.
$icon_url = !empty($icon['url']) ? $icon['url'] : '';
if ($icon_url) {
  $icon_html = '<img src="' . esc_url($icon_url) . '" alt="' . esc_attr($icon['alt'] ?? '') . '" class="oit-icon-text__icon w-[18px] h-auto shrink-0" />';
} else {
  $icon_html = $icons[$preset] ?? '';
}

$row_class = 'oit-icon-text__row inline-flex items-center gap-2 font-dm font-medium text-[18px] leading-[1.5] text-black no-underline'
  . ($underline ? ' border-b-2 border-brand-red' : '')
  . ($url ? ' hover:text-brand-red transition-colors' : '');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-icon-text']);
$tag = $url ? 'a' : 'span';
?>

<div <?php echo $wrapper_attributes; ?>>
  <<?php echo $tag; ?> class="<?php echo esc_attr($row_class); ?>"<?php
    if ($url) { echo ' href="' . esc_url($url) . '"' . $target . $rel; }
  ?>>
    <?php echo $icon_html; ?>
    <span data-proto-field="item"><?php echo esc_html($text); ?></span>
  </<?php echo $tag; ?>>
</div>
