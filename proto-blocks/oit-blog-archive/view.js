/**
 * OIT Blog Archive -- dropdown behavior.
 *
 * The filter/sort dropdowns are native <details> elements, which do NOT
 * close when clicking elsewhere on the page. Delegate a document-level
 * click handler that closes any open dropdown the click didn't land in
 * (this also closes the other dropdown when opening one), plus Escape
 * to dismiss.
 */
(function () {
  'use strict';

  function closeAll(except) {
    document.querySelectorAll('details.oit-blog-archive__dropdown[open]').forEach(function (d) {
      if (d !== except) {
        d.removeAttribute('open');
      }
    });
  }

  document.addEventListener('click', function (e) {
    var inside = e.target && e.target.closest
      ? e.target.closest('details.oit-blog-archive__dropdown')
      : null;
    closeAll(inside);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAll(null);
    }
  });
})();
