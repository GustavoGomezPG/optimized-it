/**
 * OIT Stats -- scroll-triggered roll-up counters.
 *
 * Each `.oit-stats__value-number` element carries a `data-target` of the
 * final numeric value (eg. "99.5", "26"). When the section enters view
 * (top 80%), each counter tweens from 0 to its target with power3.out
 * over ~1.6 s. Decimal precision is taken from the source string -- "99.5"
 * rolls in tenths, "26" rolls as whole numbers.
 *
 * The intro headline + body slide in just before the counters start
 * rolling, giving the section a calm staged feel rather than dumping
 * everything in at once.
 *
 * Bails on prefers-reduced-motion / missing GSAP / ScrollTrigger -- in
 * those cases the markup already shows the final value, so nothing to do.
 */
(function () {
  'use strict';

  function precisionOf(str) {
    var s = String(str || '').trim();
    var dot = s.indexOf('.');
    if (dot === -1) return 0;
    // Count digits after the decimal point, ignoring trailing non-digits.
    var tail = s.slice(dot + 1);
    var digits = tail.match(/^[0-9]+/);
    return digits ? digits[0].length : 0;
  }

  function init(section) {
    if (section.dataset.oitStatsInit === '1') return;
    section.dataset.oitStatsInit = '1';

    if (!window.gsap || !window.ScrollTrigger) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    window.gsap.registerPlugin(window.ScrollTrigger);

    var headline = section.querySelector('.oit-stats__headline');
    var body     = section.querySelector('.oit-stats__body');
    var items    = section.querySelectorAll('.oit-stats__item');
    var values   = section.querySelectorAll('.oit-stats__value-number');

    if (headline) window.gsap.set(headline, { opacity: 0, y: 20 });
    if (body)     window.gsap.set(body,     { opacity: 0, y: 20 });
    if (items.length) window.gsap.set(items, { opacity: 0, y: 28 });

    // Pre-fill each counter at 0 so the roll starts visibly from zero
    // rather than snapping back from the rendered value when the timeline
    // begins. Keep the original text on a data attribute to restore on
    // bail-out paths (we don't bail here, but the precision is parsed
    // from this string).
    var counters = [];
    values.forEach(function (el) {
      var target = parseFloat(el.getAttribute('data-target'));
      if (isNaN(target)) {
        // Non-numeric value -- nothing to roll, skip.
        counters.push(null);
        return;
      }
      var places = precisionOf(el.getAttribute('data-target'));
      counters.push({ el: el, target: target, places: places });
      el.textContent = (0).toFixed(places);
    });

    var tl = window.gsap.timeline({
      defaults: { ease: 'power3.out' },
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        invalidateOnRefresh: true,
      },
    });

    if (headline) tl.to(headline, { opacity: 1, y: 0, duration: 0.6 }, 0);
    if (body)     tl.to(body,     { opacity: 1, y: 0, duration: 0.6 }, 0.10);
    if (items.length) tl.to(items, { opacity: 1, y: 0, duration: 0.55, stagger: 0.12 }, 0.25);

    // Roll each counter as its item lands. Use an object-tween + onUpdate
    // so we can format with the right decimal precision -- gsap's text
    // tween doesn't natively format numeric increments.
    counters.forEach(function (c, i) {
      if (!c) return;
      var proxy = { v: 0 };
      tl.to(proxy, {
        v: c.target,
        duration: 1.6,
        ease: 'power3.out',
        onUpdate: function () {
          c.el.textContent = proxy.v.toFixed(c.places);
        },
        onComplete: function () {
          // Snap to the exact source string so any trailing zeros / format
          // the editor typed are preserved (eg. "99.50" -> "99.50").
          c.el.textContent = c.el.getAttribute('data-target');
        },
      }, 0.30 + i * 0.12);
    });
  }

  function boot() {
    document.querySelectorAll('.oit-stats').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
