/**
 * OIT Text + Services Cards — scroll-triggered entrance.
 *
 * Motion personality matches oit-page-header / oit-two-col-header:
 * decelerated (power3.out), fires once at top 80% of viewport, total
 * runtime ~1.4 s.
 *
 * Choreography (offset / target / dur):
 *
 *   0.00s  Heading words   SplitText words, opacity 0->1, y +20->0   0.55s
 *                          (stagger 0.04s)
 *   0.20s  Body paragraphs opacity 0->1, y +12->0 (stagger 0.08s)    0.5s
 *   0.45s  Subhead         opacity 0->1, y +12->0                    0.5s
 *   0.60s  Cards           opacity 0->1, y +20->0, scale 0.97->1     0.5s
 *                          (stagger 0.07s)
 *
 * Safety nets:
 *   - prefers-reduced-motion        -> static reveal, no animation
 *   - GSAP / ScrollTrigger missing  -> same
 *   - SplitText missing             -> animate the whole heading as
 *                                      one unit instead of per-word
 *
 * The wrapPeriods() walker wraps every "." in the heading with a
 * <span class="text-brand-red"> so headlines like "...your network."
 * get the brand-red period treatment without the editor having to
 * apply it. Idempotent + runs before SplitText so the period spans
 * become part of their parent word.
 */
(function () {
  'use strict';

  function init(section) {
    if (!section || section.dataset.oitTscInit === '1') return;
    section.dataset.oitTscInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-proto-animate', 'done');
    }

    var heading = section.querySelector('.oit-text-services-cards__heading');
    var body    = section.querySelector('.oit-text-services-cards__body');
    var subhead = section.querySelector('.oit-text-services-cards__subhead');
    var cards   = section.querySelectorAll('.oit-text-services-cards__card');

    // Body paragraphs: target each <p> inside the body so multi-
    // paragraph copy staggers. If the editor didn't add paragraph
    // breaks (single <p>), the stagger collapses to a single tween.
    var bodyParas = body ? body.querySelectorAll('p') : [];
    if (!bodyParas.length && body) {
      bodyParas = [body];
    }

    if (heading) wrapPeriods(heading);

    if (!window.gsap || !window.ScrollTrigger || reduce) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);
    if (window.SplitText) {
      window.gsap.registerPlugin(window.SplitText);
    }

    // ---- Heading word split ------------------------------------------------

    var headingTargets = heading ? [heading] : [];
    var headingSplit = null;
    if (heading && window.SplitText) {
      try {
        headingSplit = new window.SplitText(heading, {
          type: 'words',
          wordsClass: 'oit-text-services-cards__word',
        });
        if (headingSplit.words && headingSplit.words.length) {
          headingTargets = headingSplit.words;
        }
      } catch (e) {
        headingSplit = null;
      }
    }

    // ---- Initial states ----------------------------------------------------
    // Inline opacity 0 wins on specificity against the data-proto-animate="done"
    // flip, so the subsequent fade actually plays (a no-op 1->1 otherwise).

    if (headingTargets.length) window.gsap.set(headingTargets, { y: 20, opacity: 0 });
    if (bodyParas.length)      window.gsap.set(bodyParas,      { y: 12, opacity: 0 });
    if (subhead)               window.gsap.set(subhead,        { y: 12, opacity: 0 });
    if (cards.length)          window.gsap.set(cards,          { y: 20, scale: 0.97, opacity: 0, transformOrigin: '50% 50%' });

    // ---- Timeline ----------------------------------------------------------

    var tl = window.gsap.timeline({
      defaults: { ease: 'power3.out' },
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        toggleActions: 'play none none none',
        invalidateOnRefresh: true,
        once: true,
        onEnter: function () {
          reveal();
        },
      },
    });

    if (headingTargets.length) {
      tl.to(headingTargets, {
        opacity: 1,
        y: 0,
        duration: 0.55,
        stagger: headingTargets.length > 1 ? 0.04 : 0,
      }, 0);
    }
    if (bodyParas.length) {
      tl.to(bodyParas, { opacity: 1, y: 0, duration: 0.5, stagger: 0.08 }, 0.20);
    }
    if (subhead) tl.to(subhead, { opacity: 1, y: 0, duration: 0.5 }, 0.45);
    if (cards.length) {
      tl.to(cards, {
        opacity: 1,
        y: 0,
        scale: 1,
        duration: 0.5,
        stagger: 0.07,
      }, 0.60);
    }
  }

  /**
   * Walk every text node inside the heading and wrap each "." in a
   * <span class="text-brand-red">. Idempotent + skips already-wrapped
   * text + runs before SplitText.
   */
  function wrapPeriods(el) {
    if (el.dataset.oitPeriods === '1') return;
    el.dataset.oitPeriods = '1';

    function walk(node) {
      if (node.nodeType === 3) {
        var text = node.nodeValue;
        if (text.indexOf('.') === -1) return;
        var parent = node.parentNode;
        if (!parent) return;
        if (parent.classList && parent.classList.contains('text-brand-red')) return;
        var parts = text.split(/(\.)/);
        if (parts.length < 2) return;
        var frag = document.createDocumentFragment();
        parts.forEach(function (part) {
          if (part === '.') {
            var span = document.createElement('span');
            span.className = 'text-brand-red';
            span.textContent = '.';
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
    document.querySelectorAll('.oit-text-services-cards').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
