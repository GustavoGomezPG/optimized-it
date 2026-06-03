/**
 * OIT Link Cards -- scroll-triggered staggered reveal.
 *
 * Cards animate in sequence (opacity 0->1, x -24->0) with a 0.12s gap
 * between each, so the row sweeps left-to-right as the section enters
 * the viewport: each card slides in from the left while the previous
 * one is still settling. ~0.6s base duration, total runtime depends on
 * card count (3 cards = ~0.84s end-to-end).
 *
 * Bails on prefers-reduced-motion / missing GSAP / missing ScrollTrigger
 * by flipping the pre-state off immediately.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitLcInit === '1') return;
    section.dataset.oitLcInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-proto-animate', 'done');
    }

    var cards = section.querySelectorAll('.oit-link-cards__card');

    if (!window.gsap || !window.ScrollTrigger || reduce || !cards.length) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);

    // The CSS pre-state hides the card before JS boots, but onEnter
    // flips data-proto-animate to "done" before the timeline runs -- without
    // an inline opacity 0 from GSAP, the card briefly snaps to opacity 1
    // and the subsequent tween has nothing to fade from. Setting it here
    // (inline) wins on specificity so the fade actually animates.
    window.gsap.set(cards, { x: -24, opacity: 0 });

    window.gsap.timeline({
      defaults: { ease: 'power3.out' },
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        invalidateOnRefresh: true,
        once: true,
        onEnter: reveal,
      },
    }).to(cards, {
      opacity: 1,
      x: 0,
      duration: 0.6,
      stagger: 0.12,
    });
  }

  function boot() {
    document.querySelectorAll('.oit-link-cards').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
