<?php
/**
 * OIT Page Header
 *
 * Reusable section-page header card matching the Figma variants for
 * Solutions, Managed IT, Industries, Careers, etc. One block covers the
 * whole family via three toggles:
 *
 *   isLight       -> white card + dark text (vs. default black card + white)
 *   showCircuit   -> render the circuit-board Lottie behind/right of the card
 *   showIconBadge -> render a 160x160 red-gradient icon badge instead
 *                    (badge wins over circuit when both are on, matching
 *                    the Managed IT variant where the icon replaces the
 *                    circuit graphic)
 *
 * The giant uppercase wordmark behind the card sits at the section level
 * (not inside the card) so it spills below the card edge -- the visual
 * signature of every section header in the Figma file.
 *
 * Decoration / Lottie loading mirrors oit-hero: a static SVG fallback is
 * rendered server-side, and view.js swaps it for the Lottie player on the
 * front end. Editor preview, no-JS, and prefers-reduced-motion all keep
 * the static SVG.
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

// Proto-Blocks initializes declared fields/controls to '' in the editor
// so inline edit hooks have something to bind to. ?? only handles null,
// so we use empty() to treat '' and unset the same and fall back to
// placeholder copy. The wysiwyg fields keep their <p>/<br> markup, so
// title and subtitle are rendered with wp_kses_post.
$title    = !empty($attributes['title'])    ? $attributes['title']    : '<p>Section title<span class="oit-page-header__accent text-brand-red">.</span></p>';
$subtitle = !empty($attributes['subtitle']) ? $attributes['subtitle'] : 'Short supporting line that explains what this page covers.';
$wordmark = !empty($attributes['wordmark']) ? $attributes['wordmark'] : '';
$icon     = $attributes['icon'] ?? null;

$is_light       = !empty($attributes['isLight']);
$show_circuit   = $attributes['showCircuit']   ?? true;
$show_icon      = !empty($attributes['showIconBadge']);
$invert_icon    = !empty($attributes['invertIcon']);
$highlight_word = trim((string) ($attributes['highlightWord'] ?? ''));

// Two independent toggle-gated buttons. Each lives in its own link
// field (text + url + target + rel) and is paired with a per-button
// "solid red" style toggle: on = filled red pill, off = outlined pill.
// Both buttons default off so a freshly-inserted block has no CTAs.
$show_button1  = !empty($attributes['showButton1']);
$show_button2  = !empty($attributes['showButton2']);
$button1_solid = $attributes['button1Solid'] ?? true;
$button2_solid = $attributes['button2Solid'] ?? false;
$button1       = is_array($attributes['button1'] ?? null) ? $attributes['button1'] : [];
$button2       = is_array($attributes['button2'] ?? null) ? $attributes['button2'] : [];
$show_buttons  = $show_button1 || $show_button2;

// Highlight pass -- when the editor sets a highlightWord control, find
// the first case-insensitive occurrence in the title and wrap it in a
// brand-red span. Operates on the raw title string (rather than a DOM
// walker) for speed; case is preserved by substring-extracting from the
// original. The wrapping span carries .oit-page-header__accent so the
// period auto-wrap walker in view.js knows to skip already-wrapped
// content if the highlight happens to end with a "." too.
if ($highlight_word !== '') {
  $pos = stripos($title, $highlight_word);
  if ($pos !== false) {
    $matched = substr($title, $pos, strlen($highlight_word));
    $title   = substr($title, 0, $pos)
      . '<span class="oit-page-header__accent text-brand-red">' . $matched . '</span>'
      . substr($title, $pos + strlen($highlight_word));
  }
}

// Breadcrumb rendering and the giant wordmark live in
// inc/oit-header-partials.php so the new oit-two-col-header block can
// share them. The partials emit the same markup and class names this
// block has always produced, so style.css and view.js below keep
// working without any selector changes.
//
// In the editor preview $block is null. The data-animate="pending" pre-
// state would leave everything invisible there because view.js doesn't
// run; only apply it on the front end.
$is_preview = !isset($block) || $block === null;

// Theme-driven swap: light = white card / black text; dark = black
// card / white text. The breadcrumb partial inherits text color from
// the card surface via the cascade, so it picks up the right idle
// color automatically.
$card_classes      = $is_light ? 'bg-white text-black' : 'bg-black text-white';

// Button visuals.
//   solid:    filled cta-red pill, white text, darker red on hover.
//   outlined: transparent pill with a red border. Text color follows
//             the card surface (black on light theme, white on dark)
//             so it always reads against the underlying card.
$cta_base       = 'oit-page-header__cta inline-flex items-center gap-3 px-5 py-2.5 rounded-full font-grotesk font-medium text-body-sm leading-[1.3] uppercase whitespace-nowrap no-underline transition-colors';
$cta_solid      = 'bg-cta-red hover:bg-cta-red-700 text-white border border-brand-red';
$cta_outlined   = $is_light
  ? 'bg-transparent text-black border-2 border-brand-red hover:bg-brand-red hover:text-white'
  : 'bg-transparent text-white border-2 border-brand-red hover:bg-brand-red';

$circuit_url        = get_stylesheet_directory_uri() . '/assets/img/circuit.svg';
$circuit_lottie_url = get_stylesheet_directory_uri() . '/assets/lottie/circuit.json';

$wrapper_args = ['class' => 'oit-page-header relative'];
if (!$is_preview) {
  $wrapper_args['data-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

// Double-chevron used as the trailing arrow on CTA buttons. (The
// breadcrumb's chevron lives in inc/oit-header-partials.php.) Fill is
// currentColor so it inherits the button's text color.
$chevron = '<svg class="w-[13px] h-3 shrink-0" viewBox="0 0 13 12" fill="currentColor" aria-hidden="true"><path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-page-header__inner relative max-w-[1440px] mx-auto px-6 lg:px-20 pt-10 pb-0">

    <article class="oit-page-header__card relative z-10 <?php echo esc_attr($card_classes); ?> rounded-3xl <?php echo $show_icon ? '' : 'overflow-clip'; ?> shadow-red-glow">
      <div class="oit-page-header__pad relative p-6 lg:p-10 lg:pr-[260px] min-h-[234px]">

        <div class="oit-page-header__content relative z-10 flex flex-col gap-5 lg:gap-6 max-w-[900px]">

          <?php oit_render_breadcrumb(); ?>

          <div
            data-proto-field="title"
            class="oit-page-header__title m-0 font-grotesk font-bold text-[32px] leading-[1.2] lg:text-[56px] [&_p]:m-0 break-words">
            <?php echo wp_kses_post($title); ?>
          </div>

          <div
            data-proto-field="subtitle"
            class="oit-page-header__subtitle m-0 font-dm font-medium text-body-sm lg:text-body-md leading-[1.5] max-w-[820px] [&_p]:m-0 [&_p+p]:mt-1">
            <?php echo wp_kses_post(wpautop($subtitle)); ?>
          </div>

          <?php if ($show_buttons): ?>
          <div class="oit-page-header__ctas flex flex-wrap gap-4 mt-2">

            <?php if ($show_button1):
              $b1_skin   = $button1_solid ? $cta_solid : $cta_outlined;
              $b1_url    = !empty($button1['url']) ? $button1['url'] : '#';
              $b1_text   = !empty($button1['text']) ? $button1['text'] : 'Button one';
              $b1_target = !empty($button1['target']) ? ' target="' . esc_attr($button1['target']) . '"' : '';
              $b1_rel    = !empty($button1['rel'])    ? ' rel="'    . esc_attr($button1['rel'])    . '"' : '';
            ?>
            <a
              href="<?php echo esc_url($b1_url); ?>"<?php echo $b1_target; echo $b1_rel; ?>
              class="<?php echo esc_attr($cta_base . ' ' . $b1_skin); ?>">
              <span data-proto-field="button1"><?php echo esc_html($b1_text); ?></span>
              <?php echo $chevron; ?>
            </a>
            <?php endif; ?>

            <?php if ($show_button2):
              $b2_skin   = $button2_solid ? $cta_solid : $cta_outlined;
              $b2_url    = !empty($button2['url']) ? $button2['url'] : '#';
              $b2_text   = !empty($button2['text']) ? $button2['text'] : 'Button two';
              $b2_target = !empty($button2['target']) ? ' target="' . esc_attr($button2['target']) . '"' : '';
              $b2_rel    = !empty($button2['rel'])    ? ' rel="'    . esc_attr($button2['rel'])    . '"' : '';
            ?>
            <a
              href="<?php echo esc_url($b2_url); ?>"<?php echo $b2_target; echo $b2_rel; ?>
              class="<?php echo esc_attr($cta_base . ' ' . $b2_skin); ?>">
              <span data-proto-field="button2"><?php echo esc_html($b2_text); ?></span>
              <?php echo $chevron; ?>
            </a>
            <?php endif; ?>

          </div>
          <?php endif; ?>

        </div>

        <?php if ($show_icon): ?>
        <div class="oit-page-header__icon-badge oit-page-header__icon-badge--gradient hidden lg:flex absolute right-0 top-1/2 translate-x-[10%] -translate-y-1/2 w-[160px] h-[160px] rounded-3xl overflow-clip items-center justify-center shadow-red-glow z-10">
          <?php if ($icon && !empty($icon['url'])): ?>
          <img
            data-proto-field="icon"
            src="<?php echo esc_url($icon['url']); ?>"
            alt="<?php echo esc_attr($icon['alt'] ?? ''); ?>"
            class="oit-page-header__icon w-[82px] h-[91px] object-contain<?php echo $invert_icon ? ' oit-page-header__icon--invert' : ''; ?>" />
          <?php else: ?>
          <div data-proto-field="icon" class="oit-page-header__icon w-[82px] h-[91px] flex items-center justify-center text-white/70 text-center text-body-xs font-grotesk px-2">
            <span>Pick icon</span>
          </div>
          <?php endif; ?>
        </div>
        <?php elseif ($show_circuit): ?>
        <?php
          // Light theme variant: shifted further right (so more of the
          // graphic is clipped by the card's overflow-clip) and a
          // brightness(0) CSS filter turns the red lines into a dark
          // silhouette that reads well on the white card. Dark theme
          // keeps the original red-on-black positioning.
          $deco_position_classes = $is_light
            ? 'right-[-14%] w-[700px] max-w-[65%] opacity-10'
            : 'right-0 w-[614px] max-w-[55%] opacity-60';
          $deco_variant_class    = $is_light ? 'oit-page-header__decoration--light' : '';
        ?>
        <div
          class="oit-page-header__decoration <?php echo esc_attr($deco_variant_class); ?> hidden lg:block absolute top-0 <?php echo esc_attr($deco_position_classes); ?> aspect-[614/518] pointer-events-none z-0 select-none"
          data-lottie-url="<?php echo esc_url($circuit_lottie_url); ?>"
          aria-hidden="true">
          <img
            src="<?php echo esc_url($circuit_url); ?>"
            alt=""
            class="block w-full h-full object-contain"
            loading="lazy" />
        </div>
        <?php endif; ?>

      </div>
    </article>

    <?php oit_render_wordmark($wordmark); ?>

  </div>
</section>
