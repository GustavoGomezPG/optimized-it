/**
 * OIT Testimonial Callout -- scroll-triggered reveal.
 *
 * Corporate / Premium archetype, power3.out, ~1.4s total, staged.
 *
 *   0.00s  Card        opacity 0->1, y +30->0, scale 0.98->1     0.7s
 *   0.15s  Quote frame opacity 0->1, x +24->0                    0.6s
 *   0.30s  Headline    opacity 0->1, y +16->0                    0.55s
 *   0.45s  Steps       opacity 0->1, x +16->0  (stagger 0.08s)   0.45s
 *   0.55s  Quote mark  opacity 0->1, scale 0.6->1                0.5s
 *   0.65s  Quote text  opacity 0->1, y +12->0                    0.5s
 *   0.85s  CTA         opacity 0->1, scale 0.94->1               0.4s
 *   0.95s  Attribution opacity 0->1, y +8->0                     0.45s
 *
 * Bails on prefers-reduced-motion / missing GSAP / ScrollTrigger — the
 * card renders fully visible in those cases.
 *
 * Also runs the same colon-accent auto-wrap as oit-featured-section so
 * the trailing ":" in the headline paints brand red without the editor
 * having to remember to apply it.
 */
(function () {
  'use strict';

  function init(section) {
    if (section.dataset.oitTcInit === '1') return;
    section.dataset.oitTcInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-animate', 'done');
    }

    var card        = section.querySelector('.oit-testimonial-callout__card');
    var headline    = section.querySelector('.oit-testimonial-callout__headline');
    var steps       = section.querySelectorAll('.oit-testimonial-callout__step');
    var cta         = section.querySelector('.oit-testimonial-callout__cta');
    var quoteFrame  = section.querySelector('.oit-testimonial-callout__quote-frame');
    var quoteMark   = section.querySelector('.oit-testimonial-callout__quote-mark');
    var quote       = section.querySelector('.oit-testimonial-callout__quote');
    var attribution = section.querySelector('.oit-testimonial-callout__attribution');

    // Brand colon accent — runs whether or not GSAP can play.
    if (headline) wrapColons(headline);

    if (!window.gsap || !window.ScrollTrigger || reduce) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);

    if (card)        window.gsap.set(card,        { y: 30, scale: 0.98, transformOrigin: '50% 50%' });
    if (quoteFrame)  window.gsap.set(quoteFrame,  { x: 24 });
    if (headline)    window.gsap.set(headline,    { y: 16 });
    if (steps.length) window.gsap.set(steps,      { x: 16 });
    if (cta)         window.gsap.set(cta,         { scale: 0.94, transformOrigin: '50% 50%' });
    // Quote mark is absolutely-positioned over the figure's top-left
    // padding -- a scale/translate animation would reflow oddly against
    // the frame edge, so we keep it to a simple opacity fade.
    if (quote)       window.gsap.set(quote,       { y: 12 });
    if (attribution) window.gsap.set(attribution, { y: 8 });

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

    if (card)        tl.to(card,        { opacity: 1, y: 0, scale: 1, duration: 0.7 }, 0);
    if (quoteFrame)  tl.to(quoteFrame,  { opacity: 1, x: 0, duration: 0.6 }, 0.15);
    if (headline)    tl.to(headline,    { opacity: 1, y: 0, duration: 0.55 }, 0.30);
    if (steps.length) tl.to(steps,      { opacity: 1, x: 0, duration: 0.45, stagger: 0.08 }, 0.45);
    if (quoteMark)   tl.to(quoteMark,   { opacity: 1, duration: 0.5 }, 0.55);
    if (quote)       tl.to(quote,       { opacity: 1, y: 0, duration: 0.5 }, 0.65);
    if (cta)         tl.to(cta,         { opacity: 1, scale: 1, duration: 0.4 }, 0.85);
    if (attribution) tl.to(attribution, { opacity: 1, y: 0, duration: 0.45 }, 0.95);
  }

  /**
   * Walk every text node inside the headline and wrap each ":" in a
   * <span class="text-brand-red"> so the brand colon accent renders
   * automatically. Idempotent: bails on a second run via a data flag
   * and skips text already nested inside a wrap span.
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
        if (parent.classList && parent.classList.contains('oit-testimonial-callout__accent')) {
          return;
        }

        var parts = text.split(/(:)/);
        if (parts.length < 2) return;

        var frag = document.createDocumentFragment();
        parts.forEach(function (part) {
          if (part === ':') {
            var span = document.createElement('span');
            span.className = 'oit-testimonial-callout__accent text-brand-red';
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
    document.querySelectorAll('.oit-testimonial-callout').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
