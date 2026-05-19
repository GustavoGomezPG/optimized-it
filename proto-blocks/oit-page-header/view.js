/**
 * OIT Page Header — scroll-triggered entrance.
 *
 * Motion personality: Corporate / Premium, decelerated (power3.out).
 * Total runtime ~1.4 s, triggered the first time the section's top
 * crosses 80% of the viewport from below.
 *
 * Choreography:
 *
 *   0.00s  Card        opacity 0->1, y +30->0, scale 0.98->1     0.7s
 *   0.10s  Circuit     Lottie goToAndPlay(0) + opacity 0->0.6    0.9s
 *   0.10s  Icon badge  opacity 0->1, scale 0.9->1                0.6s
 *   0.20s  Wordmark    opacity 0->0.4, y +60->0                  0.9s
 *   0.30s  Breadcrumb  opacity 0->1, y +8->0 (stagger 0.05s)     0.4s
 *   0.40s  Title       SplitText words: opacity 0->1, y +20->0
 *                      (stagger 0.04s)                            0.55s
 *   0.75s  Subtitle    opacity 0->1, y +12->0                    0.5s
 *   0.90s  CTAs        opacity 0->1, scale 0.94->1 (stagger)     0.4s
 *
 * Safety nets:
 *   - prefers-reduced-motion   -> flip pre-state to done, no animation
 *   - GSAP / ScrollTrigger gone -> same: static reveal, no failures
 *   - SplitText gone           -> fall back to animating the whole title
 *                                 element instead of word-by-word
 */
