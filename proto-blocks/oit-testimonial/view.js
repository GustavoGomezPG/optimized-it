/**
 * OIT Single Testimonial -- scroll-triggered reveal + scrubbed circuit trace.
 *
 * The card stack (card, quote icon, quote text, attribution) plays as a
 * one-shot staged reveal when the section enters the viewport.
 *
 * The side circuits use a second ScrollTrigger with `scrub`: each side
 * mounts the shared circuit.json Lottie and the player's current frame is
 * tied 1:1 to scroll progress between
 *   start: card center is 100% (= 1 viewport height) BELOW viewport center
 *   end:   card center is 100% ABOVE viewport center
 * so the trace draws in as the user scrolls past the card and un-draws on
 * scroll back -- both circuits move in lockstep.
 *
 * Bails on prefers-reduced-motion / missing GSAP / ScrollTrigger -- the
 * section's static-image fallback for each circuit stays visible.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitTestimonialInit === '1') return;
    section.dataset.oitTestimonialInit = '1';

    if (!window.gsap || !window.ScrollTrigger) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) return;

    window.gsap.registerPlugin(window.ScrollTrigger);

    var card        = section.querySelector('.oit-testimonial__card');
    var quoteIcon   = section.querySelector('.oit-testimonial__quote-icon');
    var quoteText   = section.querySelector('.oit-testimonial__quote');
    var attribution = section.querySelector('.oit-testimonial__attribution');
    var circuits    = section.querySelectorAll('.oit-testimonial__circuit');

    // -- Staged entry reveal (one-shot) ---------------------------------
    if (card)        window.gsap.set(card,        { opacity: 0, y: 24, scale: 0.97 });
    if (quoteIcon)   window.gsap.set(quoteIcon,   { opacity: 0, scale: 0.6 });
    if (quoteText)   window.gsap.set(quoteText,   { opacity: 0, y: 16 });
    if (attribution) window.gsap.set(attribution, { opacity: 0, y: 12 });

    var tl = window.gsap.timeline({
      defaults: { ease: 'power3.out' },
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        invalidateOnRefresh: true,
      },
    });

    if (card) {
      tl.to(card, { opacity: 1, y: 0, scale: 1, duration: 0.65 }, 0);
    }
    if (quoteIcon) {
      tl.to(quoteIcon, { opacity: 1, scale: 1, duration: 0.4, ease: 'back.out(1.6)' }, 0.40);
    }
    if (quoteText) {
      tl.to(quoteText, { opacity: 1, y: 0, duration: 0.55 }, 0.45);
    }
    if (attribution) {
      tl.to(attribution, { opacity: 1, y: 0, duration: 0.4 }, 0.65);
    }

    // -- Circuits: scrubbed Lottie trace --------------------------------
    if (!circuits.length || !window.lottie) return;

    var circuitAnims = [];
    circuits.forEach(function (deco) {
      if (deco.dataset.oitLottieLoaded === '1') return;
      var url = deco.getAttribute('data-lottie-url');
      if (!url) return;

      // Pull the static <img> fallback so it doesn't sit beneath the
      // Lottie SVG and pre-render the fully drawn circuit.
      var fallback = deco.querySelector('img');
      if (fallback && fallback.parentNode === deco) {
        fallback.parentNode.removeChild(fallback);
      }

      deco.dataset.oitLottieLoaded = '1';

      try {
        var anim = window.lottie.loadAnimation({
          container: deco,
          renderer: 'svg',
          loop: false,
          autoplay: false,
          path: url,
        });
        circuitAnims.push(anim);
      } catch (e) { /* ignore */ }
    });

    if (!circuitAnims.length) return;

    // Scrub trigger -- card center crosses from one viewport below the
    // viewport's center to one viewport above it. progress 0 -> frame 0,
    // progress 1 -> last frame.
    var scrubTrigger = card || section;
    window.ScrollTrigger.create({
      trigger: scrubTrigger,
      start: '-=100% center',
      end: 'bottom center',
      scrub: true,
      markers: false,
      invalidateOnRefresh: true,
      onUpdate: function (self) {
        var p = self.progress;
        circuitAnims.forEach(function (a) {
          var total = a.totalFrames || (a.animationData && a.animationData.op) || 220;
          var frame = p * (total - 1);
          try { a.goToAndStop(frame, true); } catch (e) {}
        });
      },
    });
  }

  function boot() {
    document.querySelectorAll('.oit-testimonial').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
