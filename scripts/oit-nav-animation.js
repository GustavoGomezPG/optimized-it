/**
 * OptimizedIT nav entrance animation.
 *
 * Plays a staged GSAP reveal of the site navigation exactly once per
 * fresh browser session, immediately after the intro screen hands
 * control back via the `oit:intro-complete` event.
 *
 * Contract with sessionStorage('introShown'):
 *
 *   First visit  -> html.oit-intro-pending (set inline in <head>)
 *                   .oit-nav__shell hidden via CSS, then animated in
 *                   here when intro fires the event.
 *
 *   Repeat visit -> html.oit-intro-skip (set inline in <head>)
 *                   No CSS hide, no event ever fires, no timeline,
 *                   nav renders in its natural final state.
 *
 *   prefers-reduced-motion -> strip the pending class immediately, skip
 *                             the timeline, render the nav statically.
 *
 * Motion personality: Corporate / Premium. Decelerated easing
 * (power3.out), no overshoot, ~1 s total reveal. Choreography stages
 * the eye logo -> menu items -> CTA.
 *
 * Loaded as `optimizedit-nav-anim` with deps on optimizedit-gsap and
 * optimizedit-init.
 */
(function () {
  'use strict';

  if (!window.gsap) return;

  var html = document.documentElement;

  // Already-seen-this-session path: nothing to do, nav is already visible.
  if (html.classList.contains('oit-intro-skip')) return;

  // Reveal helper -- restore nav visibility without a timeline.
  function reveal() {
    html.classList.remove('oit-intro-pending');
  }

  // Respect OS-level reduced-motion preference.
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) {
    document.addEventListener('oit:intro-complete', reveal, { once: true });
    // Also reveal eagerly if the intro never plays (eg. JS error path).
    window.setTimeout(reveal, 9000);
    return;
  }

  function play() {
    // Release the CSS hide so GSAP owns visibility from here on.
    reveal();

    var tl = window.gsap.timeline({ defaults: { ease: 'power3.out' } });

    tl.fromTo(
      '.oit-nav__shell',
      { opacity: 0, y: -16 },
      { opacity: 1, y: 0, duration: 0.55 }
    );

    tl.fromTo(
      '.oit-nav__menu > li',
      { opacity: 0, y: -8 },
      { opacity: 1, y: 0, duration: 0.35, stagger: 0.06 },
      '-=0.25'
    );

    tl.fromTo(
      '.oit-nav__cta',
      { opacity: 0, scale: 0.94 },
      { opacity: 1, scale: 1, duration: 0.35 },
      '-=0.20'
    );
  }

  document.addEventListener('oit:intro-complete', play, { once: true });
})();