(function () {
  'use strict';

  function init(section) {
    if (!section || section.dataset.oitPhInit === '1') return;
    section.dataset.oitPhInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-animate', 'done');
    }

    var card      = section.querySelector('.oit-page-header__card');
    var wordmark  = section.querySelector('.oit-page-header__wordmark');
    var crumbs    = section.querySelectorAll('.oit-page-header__crumb');
    var title     = section.querySelector('.oit-page-header__title');
    var subtitle  = section.querySelector('.oit-page-header__subtitle');
    var ctas      = section.querySelectorAll('.oit-page-header__cta');
    var iconBadge = section.querySelector('.oit-page-header__icon-badge');
    var deco      = section.querySelector('.oit-page-header__decoration');

    // The light theme variant of the decoration sits behind a darker
    // (filtered black) silhouette and only wants 10% opacity; the dark
    // variant keeps the original 60%. Detect via the modifier class on
    // the decoration element itself so the JS doesn't need to know
    // about the parent's theme toggle.
    var decoTargetOpacity = (deco && deco.classList.contains('oit-page-header__decoration--light')) ? 0.1 : 0.6;

    // Period accent (e.g. "...your business." -> red period) regardless of
    // whether the animation can run. Purely visual; matches oit-hero.
    if (title) wrapPeriods(title);

    // No GSAP / ScrollTrigger or reduced motion -> static reveal.
    if (!window.gsap || !window.ScrollTrigger || reduce) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);
    if (window.SplitText) {
      window.gsap.registerPlugin(window.SplitText);
    }

    // ---- Lottie wiring -----------------------------------------------------
    // Same pattern as oit-hero: pull the static <img> fallback before
    // loadAnimation() injects its <svg>, otherwise the fully-drawn fallback
    // sits behind the trace-in.

    var circuitAnim = null;
    if (deco && deco.dataset.oitLottieLoaded !== '1' && window.lottie) {
      var lottieUrl = deco.getAttribute('data-lottie-url');
      if (lottieUrl) {
        deco.dataset.oitLottieLoaded = '1';
        var fallback = deco.querySelector('img');
        if (fallback && fallback.parentNode === deco) {
          fallback.parentNode.removeChild(fallback);
        }
        try {
          circuitAnim = window.lottie.loadAnimation({
            container: deco,
            renderer: 'svg',
            loop: false,
            autoplay: false,
            path: lottieUrl,
          });
        } catch (e) {
          circuitAnim = null;
        }
      }
    }

    // ---- Title word split --------------------------------------------------
    // SplitText is loaded globally by the theme; if for any reason it isn't
    // there we degrade gracefully and animate the whole title as one unit.

    var titleTargets = title ? [title] : [];
    var titleSplit = null;
    if (title && window.SplitText) {
      try {
        titleSplit = new window.SplitText(title, {
          type: 'words',
          wordsClass: 'oit-page-header__word',
        });
        if (titleSplit.words && titleSplit.words.length) {
          titleTargets = titleSplit.words;
        }
      } catch (e) {
        titleSplit = null;
      }
    }

    // ---- Wordmark char split ----------------------------------------------
    // The wordmark is the giant background watermark. We split it into
    // individual chars so each letter can stagger in. The outer wrapper
    // (.oit-page-header__wordmark) stays at the CSS pre-state opacity 0
    // until ScrollTrigger fires, then reverts to its base opacity-40 from
    // the inner .__wordmark-text span -- chars themselves animate 0 -> 1,
    // so the perceived final opacity is 0.4 * 1 = 0.4 (per the design spec).

    var wordmarkChars = null;
    var wordmarkSplit = null;
    if (wordmark && window.SplitText) {
      var wordmarkText = wordmark.querySelector('.oit-page-header__wordmark-text');
      if (wordmarkText) {
        try {
          wordmarkSplit = new window.SplitText(wordmarkText, {
            type: 'chars',
            charsClass: 'oit-page-header__wordmark-char',
          });
          if (wordmarkSplit.chars && wordmarkSplit.chars.length) {
            wordmarkChars = wordmarkSplit.chars;
          }
        } catch (e) {
          wordmarkSplit = null;
        }
      }
    }

    // ---- Set initial states for everything that isn't covered by the CSS
    // pre-hide rule. The CSS just zeros opacity to avoid the FOUC flash;
    // GSAP needs the actual transforms so fromTo can interpolate.

    // Every gsap.set below also sets opacity: 0 inline. The CSS
    // pre-state hides these elements before JS runs, but reveal() flips
    // data-animate to "done" the moment ScrollTrigger fires -- without
    // an inline opacity 0, the element snaps to visible and the
    // subsequent opacity tween has nothing to fade from (a no-op
    // 1->1). Inline 0 wins on specificity so the fade actually plays.
    if (card)      window.gsap.set(card,      { y: 30, scale: 0.98, opacity: 0, transformOrigin: '50% 50%' });
    if (deco)      window.gsap.set(deco,      { opacity: 0 });
    if (wordmarkChars) {
      // SplitText path: each letter slides in individually.
      window.gsap.set(wordmarkChars, { opacity: 0, y: 40 });
    } else if (wordmark) {
      // Fallback: animate the whole wordmark wrapper.
      window.gsap.set(wordmark, { y: 60, opacity: 0 });
    }
    if (crumbs.length) window.gsap.set(crumbs, { y: 8, opacity: 0 });
    if (titleTargets.length) window.gsap.set(titleTargets, { y: 20, opacity: 0 });
    if (subtitle)  window.gsap.set(subtitle,  { y: 12, opacity: 0 });
    if (ctas.length) window.gsap.set(ctas,    { scale: 0.94, opacity: 0, transformOrigin: '50% 50%' });
    if (iconBadge) window.gsap.set(iconBadge, { scale: 0.9, opacity: 0, transformOrigin: '50% 50%' });

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
          // Flip pre-state off so GSAP fully owns visibility from here on.
          reveal();
          // Trace the circuit at the start of the reveal. goToAndPlay is
          // safe to call before the Lottie JSON has resolved; lottie-web
          // queues it.
          if (circuitAnim) {
            try { circuitAnim.goToAndPlay(0, true); } catch (e) {}
          }
        },
      },
    });

    if (card)      tl.to(card,      { opacity: 1, y: 0, scale: 1, duration: 0.7 }, 0);
    if (deco)      tl.to(deco,      { opacity: decoTargetOpacity, duration: 0.9 }, 0.10);
    if (iconBadge) tl.to(iconBadge, { opacity: 1, scale: 1, duration: 0.6 }, 0.10);
    if (wordmarkChars) {
      // Chars ride from opacity 0 -> 1 inside a wrapper whose own
      // opacity is 0.4 (CSS), so visible final = 0.4 per design.
      tl.to(wordmarkChars, {
        opacity: 1,
        y: 0,
        duration: 0.5,
        stagger: 0.04,
        ease: 'power3.out',
      }, 0.20);
    } else if (wordmark) {
      // Whole-wrapper fallback when SplitText isn't available.
      tl.to(wordmark, { opacity: 0.4, y: 0, duration: 0.9 }, 0.20);
    }
    if (crumbs.length) {
      tl.to(crumbs, { opacity: 1, y: 0, duration: 0.4, stagger: 0.05 }, 0.30);
    }
    if (titleTargets.length) {
      tl.to(titleTargets, {
        opacity: 1,
        y: 0,
        duration: 0.55,
        stagger: titleTargets.length > 1 ? 0.04 : 0,
      }, 0.40);
    }
    if (subtitle)  tl.to(subtitle,  { opacity: 1, y: 0, duration: 0.5 }, 0.75);
    if (ctas.length) {
      tl.to(ctas, { opacity: 1, scale: 1, duration: 0.4, stagger: 0.08 }, 0.90);
    }
  }

  /**
   * Walk every text node inside the title and wrap each "." in a
   * <span class="oit-page-header__accent text-brand-red"> so the brand
   * red period treatment renders without the editor having to apply it.
   *
   * Idempotent: bails on second run via a data flag, skips text already
   * wrapped, and runs BEFORE SplitText so the resulting spans become part
   * of the same word and travel together during the stagger.
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
        if (parent.classList && parent.classList.contains('oit-page-header__accent')) {
          return;
        }

        var parts = text.split(/(\.)/);
        if (parts.length < 2) return;

        var frag = document.createDocumentFragment();
        parts.forEach(function (part) {
          if (part === '.') {
            var span = document.createElement('span');
            span.className = 'oit-page-header__accent text-brand-red';
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
    document.querySelectorAll('.oit-page-header').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
