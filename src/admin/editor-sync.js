/**
 * Block editor: keep the Pigeon Settings box and the Tags panel in step.
 *
 * The block editor saves the post over REST, then posts the meta boxes separately. Pigeon's box
 * sets the access by adding or removing a reserved tag on that second request, which the editor
 * never re-reads: the Tags panel would show stale tags, and the next save would send them back,
 * dropping the override. After each meta box save this re-reads the post's tags into the editor
 * (without marking the post dirty) and points the dropdown at whatever the tags now say.
 *
 * pigeonEditorSync.reserved = { "1": <pigeon-free term id>, "2": <pigeon-restricted term id> }.
 */
( function ( wp, settings ) {
	if ( ! wp || ! wp.data || ! wp.apiFetch || ! settings ) {
		return;
	}

	var reserved = settings.reserved || {};
	var wasSaving = false;

	// The access the post's tags gave at load or at the last refresh. Read from the editor's copy
	// once it exists; by the time a meta box save finishes, that copy already reflects the REST
	// save, so it can't tell us what the tags said before.
	var lastTagAccess = null;

	function accessFromTags( tags ) {
		var ids = ( tags || [] ).map( Number );

		if ( reserved[ 2 ] && -1 !== ids.indexOf( Number( reserved[ 2 ] ) ) ) {
			return 2;
		}
		if ( reserved[ 1 ] && -1 !== ids.indexOf( Number( reserved[ 1 ] ) ) ) {
			return 1;
		}
		return 0;
	}

	function afterMetaBoxSave() {
		var select = document.getElementById( 'pigeon-content-access' );
		var original = document.querySelector( 'input[name="pigeon_content_access_original"]' );
		var editor = wp.data.select( 'core/editor' );
		var core = wp.data.select( 'core' );
		var type = editor.getCurrentPostType();
		var id = editor.getCurrentPostId();
		var postType = core.getPostType( type );

		// What was just saved is now the baseline for the next "did the dropdown change?" check.
		if ( select && original ) {
			original.value = select.value;
		}

		if ( ! postType || ! postType.rest_base ) {
			return;
		}

		var accessBefore = lastTagAccess || 0;

		wp.apiFetch( {
			path: '/' + ( postType.rest_namespace || 'wp/v2' ) + '/' + postType.rest_base + '/' + id + '?context=edit&_fields=tags',
		} ).then( function ( response ) {
			var record = wp.data.select( 'core' ).getEntityRecord( 'postType', type, id );

			if ( record && response && response.tags ) {
				// Replace the saved copy, not an edit, so the post doesn't read as having unsaved changes.
				wp.data.dispatch( 'core' ).receiveEntityRecords( 'postType', type, [ Object.assign( {}, record, { tags: response.tags } ) ] );
			}

			// A post with no reserved tag may still carry an old meta-only override, so only move the
			// dropdown when a reserved tag is involved now or was a moment ago.
			var accessAfter = accessFromTags( response && response.tags );
			if ( select && original && ( accessAfter || accessBefore ) ) {
				select.value = String( accessAfter );
				original.value = String( accessAfter );
			}
			lastTagAccess = accessAfter;
		} );
	}

	wp.data.subscribe( function () {
		var editPost = wp.data.select( 'core/edit-post' );
		var saving = !! ( editPost && editPost.isSavingMetaBoxes() );

		if ( null === lastTagAccess ) {
			var editor = wp.data.select( 'core/editor' );
			var record = editor && wp.data.select( 'core' ).getEntityRecord( 'postType', editor.getCurrentPostType(), editor.getCurrentPostId() );
			if ( record && record.tags ) {
				lastTagAccess = accessFromTags( record.tags );
			}
		}

		if ( wasSaving && ! saving ) {
			afterMetaBoxSave();
		}
		wasSaving = saving;
	} );
}( window.wp, window.pigeonEditorSync ) );
