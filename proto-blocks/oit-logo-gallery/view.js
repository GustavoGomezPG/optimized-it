/**
 * OIT Logo Gallery -- scroll-triggered reveal.
 *
 * Motion personality: decelerated (power3.out), about 1.0 s total.
 * Triggered the first time the section's top crosses 80% of the
 * viewport from below.
 *
 * Choreography (offset / target / dur):
 *
 *   0.00s  Title    opacity 0->1, x -16->0                   0.55s
 *   0.10s  Intro    opacity 0->1, y +12->0                   0.5s
 *   0.20s  Cells    opacity 0->1, y +16->0 (stagger 0.05s)   0.45s
 *
 * Safety nets:
 *   - prefers-reduced-motion        -> static reveal, no timeline
 *   - GSAP / ScrollTrigger missing  -> static reveal, no failures
 */
(function () {
  'use strict';

  function init(section) {
    if (!section || section.dataset.oitLgInit === '1') return;
    section.dataset.oitLgInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-animate', 'done');
    }

    var title = section.querySelector('.oit-logo-gallery__title');
    var intro = section.querySelector('.oit-logo-gallery__intro');
    var cells = section.querySelectorAll('.oit-logo-gallery__cell');

    if (!window.gsap || !window.ScrollTrigger || reduce) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);

    // Inline opacity 0 wins specificity over the data-animate="done"
    // flip so the tween has something to fade from. Without these
    // `set` calls the elements would jump to 1 the moment reveal()
    // runs, leaving nothing for the timeline to animate.
    if (title)       window.gsap.set(title, { x: -16, opacity: 0 });
    if (intro)       window.gsap.set(intro, { y: 12, opacity: 0 });
    if (cells.length) window.gsap.set(cells, { y: 16, opacity: 0 });

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

    if (title) tl.to(title, { opacity: 1, x: 0, duration: 0.55 }, 0);
    if (intro) tl.to(intro, { opacity: 1, y: 0, duration: 0.5 }, 0.10);
    if (cells.length) {
      tl.to(cells, {
        opacity: 1,
        y: 0,
        duration: 0.45,
        stagger: 0.05,
      }, 0.20);
    }
  }

  function boot() {
    document.querySelectorAll('.oit-logo-gallery').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
