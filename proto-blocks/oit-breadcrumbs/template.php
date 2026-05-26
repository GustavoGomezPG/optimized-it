<?php
/**
 * OIT Breadcrumbs
 *
 * Standalone breadcrumb trail, isolated from the header blocks. Delegates
 * to the shared oit_render_breadcrumb() helper (inc/oit-header-partials.php)
 * so the markup + styling stay identical to oit-page-header /
 * oit-two-col-header: Home > ancestors > current, current crumb in brand
 * red, links inheriting the surrounding text color via the cascade.
 *
 * Width is left to the surrounding layout by default; flip the
 * "Constrain to container width" control to cap it at the 1280px content
 * width when the block is dropped on its own (not inside a container).
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$constrain   = !empty($attributes['constrainWidth']);
$inner_class = 'oit-breadcrumbs__inner' . ($constrain ? ' mx-auto max-w-[1440px] px-6 lg:px-20' : '');

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'oit-breadcrumbs']);
?>

<div <?php echo $wrapper_attributes; ?>>
  <div class="<?php echo esc_attr($inner_class); ?>">
    <?php
    if (function_exists('oit_render_breadcrumb')) {
      oit_render_breadcrumb();
    }
    ?>
  </div>
</div>
