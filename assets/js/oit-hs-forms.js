/**
 * OIT HubSpot forms loader.
 *
 * Loads HubSpot's *developer* embed script (once per portal) whenever a
 * `.hs-form-html` container is present, then lets HubSpot render the form as
 * inline HTML (the `.hsfc-*` markup) into it. The developer embed renders in
 * the light DOM (no cross-origin iframe), so the form is styled by our theme
 * CSS and is natively interactive/selectable in the editor.
 *
 * Runs on the front end AND inside the block-editor canvas iframe (enqueued via
 * enqueue_block_assets). A MutationObserver re-scans because the editor injects
 * the block preview after initial load.
 */
(function () {
	'use strict';

	function loadPortal(portalId) {
		if (!portalId) return;
		var id = 'hsforms-dev-' + portalId;
		if (document.getElementById(id)) return;
		var s = document.createElement('script');
		s.id = id;
		s.src = 'https://js.hsforms.net/forms/embed/developer/' + portalId + '.js';
		s.defer = true;
		document.head.appendChild(s);
	}

	function scan() {
		var hosts = document.querySelectorAll('.hs-form-html[data-portal-id]');
		if (!hosts.length) return;
		hosts.forEach(function (host) {
			loadPortal(host.getAttribute('data-portal-id'));
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', scan);
	} else {
		scan();
	}

	// The editor renders/re-renders block previews after load, so keep watching
	// for newly injected form containers.
	if (typeof MutationObserver !== 'undefined') {
		new MutationObserver(function () {
			scan();
		}).observe(document.documentElement, { childList: true, subtree: true });
	}
})();
