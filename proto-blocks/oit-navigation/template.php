<?php
/**
 * OIT Main Navigation
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$logo = $attributes['logo'] ?? [];
$cta_button = $attributes['ctaButton'] ?? ['url' => '#', 'text' => 'SCHEDULE A CONSULTATION'];
$phone_number = $attributes['phoneNumber'] ?? '';
$social_links = $attributes['socialLinks'] ?? [];

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

if (!function_exists('oit_nav_social_icon')) {
  function oit_nav_social_icon($platform)
  {
    $platform = strtolower(trim((string) $platform));
    switch ($platform) {
      case 'linkedin':
        return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.86-3.04-1.86 0-2.14 1.45-2.14 2.95v5.66H9.35V9h3.41v1.56h.05a3.74 3.74 0 0 1 3.37-1.85c3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.11 2.06 2.06 0 0 1 0 4.11Zm1.78 13.02H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.72V1.72C24 .77 23.2 0 22.22 0Z"/></svg>';
      case 'facebook':
        return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12a12 12 0 1 0-13.88 11.85v-8.39H7.08V12h3.04V9.36c0-3 1.79-4.67 4.53-4.67 1.31 0 2.69.24 2.69.24v2.96h-1.52c-1.49 0-1.96.93-1.96 1.88V12h3.33l-.53 3.47h-2.8v8.39A12 12 0 0 0 24 12Z"/></svg>';
      case 'youtube':
        return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.12-2.13C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.52A3 3 0 0 0 .5 6.2 31.4 31.4 0 0 0 0 12a31.4 31.4 0 0 0 .5 5.8 3 3 0 0 0 2.12 2.13C4.5 20.45 12 20.45 12 20.45s7.5 0 9.38-.52a3 3 0 0 0 2.12-2.13A31.4 31.4 0 0 0 24 12a31.4 31.4 0 0 0-.5-5.8ZM9.55 15.57V8.43L15.82 12l-6.27 3.57Z"/></svg>';
      case 'x':
      case 'twitter':
        return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 1.16h3.68l-8.03 9.17L24 22.85h-7.4l-5.79-7.57-6.63 7.57H.5l8.59-9.81L0 1.16h7.58l5.23 6.92 6.09-6.92Zm-1.29 19.5h2.04L6.49 3.24H4.3L17.6 20.65Z"/></svg>';
      default:
        return '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="10"/></svg>';
    }
  }
}
?>

<div <?php echo $wrapper_attributes; ?>>
  <div
    class="oit-nav__shell bg-[#1A0303] text-white rounded-[24px] max-w-[1360px] mx-auto shadow-[0_38px_41.5px_rgba(224,5,35,0.10),0_151px_75.5px_rgba(224,5,35,0.09),0_341px_102px_rgba(224,5,35,0.05),0_605px_121px_rgba(224,5,35,0.01)]">
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
            class="oit-nav__link group/link no-underline flex items-center gap-2 text-white oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase hover:text-[#E00523] transition-colors py-2"
            <?php echo $has_children ? 'aria-haspopup="true" aria-expanded="false"' : ''; ?>>
            <span><?php echo esc_html($item->title); ?></span>
            <?php if ($has_children): ?>
              <svg class="w-[14px] h-[9px] transition-transform group-aria-expanded/link:rotate-180" viewBox="0 0 18 11" fill="none" aria-hidden="true"><path d="M1 1L9 9L17 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php endif; ?>
          </a>
          <?php if ($has_children): ?>
          <div class="oit-nav__submenu-wrap absolute top-full left-0 pt-2 hidden group-hover/item:block focus-within:block">
            <div
              class="oit-nav__submenu bg-[#1A0303] rounded-[16px] py-5 px-6 flex flex-col gap-4 min-w-[220px] shadow-[0_38px_42px_-10px_rgba(224,5,35,0.15)]">
              <?php foreach ($children as $child): ?>
              <a href="<?php echo esc_url($child->url ?? '#'); ?>"
                class="oit-nav__sublink relative self-start no-underline text-white oit-font-dm font-medium text-[16px] leading-[1.5] whitespace-nowrap">
                <?php echo esc_html($child->title); ?>
              </a>
              <?php endforeach; ?>
              <?php if ($show_view_all): ?>
              <a href="<?php echo esc_url($item->url ?? '#'); ?>"
                class="oit-nav__view-all no-underline mt-1 inline-flex items-center gap-2 text-white oit-font-dm font-medium text-[16px] leading-[1.5] border-b-2 border-[#D1001D] pb-1 self-start hover:text-[#E00523] transition-colors">
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
          class="oit-nav__cta no-underline hidden sm:inline-flex items-center gap-3 bg-[#E00523] hover:bg-[#B8001D] text-white oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase px-5 py-2.5 rounded-full transition-colors whitespace-nowrap"
          data-proto-field="ctaButton"><?php echo esc_html($cta_button['text'] ?? 'SCHEDULE A CONSULTATION'); ?></a>
        <?php endif; ?>

        <button type="button" class="oit-nav__toggle group lg:hidden flex items-center gap-2.5 text-white"
          aria-label="Toggle menu" aria-expanded="false" aria-controls="<?php echo esc_attr($nav_id); ?>-panel">
          <span class="oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase group-aria-expanded:hidden">MENU</span>
          <span class="oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase hidden group-aria-expanded:inline">CLOSE</span>
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
                class="oit-nav__panel-trigger group w-full flex items-center justify-between text-white oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase bg-transparent border-0 p-0 cursor-pointer"
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
                        class="oit-nav__panel-sublink relative self-start no-underline py-2 inline-block text-white oit-font-dm font-medium text-[14px] leading-[1.5]">
                        <?php echo esc_html($child->title); ?>
                      </a>
                    </li>
                    <?php endforeach; ?>
                    <?php if ($show_view_all): ?>
                    <li>
                      <a href="<?php echo esc_url($item->url ?? '#'); ?>"
                        class="oit-nav__panel-sublink oit-nav__panel-view-all no-underline inline-flex items-center gap-2 text-white oit-font-dm font-medium text-[14px] leading-[1.5] border-b-2 border-[#D1001D] pb-1 self-start mt-1">
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
                class="oit-nav__panel-link no-underline flex items-center justify-between text-white oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase">
                <span><?php echo esc_html($item->title); ?></span>
                <svg class="w-[8px] h-[14px]" viewBox="0 0 11 18" fill="none" aria-hidden="true"><path d="M1 1L9 9L1 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </a>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>

          <?php if ($show_cta): ?>
          <a href="<?php echo esc_url($cta_button['url'] ?? '#'); ?>"
            <?php echo !empty($cta_button['target']) ? 'target="' . esc_attr($cta_button['target']) . '"' : ''; ?>
            class="oit-nav__cta no-underline self-start inline-flex items-center gap-3 bg-[#E00523] hover:bg-[#B8001D] text-white oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase px-5 py-2.5 rounded-full transition-colors"><?php echo esc_html($cta_button['text'] ?? 'SCHEDULE A CONSULTATION'); ?></a>
          <?php endif; ?>

          <?php if (($show_phone && !empty($phone_number)) || ($show_social && !empty($social_links))): ?>
          <div class="flex flex-col gap-4 pt-2">
            <?php if ($show_phone && !empty($phone_number)): ?>
            <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_number)); ?>"
              class="oit-nav__panel-phone no-underline flex items-center gap-2 text-white oit-font-dm font-medium text-[14px]">
              <svg class="w-[18px] h-[17px] text-[#D1001D]" viewBox="0 0 18 17" fill="currentColor" aria-hidden="true">
                <path
                  d="M16.1 12.4v2.1c0 1-.8 1.8-1.8 1.7-3.6-.4-7-1.7-9.8-3.9-2.6-2-4.7-4.8-6-7.8-.6-1.4-.6-3 .1-4.4.3-.6.9-1 1.6-1H2c.8 0 1.5.6 1.6 1.4.1.7.3 1.4.6 2 .3.7.1 1.5-.4 2L2.8 5.6c1.4 2.6 3.5 4.7 6.1 6.1l1-1c.5-.5 1.3-.7 2-.4.6.3 1.3.5 2 .6.8.1 1.4.8 1.4 1.6Z" />
              </svg>
              <span data-proto-field="phoneNumber"><?php echo esc_html($phone_number); ?></span>
            </a>
            <?php endif; ?>

            <?php if ($show_social && !empty($social_links)): ?>
            <div class="flex items-center gap-3" data-proto-repeater="socialLinks">
              <?php foreach ($social_links as $social):
                    $platform = $social['platform'] ?? '';
                    $url = $social['url'] ?? '#';
                    ?>
              <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"
                class="no-underline text-white hover:text-[#E00523] transition-colors"
                aria-label="<?php echo esc_attr(ucfirst($platform)); ?>" data-proto-repeater-item>
                <?php echo oit_nav_social_icon($platform); ?>
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