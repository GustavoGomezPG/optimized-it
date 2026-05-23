/**
 * OIT Form -- submit button chevron decoration.
 *
 * Gravity Forms renders the submit as a bare `<input type="submit">`,
 * which can't host child SVG. To get the brand's trailing double-
 * chevron we paint an SVG as a positioned sibling overlaying the
 * input's right edge. `pointer-events: none` keeps clicks falling
 * through to the input itself.
 *
 * Re-runs on `gform_post_render` so AJAX-submitted forms (which GF
 * re-renders into the same wrapper) still get the chevron after
 * confirmation/back navigation.
 */
(function () {
  'use strict';

  var CHEVRON_SVG =
    '<svg viewBox="0 0 13 12" fill="currentColor" aria-hidden="true">' +
    '<path d="M0 1.41L1.36689 0L7.18345 6L1.36689 12L0 10.59L4.43997 6L0 1.41ZM5.81656 1.41L7.18345 0L13 6L7.18345 12L5.81656 10.59L10.2565 6L5.81656 1.41Z"/>' +
    '</svg>';

  function decorate(submit) {
    if (!submit || submit.dataset.oitFormChevron === '1') return;
    submit.dataset.oitFormChevron = '1';

    var footer = submit.parentNode;
    if (!footer || !footer.classList.contains('gform_footer')) return;

    var chevron = document.createElement('span');
    chevron.className = 'oit-form__chevron';
    chevron.setAttribute('aria-hidden', 'true');
    chevron.innerHTML = CHEVRON_SVG;
    footer.appendChild(chevron);
  }

  function bootstrap(root) {
    var scope = root || document;
    scope
      .querySelectorAll('.oit-form__card .gform_footer input[type="submit"]')
      .forEach(decorate);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { bootstrap(); });
  } else {
    bootstrap();
  }

  // GF AJAX re-render hook.
  if (window.jQuery) {
    window.jQuery(document).on('gform_post_render', function () {
      bootstrap();
    });
  }
})();
