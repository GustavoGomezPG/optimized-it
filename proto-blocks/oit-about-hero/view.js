/**
 * OIT About Hero — scroll-triggered entrance.
 *
 * Motion personality: Corporate / Premium, decelerated (power3.out).
 * Total runtime ~1.5 s, triggered the first time the section's top
 * crosses 80% of the viewport from below.
 *
 * Choreography (offset / target / dur):
 *
 *   0.00s  Grid (whole)    opacity 0->1, y +20->0                    0.7s
 *   0.10s  Featured image  opacity 0->1, scale 0.96->1               0.7s
 *   0.20s  Wordmark chars  opacity 0->1, y +60->0 (stagger 0.04s)    0.5s
 *                          (across both primary and accent segments)
 *   0.30s  Breadcrumb      opacity 0->1, y +8->0 (stagger 0.05s)     0.4s
 *   0.45s  Title words     SplitText words, opacity 0->1, y +20->0   0.55s
 *   0.80s  Subtitle        opacity 0->1, y +12->0                    0.5s
 *   0.95s  Awards items    opacity 0->1, y +12->0 (stagger 0.06s)    0.45s
 *
 * Safety nets:
 *   - prefers-reduced-motion   -> flip pre-state to done, no animation
 *   - GSAP / ScrollTrigger gone -> same: static reveal, no failures
 *   - SplitText gone           -> animate whole title and whole
 *                                 wordmark wrappers instead of per
 *                                 word / per char
 *
 * Period accent: any "." inside the title gets wrapped in
 * <span class="text-brand-red">.</span> via wrapPeriods() so headlines
 * that end in a period render it in brand red without the editor
 * having to apply the span. Idempotent and runs before SplitText.
 */
(function () {
  'use strict';

  function init(section) {
    if (!section || section.dataset.oitAhInit === '1') return;
    section.dataset.oitAhInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-proto-animate', 'done');
    }

    var grid      = section.querySelector('.oit-about-hero__grid');
    var title     = section.querySelector('.oit-about-hero__title');
    var subtitle  = section.querySelector('.oit-about-hero__subtitle');
    var awards    = section.querySelectorAll('.oit-about-hero__award');
    var imageWrap = section.querySelector('.oit-about-hero__image-wrap');
    // Breadcrumb and wordmark markup come from the shared partial; the
    // class names stay oit-page-header__* so existing CSS keeps working.
    var crumbs       = section.querySelectorAll('.oit-page-header__crumb');
    var wordmark     = section.querySelector('.oit-page-header__wordmark');
    var wordmarkText = section.querySelectorAll('.oit-page-header__wordmark-text');

    // Period accent runs regardless of animation availability -- it's a
    // purely visual treatment for headlines that end in "." and should
    // render even when motion is disabled.
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

    // ---- Title word split --------------------------------------------------
    // SplitText is loaded globally by the theme; if for any reason it
    // isn't there we degrade gracefully and animate the whole title as
    // one unit.

    var titleTargets = title ? [title] : [];
    var titleSplit = null;
    if (title && window.SplitText) {
      try {
        titleSplit = new window.SplitText(title, {
          type: 'words',
          wordsClass: 'oit-about-hero__word',
        });
        if (titleSplit.words && titleSplit.words.length) {
          titleTargets = titleSplit.words;
        }
      } catch (e) {
        titleSplit = null;
      }
    }

    // ---- Wordmark char split -----------------------------------------------
    // Split both segments (primary + accent) into chars so each letter
    // staggers in as one continuous run. The final char opacity tweens
    // 0 -> 1 while the wrapper sits at opacity 0.4 (CSS) -- perceived
    // final = 0.4, matching spec.

    var wordmarkChars = [];
    var wordmarkSplits = [];
    if (wordmark && window.SplitText && wordmarkText.length) {
      wordmarkText.forEach(function (segEl) {
        try {
          var split = new window.SplitText(segEl, {
            type: 'chars',
            charsClass: 'oit-about-hero__wordmark-char',
          });
          if (split.chars && split.chars.length) {
            wordmarkSplits.push(split);
            for (var i = 0; i < split.chars.length; i++) {
              wordmarkChars.push(split.chars[i]);
            }
          }
        } catch (e) {
          // segment fell back to wrapper-level animation
        }
      });
    }

    // ---- Initial states for everything not covered by the CSS pre-hide.
    // Inline opacity 0 wins on specificity against the data-proto-animate="done"
    // flip, so the subsequent fade actually plays (a no-op 1->1 otherwise).

    if (grid)      window.gsap.set(grid,      { y: 20, opacity: 0 });
    if (imageWrap) window.gsap.set(imageWrap, { scale: 0.96, opacity: 0, transformOrigin: '50% 50%' });
    if (wordmarkChars.length) {
      window.gsap.set(wordmarkChars, { opacity: 0, y: 40 });
    } else if (wordmark) {
      window.gsap.set(wordmark, { y: 60, opacity: 0 });
    }
    if (crumbs.length) window.gsap.set(crumbs, { y: 8, opacity: 0 });
    if (titleTargets.length) window.gsap.set(titleTargets, { y: 20, opacity: 0 });
    if (subtitle)      window.gsap.set(subtitle, { y: 12, opacity: 0 });
    if (awards.length) window.gsap.set(awards,   { y: 12, opacity: 0 });

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

    if (grid)      tl.to(grid,      { opacity: 1, y: 0, duration: 0.7 }, 0);
    if (imageWrap) tl.to(imageWrap, { opacity: 1, scale: 1, duration: 0.7 }, 0.10);
    if (wordmarkChars.length) {
      tl.to(wordmarkChars, {
        opacity: 1,
        y: 0,
        duration: 0.5,
        stagger: 0.04,
      }, 0.20);
    } else if (wordmark) {
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
      }, 0.45);
    }
    if (subtitle) tl.to(subtitle, { opacity: 1, y: 0, duration: 0.5 }, 0.80);
    if (awards.length) {
      tl.to(awards, { opacity: 1, y: 0, duration: 0.45, stagger: 0.06 }, 0.95);
    }
  }

  /**
   * Walk every text node inside the title and wrap each "." in a
   * <span class="text-brand-red"> so the brand-red period treatment
   * renders without the editor having to apply it.
   *
   * Idempotent (bails on second run via data flag), skips text already
   * wrapped in a .text-brand-red span, and runs BEFORE SplitText so
   * the resulting spans become part of the same word and travel
   * together during the stagger.
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
        if (parent.classList && parent.classList.contains('text-brand-red')) {
          return;
        }

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
    document.querySelectorAll('.oit-about-hero').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
