/**
 * OptimizedIT site-wide init.
 *
 * Boots a single shared Lenis instance and exposes it on the window as
 *   window.oitLenis
 * so any block-level script (or theme code) can do `oitLenis.stop()`
 * / `oitLenis.start()` without re-instantiating its own Lenis.
 *
 * Loaded as `optimizedit-init` with `optimizedit-lenis` as a dep, so by
 * the time this file runs window.Lenis is guaranteed to exist.
 */
(function () {
  'use strict';

  if (!window.Lenis) {
    return;
  }

  var lenis = new window.Lenis({
    duration: 1.2,
    easing: function (t) { return Math.min(1, 1.001 - Math.pow(2, -10 * t)); },
    smoothWheel: true,
    smoothTouch: false,
  });

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }
  requestAnimationFrame(raf);

  window.oitLenis = lenis;
})();
