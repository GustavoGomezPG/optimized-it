<?php
/**
 * OIT Footer
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$logo                 = $attributes['logo'] ?? [];
$cta_button           = $attributes['ctaButton'] ?? ['url' => '#', 'text' => 'SCHEDULE A CONSULTATION'];
$menu_location        = $attributes['menuLocation'] ?? 'footer';
$show_newsletter      = $attributes['showNewsletter'] ?? true;
$newsletter_heading   = $attributes['newsletterHeading'] ?? 'STAY CONNECTED WITH US';
$newsletter_shortcode = trim($attributes['newsletterShortcode'] ?? '');
$tagline              = $attributes['tagline'] ?? '';
$phone_number         = $attributes['phoneNumber'] ?? '';
$address_lines        = $attributes['addressLines'] ?? '';
$copyright_text       = $attributes['copyrightText'] ?? '';

$social_links = array_values(array_filter([
    ['platform' => 'linkedin',  'url' => $attributes['linkedinUrl']  ?? ''],
    ['platform' => 'facebook',  'url' => $attributes['facebookUrl']  ?? ''],
    ['platform' => 'youtube',   'url' => $attributes['youtubeUrl']   ?? ''],
    ['platform' => 'instagram', 'url' => $attributes['instagramUrl'] ?? ''],
    ['platform' => 'x',         'url' => $attributes['xUrl']         ?? ''],
], fn($s) => !empty($s['url'])));

// Build menu tree from the registered footer location.
$menu_tree = [];
$locations = get_nav_menu_locations();
$menu_id   = $locations[$menu_location] ?? null;
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

// Editor / "no menu wired up yet" preview.
if (empty($menu_tree)) {
    $fallback = [
        ['title' => 'SOLUTIONS', 'url' => '#', 'children' => [
            ['title' => 'Managed IT', 'url' => '#'],
            ['title' => 'Co-Managed IT', 'url' => '#'],
            ['title' => 'Cybersecurity', 'url' => '#'],
            ['title' => 'Cloud & Modern Workplace', 'url' => '#'],
            ['title' => 'Strategic IT/vCIO', 'url' => '#'],
            ['title' => 'Data Protection', 'url' => '#'],
            ['title' => 'View All', 'url' => '#'],
        ]],
        ['title' => 'INDUSTRIES', 'url' => '#', 'children' => [
            ['title' => 'Manufacturing', 'url' => '#'],
            ['title' => 'Government', 'url' => '#'],
            ['title' => 'Healthcare', 'url' => '#'],
            ['title' => 'Education', 'url' => '#'],
            ['title' => 'Financial Services', 'url' => '#'],
            ['title' => 'Nonprofit', 'url' => '#'],
            ['title' => 'Professional Services', 'url' => '#'],
            ['title' => 'View All', 'url' => '#'],
        ]],
        ['title' => 'ABOUT', 'url' => '#', 'children' => [
            ['title' => 'About Optimized IT', 'url' => '#'],
            ['title' => 'Careers', 'url' => '#'],
            ['title' => 'Contact Us', 'url' => '#'],
        ]],
        ['title' => 'LOCATIONS', 'url' => '#', 'children' => [
            ['title' => 'Cincinnati', 'url' => '#'],
            ['title' => 'Columbus', 'url' => '#'],
            ['title' => 'Dayton', 'url' => '#'],
            ['title' => 'Pittsburgh', 'url' => '#'],
            ['title' => 'View All', 'url' => '#'],
        ]],
        ['title' => 'RESOURCES', 'url' => '#', 'children' => [
            ['title' => 'Resource Library', 'url' => '#'],
            ['title' => 'Blog', 'url' => '#'],
        ]],
    ];
    foreach ($fallback as $fb) {
        $kids = [];
        foreach ($fb['children'] as $c) {
            $kids[] = (object) $c;
        }
        $menu_tree[] = [
            'item'     => (object) ['title' => $fb['title'], 'url' => $fb['url']],
            'children' => $kids,
        ];
    }
}

$wrapper_attributes = get_block_wrapper_attributes(['class' => 'relative w-full']);

$show_newsletter_strip = $show_newsletter && (!empty($newsletter_heading) || !empty($newsletter_shortcode));
?>

<footer <?php echo $wrapper_attributes; ?>>
  <div class="oit-footer bg-[#1A0303] text-white px-4 pt-12 pb-8 lg:px-20 lg:pt-16 lg:pb-8">
    <div class="max-w-[1360px] mx-auto flex flex-col gap-12">

      <?php if ($show_newsletter_strip): ?>
        <div class="oit-footer__newsletter bg-[#353333] rounded-[24px] flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 lg:gap-8 px-6 py-5 lg:px-6 lg:py-4">
          <?php if (!empty($newsletter_heading)): ?>
            <h2 class="oit-font-grotesk font-bold text-[20px] leading-[1.4] uppercase whitespace-nowrap m-0">
              <?php echo esc_html($newsletter_heading); ?>
            </h2>
          <?php endif; ?>

          <div class="oit-footer__newsletter-form flex-1 min-w-0 w-full lg:w-auto">
            <?php if (!empty($newsletter_shortcode)): ?>
              <?php echo do_shortcode($newsletter_shortcode); ?>
            <?php else: ?>
              <p class="oit-font-dm font-medium text-[14px] leading-[1.5] text-white/60 m-0">
                <em>Paste your newsletter form shortcode in the block inspector to wire up signups.</em>
              </p>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="grid grid-cols-1 lg:grid-cols-[max-content_1fr] gap-12 lg:gap-[80px]">
        <div class="flex flex-col gap-6 lg:max-w-[329px]">
          <div class="flex flex-col gap-2">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="no-underline self-start" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
              <?php if (!empty($logo['url'])): ?>
                <img data-proto-field="logo" src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt'] ?? get_bloginfo('name')); ?>" class="h-[73px] w-auto" />
              <?php else: ?>
                <img data-proto-field="logo" src="" alt="Logo" class="h-[73px] w-auto" />
              <?php endif; ?>
            </a>

            <?php if (!empty($tagline)): ?>
              <p class="oit-font-dm font-medium text-[14px] leading-[1.5] m-0">
                <?php echo esc_html($tagline); ?>
              </p>
            <?php endif; ?>
          </div>

          <a href="<?php echo esc_url($cta_button['url'] ?? '#'); ?>"
            <?php echo !empty($cta_button['target']) ? 'target="' . esc_attr($cta_button['target']) . '"' : ''; ?>
            <?php echo !empty($cta_button['rel']) ? 'rel="' . esc_attr($cta_button['rel']) . '"' : ''; ?>
            class="oit-nav__cta no-underline self-start inline-flex items-center gap-3 bg-[#D1001D] hover:bg-[#A0001A] text-white oit-font-grotesk font-medium text-[16px] leading-[1.3] uppercase px-5 py-2.5 rounded-full transition-colors whitespace-nowrap"
            data-proto-field="ctaButton"><?php echo esc_html($cta_button['text'] ?? 'SCHEDULE A CONSULTATION'); ?></a>

          <?php if (!empty($phone_number) || !empty($address_lines)): ?>
            <div class="oit-footer__contact flex flex-col gap-2">
              <?php if (!empty($phone_number)): ?>
                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_number)); ?>"
                  class="oit-footer__phone no-underline inline-flex items-center gap-2 self-start border-b-2 border-[#D1001D] pt-1 pb-2 text-white oit-font-dm font-medium text-[16px] leading-[1.5]">
                  <svg width="18" height="17" viewBox="0 0 18 17" fill="currentColor" aria-hidden="true" class="text-[#D1001D]">
                    <path d="M16.1 12.4v2.1c0 1-.8 1.8-1.8 1.7-3.6-.4-7-1.7-9.8-3.9-2.6-2-4.7-4.8-6-7.8-.6-1.4-.6-3 .1-4.4.3-.6.9-1 1.6-1H2c.8 0 1.5.6 1.6 1.4.1.7.3 1.4.6 2 .3.7.1 1.5-.4 2L2.8 5.6c1.4 2.6 3.5 4.7 6.1 6.1l1-1c.5-.5 1.3-.7 2-.4.6.3 1.3.5 2 .6.8.1 1.4.8 1.4 1.6Z"/>
                  </svg>
                  <span><?php echo esc_html($phone_number); ?></span>
                </a>
              <?php endif; ?>
              <?php if (!empty($address_lines)): ?>
                <address class="oit-footer__address not-italic m-0 text-white oit-font-dm font-medium text-[16px] leading-[1.5] whitespace-pre-line"><?php echo esc_html($address_lines); ?></address>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <nav class="oit-footer__menus" aria-label="Footer navigation">
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-x-6 gap-y-8 lg:gap-x-12">
            <?php foreach ($menu_tree as $entry):
                $item     = $entry['item'];
                $children = $entry['children'];
            ?>
              <div class="flex flex-col gap-2.5">
                <h3 class="oit-font-grotesk font-medium text-[16px] leading-[1.4] uppercase text-white m-0">
                  <?php if (!empty($item->url) && $item->url !== '#'): ?>
                    <a href="<?php echo esc_url($item->url); ?>" class="no-underline text-white hover:text-[#D1001D] transition-colors">
                      <?php echo esc_html($item->title); ?>
                    </a>
                  <?php else: ?>
                    <?php echo esc_html($item->title); ?>
                  <?php endif; ?>
                </h3>
                <?php if (!empty($children)): ?>
                  <ul class="flex flex-col gap-2.5 m-0 p-0 list-none">
                    <?php foreach ($children as $child): ?>
                      <li>
                        <a href="<?php echo esc_url($child->url ?? '#'); ?>"
                          class="oit-footer__link no-underline oit-font-dm font-medium text-[16px] leading-[1.5] text-white hover:text-[#D1001D] transition-colors">
                          <?php echo esc_html($child->title); ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </nav>
      </div>

      <div class="flex flex-col gap-6">
        <hr class="border-0 border-t-2 border-[#D1001D] m-0" />
        <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-4 sm:gap-8">
          <?php if (!empty($social_links)): ?>
            <div class="flex items-center gap-3">
              <?php foreach ($social_links as $social):
                  $platform = $social['platform'];
                  $url      = $social['url'];
              ?>
                <a href="<?php echo esc_url($url); ?>"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="no-underline text-white hover:text-[#D1001D] transition-colors"
                  aria-label="<?php echo esc_attr(ucfirst($platform)); ?>">
                  <?php echo oit_social_icon($platform); ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($copyright_text)): ?>
            <p class="oit-font-dm font-medium text-[14px] leading-[1.5] text-white m-0">
              <?php echo esc_html($copyright_text); ?>
            </p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</footer>
