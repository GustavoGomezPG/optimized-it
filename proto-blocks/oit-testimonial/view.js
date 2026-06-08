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

  // -- SmileBack review carousel (independent of GSAP) --------------------
  //
  // Rotates the stacked review slides. Auto-advances every data-autoplay ms
  // (skipped under prefers-reduced-motion), pauses on hover/focus, and is
  // driven by the prev/next arrows + dots. Visibility/active state is a
  // [data-active] attribute the CSS keys off of, so it degrades to the
  // first slide shown if this script never runs.
  function initCarousel(carousel) {
    if (carousel.dataset.oitCarouselInit === '1') return;
    carousel.dataset.oitCarouselInit = '1';

    var slides = Array.prototype.slice.call(carousel.querySelectorAll('.oit-testimonial__slide'));
    if (slides.length < 2) return;

    var dots   = Array.prototype.slice.call(carousel.querySelectorAll('.oit-testimonial__dot'));
    var arrows = Array.prototype.slice.call(carousel.querySelectorAll('.oit-testimonial__arrow'));
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var autoplayMs = parseInt(carousel.getAttribute('data-autoplay'), 10) || 0;
    var current = 0;
    var timer = null;

    function show(idx) {
      current = (idx + slides.length) % slides.length;
      slides.forEach(function (s, i) {
        if (i === current) { s.setAttribute('data-active', '1'); s.removeAttribute('aria-hidden'); }
        else { s.removeAttribute('data-active'); s.setAttribute('aria-hidden', 'true'); }
      });
      dots.forEach(function (d, i) {
        if (i === current) d.setAttribute('data-active', '1'); else d.removeAttribute('data-active');
      });
    }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function start() { if (!reduce && autoplayMs > 0) { stop(); timer = setInterval(function () { show(current + 1); }, autoplayMs); } }

    arrows.forEach(function (btn) {
      var dir = parseInt(btn.getAttribute('data-dir'), 10) || 1;
      btn.addEventListener('click', function () { stop(); show(current + dir); start(); });
    });
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () { stop(); show(parseInt(dot.getAttribute('data-index'), 10) || 0); start(); });
    });

    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    carousel.addEventListener('focusin', stop);
    carousel.addEventListener('focusout', start);

    show(0);
    start();
  }

  function boot() {
    document.querySelectorAll('.oit-testimonial').forEach(init);
    document.querySelectorAll('.oit-testimonial__carousel').forEach(initCarousel);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
