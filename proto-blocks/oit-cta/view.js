/**
 * OIT CTA -- scroll-triggered reveal.
 *
 * Motion personality: decelerated (power3.out). Triggered the first
 * time the section's top crosses 80% of the viewport from below.
 *
 * Choreography (offset / target / dur):
 *
 *   0.00s  Card       opacity 0->1, y +30->0, scale 0.98->1     0.7s
 *   0.10s  Headline   opacity 0->1, y +12->0                    0.5s
 *   0.20s  Button     opacity 0->1, scale 0.94->1               0.4s
 *
 * Safety nets:
 *   - prefers-reduced-motion        -> static reveal, no timeline
 *   - GSAP / ScrollTrigger missing  -> same: static reveal
 */
(function () {
  'use strict';

  function init(section) {
    if (!section || section.dataset.oitCtaInit === '1') return;
    section.dataset.oitCtaInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-proto-animate', 'done');
    }

    var card     = section.querySelector('.oit-cta__card');
    var headline = section.querySelector('.oit-cta__headline');
    var button   = section.querySelector('.oit-cta__button');

    if (!window.gsap || !window.ScrollTrigger || reduce) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);

    // Inline opacity 0 wins specificity over the data-proto-animate="done"
    // flip so the tween has something to fade from.
    if (card)     window.gsap.set(card,     { y: 30, scale: 0.98, opacity: 0, transformOrigin: '50% 50%' });
    if (headline) window.gsap.set(headline, { y: 12, opacity: 0 });
    if (button)   window.gsap.set(button,   { scale: 0.94, opacity: 0, transformOrigin: '50% 50%' });

    var tl = window.gsap.timeline({
      defaults: { ease: 'power3.out' },
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        invalidateOnRefresh: true,
        once: true,
        onEnter: reveal,
      },
    });

    if (card)     tl.to(card,     { opacity: 1, y: 0, scale: 1, duration: 0.7 }, 0);
    if (headline) tl.to(headline, { opacity: 1, y: 0, duration: 0.5 }, 0.10);
    if (button)   tl.to(button,   { opacity: 1, scale: 1, duration: 0.4 }, 0.20);
  }

  function boot() {
    document.querySelectorAll('.oit-cta').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
