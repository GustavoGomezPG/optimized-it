/**
 * OIT Featured Section -- scroll-triggered reveal.
 *
 * Corporate / Premium archetype, power3.out, ~1.2 s, staged.
 *
 *   0.00s  Card        opacity 0->1, y +30->0, scale 0.98->1     0.7s   (establishing)
 *   0.20s  Image       opacity 0->1, x -24->0                    0.6s   (slides in)
 *   0.30s  Headline    opacity 0->1, y +16->0                    0.55s
 *   0.45s  Steps       opacity 0->1, x +16->0  (stagger 0.08s)   0.45s
 *   0.85s  CTA         opacity 0->1, scale 0.94->1               0.4s
 *
 * Bails on prefers-reduced-motion / missing GSAP / ScrollTrigger -- the
 * card renders fully visible in those cases.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitFsInit === '1') return;
    section.dataset.oitFsInit = '1';

    if (!window.gsap || !window.ScrollTrigger) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    window.gsap.registerPlugin(window.ScrollTrigger);

    var card     = section.querySelector('.oit-featured-section__card');
    var image    = section.querySelector('.oit-featured-section__media');
    var headline = section.querySelector('.oit-featured-section__headline');
    var steps    = section.querySelectorAll('.oit-featured-section__step');
    var cta      = section.querySelector('.oit-featured-section__cta');

    // Wrap every ":" in the headline in <span class="text-brand-red"> so
    // the brand colon accent renders automatically -- editors don't have
    // to remember to colour it manually. Same idempotent walker used by
    // the hero block for periods.
    if (headline) wrapColons(headline);

    if (card)     window.gsap.set(card,     { opacity: 0, y: 30, scale: 0.98 });
    if (image)    window.gsap.set(image,    { opacity: 0, x: -24 });
    if (headline) window.gsap.set(headline, { opacity: 0, y: 16 });
    if (steps.length) window.gsap.set(steps, { opacity: 0, x: 16 });
    if (cta)      window.gsap.set(cta,      { opacity: 0, scale: 0.94 });

    var tl = window.gsap.timeline({
      defaults: { ease: 'power3.out' },
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        invalidateOnRefresh: true,
      },
    });

    if (card)     tl.to(card,     { opacity: 1, y: 0, scale: 1, duration: 0.7 }, 0);
    if (image)    tl.to(image,    { opacity: 1, x: 0, duration: 0.6 }, 0.20);
    if (headline) tl.to(headline, { opacity: 1, y: 0, duration: 0.55 }, 0.30);
    if (steps.length) {
      tl.to(steps, { opacity: 1, x: 0, duration: 0.45, stagger: 0.08 }, 0.45);
    }
    if (cta)      tl.to(cta,      { opacity: 1, scale: 1, duration: 0.4 }, 0.85);
  }

  /**
   * Walk every text node inside the headline and wrap each ":" in a
   * <span class="text-brand-red"> so the brand colon accent renders
   * automatically. Idempotent: bails on a second run via a data flag and
   * skips text already nested inside a wrap span.
   */
  function wrapColons(el) {
    if (el.dataset.oitColons === '1') return;
    el.dataset.oitColons = '1';

    function walk(node) {
      if (node.nodeType === 3) {
        var text = node.nodeValue;
        if (text.indexOf(':') === -1) return;

        var parent = node.parentNode;
        if (!parent) return;
        if (parent.classList && parent.classList.contains('oit-featured-section__colon')) {
          return;
        }

        var parts = text.split(/(:)/);
        if (parts.length < 2) return;

        var frag = document.createDocumentFragment();
        parts.forEach(function (part) {
          if (part === ':') {
            var span = document.createElement('span');
            span.className = 'oit-featured-section__colon text-brand-red';
            span.textContent = ':';
            frag.appendChild(span);
          } else if (part.length > 0) {
            frag.appendChild(document.createTextNode(part));
          }
        });
        parent.replaceChild(frag, node);
      } else if (node.nodeType === 1) {
        var kids = Array.prototype.slice.call(node.childNodes);
        kids.forEach(walk);
      }
    }
    walk(el);
  }

  function boot() {
    document.querySelectorAll('.oit-featured-section').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
