<?php
/**
 * OIT Main Navigation
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$logo         = $attributes['logo'] ?? [];
$cta_button   = $attributes['ctaButton'] ?? ['url' => '#', 'text' => 'SCHEDULE A CONSULTATION'];
$phone_number = $attributes['phoneNumber'] ?? '';
$support_text = trim((string) ($attributes['supportText'] ?? 'Get Support:'));

$social_links = array_values(array_filter([
    ['platform' => 'linkedin',  'url' => $attributes['linkedinUrl']  ?? ''],
    ['platform' => 'facebook',  'url' => $attributes['facebookUrl']  ?? ''],
    ['platform' => 'youtube',   'url' => $attributes['youtubeUrl']   ?? ''],
    ['platform' => 'instagram', 'url' => $attributes['instagramUrl'] ?? ''],
    ['platform' => 'x',         'url' => $attributes['xUrl']         ?? ''],
], fn($s) => !empty($s['url'])));

$menu_location = $attributes['menuLocation'] ?? 'primary';
$show_cta = $attributes['showCta'] ?? true;
$show_view_all = $attributes['showViewAll'] ?? false;
$show_phone = $attributes['showPhone'] ?? true;
$show_social = $attributes['showSocial'] ?? true;
$sticky = !empty($attributes['sticky']);

$show_phone_bar  = $attributes['showPhoneBar'] ?? true;
$phone_bar_theme = in_array(($attributes['phoneBarTheme'] ?? 'auto'), ['auto', 'light', 'dark'], true)
  ? ($attributes['phoneBarTheme'] ?? 'auto')
  : 'auto';
$is_preview = !isset($block) || $block === null;

// Phone-bar color treatment. Auto = white text on the homepage (the dark
// hero) and dark text on standard white pages. The header is ONE global
// template part shared by every page, so the home/standard difference can't
// be a per-instance setting -- it's resolved per request via is_front_page().
if ($phone_bar_theme === 'light') {
  $phone_bar_light = true;
} elseif ($phone_bar_theme === 'dark') {
  $phone_bar_light = false;
} else {
  $phone_bar_light = (!$is_preview && function_exists('is_front_page') && is_front_page());
}

$show_social_bar = $attributes['showSocialBar'] ?? true;

$phone_tel          = preg_replace('/[^0-9+]/', '', (string) $phone_number);
$show_phone_in_bar  = $show_phone_bar && trim((string) $phone_number) !== '';
$show_social_in_bar = $show_social_bar && !empty($social_links);
// The desktop top bar carries the "Get Support" phone link only; social icons
// live in the mobile menu, not up here.
$bar_visible        = $show_phone_in_bar;
$bar_text = $phone_bar_light ? 'text-white' : 'text-black';

$nav_id = 'oit-nav-' . wp_unique_id();

$menu_tree = [];
$locations = get_nav_menu_locations();
$menu_id = $locations[$menu_location] ?? null;
if ($menu_id) {
  $items = wp_get_nav_menu_items($menu_id);
  if ($items) {
    foreach ($items as $item) {
      if ((int) $item->menu_item_parent === 0) {
        $menu_tree[$item->ID] = ['item' => $item, 'children' => []];
      }
    }
    foreach ($items as $item) {
      $parent = (int) $item->menu_item_parent;
      if ($parent !== 0 && isset($menu_tree[$parent])) {
        $menu_tree[$parent]['children'][] = $item;
      }
    }
  }
}

if (empty($menu_tree)) {
  $fallback = [
    [
      'title' => 'SOLUTIONS',
      'url' => '#',
      'children' => [
        ['title' => 'Managed IT', 'url' => '#'],
        ['title' => 'Co-Managed IT', 'url' => '#'],
        ['title' => 'Cybersecurity', 'url' => '#'],
        ['title' => 'Cloud & Modern Workplace', 'url' => '#'],
        ['title' => 'Strategic IT/vCIO', 'url' => '#'],
        ['title' => 'Data Protection', 'url' => '#'],
      ]
    ],
    ['title' => 'INDUSTRIES', 'url' => '#', 'children' => []],
    ['title' => 'ABOUT', 'url' => '#', 'children' => []],
    ['title' => 'LOCATIONS', 'url' => '#', 'children' => []],
    ['title' => 'RESOURCES', 'url' => '#', 'children' => []],
  ];
  foreach ($fallback as $fb) {
    $kids = [];
    foreach ($fb['children'] as $c) {
      $kids[] = (object) $c;
    }
    $menu_tree[] = [
      'item' => (object) ['title' => $fb['title'], 'url' => $fb['url']],
      'children' => $kids,
    ];
  }
}

// Extra top room on desktop so the phone bar has somewhere to sit above the
// pill; mobile keeps the tighter top padding since the bar is desktop-only.
$top_pad = $bar_visible ? 'pt-4 lg:pt-7' : 'pt-4';
$wrapper_class = ($sticky ? 'sticky top-0' : 'relative') . ' z-50 w-full px-4 pb-4 ' . $top_pad;
$wrapper_attributes = get_block_wrapper_attributes(['class' => $wrapper_class]);

$chevron_down = '<svg class="oit-chevron w-[14px] h-[9px] transition-transform" viewBox="0 0 18 11" fill="none" aria-hidden="true"><path d="M1 1L9 9L17 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$chevron_right = '<svg class="w-[8px] h-[14px]" viewBox="0 0 11 18" fill="none" aria-hidden="true"><path d="M1 1L9 9L1 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$cta_arrow = '<svg class="w-[10px] h-[10px]" viewBox="0 0 12 13" fill="none" aria-hidden="true"><path d="M1 6.5H11M11 6.5L6 1.5M11 6.5L6 11.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// Headset (support) icon, shared by the desktop phone bar and the mobile menu.
// Communicates "phone" + "a person is here to help". Color is set by the parent
// (fill="currentColor"), so each context tints it.
$phone_icon = '<svg class="w-[18px] h-[17px]" viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M256 48C141.1 48 48 141.1 48 256v40c0 13.3-10.7 24-24 24S0 309.3 0 296V256C0 114.6 114.6 0 256 0S512 114.6 512 256V400.1c0 48.6-39.4 88-88.1 88L313.6 488c-8.3 14.3-23.8 24-41.6 24H240c-26.5 0-48-21.5-48-48s21.5-48 48-48h32c17.8 0 33.3 9.7 41.6 24l110.4 .1c22.1 0 40-17.9 40-40V256c0-114.9-93.1-208-208-208zM144 208h16c17.7 0 32 14.3 32 32V352c0 17.7-14.3 32-32 32H144c-35.3 0-64-28.7-64-64V272c0-35.3 28.7-64 64-64zm224 0c35.3 0 64 28.7 64 64v48c0 35.3-28.7 64-64 64H352c-17.7 0-32-14.3-32-32V240c0-17.7 14.3-32 32-32h16z" /></svg>';

// Social icon SVGs are provided by the theme-global helper oit_social_icon()
// declared in functions.php (also includes Instagram, shared with the footer).

// Build target/rel attributes from a WP menu item, honoring the menu editor's
// "Open link in a new tab" setting ($item->target) and link relationship ($item->xfn).
// Fallback (synthetic) items have no target/xfn, so the isset() guards no-op safely.
$nav_link_attrs = function ($item) {
  $out    = '';
  $target = isset($item->target) ? trim($item->target) : '';
  $rel    = isset($item->xfn) ? trim($item->xfn) : '';
  if ($target !== '') {
    $out .= ' target="' . esc_attr($target) . '"';
    if ($target === '_blank' && strpos($rel, 'noopener') === false) {
      $rel = trim($rel . ' noopener');
    }
  }
  if ($rel !== '') {
    $out .= ' rel="' . esc_attr($rel) . '"';
  }
  return $out;
};
?>

<div <?php echo $wrapper_attributes; ?>>

  <?php if ($bar_visible): ?>
  <div class="oit-nav__topbar hidden lg:flex items-center justify-end gap-6 max-w-[1360px] mx-auto px-6 py-1 mb-3">

    <?php if ($show_phone_in_bar): ?>
    <a href="tel:<?php echo esc_attr($phone_tel); ?>"
      class="oit-nav__topbar-phone no-underline inline-flex items-center gap-2 font-grotesk font-medium text-[15px] leading-[1.3] transition-colors hover:text-cta-red <?php echo $bar_text; ?>">
      <span class="oit-nav__topbar-icon text-brand-red shrink-0"><?php echo $phone_icon; ?></span>
      <span><?php echo esc_html(trim($support_text . ' ' . trim((string) $phone_number))); ?></span>
    </a>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <div
    class="oit-nav__shell bg-black text-white rounded-[24px] max-w-[1360px] mx-auto shadow-red-glow">
    <nav id="<?php echo esc_attr($nav_id); ?>" class="oit-nav flex items-center justify-between gap-6 px-6 py-4 lg:py-5"
      aria-label="Main navigation">

      <a href="<?php echo esc_url(home_url('/')); ?>" class="oit-nav__logo no-underline flex-shrink-0"
        aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
        <?php if (!empty($logo['url'])): ?>
        <img data-proto-field="logo" src="<?php echo esc_url($logo['url']); ?>"
          alt="<?php echo esc_attr($logo['alt'] ?? get_bloginfo('name')); ?>" class="h-[40px] lg:h-[58px] w-auto" />
        <?php else: ?>
        <img data-proto-field="logo" src="" alt="Logo" class="h-[40px] lg:h-[58px] w-auto" />
        <?php endif; ?>
      </a>

      <ul class="oit-nav__menu hidden lg:flex items-center gap-6 ml-auto">
        <?php foreach ($menu_tree as $entry):
          $item = $entry['item'];
          $children = $entry['children'];
          $has_children = !empty($children);
          ?>
        <li class="oit-nav__item group/item relative<?php echo $has_children ? ' has-submenu' : ''; ?>">
          <a href="<?php echo esc_url($item->url ?? '#'); ?>"<?php echo $nav_link_attrs($item); ?>
            class="oit-nav__link group/link no-underline flex items-center gap-2 text-white font-grotesk font-medium text-[16px] leading-[1.3] uppercase hover:text-cta-red transition-colors py-2"
            <?php echo $has_children ? 'aria-haspopup="true" aria-expanded="false"' : ''; ?>>
            <span><?php echo esc_html($item->title); ?></span>
            <?php if ($has_children): ?>
              <svg class="w-[14px] h-[9px] transition-transform group-aria-expanded/link:rotate-180" viewBox="0 0 18 11" fill="none" aria-hidden="true"><path d="M1 1L9 9L17 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php endif; ?>
          </a>
          <?php if ($has_children): ?>
          <div class="oit-nav__submenu-wrap absolute top-full left-0 pt-2 hidden group-hover/item:block focus-within:block">
            <div
              class="oit-nav__submenu bg-black rounded-[16px] py-5 px-6 flex flex-col gap-4 min-w-[220px] shadow-[0_38px_42px_-10px_rgba(224,5,35,0.15)]">
              <?php foreach ($children as $child): ?>
              <a href="<?php echo esc_url($child->url ?? '#'); ?>"<?php echo $nav_link_attrs($child); ?>
                class="oit-nav__sublink relative self-start no-underline text-white font-dm font-medium text-[16px] leading-[1.5] whitespace-nowrap">
                <?php echo esc_html($child->title); ?>
              </a>
              <?php endforeach; ?>
              <?php if ($show_view_all): ?>
              <a href="<?php echo esc_url($item->url ?? '#'); ?>"
                class="oit-nav__view-all no-underline mt-1 inline-flex items-center gap-2 text-white font-dm font-medium text-[16px] leading-[1.5] border-b-2 border-brand-red pb-1 self-start hover:text-cta-red transition-colors">
                <span>View All</span>
                <?php echo $chevron_right; ?>
              </a>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </li>
        <?php endforeach; ?>
      </ul>

      <div class="flex items-center gap-4 ml-auto lg:ml-0">
        <?php if ($show_cta): ?>
        <a href="<?php echo esc_url($cta_button['url'] ?? '#'); ?>"
          <?php echo !empty($cta_button['target']) ? 'target="' . esc_attr($cta_button['target']) . '"' : ''; ?>
          <?php echo !empty($cta_button['rel']) ? 'rel="' . esc_attr($cta_button['rel']) . '"' : ''; ?>
          class="oit-nav__cta no-underline hidden sm:inline-flex items-center gap-3 bg-cta-red hover:bg-cta-red-700 text-white font-grotesk font-medium text-[16px] leading-[1.3] uppercase px-5 py-2.5 rounded-full transition-colors whitespace-nowrap"
          data-proto-field="ctaButton"><?php echo esc_html($cta_button['text'] ?? 'SCHEDULE A CONSULTATION'); ?></a>
        <?php endif; ?>

        <button type="button" class="oit-nav__toggle group lg:hidden flex items-center gap-2.5 text-white"
          aria-label="Toggle menu" aria-expanded="false" aria-controls="<?php echo esc_attr($nav_id); ?>-panel">
          <span class="font-grotesk font-medium text-[16px] leading-[1.3] uppercase group-aria-expanded:hidden">MENU</span>
          <span class="font-grotesk font-medium text-[16px] leading-[1.3] uppercase hidden group-aria-expanded:inline">CLOSE</span>
          <svg class="w-[18px] h-[12px] group-aria-expanded:hidden" viewBox="0 0 18 12" fill="none" aria-hidden="true">
            <path d="M1 1H17M1 6H17M1 11H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
          <svg class="w-[13px] h-[13px] hidden group-aria-expanded:inline-block" viewBox="0 0 13 13" fill="none" aria-hidden="true">
            <path d="M1 1L12 12M12 1L1 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
      </div>
    </nav>

    <div id="<?php echo esc_attr($nav_id); ?>-panel" class="oit-nav__panel lg:hidden grid grid-rows-[0fr] aria-[hidden=false]:grid-rows-[1fr] transition-[grid-template-rows] duration-300 ease-out" aria-hidden="true">
      <div class="oit-nav__panel-inner min-h-0 overflow-hidden">
        <div class="oit-nav__panel-card px-6 pb-6 pt-16 flex flex-col gap-5">
          <ul class="oit-nav__panel-list flex flex-col gap-8 list-none m-0 p-0">
            <?php foreach ($menu_tree as $entry):
              $item = $entry['item'];
              $children = $entry['children'];
              $has_children = !empty($children);
              $sub_id = $nav_id . '-sub-' . (isset($item->ID) ? $item->ID : md5(($item->title ?? '') . ($item->url ?? '')));
              ?>
            <li class="oit-nav__panel-item">
              <?php if ($has_children): ?>
              <button type="button"
                class="oit-nav__panel-trigger group w-full flex items-center justify-between text-white font-grotesk font-medium text-[16px] leading-[1.3] uppercase bg-transparent border-0 p-0 cursor-pointer"
                aria-expanded="false" aria-controls="<?php echo esc_attr($sub_id); ?>">
                <span><?php echo esc_html($item->title); ?></span>
                <svg class="w-[8px] h-[14px] transition-transform group-aria-expanded:rotate-90" viewBox="0 0 11 18" fill="none" aria-hidden="true"><path d="M1 1L9 9L1 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
              <div id="<?php echo esc_attr($sub_id); ?>" class="oit-nav__panel-sub grid grid-rows-[0fr] aria-[hidden=false]:grid-rows-[1fr] transition-[grid-template-rows] duration-300 ease-out" aria-hidden="true">
                <div class="oit-nav__panel-sub-inner min-h-0 overflow-hidden">
                  <ul
                    class="oit-nav__panel-sub-list bg-[#0F0202] rounded-[12px] mt-3 px-4 py-8 flex flex-col gap-3 list-none m-0">
                    <?php foreach ($children as $child): ?>
                    <li>
                      <a href="<?php echo esc_url($child->url ?? '#'); ?>"<?php echo $nav_link_attrs($child); ?>
                        class="oit-nav__panel-sublink relative self-start no-underline py-2 inline-block text-white font-dm font-medium text-[14px] leading-[1.5]">
                        <?php echo esc_html($child->title); ?>
                      </a>
                    </li>
                    <?php endforeach; ?>
                    <?php if ($show_view_all): ?>
                    <li>
                      <a href="<?php echo esc_url($item->url ?? '#'); ?>"
                        class="oit-nav__panel-sublink oit-nav__panel-view-all no-underline inline-flex items-center gap-2 text-white font-dm font-medium text-[14px] leading-[1.5] border-b-2 border-brand-red pb-1 self-start mt-1">
                        <span>View All</span>
                        <?php echo $chevron_right; ?>
                      </a>
                    </li>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
              <?php else: ?>
              <a href="<?php echo esc_url($item->url ?? '#'); ?>"<?php echo $nav_link_attrs($item); ?>
                class="oit-nav__panel-link no-underline flex items-center text-white font-grotesk font-medium text-[16px] leading-[1.3] uppercase">
                <span><?php echo esc_html($item->title); ?></span>
              </a>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>

          <?php if ($show_cta): ?>
          <a href="<?php echo esc_url($cta_button['url'] ?? '#'); ?>"
            <?php echo !empty($cta_button['target']) ? 'target="' . esc_attr($cta_button['target']) . '"' : ''; ?>
            class="oit-nav__cta no-underline self-start inline-flex items-center gap-3 bg-cta-red hover:bg-cta-red-700 text-white font-grotesk font-medium text-[16px] leading-[1.3] uppercase px-5 py-2.5 rounded-full transition-colors"><?php echo esc_html($cta_button['text'] ?? 'SCHEDULE A CONSULTATION'); ?></a>
          <?php endif; ?>

          <?php if (($show_phone && !empty($phone_number)) || ($show_social && !empty($social_links))): ?>
          <div class="flex flex-col gap-4 pt-2">
            <?php if ($show_phone && !empty($phone_number)): ?>
            <a href="tel:<?php echo esc_attr($phone_tel); ?>"
              class="oit-nav__panel-phone no-underline flex items-center gap-2 text-white font-dm font-medium text-[14px]">
              <span class="text-brand-red shrink-0"><?php echo $phone_icon; ?></span>
              <span><?php echo esc_html($phone_number); ?></span>
            </a>
            <?php endif; ?>

            <?php if ($show_social && !empty($social_links)): ?>
            <div class="flex items-center gap-3">
              <?php foreach ($social_links as $social):
                    $platform = $social['platform'] ?? '';
                    $url = $social['url'] ?? '#';
                    ?>
              <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"
                class="no-underline text-white hover:text-cta-red transition-colors"
                aria-label="<?php echo esc_attr(ucfirst($platform)); ?>">
                <?php echo oit_social_icon($platform); ?>
              </a>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>