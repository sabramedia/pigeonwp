<?php
/**
 * The Pigeon Settings panel in the block editor.
 *
 * @since 1.7.0
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Editor_Panel
 *
 * In the block editor, Pigeon Settings is a native document panel (like Tags) instead of a classic
 * meta box. The access dropdown edits the post's reserved tags in the editor itself, so the Tags
 * panel updates as you choose and everything saves with the post in one request. The classic
 * editor keeps the meta box (see Admin::add_meta_box).
 *
 * @since 1.7.0
 */
class Editor_Panel {

	/**
	 * The script handle.
	 *
	 * @since 1.7.0
	 */
	const JS_HANDLE = 'pigeon-editor-panel';

	/**
	 * Meta keys the panel edits over REST, with their types and defaults.
	 *
	 * @since 1.7.0
	 */
	const META = array(
		Access_Tags::LEGACY_META_KEY => array( 'integer', 0 ),
		'_wp_pigeon_content_price'   => array( 'string', '' ),
		'_wp_pigeon_content_value'   => array( 'integer', 0 ),
		'_wp_pigeon_content_prompt'  => array( 'integer', 0 ),
	);

	/**
	 * Hooks.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function hooks() {
		// After post types (and Access_Tags' taxonomy registration) are in place.
		add_action( 'init', array( $this, 'register_meta' ), 30 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );
	}

	/**
	 * Expose the Pigeon post meta to the editor over REST, to users who can edit the post.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function register_meta() {
		foreach ( Access_Tags::get_post_types() as $post_type ) {
			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			// Registered meta only appears in a post type's REST response when it supports custom fields.
			if ( ! post_type_supports( $post_type, 'custom-fields' ) ) {
				add_post_type_support( $post_type, 'custom-fields' );
			}

			foreach ( self::META as $key => $definition ) {
				register_post_meta(
					$post_type,
					$key,
					array(
						'type'          => $definition[0],
						'default'       => $definition[1],
						'single'        => true,
						'show_in_rest'  => true,
						'auth_callback' => array( $this, 'can_edit_meta' ),
					)
				);
			}
		}
	}

	/**
	 * The meta is private (underscored), so REST access needs an explicit check.
	 *
	 * @since 1.7.0
	 *
	 * @param bool   $allowed  Whether the user can edit the meta.
	 * @param string $meta_key The meta key.
	 * @param int    $post_id  The post ID.
	 *
	 * @return bool
	 */
	public function can_edit_meta( $allowed, $meta_key, $post_id ) { // @phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return current_user_can( 'edit_post', $post_id );
	}

	/**
	 * Load the panel on block editor screens for Pigeon post types.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function enqueue() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || ! in_array( $screen->post_type, Access_Tags::get_post_types(), true ) ) {
			return;
		}

		$settings = get_plugin_settings();
		$credits  = array();

		if ( ! empty( $settings['pigeon_content_value_meter'] ) && ! empty( $settings['pigeon_content_value'] ) ) {
			foreach ( (array) $settings['pigeon_content_value'] as $value ) {
				$credits[] = array(
					'value' => (int) $value,
					// translators: Number of credits.
					'label' => sprintf( _n( '%s credit', '%s credits', (int) $value, 'pigeon' ), $value ),
				);
			}
		}

		wp_enqueue_script(
			self::JS_HANDLE,
			PIGEONWP_URL . 'src/admin/editor-panel.js',
			array( 'wp-plugins', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n', 'wp-editor', 'wp-edit-post' ),
			PIGEONWP_VERSION,
			true
		);

		wp_add_inline_script(
			self::JS_HANDLE,
			'window.pigeonEditorPanel = ' . wp_json_encode(
				array(
					'reserved'  => Access_Tags::get_term_ids(),
					'accessKey' => Access_Tags::LEGACY_META_KEY,
					'pricing'   => ! empty( $settings['pigeon_content_value_pricing'] ),
					'credits'   => $credits,
					'labels'    => array(
						'title'      => __( 'Pigeon Settings', 'pigeon' ),
						'access'     => __( 'Access', 'pigeon' ),
						'rules'      => __( 'Pigeon rules (default)', 'pigeon' ),
						'free'       => __( 'Free', 'pigeon' ),
						'restricted' => __( 'Restricted', 'pigeon' ),
						'accessHelp' => __( 'Saved as a reserved Pigeon tag.', 'pigeon' ),
						'price'      => __( 'Price', 'pigeon' ),
						'priceHelp'  => __( 'e.g., 4.95', 'pigeon' ),
						'value'      => __( 'Value', 'pigeon' ),
						'valueFree'  => __( '-- Free --', 'pigeon' ),
						'prompt'     => __( 'Prompt', 'pigeon' ),
					),
				)
			) . ';',
			'before'
		);
	}
}
