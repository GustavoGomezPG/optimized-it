<?php
/**
 * OIT Text + Image
 *
 * Two-column content row: a body column (heading + paragraph + optional
 * red eyebrow + bulleted list) paired with a supporting image. Image
 * position is controlled by the `imagePosition` select; the body column
 * grows (~2fr) while the image stays at a fixed-ish (~1fr) lane so
 * marketing copy gets the larger share.
 *
 * Bullets are rendered as a styled `<ul>`; empty bullets/labels are
 * skipped so authors can drop the section by leaving them blank.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$heading        = (string) ($attributes['heading'] ?? 'Let Us Email You a Copy');
$image          = $attributes['image'] ?? null;
$image_position = ($attributes['imagePosition'] ?? 'right') === 'left' ? 'left' : 'right';

$is_preview = !isset($block) || $block === null;
$wrapper_args = ['class' => 'oit-text-image'];
if (!$is_preview) {
  $wrapper_args['data-proto-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Grid template + visual order both swap based on imagePosition so the
// text column always takes the 70% lane regardless of which side the
// image sits on.
$grid_cols   = $image_position === 'right' ? 'md:grid-cols-[7fr_3fr]' : 'md:grid-cols-[3fr_7fr]';
$image_order = $image_position === 'right' ? 'md:order-2' : 'md:order-1';
$text_order  = $image_position === 'right' ? 'md:order-1' : 'md:order-2';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-text-image__inner max-w-[900px] mx-auto px-6 lg:px-0">

    <div class="oit-text-image__grid grid grid-cols-1 <?php echo esc_attr($grid_cols); ?> gap-8 lg:gap-12 items-center">

      <div class="oit-text-image__image-col <?php echo esc_attr($image_order); ?>">
        <?php if (!empty($image['url'])): ?>
        <img data-proto-field="image"
             src="<?php echo esc_url($image['url']); ?>"
             alt="<?php echo esc_attr($image['alt'] ?? ''); ?>"
             class="oit-text-image__image block w-full h-auto object-contain" />
        <?php else: ?>
        <div data-proto-field="image"
             class="oit-text-image__image aspect-square w-full rounded-md border border-dashed border-black/20 flex items-center justify-center text-body-sm text-black/40"
             aria-hidden="true">Image</div>
        <?php endif; ?>
      </div>

      <div class="oit-text-image__text-col <?php echo esc_attr($text_order); ?> flex flex-col gap-4">
        <h3 data-proto-field="heading"
            class="oit-text-image__heading m-0 font-grotesk font-bold text-[24px] lg:text-[30px] leading-[1.3] text-brand-black">
          <?php echo esc_html($heading); ?>
        </h3>

        <div data-proto-inner-blocks
             class="oit-text-image__body font-dm font-medium text-[16px] lg:text-[18px] leading-[1.5] text-brand-black">
          <?php echo $innerBlocksContent ?? ($content ?? ''); ?>
        </div>
      </div>

    </div>

  </div>
</section>
