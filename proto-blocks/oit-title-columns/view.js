/**
 * OIT Title + 3 Columns -- scroll-triggered reveal.
 *
 * Title slides in from the left, optional intro paragraph (stacked
 * layout only) fades up just after, columns then stagger up from below.
 *
 *   0.00s  Title    opacity 0->1, x -16->0     0.55s
 *   0.10s  Intro    opacity 0->1, y +12->0     0.5s   (if present)
 *   0.20s  Columns  opacity 0->1, y +24->0     0.5s (stagger 0.12s)
 *
 * Total runtime ~1.0s. Triggered the first time the section's top
 * crosses 80% of the viewport.
 *
 * Bails on prefers-reduced-motion / missing GSAP / missing ScrollTrigger.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitTcolInit === '1') return;
    section.dataset.oitTcolInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-proto-animate', 'done');
    }

    var title = section.querySelector('.oit-title-columns__title');
    var intro = section.querySelector('.oit-title-columns__intro');
    var cols  = section.querySelectorAll('.oit-title-columns__col');

    if (!window.gsap || !window.ScrollTrigger || reduce) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);

    // Set both transforms AND opacity inline so the GSAP tween can fade
    // smoothly when reveal() flips the data-proto-animate CSS
    // pre-state to "done". Without inline opacity 0, the elements jump to 1
    // the moment data-proto-animate flips, leaving nothing for the timeline to
    // fade.
    if (title)     window.gsap.set(title, { x: -16, opacity: 0 });
    if (intro)     window.gsap.set(intro, { y: 12, opacity: 0 });
    if (cols.length) window.gsap.set(cols, { y: 24, opacity: 0 });

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

    if (title)     tl.to(title, { opacity: 1, x: 0, duration: 0.55 }, 0);
    if (intro)     tl.to(intro, { opacity: 1, y: 0, duration: 0.5 }, 0.10);
    if (cols.length) tl.to(cols, { opacity: 1, y: 0, duration: 0.5, stagger: 0.12 }, 0.20);
  }

  function boot() {
    document.querySelectorAll('.oit-title-columns').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
