/**
 * OIT Single Location -- scroll-triggered reveal.
 *
 * The map is now a server-rendered Google Maps embed (a plain iframe;
 * see template.php), so there is no map JS, no Leaflet, and no tile CDN
 * to load -- this file is reveal-only.
 *
 * Scroll reveal uses the same `power3.out` family as the other header
 * blocks (breadcrumb / title / subtitle / contact rows / map). Falls
 * back to a static reveal when GSAP/ScrollTrigger are missing or
 * `prefers-reduced-motion` is set.
 *
 * Choreography (offset / target / dur):
 *
 *   0.00s  Grid (whole)     opacity 0->1, y +20->0                  0.7s
 *   0.10s  Map wrap         opacity 0->1, scale 0.96->1             0.7s
 *   0.30s  Breadcrumb       opacity 0->1, y +8->0 (stagger 0.05s)   0.4s
 *   0.50s  Title words      SplitText words, opacity 0->1, y +20->0 0.55s
 *   0.80s  Subtitle         opacity 0->1, y +12->0                  0.5s
 *   0.95s  Contact rows     opacity 0->1, y +8->0 (stagger 0.08s)   0.4s
 */
(function () {
  'use strict';

  // ---- Scroll reveal --------------------------------------------------------

  function initReveal(section) {
    if (!section || section.dataset.oitSlInit === '1') return;
    section.dataset.oitSlInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-proto-animate', 'done');
    }

    var grid     = section.querySelector('.oit-single-location__grid');
    var title    = section.querySelector('.oit-single-location__title');
    var subtitle = section.querySelector('.oit-single-location__subtitle');
    var rows     = section.querySelectorAll('.oit-single-location__row');
    var mapWrap  = section.querySelector('.oit-single-location__map-wrap');
    // Breadcrumb markup is rendered by the shared partial; its hook
    // class stays in the oit-page-header__ namespace for historical
    // compatibility.
    var crumbs   = section.querySelectorAll('.oit-page-header__crumb');

    if (!window.gsap || !window.ScrollTrigger || reduce) {
      reveal();
      return;
    }

    window.gsap.registerPlugin(window.ScrollTrigger);
    if (window.SplitText) window.gsap.registerPlugin(window.SplitText);

    var titleTargets = title ? [title] : [];
    var titleSplit = null;
    if (title && window.SplitText) {
      try {
        titleSplit = new window.SplitText(title, {
          type: 'words',
          wordsClass: 'oit-single-location__word',
        });
        if (titleSplit.words && titleSplit.words.length) {
          titleTargets = titleSplit.words;
        }
      } catch (e) {
        titleSplit = null;
      }
    }

    if (grid)              window.gsap.set(grid,    { y: 20, opacity: 0 });
    if (mapWrap)           window.gsap.set(mapWrap, { scale: 0.96, opacity: 0, transformOrigin: '50% 50%' });
    if (crumbs.length)     window.gsap.set(crumbs,  { y: 8, opacity: 0 });
    if (titleTargets.length) window.gsap.set(titleTargets, { y: 20, opacity: 0 });
    if (subtitle)          window.gsap.set(subtitle, { y: 12, opacity: 0 });
    if (rows.length)       window.gsap.set(rows,     { y: 8, opacity: 0 });

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

    if (grid)    tl.to(grid,    { opacity: 1, y: 0, duration: 0.7 }, 0);
    if (mapWrap) tl.to(mapWrap, { opacity: 1, scale: 1, duration: 0.7 }, 0.10);
    if (crumbs.length) {
      tl.to(crumbs, { opacity: 1, y: 0, duration: 0.4, stagger: 0.05 }, 0.30);
    }
    if (titleTargets.length) {
      tl.to(titleTargets, {
        opacity: 1,
        y: 0,
        duration: 0.55,
        stagger: titleTargets.length > 1 ? 0.04 : 0,
      }, 0.50);
    }
    if (subtitle)  tl.to(subtitle, { opacity: 1, y: 0, duration: 0.5 }, 0.80);
    if (rows.length) {
      tl.to(rows, { opacity: 1, y: 0, duration: 0.4, stagger: 0.08 }, 0.95);
    }
  }

  function bootReveal() {
    document.querySelectorAll('.oit-single-location').forEach(initReveal);
  }

  // ---- Map loading state ----------------------------------------------------

  /**
   * Resolve window.lottie. The lottie-lite build is enqueued globally by the
   * theme (footer), but load order vs this view script isn't guaranteed, so
   * poll briefly. Calls back with the lib, or null after ~4s (the static
   * circuit.svg fallback then stays until the map covers it).
   */
  function ensureLottie(cb) {
    if (window.lottie) { cb(window.lottie); return; }
    var tries = 0;
    var timer = setInterval(function () {
      if (window.lottie) {
        clearInterval(timer);
        cb(window.lottie);
      } else if (++tries > 40) {
        clearInterval(timer);
        cb(null);
      }
    }, 100);
  }

  function initMapLoader(wrap) {
    if (!wrap || wrap.dataset.oitMapLoaderInit === '1') return;
    wrap.dataset.oitMapLoaderInit = '1';

    var iframe = wrap.querySelector('.oit-single-location__map');
    var loader = wrap.querySelector('[data-oit-map-loader]');
    if (!iframe || !loader) return;

    var anim = null;
    var done = false;
    var reveal = function () {
      if (done) return;
      done = true;
      wrap.classList.add('is-map-loaded');
      // Stop the Lottie once the map is in, to free CPU.
      if (anim) {
        try { anim.destroy(); } catch (e) { /* noop */ }
        anim = null;
      }
    };

    // The map's load event is the precise signal (fires for cross-origin
    // iframes too). The CSS animation backstop also hides the loader at 8s,
    // so a missed/cached load can't leave it stuck.
    iframe.addEventListener('load', reveal);

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var url = loader.getAttribute('data-lottie-url');

    if (!reduce && url) {
      ensureLottie(function (lottie) {
        if (!lottie || done) return; // map already painted -> skip
        loader.classList.add('is-animated'); // hide static SVG fallback
        try {
          anim = lottie.loadAnimation({
            container: loader,
            renderer: 'svg',
            loop: false,
            autoplay: true,
            path: url,
            rendererSettings: {
              // Cover the whole card so the circuit fills it edge to edge
              // (the traces reach the borders) instead of a small centered
              // graphic.
              preserveAspectRatio: 'xMidYMid slice',
            },
          });
        } catch (e) {
          loader.classList.remove('is-animated'); // restore static fallback
        }
      });
    }
  }

  function bootMapLoaders() {
    document.querySelectorAll('.oit-single-location__map-wrap').forEach(initMapLoader);
  }

  function bootAll() {
    bootReveal();
    bootMapLoaders();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAll);
  } else {
    bootAll();
  }
})();
