/**
 * OIT Featured Cards -- scroll-triggered card reveal.
 *
 * Cards stagger in (opacity 0->1, y +32->0) when the grid enters the
 * viewport (top 80%). One-shot. Bails on prefers-reduced-motion or missing
 * GSAP / ScrollTrigger -- in those cases the grid renders fully visible.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitFcInit === '1') return;
    section.dataset.oitFcInit = '1';

    if (!window.gsap || !window.ScrollTrigger) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    window.gsap.registerPlugin(window.ScrollTrigger);

    var cards = section.querySelectorAll('.oit-featured-cards__card');
    if (!cards.length) return;

    window.gsap.set(cards, { opacity: 0, y: 32 });

    window.gsap.to(cards, {
      opacity: 1,
      y: 0,
      duration: 0.55,
      stagger: 0.08,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        invalidateOnRefresh: true,
      },
    });
  }

  function boot() {
    document.querySelectorAll('.oit-featured-cards').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
