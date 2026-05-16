/**
 * OIT Call to Action -- scroll-triggered reveal.
 *
 * Corporate / Premium archetype, power3.out, ~0.9 s, staged.
 *
 *   0.00s  Headline   opacity 0->1, y +24->0    0.60s
 *   0.12s  Body       opacity 0->1, y +24->0    0.60s
 *   0.25s  CTA        opacity 0->1, y +24->0    0.40s
 *
 * Bails on prefers-reduced-motion or missing GSAP / ScrollTrigger -- the
 * block renders fully visible in those cases.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitCtaInit === '1') return;
    section.dataset.oitCtaInit = '1';

    if (!window.gsap || !window.ScrollTrigger) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    window.gsap.registerPlugin(window.ScrollTrigger);

    var headline = section.querySelector('.oit-call-to-action__headline');
    var body     = section.querySelector('.oit-call-to-action__body');
    var cta      = section.querySelector('.oit-call-to-action__cta');

    if (headline) window.gsap.set(headline, { opacity: 0, y: 24 });
    if (body)     window.gsap.set(body,     { opacity: 0, y: 24 });
    if (cta)      window.gsap.set(cta,      { opacity: 0, y: 24 });

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
    if (body)     tl.to(body,     { opacity: 1, y: 0, duration: 0.6 }, 0.12);
    if (cta)      tl.to(cta,      { opacity: 1, y: 0, duration: 0.4 }, 0.25);
  }

  function boot() {
    document.querySelectorAll('.oit-call-to-action').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
