/**
 * OIT Single Location -- scroll-triggered reveal + Leaflet map init.
 *
 * Two responsibilities, both view-side:
 *
 *   1. Scroll reveal -- same `power3.out` family as the other header
 *      blocks (breadcrumb / title / subtitle / contact rows / map).
 *      Falls back to a static reveal when GSAP/ScrollTrigger are
 *      missing or `prefers-reduced-motion` is set.
 *
 *   2. Leaflet map -- lazy-loads Leaflet 1.9 from unpkg (CSS + JS) the
 *      first time a .oit-single-location__map element is found in the
 *      DOM. Tiles come from the free CARTO basemap network; the theme
 *      is whichever the author picked via the `mapTheme` control,
 *      threaded into the container as `data-oit-map-theme`. Scroll-
 *      wheel zoom is disabled by default so the map doesn't hijack the
 *      page scroll; double-click and pinch-zoom still work.
 *
 * Choreography (offset / target / dur):
 *
 *   0.00s  Grid (whole)     opacity 0->1, y +20->0                  0.7s
 *   0.10s  Map wrap         opacity 0->1, scale 0.96->1             0.7s
 *   0.30s  Breadcrumb       opacity 0->1, y +8->0 (stagger 0.05s)   0.4s
 *   0.50s  Title words      SplitText words, opacity 0->1, y +20->0 0.55s
 *   0.80s  Subtitle         opacity 0->1, y +12->0                  0.5s
 *   0.95s  Contact rows     opacity 0->1, y +8->0 (stagger 0.08s)   0.4s
 *
 * Leaflet boot is independent of GSAP and runs immediately on
 * DOMContentLoaded, so the map is interactive even before the reveal
 * timeline plays.
 */
(function () {
  'use strict';

  var LEAFLET_VERSION = '1.9.4';
  var LEAFLET_CSS = 'https://unpkg.com/leaflet@' + LEAFLET_VERSION + '/dist/leaflet.css';
  var LEAFLET_JS  = 'https://unpkg.com/leaflet@' + LEAFLET_VERSION + '/dist/leaflet.js';

  // CARTO basemap tile URLs. {s} cycles a-d subdomains. Retina suffix
  // {r} resolves to "@2x" on high-DPI screens for crisp tiles.
  var TILE_URLS = {
    dark_all:           'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    dark_nolabels:      'https://{s}.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}{r}.png',
    voyager:            'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
    voyager_nolabels:   'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}{r}.png',
    positron:           'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
    positron_nolabels:  'https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}{r}.png',
  };

  var TILE_ATTRIBUTION =
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>';

  // ---- Leaflet lazy-load ----------------------------------------------------

  var leafletReady = null;

  function loadLeaflet() {
    if (window.L) return Promise.resolve(window.L);
    if (leafletReady) return leafletReady;

    leafletReady = new Promise(function (resolve, reject) {
      // CSS first so we don't get a layout flash when the JS injects
      // .leaflet-container with default zero-height styles.
      if (!document.querySelector('link[data-oit-leaflet]')) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = LEAFLET_CSS;
        link.dataset.oitLeaflet = '1';
        document.head.appendChild(link);
      }

      var script = document.createElement('script');
      script.src = LEAFLET_JS;
      script.async = true;
      script.onload = function () { resolve(window.L); };
      script.onerror = function () { reject(new Error('Leaflet failed to load')); };
      document.head.appendChild(script);
    });

    return leafletReady;
  }

  // ---- Map init -------------------------------------------------------------

  function initMap(container, L) {
    if (container.dataset.oitMapInit === '1') return;
    container.dataset.oitMapInit = '1';

    var raw = (container.getAttribute('data-oit-map-center') || '').split(',');
    var lat = parseFloat(raw[0]);
    var lng = parseFloat(raw[1]);
    if (isNaN(lat) || isNaN(lng)) {
      // Fall back to Pittsburgh if the author left garbage in the field.
      lat = 40.4406;
      lng = -79.9959;
    }

    var zoom = parseInt(container.getAttribute('data-oit-map-zoom'), 10);
    if (isNaN(zoom)) zoom = 12;

    var theme = container.getAttribute('data-oit-map-theme') || 'dark_all';
    var tileUrl = TILE_URLS[theme] || TILE_URLS.dark_all;

    var map = L.map(container, {
      center: [lat, lng],
      zoom: zoom,
      // Disable scroll-wheel zoom so the map doesn't hijack page
      // scrolling. Pan + double-click zoom + pinch still work.
      scrollWheelZoom: false,
      zoomControl: true,
      attributionControl: true,
    });

    L.tileLayer(tileUrl, {
      attribution: TILE_ATTRIBUTION,
      subdomains: 'abcd',
      maxZoom: 19,
      // The {r} placeholder resolves to "" or "@2x" based on devicePixelRatio.
      detectRetina: true,
    }).addTo(map);

    // Brand-red pin marker. divIcon lets us style it entirely in CSS
    // (see .oit-single-location__marker in style.css). The icon's
    // 28x28 size + 10px tail mean the anchor sits at bottom-center of
    // the tail, so the marker points exactly at the lat/lng.
    var icon = L.divIcon({
      className: 'oit-single-location__marker',
      iconSize: [28, 28],
      iconAnchor: [14, 38],
    });

    L.marker([lat, lng], { icon: icon }).addTo(map);
  }

  function bootMaps() {
    var containers = document.querySelectorAll('.oit-single-location__map[data-oit-map="1"]');
    if (!containers.length) return;

    loadLeaflet().then(function (L) {
      containers.forEach(function (c) { initMap(c, L); });
    }).catch(function () {
      // Leaflet didn't load -- container stays empty (light-grey
      // background from the wrap). Not worth surfacing to the user.
    });
  }

  // ---- Scroll reveal --------------------------------------------------------

  function initReveal(section) {
    if (!section || section.dataset.oitSlInit === '1') return;
    section.dataset.oitSlInit = '1';

    var reduce = window.matchMedia &&
      window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function reveal() {
      section.setAttribute('data-animate', 'done');
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

  function bootAll() {
    bootMaps();
    bootReveal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAll);
  } else {
    bootAll();
  }
})();
