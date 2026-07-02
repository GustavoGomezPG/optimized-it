(function () {
  'use strict';

  function initNav(nav) {
    if (!nav || nav.dataset.oitInit === '1') return;
    nav.dataset.oitInit = '1';

    var toggle = nav.querySelector('.oit-nav__toggle');
    var panelId = toggle ? toggle.getAttribute('aria-controls') : null;
    var panel = panelId ? document.getElementById(panelId) : null;

    function setPanelState(open) {
      if (!panel || !toggle) return;
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      panel.setAttribute('aria-hidden', open ? 'false' : 'true');
      // Full-viewport overlay: lock page scroll while open so the content
      // behind the menu can't scroll (the panel scrolls internally instead).
      // The CSS class sets overflow:hidden as a fallback, but this site drives
      // scrolling with Lenis (window.oitLenis), which bypasses overflow -- so
      // pause/resume Lenis too, or the page still scrolls behind the menu.
      document.documentElement.classList.toggle('oit-nav-locked', open);
      if (window.oitLenis) {
        if (open) {
          window.oitLenis.stop();
        } else {
          window.oitLenis.start();
        }
      }
    }

    if (toggle && panel) {
      toggle.addEventListener('click', function () {
        var open = toggle.getAttribute('aria-expanded') === 'true';
        setPanelState(!open);
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
          setPanelState(false);
          toggle.focus();
        }
      });

      var triggers = panel.querySelectorAll('.oit-nav__panel-trigger');
      triggers.forEach(function (trigger) {
        var subId = trigger.getAttribute('aria-controls');
        var sub = subId ? document.getElementById(subId) : null;
        if (!sub) return;
        trigger.addEventListener('click', function () {
          var open = trigger.getAttribute('aria-expanded') === 'true';
          trigger.setAttribute('aria-expanded', open ? 'false' : 'true');
          sub.setAttribute('aria-hidden', open ? 'true' : 'false');
        });
      });
    }

    var items = nav.querySelectorAll('.oit-nav__item.has-submenu');
    items.forEach(function (item) {
      var link = item.querySelector('.oit-nav__link');
      var submenuWrap = item.querySelector('.oit-nav__submenu-wrap');
      if (!link || !submenuWrap) return;

      item.addEventListener('mouseenter', function () {
        link.setAttribute('aria-expanded', 'true');
      });
      item.addEventListener('mouseleave', function () {
        link.setAttribute('aria-expanded', 'false');
      });
    });
  }

  function bootAll() {
    document.querySelectorAll('.oit-nav').forEach(initNav);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAll);
  } else {
    bootAll();
  }
})();
