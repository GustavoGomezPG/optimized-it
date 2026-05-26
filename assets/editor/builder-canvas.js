/**
 * Builder Canvas — editor enhancement.
 *
 * Triggers on EITHER of the following:
 *
 *   - The page is using the "Builder Canvas" template (slug:
 *     page-builder), OR
 *   - The post is an `oit_resource` or a standard blog `post` (their
 *     single templates always use the builder-canvas behavior so the
 *     title can be edited from the sidebar while the canvas hosts only
 *     blocks -- the oit-blog-header / oit-breadcrumbs-eyebrow-title
 *     block renders the visible title on the front end instead).
 *
 * When active, the script:
 *
 *   1. Toggles an `oit-builder-canvas` class on the editor chrome body
 *      AND the iframed canvas body. The matching CSS in
 *      builder-canvas.css uses that class to hide WP's default post-
 *      title input so the canvas is reserved for blocks.
 *
 *   2. Registers a "Page Title" / "Resource Title" panel in the
 *      document settings sidebar with a TextControl bound to the
 *      post's title entity, so authors still have a place to set/edit
 *      the title.
 *
 * The title field stays in the database -- slug, menus, breadcrumbs,
 * SEO plugins and the browser tab title all continue to work normally.
 */
( function () {
	var plugins = window.wp && window.wp.plugins;
	var components = window.wp && window.wp.components;
	var data = window.wp && window.wp.data;
	var element = window.wp && window.wp.element;
	var i18n = window.wp && window.wp.i18n;

	// PluginDocumentSettingPanel lived in @wordpress/edit-post historically
	// and moved to @wordpress/editor in newer versions. Accept either.
	var PluginDocumentSettingPanel =
		( window.wp.editor && window.wp.editor.PluginDocumentSettingPanel ) ||
		( window.wp.editPost && window.wp.editPost.PluginDocumentSettingPanel );

	if ( ! plugins || ! components || ! data || ! element || ! i18n || ! PluginDocumentSettingPanel ) {
		return;
	}

	var registerPlugin = plugins.registerPlugin;
	var TextControl = components.TextControl;
	var useSelect = data.useSelect;
	var useDispatch = data.useDispatch;
	var el = element.createElement;
	var useEffect = element.useEffect;
	var __ = i18n.__;

	var BUILDER_TEMPLATE = 'page-builder';
	var BUILDER_POST_TYPES = [ 'oit_resource', 'post' ];
	var BODY_CLASS = 'oit-builder-canvas';

	function setClassOn( node, on ) {
		if ( node && node.classList ) {
			node.classList.toggle( BODY_CLASS, on );
		}
	}

	function syncBodies( on ) {
		setClassOn( document.body, on );
		var iframe = document.querySelector( 'iframe[name="editor-canvas"]' );
		if ( ! iframe ) {
			return;
		}
		try {
			setClassOn( iframe.contentDocument && iframe.contentDocument.body, on );
		} catch ( e ) {
			// Same-origin in practice; swallow defensively so a transient
			// access error never blocks the editor from rendering.
		}
	}

	function BuilderCanvasPanel() {
		var state = useSelect( function ( select ) {
			var editor = select( 'core/editor' );
			return {
				title: editor.getEditedPostAttribute( 'title' ) || '',
				template: editor.getEditedPostAttribute( 'template' ) || '',
				postType: editor.getCurrentPostType() || '',
			};
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;

		var isBuilder = state.template === BUILDER_TEMPLATE ||
			BUILDER_POST_TYPES.indexOf( state.postType ) !== -1;

		useEffect( function () {
			syncBodies( isBuilder );

			if ( ! isBuilder ) {
				return undefined;
			}

			// The canvas iframe mounts asynchronously and can re-mount when
			// the user switches devices/preview modes. Watch for DOM
			// changes briefly so the class survives those transitions.
			var observer = new MutationObserver( function () {
				syncBodies( true );
			} );
			observer.observe( document.body, { childList: true, subtree: true } );

			return function () {
				observer.disconnect();
				syncBodies( false );
			};
		}, [ isBuilder ] );

		if ( ! isBuilder ) {
			return null;
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'oit-builder-canvas',
				title: __( 'Page Title', 'optimizedit' ),
				className: 'oit-builder-canvas-panel',
			},
			el( TextControl, {
				label: __( 'Title', 'optimizedit' ),
				value: state.title,
				onChange: function ( next ) {
					editPost( { title: next } );
				},
				help: __(
					'Hidden from the canvas while the Builder Canvas template is in use. Still used for the slug, menus, browser tab, and SEO.',
					'optimizedit'
				),
			} )
		);
	}

	registerPlugin( 'oit-builder-canvas', { render: BuilderCanvasPanel } );
} )();
