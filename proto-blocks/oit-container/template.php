<?php
/**
 * OIT Container
 *
 * Layout wrapper around nested blocks.
 *
 * Most of the chrome is handled by WordPress core via `supports`:
 *
 *   - supports.spacing.padding/margin -> Dimensions panel with side
 *     (T/R/B/L) controls plus the linked-pad toggle. WP writes the
 *     values as inline `style="padding-top:…; margin-…"`.
 *   - supports.color.background + .gradients -> Color panel with both
 *     the solid swatch picker and a gradient picker (direction + 2
 *     stops). Output again as inline style by WP.
 *
 * The only custom controls left are the optional per-breakpoint
 * padding top/bottom overrides. When `useResponsivePadding` is on we
 * emit four CSS custom properties on the section and tag it with the
 * `--responsive` modifier class; style.css's media queries then apply
 * those variables with !important so they beat WP's native inline
 * padding at the relevant breakpoints.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$use_responsive = !empty($attributes['useResponsivePadding']);
$pt_tablet      = (int) ($attributes['paddingTopTablet']     ?? 64);
$pb_tablet      = (int) ($attributes['paddingBottomTablet']  ?? 64);
$pt_desktop     = (int) ($attributes['paddingTopDesktop']    ?? 80);
$pb_desktop     = (int) ($attributes['paddingBottomDesktop'] ?? 80);

// Only emit the variables when the toggle is on, otherwise the media
// queries in style.css would treat the unset vars as `initial` (0) and
// stomp on the native padding the editor has set.
$style = '';
if ($use_responsive) {
  $style = implode('', [
    '--oit-pt-tablet:'  . $pt_tablet  . 'px;',
    '--oit-pb-tablet:'  . $pb_tablet  . 'px;',
    '--oit-pt-desktop:' . $pt_desktop . 'px;',
    '--oit-pb-desktop:' . $pb_desktop . 'px;',
  ]);
}

$wrapper_class = 'oit-container' . ($use_responsive ? ' oit-container--responsive' : '');

$wrapper_args = ['class' => $wrapper_class];
if ($style !== '') {
  $wrapper_args['style'] = $style;
}

$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-container__inner" data-proto-inner-blocks>
    <?php
      // Proto-Blocks pipes WP's $content into $attributes['innerBlocksContent']
      // in Engine.php and the template's extract() gives us back
      // $innerBlocksContent -- NOT $content. Both fallbacks are
      // null-coalesced so a freshly inserted / empty instance can't
      // throw "Undefined variable" warnings.
      echo $innerBlocksContent ?? ($content ?? '');
    ?>
  </div>
</section>
