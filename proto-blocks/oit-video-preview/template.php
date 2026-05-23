<?php
/**
 * OIT Video Preview
 *
 * Renders a large rounded video card with a centered play button.
 * Clicking the card opens a fullscreen modal lightbox with the actual
 * player. Supports three sources:
 *
 *   - youtube : extracts the video ID, uses img.youtube.com for the
 *               thumbnail (maxresdefault, falls back to hqdefault
 *               client-side via onerror), embeds via youtube.com/embed
 *               on open.
 *   - vimeo   : extracts the video ID, fetches thumbnail via Vimeo
 *               oEmbed (cached as a 24h transient), embeds via
 *               player.vimeo.com on open.
 *   - mp4     : self-hosted URL; thumbnail must be supplied by the
 *               author; plays via native <video controls autoplay>.
 *
 * Author can override the auto-fetched thumbnail by setting
 * customThumbnail. Optional overlay caption + CTA label render on the
 * right side of the card (matching the Figma red headline + Download
 * Now pill).
 *
 * @var array         $attributes
 * @var string        $content
 * @var WP_Block|null $block
 */

$video_url       = trim((string) ($attributes['videoUrl'] ?? ''));
$custom_thumb    = $attributes['customThumbnail'] ?? null;
$source          = $attributes['source'] ?? 'youtube';

// ---- Helpers ---------------------------------------------------------

/** Extract YouTube video ID from URL or raw ID. */
$yt_id_from = static function (string $input): string {
  if ($input === '') return '';
  if (preg_match('/^[A-Za-z0-9_-]{6,15}$/', $input)) return $input;
  if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/|v/))([A-Za-z0-9_-]{6,15})~', $input, $m)) {
    return $m[1];
  }
  return '';
};

/** Extract Vimeo video ID from URL or raw ID. */
$vm_id_from = static function (string $input): string {
  if ($input === '') return '';
  if (preg_match('/^\d{6,12}$/', $input)) return $input;
  if (preg_match('~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d{6,12})~', $input, $m)) {
    return $m[1];
  }
  return '';
};

/** Fetch Vimeo thumbnail URL via oEmbed, cached for 24h. */
$vm_thumb = static function (string $vimeo_id): string {
  if ($vimeo_id === '') return '';
  $key = 'oit_vimeo_thumb_' . $vimeo_id;
  $cached = get_transient($key);
  if (is_string($cached)) return $cached;
  $response = wp_remote_get('https://vimeo.com/api/oembed.json?url=' . rawurlencode('https://vimeo.com/' . $vimeo_id), [
    'timeout' => 5,
  ]);
  $thumb = '';
  if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (is_array($body) && !empty($body['thumbnail_url'])) {
      // Vimeo returns a sized thumb; strip the size suffix for a bigger one.
      $thumb = preg_replace('/_\d+x\d+(\.[a-z]+)$/', '_1280$1', $body['thumbnail_url']) ?: $body['thumbnail_url'];
    }
  }
  set_transient($key, $thumb, DAY_IN_SECONDS);
  return $thumb;
};

// ---- Resolve source + thumbnail + embed URL --------------------------
$video_id    = '';
$thumb_url   = '';
$embed_url   = '';
$mp4_url     = '';

if ($source === 'youtube') {
  $video_id  = $yt_id_from($video_url);
  $thumb_url = $video_id ? "https://img.youtube.com/vi/{$video_id}/maxresdefault.jpg" : '';
  $embed_url = $video_id ? "https://www.youtube.com/embed/{$video_id}?autoplay=1&rel=0" : '';
} elseif ($source === 'vimeo') {
  $video_id  = $vm_id_from($video_url);
  $thumb_url = $video_id ? $vm_thumb($video_id) : '';
  $embed_url = $video_id ? "https://player.vimeo.com/video/{$video_id}?autoplay=1" : '';
} else { // mp4
  $mp4_url   = $video_url ? esc_url_raw($video_url) : '';
}

// Custom thumbnail always wins.
if (!empty($custom_thumb['url'])) {
  $thumb_url = $custom_thumb['url'];
}

$thumb_alt = !empty($custom_thumb['alt']) ? $custom_thumb['alt'] : 'Video thumbnail';

$is_preview = !isset($block) || $block === null;
$wrapper_args = ['class' => 'oit-video-preview'];
if (!$is_preview) {
  $wrapper_args['data-animate'] = 'pending';
}
$wrapper_attributes = get_block_wrapper_attributes($wrapper_args);

$player_payload = wp_json_encode([
  'source'   => $source,
  'embedUrl' => $embed_url,
  'mp4Url'   => $mp4_url,
]);

$play_icon = '<svg viewBox="0 0 64 64" fill="white" aria-hidden="true" class="w-16 h-16 drop-shadow-lg"><circle cx="32" cy="32" r="32" fill="rgba(255,255,255,0.95)"/><path d="M26 21l18 11-18 11V21z" fill="#1a0303"/></svg>';
$close_icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-6 h-6"><path d="M6 6l12 12M18 6L6 18"/></svg>';
?>

<section <?php echo $wrapper_attributes; ?>>
  <div class="oit-video-preview__inner max-w-[900px] mx-auto px-6 lg:px-0">

    <button type="button"
      class="oit-video-preview__trigger group relative block w-full overflow-hidden rounded-3xl bg-brand-red text-white cursor-pointer border-0 p-0 shadow-red-glow"
      data-oit-video='<?php echo esc_attr($player_payload); ?>'
      aria-label="Play video">

      <?php if ($thumb_url): ?>
      <img
        src="<?php echo esc_url($thumb_url); ?>"
        alt="<?php echo esc_attr($thumb_alt); ?>"
        class="oit-video-preview__thumb block w-full h-auto aspect-[2/1] object-cover"
        <?php if ($source === 'youtube' && empty($custom_thumb['url']) && $video_id): ?>
        onerror="this.onerror=null;this.src='https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/hqdefault.jpg';"
        <?php endif; ?>
      />
      <?php else: ?>
      <div class="oit-video-preview__thumb aspect-[2/1] w-full bg-gradient-to-br from-brand-red to-black flex items-center justify-center text-white/70 text-body-sm">
        <?php echo $source === 'mp4' ? esc_html__('Add a thumbnail in the inspector.', 'optimizedit') : esc_html__('Add a video URL to load the thumbnail.', 'optimizedit'); ?>
      </div>
      <?php endif; ?>

      <span class="oit-video-preview__overlay absolute inset-0 bg-black/30 transition-colors duration-300 group-hover:bg-black/40"></span>

      <span class="oit-video-preview__play absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 transition-transform duration-300 group-hover:scale-110">
        <?php echo $play_icon; ?>
      </span>

    </button>

  </div>

  <!-- Lightbox -- hidden by default; view.js toggles `is-open` to show. -->
  <div class="oit-video-preview__modal fixed inset-0 z-[9999] hidden items-center justify-center bg-black/85 p-4 lg:p-12" role="dialog" aria-modal="true" aria-label="Video player">
    <button type="button" class="oit-video-preview__close absolute top-4 right-4 lg:top-6 lg:right-6 text-white bg-black/50 hover:bg-black/80 rounded-full p-2 transition-colors" aria-label="Close video">
      <?php echo $close_icon; ?>
    </button>
    <div class="oit-video-preview__stage relative w-full max-w-[1100px] aspect-video bg-black rounded-2xl overflow-hidden"></div>
  </div>
</section>
