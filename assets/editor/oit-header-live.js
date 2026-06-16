/**
 * OIT header live preview.
 *
 * The oit-blog-header and oit-breadcrumbs-eyebrow-title blocks auto-derive
 * their title, breadcrumb crumb and category/eyebrow from the current post.
 * The Proto-Blocks editor preview renders server-side and can only read
 * SAVED values, so a brand-new (or mid-edit) post shows placeholders.
 *
 * This mirrors the LIVE editor state -- the post title and its assigned
 * terms -- into the canvas as the author types, the same way the document
 * bar and document sidebar update. Editor-only and display-only: it never
 * writes to the post, and bails quietly if @wordpress/data is unavailable.
 */
( function () {
	'use strict';

	var data = window.wp && window.wp.data;
	if ( ! data || ! data.select || ! data.subscribe ) {
		return;
	}

	function sel( store ) {
		try {
			return data.select( store );
		} catch ( e ) {
			return null;
		}
	}

	function liveTitle() {
		var ed = sel( 'core/editor' );
		var t = ed ? ed.getEditedPostAttribute( 'title' ) || '' : '';
		return String( t ).trim();
	}

	// Resolve assigned term names for a taxonomy. Returns an array (possibly
	// empty) when resolvable, or null when the editor stores aren't ready.
	// getEntityRecord triggers a resolver; once terms load, the store change
	// re-fires our subscriber so names fill in.
	function termNames( attrKey, taxonomy ) {
		var ed = sel( 'core/editor' );
		var core = sel( 'core' );
		if ( ! ed || ! core ) {
			return null;
		}
		var ids = ed.getEditedPostAttribute( attrKey );
		if ( ! Array.isArray( ids ) ) {
			return null;
		}
		if ( ! ids.length ) {
			return [];
		}
		var names = [];
		ids.forEach( function ( id ) {
			var term = core.getEntityRecord( 'taxonomy', taxonomy, id );
			if ( term && term.name ) {
				names.push( term.name );
			}
		} );
		return names;
	}

	// The block preview lives inside the editor-canvas iframe (modern WP);
	// fall back to the main document for non-iframed editors. Re-resolved on
	// every apply so a remounted iframe is picked up.
	function canvasDocs() {
		var docs = [];
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( iframe ) {
			try {
				if ( iframe.contentDocument ) {
					docs.push( iframe.contentDocument );
				}
			} catch ( e ) {}
		}
		docs.push( document );
		return docs;
	}

	function escapeHtml( s ) {
		return String( s ).replace( /[&<>"']/g, function ( c ) {
			return {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#39;',
			}[ c ];
		} );
	}

	// Only mutate when the value actually changes -- this keeps the
	// MutationObserver below from looping on our own edits.
	function setText( el, text ) {
		if ( el && el.textContent !== text ) {
			el.textContent = text;
		}
	}

	function applyTo( doc ) {
		var title = liveTitle();

		doc.querySelectorAll( '.oit-blog-header' ).forEach( function ( hdr ) {
			setText( hdr.querySelector( '.oit-blog-header__title' ), title || 'Blog Post Title' );
			setText( hdr.querySelector( '.oit-blog-header__crumbs .text-brand-red' ), title || 'Blog Post Title' );

			var ul = hdr.querySelector( '.oit-blog-header__tags' );
			if ( ul ) {
				var names = termNames( 'categories', 'category' );
				if ( names && names.length ) {
					var html = names
						.map( function ( n ) {
							return (
								'<li class="oit-blog-header__tag inline-flex items-center rounded-full bg-light-grey px-4 py-2 font-grotesk font-medium uppercase text-[14px] leading-[1.4] tracking-wide text-brand-black">' +
								escapeHtml( n ) +
								'</li>'
							);
						} )
						.join( '' );
					if ( ul.innerHTML !== html ) {
						ul.innerHTML = html;
					}
				}
			}
		} );

		doc.querySelectorAll( '.oit-bet' ).forEach( function ( hdr ) {
			setText( hdr.querySelector( '.oit-bet__title' ), title || 'Resource Title' );
			setText( hdr.querySelector( '.oit-bet__crumbs .text-brand-red' ), title || 'Resource Title' );

			var eyebrow = hdr.querySelector( '.oit-bet__eyebrow' );
			if ( eyebrow ) {
				var rt = termNames( 'resource_type', 'resource_type' );
				if ( rt && rt.length ) {
					setText( eyebrow, rt[ 0 ] );
				}
			}
		} );
	}

	function apply() {
		canvasDocs().forEach( applyTo );
	}

	// Coalesce the frequent store/DOM notifications into one apply per frame.
	var scheduled = false;
	function schedule() {
		if ( scheduled ) {
			return;
		}
		scheduled = true;
		window.setTimeout( function () {
			scheduled = false;
			apply();
		}, 16 );
	}

	// 1) Live editor state (title typed, terms assigned).
	data.subscribe( schedule );

	// 2) Proto-Blocks rebuilds the block preview HTML on its own renders,
	//    which would wipe our text -- re-apply when the canvas mutates.
	//    Re-bind if the canvas iframe (re)mounts.
	var observedBody = null;
	function ensureObserver() {
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		var body = iframe && iframe.contentDocument ? iframe.contentDocument.body : document.body;
		if ( ! body || body === observedBody ) {
			return;
		}
		observedBody = body;
		try {
			new MutationObserver( schedule ).observe( body, {
				childList: true,
				subtree: true,
				characterData: true,
			} );
		} catch ( e ) {}
		schedule();
	}

	var ticks = 0;
	var poll = window.setInterval( function () {
		ensureObserver();
		if ( ++ticks > 120 ) {
			window.clearInterval( poll );
		}
	}, 250 );

	if ( document.readyState !== 'loading' ) {
		ensureObserver();
		apply();
	} else {
		document.addEventListener( 'DOMContentLoaded', function () {
			ensureObserver();
			apply();
		} );
	}
} )();
