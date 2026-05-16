/**
 * OIT Text and List -- scroll-triggered reveal.
 *
 * Motion personality: Corporate / Premium.
 *   - power3.out (decelerated, no overshoot).
 *   - Total ~1.0 s, staged: headline -> body -> label -> items.
 *   - Each list item's rule line draws in left-to-right; the dot
 *     terminator pops at the end.
 *
 * Triggered by ScrollTrigger when the section enters the viewport
 * (start: top 80%). One-shot -- doesn't reverse when scrolled past or
 * replay on scroll-back. Lenis is bridged to ScrollTrigger in oit-init.js
 * so trigger positions stay accurate under smooth scroll.
 *
 * Bails on prefers-reduced-motion or missing GSAP / ScrollTrigger --
 * the block already renders in its final state, so a bail = no
 * animation, no broken state.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitTextListInit === '1') return;
    section.dataset.oitTextListInit = '1';

    if (!window.gsap || !window.ScrollTrigger) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    window.gsap.registerPlugin(window.ScrollTrigger);

    var headline = section.querySelector('.oit-text-list__headline');
    var body     = section.querySelector('.oit-text-list__body');
    var label    = section.querySelector('.oit-text-list__label');
    var items    = section.querySelectorAll('.oit-text-list__item');

    // Pre-hide everything that animates in. We're using gsap.set rather
    // than CSS so the static (no-JS / reduced-motion) path stays fully
    // visible -- if this function bails, nothing is hidden.
    var textTargets = [];
    if (headline) textTargets.push(headline);
    if (body)     textTargets.push(body);
    if (label)    textTargets.push(label);
    if (textTargets.length) {
      window.gsap.set(textTargets, { opacity: 0, y: 24 });
    }
    if (items.length) {
      window.gsap.set(items, { opacity: 0, y: 24 });
    }

    // Prime the rule lines for the stroke-draw effect. Each item's <line>
    // gets dasharray = its length, dashoffset = length (fully hidden), and
    // the dot starts at scale 0 from its right end.
    var rules = section.querySelectorAll('.oit-text-list__item-rule line');
    var dots  = section.querySelectorAll('.oit-text-list__item-rule circle');
    if (rules.length) {
      window.gsap.set(rules, { strokeDasharray: 180, strokeDashoffset: 180 });
    }
    if (dots.length) {
      window.gsap.set(dots, { opacity: 0 });
    }

    var tl = window.gsap.timeline({
      defaults: { ease: 'power3.out' },
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        // invalidateOnRefresh: keeps cached start values fresh if Lenis
        // resizes the document (eg. font load shifting layout).
        invalidateOnRefresh: true,
      },
    });

    if (headline) tl.to(headline, { opacity: 1, y: 0, duration: 0.6 }, 0);
    if (body)     tl.to(body,     { opacity: 1, y: 0, duration: 0.6 }, 0.12);
    if (label)    tl.to(label,    { opacity: 1, y: 0, duration: 0.5 }, 0.28);
    if (items.length) {
      tl.to(items, { opacity: 1, y: 0, duration: 0.5, stagger: 0.08 }, 0.42);
    }
    if (rules.length) {
      tl.to(rules, { strokeDashoffset: 0, duration: 0.6, stagger: 0.08 }, 0.5);
    }
    if (dots.length) {
      tl.to(dots, { opacity: 1, duration: 0.3, stagger: 0.08 }, 0.85);
    }
  }

  function boot() {
    document.querySelectorAll('.oit-text-list').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
