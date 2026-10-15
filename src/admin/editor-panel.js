/**
 * Pigeon Settings as a native block editor panel.
 *
 * The access dropdown edits the post's reserved tags in the editor (pigeon-free / pigeon-restricted),
 * so the Tags panel follows it as you choose and both save with the post in one request. The legacy
 * access meta is set to 0 on any change: from then on the tag alone says what the post is.
 *
 * window.pigeonEditorPanel comes from PigeonWP\Editor_Panel::enqueue().
 */
( function ( wp, settings ) {
	if ( ! wp || ! wp.plugins || ! wp.data || ! settings ) {
		return;
	}

	var el = wp.element.createElement;
	var components = wp.components;
	var Panel = ( wp.editor && wp.editor.PluginDocumentSettingPanel ) || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	var labels = settings.labels;
	var reserved = settings.reserved || {};
	var FREE = 1;
	var RESTRICTED = 2;
	var reservedIds = [ Number( reserved[ FREE ] ), Number( reserved[ RESTRICTED ] ) ];

	if ( ! Panel ) {
		return;
	}

	// The tag decides; restricted wins if both are set. Without a tag, an unmigrated post may still
	// carry its old meta-only override.
	function accessFor( tags, meta ) {
		var ids = ( tags || [] ).map( Number );

		if ( reserved[ RESTRICTED ] && -1 !== ids.indexOf( Number( reserved[ RESTRICTED ] ) ) ) {
			return RESTRICTED;
		}
		if ( reserved[ FREE ] && -1 !== ids.indexOf( Number( reserved[ FREE ] ) ) ) {
			return FREE;
		}

		var legacy = Number( meta && meta[ settings.accessKey ] );
		return FREE === legacy || RESTRICTED === legacy ? legacy : 0;
	}

	function PigeonSettingsPanel() {
		var post = wp.data.useSelect( function ( select ) {
			var editor = select( 'core/editor' );
			return {
				tags: editor.getEditedPostAttribute( 'tags' ),
				meta: editor.getEditedPostAttribute( 'meta' ) || {},
			};
		}, [] );
		var editPost = wp.data.useDispatch( 'core/editor' ).editPost;

		// No tags field means this post type isn't tagged over REST; nothing to edit.
		if ( ! Array.isArray( post.tags ) ) {
			return null;
		}

		function setAccess( value ) {
			var access = Number( value );
			var meta = {};
			var tags = post.tags.map( Number ).filter( function ( id ) {
				return -1 === reservedIds.indexOf( id );
			} );

			if ( access && reserved[ access ] ) {
				tags.push( Number( reserved[ access ] ) );
			}

			meta[ settings.accessKey ] = 0;
			editPost( { tags: tags, meta: meta } );
		}

		function setMeta( key, value ) {
			var meta = {};
			meta[ key ] = value;
			editPost( { meta: meta } );
		}

		var fields = [
			el( components.SelectControl, {
				key: 'access',
				label: labels.access,
				value: String( accessFor( post.tags, post.meta ) ),
				options: [
					{ value: '0', label: labels.rules },
					{ value: String( FREE ), label: labels.free },
					{ value: String( RESTRICTED ), label: labels.restricted },
				],
				help: labels.accessHelp,
				onChange: setAccess,
			} ),
		];

		if ( settings.pricing ) {
			fields.push(
				el( components.TextControl, {
					key: 'price',
					label: labels.price,
					help: labels.priceHelp,
					value: post.meta._wp_pigeon_content_price || '',
					onChange: function ( value ) {
						setMeta( '_wp_pigeon_content_price', value );
					},
				} )
			);
		}

		if ( settings.credits && settings.credits.length ) {
			fields.push(
				el( components.SelectControl, {
					key: 'value',
					label: labels.value,
					value: String( post.meta._wp_pigeon_content_value || 0 ),
					options: [ { value: '0', label: labels.valueFree } ].concat(
						settings.credits.map( function ( credit ) {
							return { value: String( credit.value ), label: credit.label };
						} )
					),
					onChange: function ( value ) {
						setMeta( '_wp_pigeon_content_value', Number( value ) );
					},
				} ),
				el( components.CheckboxControl, {
					key: 'prompt',
					label: labels.prompt,
					checked: 1 === Number( post.meta._wp_pigeon_content_prompt ),
					onChange: function ( checked ) {
						setMeta( '_wp_pigeon_content_prompt', checked ? 1 : 0 );
					},
				} )
			);
		}

		return el( Panel, { name: 'pigeon-settings', title: labels.title, className: 'pigeon-settings-panel' }, fields );
	}

	wp.plugins.registerPlugin( 'pigeon-settings', { render: PigeonSettingsPanel, icon: null } );
}( window.wp, window.pigeonEditorPanel ) );
