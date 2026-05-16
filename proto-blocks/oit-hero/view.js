/**
 * OIT Hero -- entrance choreography (GSAP).
 *
 * Motion personality: Corporate / Premium.
 *   - Decelerated easing (power3.out), no overshoot.
 *   - Staged reveal: media first as the establishing shot, then eyebrow,
 *     headline, CTA -- guiding the eye from photo to action.
 *   - Total runtime ~1.3 s. Long enough to feel intentional, short enough
 *     that the user isn't waiting to interact.
 *
 * Trigger contract with the intro screen (themes/optimizedit/scripts/oit-intro.js):
 *
 *   First visit:   intro plays, dispatches `oit:intro-complete` -> hero plays.
 *   Repeat visit:  <html> already carries `oit-intro-skip` (set inline in
 *                  <head> by functions.php) and no event will ever fire,
 *                  so we play as soon as the DOM is ready.
 *
 * Safety nets:
 *   - prefers-reduced-motion -> just strip the pre-hide; render statically.
 *   - GSAP missing            -> same: just strip the pre-hide.
 *   - 9 s hard timeout        -> if the event never fires for any reason,
 *                                we still reveal the hero.
 */
(function () {
  'use strict';

  function init(hero) {
    if (!hero || hero.dataset.oitHeroInit === '1') return;
    hero.dataset.oitHeroInit = '1';

    var html = document.documentElement;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      hero.setAttribute('data-animate', 'done');
    }

    var eyebrow = hero.querySelector('.oit-hero__eyebrow');
    var heading = hero.querySelector('.oit-hero__heading');
    var cta     = hero.querySelector('.oit-hero__cta');
    var media   = hero.querySelector('.oit-hero__media');
    var deco    = hero.querySelector('.oit-hero__decoration');

    // Always run -- the period coloring is purely visual and shouldn't
    // depend on whether the animation can play.
    if (heading) wrapPeriods(heading);

    // No GSAP or reduced motion -> static reveal. The decoration's static
    // SVG fallback (inside the deco container in template.php) stays
    // visible since we never load the Lottie player here.
    if (!window.gsap || reduce) {
      reveal();
      return;
    }

    // Load the circuit Lottie. lottie.loadAnimation() appends its own
    // <svg> into the container but does NOT remove pre-existing children,
    // so we explicitly pull the static <img> fallback first -- otherwise
    // the img keeps showing the fully drawn circuit underneath the
    // (initially empty) Lottie SVG and the trace-in effect is invisible.
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

    function play() {
      if (hero.dataset.oitHeroPlayed === '1') return;
      hero.dataset.oitHeroPlayed = '1';

      // Flip pre-state off so GSAP owns visibility from here on.
      reveal();

      // Trigger the circuit Lottie trace right at the start of the reveal.
      // Calling goToAndPlay before the JSON has resolved is safe -- the
      // lottie-web player queues the action and runs it on DOMLoaded. The
      // container fade-in (0.15s -> 0.95s, scheduled below) reveals the
      // trace as it draws in.
      if (circuitAnim) {
        try { circuitAnim.goToAndPlay(0, true); } catch (e) {}
      }

      var tl = window.gsap.timeline({ defaults: { ease: 'power3.out' } });

      // 1. Establishing shot: photo settles in (slight scale -> follow-through).
      if (media) {
        tl.fromTo(media,
          { opacity: 0, scale: 1.05 },
          { opacity: 1, scale: 1, duration: 0.9 },
          0
        );
      }

      // 2. Decorative circuit pattern eases in behind the photo. The
      //    Lottie trace was kicked off at the top of play() (above), so
      //    it's already drawing while this fade-in happens -- the user
      //    sees the trace progressively appear *through* the fade.
      if (deco) {
        tl.fromTo(deco,
          { opacity: 0 },
          { opacity: 0.6, duration: 0.8 },
          0.15
        );
      }

      // 3. Eyebrow rises into place -- anticipation for the headline.
      if (eyebrow) {
        tl.fromTo(eyebrow,
          { opacity: 0, y: 16 },
          { opacity: 1, y: 0, duration: 0.5 },
          0.25
        );
      }

      // 4. Headline lines stagger in (split by <br> if present, otherwise
      //    just the heading container).
      if (heading) {
        var lineTargets = splitHeadingLines(heading);
        tl.fromTo(lineTargets,
          { opacity: 0, y: 20 },
          { opacity: 1, y: 0, duration: 0.6, stagger: 0.08 },
          0.4
        );
      }

      // 5. CTA pops last -- the call to action arrives once attention has landed.
      if (cta) {
        tl.fromTo(cta,
          { opacity: 0, scale: 0.94 },
          { opacity: 1, scale: 1, duration: 0.4 },
          '-=0.15'
        );
      }
    }

    /**
     * Walk every text node inside the heading and wrap each "." in a
     * <span class="oit-hero__accent"> so the brand red period treatment
     * from the Figma comes through automatically -- the editor doesn't
     * have to remember to apply inline color.
     *
     * Idempotent: bails on second run via a data flag, and skips text
     * already nested inside an accent span.
     */
    function wrapPeriods(el) {
      if (el.dataset.oitPeriods === '1') return;
      el.dataset.oitPeriods = '1';

      function walk(node) {
        if (node.nodeType === 3) { // text node
          var text = node.nodeValue;
          if (text.indexOf('.') === -1) return;

          var parent = node.parentNode;
          if (!parent) return;
          if (parent.classList && parent.classList.contains('oit-hero__accent')) {
            return; // already wrapped
          }

          var parts = text.split(/(\.)/);
          if (parts.length < 2) return;

          var frag = document.createDocumentFragment();
          parts.forEach(function (part) {
            if (part === '.') {
              var span = document.createElement('span');
              // The Tailwind utility is what paints the period red; the
              // BEM class stays as a marker so this function can skip
              // already-wrapped periods on re-runs.
              span.className = 'oit-hero__accent text-brand-red';
              span.textContent = '.';
              frag.appendChild(span);
            } else if (part.length > 0) {
              frag.appendChild(document.createTextNode(part));
            }
          });
          parent.replaceChild(frag, node);
        } else if (node.nodeType === 1) { // element
          // Snapshot children before walking -- the walk mutates the tree.
          var kids = Array.prototype.slice.call(node.childNodes);
          kids.forEach(walk);
        }
      }
      walk(el);
    }

    /**
     * Wrap each line of the headline in a span we can animate independently.
     * The Figma headline uses `<br>` between lines, so we walk the children
     * and group runs of inline content between <br> tags.
     */
    function splitHeadingLines(el) {
      if (el.dataset.oitSplit === '1') {
        return el.querySelectorAll('.oit-hero__line');
      }

      var nodes = Array.from(el.childNodes);
      if (!nodes.length) return [el];

      var lines = [[]];
      nodes.forEach(function (n) {
        if (n.nodeType === 1 && n.tagName === 'BR') {
          lines.push([]);
          return;
        }
        lines[lines.length - 1].push(n);
      });

      // Skip splitting if there's only one line of content -- nothing to stagger.
      if (lines.length < 2) return [el];

      el.innerHTML = '';
      lines.forEach(function (group) {
        var span = document.createElement('span');
        span.className = 'oit-hero__line';
        span.style.display = 'block';
        group.forEach(function (n) { span.appendChild(n); });
        el.appendChild(span);
      });
      el.dataset.oitSplit = '1';
      return el.querySelectorAll('.oit-hero__line');
    }

    // ---- Trigger selection ------------------------------------------------
    //
    // If the intro was skipped this session, play right away. Otherwise wait
    // for the intro to finish. A safety timeout guarantees we never trap the
    // hero in its pre-state.

    if (html.classList.contains('oit-intro-skip')) {
      // DOM is already parsed by the time view.js runs in the footer, but
      // wrap in rAF so the browser has a frame to paint the pre-state first
      // (so the fade-in is visible rather than instantaneous).
      window.requestAnimationFrame(play);
    } else {
      document.addEventListener('oit:intro-complete', play, { once: true });
      window.setTimeout(play, 9000); // safety net
    }
  }

  function boot() {
    document.querySelectorAll('.oit-hero').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
