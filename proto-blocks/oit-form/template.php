<?php
/**
 * OIT Form
 *
 * Renders a Gravity Forms form inside the brand dark callout box.
 * Configure the form ID + display flags via block controls. If Gravity
 * Forms is not active or the ID is missing/invalid, prints an editor-
 * facing placeholder so authors notice the misconfiguration.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$form_id          = (int) ($attributes['formId'] ?? 0);
$show_title       = !empty($attributes['showTitle']);
$show_description = !empty($attributes['showDescription']);
$ajax             = !isset($attributes['ajax']) || !empty($attributes['ajax']);
$gradient         = !empty($attributes['gradientBackground']);

// Off (default): solid near-black surface. On: the diagonal brand
// red-to-black gradient, applied inline since it's a bespoke multi-stop
// value rather than a Tailwind utility.
$card_bg_class = $gradient ? '' : 'bg-brand-black';
$card_style    = $gradient
  ? 'background-image:linear-gradient(142deg,#E00523 1.59%,#C8051F 7.58%,#B2051C 13.57%,#9D0418 19.56%,#890415 25.54%,#780412 31.53%,#670410 37.52%,#59040D 43.51%,#4C040B 49.5%,#400309 55.48%,#360308 61.47%,#2D0306 67.46%,#260305 73.45%,#210304 79.44%,#1D0304 85.43%,#1B0303 91.41%,#1A0303 97.4%);'
  : '';

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-form']);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-form__inner max-w-[900px] mx-auto">

    <div class="oit-form__card relative rounded-3xl <?php echo esc_attr($card_bg_class); ?> text-white p-6 lg:p-12"<?php echo $card_style ? ' style="' . esc_attr($card_style) . '"' : ''; ?>>

      <?php if ($form_id > 0 && function_exists('gravity_form')): ?>
        <?php
        gravity_form(
          $form_id,
          (bool) $show_title,
          (bool) $show_description,
          false,
          null,
          (bool) $ajax
        );
        ?>
      <?php elseif ($form_id > 0): ?>
        <p class="m-0 text-white/80 font-dm text-body-sm">
          <?php esc_html_e('Gravity Forms is not active. Activate the plugin to render form #', 'optimizedit'); ?><?php echo esc_html((string) $form_id); ?>.
        </p>
      <?php else: ?>
        <p class="m-0 text-white/80 font-dm text-body-sm">
          <?php esc_html_e('Pick a form in the block inspector to render it here.', 'optimizedit'); ?>
        </p>
      <?php endif; ?>

    </div>

  </div>
</section>
