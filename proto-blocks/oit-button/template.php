<?php
/**
 * OIT Button
 *
 * Standalone brand pill button -- a thin block around the global
 * `.oit-btn` utility (defined in style.css). The link text + URL come
 * from a single `link` field; the style / size / chevron / alignment
 * come from controls and map onto the existing `.oit-btn--*` modifiers,
 * so this button is visually identical to the inline CTAs used across
 * the theme (oit-cta, oit-featured-blog, etc.).
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$button = is_array($attributes['button'] ?? null) ? $attributes['button'] : [];
$url    = !empty($button['url'])  ? $button['url']  : '#';
$text   = !empty($button['text']) ? $button['text'] : 'Schedule a consultation';
$target = !empty($button['target']) ? ' target="' . esc_attr($button['target']) . '"' : '';
$rel    = !empty($button['rel'])    ? ' rel="'    . esc_attr($button['rel'])    . '"' : '';

$style     = (string) ($attributes['variant'] ?? 'solid');
$size      = (string) ($attributes['size'] ?? 'default');
$chevron   = !isset($attributes['showChevron']) || !empty($attributes['showChevron']);
$align     = (string) ($attributes['alignment'] ?? 'left');
$text_color = (string) ($attributes['textColor'] ?? 'default');

// Map controls onto the existing .oit-btn modifier classes. The chevron
// uses currentColor, so the text-color modifier recolors it too.
$style_class = ['solid' => '', 'white' => 'oit-btn--white', 'ghost' => 'oit-btn--ghost'][$style] ?? '';
$text_class  = ['black' => 'oit-btn--text-black', 'red' => 'oit-btn--text-red'][$text_color] ?? '';
$btn_classes = array_filter([
  'oit-btn',
  $style_class,
  $text_class,
  $size === 'small' ? 'oit-btn--sm' : '',
  $chevron ? 'oit-btn--chevron' : '',
]);

$align_class = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'][$align] ?? 'text-left';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-button ' . $align_class]);
?>

<div <?php echo $wrapper_attributes; ?>>
  <a href="<?php echo esc_url($url); ?>"<?php echo $target; echo $rel; ?>
     class="<?php echo esc_attr(implode(' ', $btn_classes)); ?>">
    <span data-proto-field="button"><?php echo esc_html($text); ?></span>
  </a>
</div>
