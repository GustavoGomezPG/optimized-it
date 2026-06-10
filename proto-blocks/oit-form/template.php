<?php
/**
 * OIT Form
 *
 * Embeds a HubSpot form (by form GUID + portal id) inside the brand dark
 * callout box. HubSpot's embed renders into the `.hs-form-frame` div, which
 * we output identically in the editor and on the front end. The embed script
 * is loaded by the theme's HubSpot forms loader (inc/oit-hubspot-forms.php),
 * which runs in BOTH the front end and the editor canvas iframe via
 * `enqueue_block_assets` -- so the form is visible while editing too.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$form_id   = trim((string) ($attributes['formId'] ?? ''));
$portal_id = trim((string) ($attributes['portalId'] ?? '44833289'));
$region    = trim((string) ($attributes['region'] ?? 'na1'));
$gradient  = !empty($attributes['gradientBackground']);

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

    <div class="oit-form__card relative rounded-3xl overflow-clip <?php echo esc_attr($card_bg_class); ?> text-white"<?php echo $card_style ? ' style="' . esc_attr($card_style) . '"' : ''; ?>>

      <?php if ($form_id !== '' && $portal_id !== ''): ?>
        <div
          class="hs-form-frame"
          data-region="<?php echo esc_attr($region); ?>"
          data-form-id="<?php echo esc_attr($form_id); ?>"
          data-portal-id="<?php echo esc_attr($portal_id); ?>"></div>
      <?php else: ?>
        <p class="m-0 text-white/80 font-dm text-body-sm">
          <?php esc_html_e('Enter a HubSpot Form ID in the block inspector to render the form here.', 'optimizedit'); ?>
        </p>
      <?php endif; ?>

    </div>

  </div>
</section>
