/**
 * OIT HubSpot forms loader.
 *
 * Loads HubSpot's embed script (once per portal) whenever a `.hs-form-frame`
 * is present, then lets HubSpot render the form into it. Runs on the front end
 * AND inside the block-editor canvas iframe (enqueued via enqueue_block_assets),
 * so the form is visible while editing. A MutationObserver re-scans because the
 * editor injects the block preview after initial load.
 */
(function () {
	'use strict';

	function loadPortal(portalId) {
		if (!portalId) return;
		var id = 'hsforms-embed-' + portalId;
		if (document.getElementById(id)) return;
		var s = document.createElement('script');
		s.id = id;
		s.src = 'https://js.hsforms.net/forms/embed/' + portalId + '.js';
		s.defer = true;
		document.head.appendChild(s);
	}

	function scan() {
		var frames = document.querySelectorAll('.hs-form-frame[data-portal-id]');
		if (!frames.length) return;
		frames.forEach(function (frame) {
			loadPortal(frame.getAttribute('data-portal-id'));
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', scan);
	} else {
		scan();
	}

	// The editor renders block previews after load, and re-renders on changes,
	// so keep watching for newly injected frames.
	if (typeof MutationObserver !== 'undefined') {
		var observer = new MutationObserver(function () {
			scan();
		});
		observer.observe(document.documentElement, {
			childList: true,
			subtree: true,
		});
	}
})();
