/**
 * OIT Logo Marquee -- builds the seamless scrolling track.
 *
 * template.php renders a single editable group (the repeater <ul>).
 * Here, on the front end only, we:
 *
 *   1. Clone that group until the strip is at least as wide as the
 *      viewport, so there is never an empty gap at the trailing edge.
 *   2. Duplicate the whole filled strip once more, making the left and
 *      right halves identical -- the precondition for a `translateX(-50%)`
 *      keyframe (in style.css) to loop with no visible jump.
 *   3. Derive animation-duration from the chosen speed (constant px/sec,
 *      so the perceived velocity is the same regardless of logo count)
 *      and flip data-marquee-state to "running" to start the animation.
 *
 * Safety nets:
 *   - prefers-reduced-motion -> leave the strip static, never animate.
 *   - Re-fits on resize (debounced), since the fill target is the
 *     viewport width.
 *
 * Clones are stripped of all data-proto-* plumbing and marked
 * aria-hidden so they never reach the editor or assistive tech, and
 * never duplicate the logos in the accessibility tree.
 */
(function () {
  'use strict';

  // Perceived travel speed in CSS pixels per second.
  var SPEED = { slow: 22, medium: 45, fast: 80 };

  function sanitizeClone(node) {
    var clone = node.cloneNode(true);
    clone.classList.add('is-clone');
    clone.setAttribute('aria-hidden', 'true');
    [clone].concat(Array.prototype.slice.call(
      clone.querySelectorAll('[data-proto-repeater],[data-proto-repeater-item],[data-proto-field]')
    )).forEach(function (el) {
      el.removeAttribute('data-proto-repeater');
      el.removeAttribute('data-proto-repeater-item');
      el.removeAttribute('data-proto-field');
    });
    return clone;
  }

  function build(section) {
    if (section.dataset.oitMarqueeInit === '1') return;

    var track = section.querySelector('.oit-logo-marquee__track');
    var group = section.querySelector('.oit-logo-marquee__group');
    var viewport = section.querySelector('.oit-logo-marquee__viewport');
    if (!track || !group) return;

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) {
      section.dataset.oitMarqueeInit = '1';
      return;
    }

    section.dataset.oitMarqueeInit = '1';

    var vw = (viewport ? viewport.clientWidth : section.clientWidth) || 0;

    // 1) Fill until one sequence covers the viewport (guarded against a
    //    pathological zero-width strip).
    var guard = 0;
    while (track.scrollWidth < vw && track.scrollWidth > 0 && guard < 40) {
      track.appendChild(sanitizeClone(group));
      guard++;
    }

    // 2) Mirror the filled strip so translateX(-50%) is seamless.
    Array.prototype.slice.call(track.children).forEach(function (node) {
      track.appendChild(sanitizeClone(node));
    });

    // 3) Constant-velocity duration over one loop distance (half the track).
    var pxPerSec = SPEED[section.dataset.speed] || SPEED.medium;
    var loopDistance = track.scrollWidth / 2;
    if (loopDistance > 0) {
      track.style.animationDuration = (loopDistance / pxPerSec).toFixed(2) + 's';
      section.setAttribute('data-marquee-state', 'running');
    }
  }

  function rebuild(section) {
    var track = section.querySelector('.oit-logo-marquee__track');
    if (!track) return;
    Array.prototype.slice.call(track.querySelectorAll('.is-clone'))
      .forEach(function (n) { n.parentNode.removeChild(n); });
    track.style.animationDuration = '';
    section.removeAttribute('data-marquee-state');
    section.dataset.oitMarqueeInit = '';
    build(section);
  }

  function boot() {
    document.querySelectorAll('.oit-logo-marquee:not(.is-preview)').forEach(build);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  var resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      document.querySelectorAll('.oit-logo-marquee:not(.is-preview)').forEach(rebuild);
    }, 200);
  });
})();
