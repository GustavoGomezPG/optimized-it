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

$wrapper_class = ($sticky ? 'sticky top-0' : 'relative') . ' z-50 w-full px-4 py-4';
$wrapper_attributes = get_block_wrapper_attributes(['class' => $wrapper_class]);

$chevron_down = '<svg class="oit-chevron w-[14px] h-[9px] transition-transform" viewBox="0 0 18 11" fill="none" aria-hidden="true"><path d="M1 1L9 9L17 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$chevron_right = '<svg class="w-[8px] h-[14px]" viewBox="0 0 11 18" fill="none" aria-hidden="true"><path d="M1 1L9 9L1 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$cta_arrow = '<svg class="w-[10px] h-[10px]" viewBox="0 0 12 13" fill="none" aria-hidden="true"><path d="M1 6.5H11M11 6.5L6 1.5M11 6.5L6 11.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

// Social icon SVGs are provided by the theme-global helper oit_social_icon()
// declared in functions.php (also includes Instagram, shared with the footer).
?>

<div <?php echo $wrapper_attributes; ?>>
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
          <a href="<?php echo esc_url($item->url ?? '#'); ?>"
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
              <a href="<?php echo esc_url($child->url ?? '#'); ?>"
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
                      <a href="<?php echo esc_url($child->url ?? '#'); ?>"
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
              <a href="<?php echo esc_url($item->url ?? '#'); ?>"
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
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_number)); ?>"
              class="oit-nav__panel-phone no-underline flex items-center gap-2 text-white font-dm font-medium text-[14px]">
              <svg class="w-[18px] h-[17px] text-brand-red" viewBox="0 0 18 17" fill="currentColor" aria-hidden="true">
                <path
                  d="M16.1 12.4v2.1c0 1-.8 1.8-1.8 1.7-3.6-.4-7-1.7-9.8-3.9-2.6-2-4.7-4.8-6-7.8-.6-1.4-.6-3 .1-4.4.3-.6.9-1 1.6-1H2c.8 0 1.5.6 1.6 1.4.1.7.3 1.4.6 2 .3.7.1 1.5-.4 2L2.8 5.6c1.4 2.6 3.5 4.7 6.1 6.1l1-1c.5-.5 1.3-.7 2-.4.6.3 1.3.5 2 .6.8.1 1.4.8 1.4 1.6Z" />
              </svg>
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