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

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-form']);
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-form__inner max-w-[900px] mx-auto px-6 lg:px-0">

    <div class="oit-form__card relative rounded-3xl bg-brand-black text-white p-6 lg:p-12">

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
          <?php esc_html_e('Set a Gravity Forms ID in the block inspector to render a form here.', 'optimizedit'); ?>
        </p>
      <?php endif; ?>

    </div>

  </div>
</section>
