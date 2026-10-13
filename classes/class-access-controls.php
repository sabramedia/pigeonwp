<?php
/**
 * Admin controls for the Pigeon access override.
 *
 * @since 1.7.0
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Access_Controls
 *
 * Bulk actions and a column on the post lists, an admin bar status and switcher on the
 * front end, and a migration from the legacy access meta to the reserved tags.
 *
 * @since 1.7.0
 */
class Access_Controls {

	/**
	 * Bulk action prefix; the access value is appended.
	 *
	 * @since 1.7.0
	 */
	const BULK_PREFIX = 'pigeon_access_';

	/**
	 * The admin-post action that sets one post's access.
	 *
	 * @since 1.7.0
	 */
	const SET_ACTION = 'pigeon_set_access';

	/**
	 * The admin-post action that migrates the legacy meta.
	 *
	 * @since 1.7.0
	 */
	const MIGRATE_ACTION = 'pigeon_migrate_access';

	/**
	 * Posts migrated per query batch.
	 *
	 * @since 1.7.0
	 */
	const MIGRATE_BATCH = 200;

	/**
	 * Hooks.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'register_list_hooks' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_status' ), 101 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_admin_bar_assets' ) );

		add_action( 'admin_post_' . self::SET_ACTION, array( $this, 'handle_set_access' ) );
		add_action( 'admin_post_' . self::MIGRATE_ACTION, array( $this, 'handle_migrate' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'pigeon migrate-access', array( $this, 'cli_migrate' ) );
		}
	}

	/**
	 * Add the bulk actions and the column to each Pigeon post type's list.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function register_list_hooks() {
		foreach ( Access_Tags::get_post_types() as $post_type ) {
			add_filter( 'bulk_actions-edit-' . $post_type, array( $this, 'add_bulk_actions' ) );
			add_filter( 'handle_bulk_actions-edit-' . $post_type, array( $this, 'handle_bulk_action' ), 10, 3 );
			add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'add_column' ) );
			add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
		}
	}

	/**
	 * Add "Pigeon: …" bulk actions.
	 *
	 * @since 1.7.0
	 *
	 * @param array $actions The bulk actions.
	 *
	 * @return array
	 */
	public function add_bulk_actions( $actions ) {
		foreach ( Access_Tags::get_labels() as $access => $label ) {
			// translators: An access setting, e.g. Free.
			$actions[ self::BULK_PREFIX . $access ] = sprintf( __( 'Pigeon: %s', 'pigeon' ), $label );
		}

		return $actions;
	}

	/**
	 * Apply a "Pigeon: …" bulk action.
	 *
	 * @since 1.7.0
	 *
	 * @param string $redirect_to The redirect URL.
	 * @param string $action      The bulk action.
	 * @param array  $post_ids    The selected post IDs.
	 *
	 * @return string
	 */
	public function handle_bulk_action( $redirect_to, $action, $post_ids ) {
		if ( 0 !== strpos( $action, self::BULK_PREFIX ) ) {
			return $redirect_to;
		}

		$access = (int) substr( $action, strlen( self::BULK_PREFIX ) );

		if ( ! array_key_exists( $access, Access_Tags::get_labels() ) ) {
			return $redirect_to;
		}

		$changed = 0;

		foreach ( $post_ids as $post_id ) {
			if ( current_user_can( 'edit_post', $post_id ) && Access_Tags::set_access( $post_id, $access ) ) {
				++$changed;
			}
		}

		return add_query_arg(
			array(
				'pigeon_access_changed' => $changed,
				'pigeon_access'         => $access,
			),
			$redirect_to
		);
	}

	/**
	 * Add the Pigeon column.
	 *
	 * @since 1.7.0
	 *
	 * @param array $columns The list columns.
	 *
	 * @return array
	 */
	public function add_column( $columns ) {
		$columns['pigeon_access'] = __( 'Pigeon', 'pigeon' );

		return $columns;
	}

	/**
	 * Render the Pigeon column.
	 *
	 * @since 1.7.0
	 *
	 * @param string $column  The column key.
	 * @param int    $post_id The post ID.
	 *
	 * @return void
	 */
	public function render_column( $column, $post_id ) {
		if ( 'pigeon_access' !== $column ) {
			return;
		}

		$labels = Access_Tags::get_labels();
		$access = Access_Tags::get_access( $post_id );

		if ( Access_Tags::ACCESS_RULES === $access ) {
			echo '<span style="color:#787c82;">' . esc_html( $labels[ $access ] ) . '</span>';
			return;
		}

		echo '<strong>' . esc_html( $labels[ $access ] ) . '</strong>';
	}

	/**
	 * Notices after a bulk change or a migration.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function admin_notices() {
		// @phpcs:disable WordPress.Security.NonceVerification.Recommended -- Display only.
		if ( isset( $_GET['pigeon_access_changed'], $_GET['pigeon_access'] ) ) {
			$changed = (int) $_GET['pigeon_access_changed'];
			$labels  = Access_Tags::get_labels();
			$access  = (int) $_GET['pigeon_access'];
			$label   = isset( $labels[ $access ] ) ? $labels[ $access ] : '';

			// translators: 1: Number of posts, 2: An access setting, e.g. Free.
			$message = sprintf( _n( 'Pigeon access set to %2$s on %1$d item.', 'Pigeon access set to %2$s on %1$d items.', $changed, 'pigeon' ), $changed, $label );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
		}

		if ( isset( $_GET['pigeon_migrated'] ) ) {
			$migrated = (int) $_GET['pigeon_migrated'];

			// translators: Number of posts.
			$message = sprintf( _n( 'Moved the Pigeon access setting of %d item to tags.', 'Moved the Pigeon access settings of %d items to tags.', $migrated, 'pigeon' ), $migrated );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
		}
		// @phpcs:enable
	}

	/**
	 * The post the admin bar controls, if the current user may change it.
	 *
	 * @since 1.7.0
	 *
	 * @return \WP_Post|null
	 */
	protected function get_admin_bar_post() {
		if ( is_admin() || ! is_singular( Access_Tags::get_post_types() ) ) {
			return null;
		}

		$post = get_queried_object();

		if ( ! $post instanceof \WP_Post || ! current_user_can( 'edit_post', $post->ID ) ) {
			return null;
		}

		return $post;
	}

	/**
	 * Add the paywall status and the access switcher to the front-end admin bar.
	 *
	 * @since 1.7.0
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The admin bar object.
	 *
	 * @return void
	 */
	public function add_admin_bar_status( $wp_admin_bar ) {
		$post = $this->get_admin_bar_post();

		if ( ! $post ) {
			return;
		}

		$labels = Access_Tags::get_labels();
		$access = Access_Tags::get_access( $post->ID );
		$source = Access_Tags::ACCESS_RULES === $access ? __( 'rules', 'pigeon' ) : __( 'override', 'pigeon' );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'pigeon-access',
				'title' => sprintf(
					'<span class="pigeon-access-dot"></span><span class="pigeon-access-status">%s</span> <span class="pigeon-access-source">&middot; %s</span>',
					esc_html__( 'Paywall: checking…', 'pigeon' ),
					esc_html( $source )
				),
				'href'  => false,
				'meta'  => array(
					'class' => 'pigeon-access pigeon-access--pending',
					'title' => __( 'What a signed-out reader gets on this page, from Pigeon. Your own subscription is ignored, but a page you bought with credits shows as free.', 'pigeon' ),
				),
			)
		);

		foreach ( $labels as $value => $label ) {
			$title = Access_Tags::ACCESS_RULES === $value ? __( 'Use Pigeon rules', 'pigeon' ) : sprintf(
				// translators: An access setting, e.g. Free.
				__( 'Force %s', 'pigeon' ),
				strtolower( $label )
			);

			$wp_admin_bar->add_node(
				array(
					'parent' => 'pigeon-access',
					'id'     => 'pigeon-access-' . $value,
					'title'  => ( $value === $access ? '&#10003; ' : '' ) . esc_html( $title ),
					'href'   => $this->get_set_access_url( $post->ID, $value ),
					'meta'   => array( 'class' => $value === $access ? 'pigeon-access-current' : '' ),
				)
			);
		}

		$wp_admin_bar->add_node(
			array(
				'parent' => 'pigeon-access',
				'id'     => 'pigeon-access-edit',
				'title'  => esc_html__( 'Edit post settings', 'pigeon' ),
				'href'   => get_edit_post_link( $post->ID ),
			)
		);
	}

	/**
	 * A nonce-protected link that sets a post's access and returns to the page.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id The post ID.
	 * @param int $access  The access value.
	 *
	 * @return string
	 */
	protected function get_set_access_url( $post_id, $access ) {
		$url = add_query_arg(
			array(
				'action' => self::SET_ACTION,
				'post'   => $post_id,
				'access' => $access,
			),
			admin_url( 'admin-post.php' )
		);

		return wp_nonce_url( $url, self::SET_ACTION . '_' . $post_id );
	}

	/**
	 * Set one post's access from the admin bar, then return to the post.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function handle_set_access() {
		$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
		$access  = isset( $_GET['access'] ) ? (int) $_GET['access'] : -1;

		check_admin_referer( self::SET_ACTION . '_' . $post_id );

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to edit this post.', 'pigeon' ), 403 );
		}

		if ( ! array_key_exists( $access, Access_Tags::get_labels() ) ) {
			wp_die( esc_html__( 'Unknown Pigeon access setting.', 'pigeon' ), 400 );
		}

		Access_Tags::set_access( $post_id, $access );

		$redirect = wp_get_referer();
		wp_safe_redirect( $redirect ? $redirect : get_permalink( $post_id ) );
		exit;
	}

	/**
	 * Style and script for the admin bar status.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function enqueue_admin_bar_assets() {
		if ( ! is_admin_bar_showing() || ! $this->get_admin_bar_post() ) {
			return;
		}

		// Above the Pigeon paywall modal (99999999), so a locked page can still be switched.
		$css = '
			#wpadminbar { z-index: 100000000; }
			#wpadminbar .pigeon-access-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #a7aaad; margin-right: 6px; vertical-align: middle; }
			#wpadminbar .pigeon-access-source { opacity: 0.7; }
			#wpadminbar .pigeon-access--free .pigeon-access-dot { background: #00a32a; }
			#wpadminbar .pigeon-access--metered .pigeon-access-dot { background: #dba617; }
			#wpadminbar .pigeon-access--locked .pigeon-access-dot { background: #d63638; }
			#wpadminbar .pigeon-access-current > .ab-item { font-weight: 600; }
		';
		wp_add_inline_style( 'admin-bar', $css );

		wp_register_script( 'pigeon-admin-bar', false, array(), PIGEONWP_VERSION, true );
		wp_enqueue_script( 'pigeon-admin-bar' );
		wp_add_inline_script( 'pigeon-admin-bar', $this->get_admin_bar_js() );
	}

	/**
	 * Reads this page's paywall decision from the Pigeon SDK into the admin bar.
	 *
	 * Uses paywalled/restricted, which describe the page, not `allowed`, which also
	 * reflects the admin's own subscription.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	protected function get_admin_bar_js() {
		$text = array(
			'inactive'     => __( 'Paywall: not active', 'pigeon' ),
			'unavailable'  => __( 'Paywall: Pigeon unavailable', 'pigeon' ),
			'noPaywall'    => __( 'Paywall: free (no paywall)', 'pigeon' ),
			'free'         => __( 'Paywall: free', 'pigeon' ),
			'locked'       => __( 'Paywall: locked', 'pigeon' ),
			'registration' => __( 'Paywall: registration required', 'pigeon' ),
			'subscribers'  => __( 'Paywall: subscribers only', 'pigeon' ),
			'metered'      => __( 'Paywall: metered', 'pigeon' ),
		);

		return '(function () {
			var text = ' . wp_json_encode( $text ) . ';
			var node = document.getElementById( "wp-admin-bar-pigeon-access" );
			if ( ! node ) { return; }
			var status = node.querySelector( ".pigeon-access-status" );

			function show( label, state ) {
				status.textContent = label;
				node.className = node.className.replace( /\bpigeon-access--\w+/g, "" ) + " pigeon-access--" + state;
			}

			function describe( r ) {
				r = r || {};
				if ( r.disabled ) { return [ text.unavailable, "na" ]; }
				if ( r.special_access ) { return [ text.noPaywall, "free" ]; }
				if ( r.registration_required ) { return [ text.registration, "locked" ]; }
				if ( r.restricted ) { return [ text.locked, "locked" ]; }
				if ( r.paywalled ) {
					if ( r.meter_limit > 0 ) {
						return [ text.metered + ( "number" === typeof r.meter ? " (" + r.meter + "/" + r.meter_limit + ")" : "" ), "metered" ];
					}
					return [ text.subscribers, "locked" ];
				}
				return [ text.free, "free" ];
			}

			var tries = 0;
			( function wait() {
				var pigeon = window.Pigeon;
				if ( pigeon && pigeon.paywallPromise && "function" === typeof pigeon.paywallPromise.done ) {
					pigeon.paywallPromise.done( function ( r ) { var d = describe( r ); show( d[0], d[1] ); } );
					return;
				}
				if ( ++tries > 40 ) { show( text.inactive, "na" ); return; }
				setTimeout( wait, 250 );
			} )();
		})();';
	}

	/**
	 * Migrate the legacy meta from the settings page.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function handle_migrate() {
		check_admin_referer( self::MIGRATE_ACTION );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'pigeon' ), 403 );
		}

		$migrated = self::migrate();

		wp_safe_redirect( add_query_arg( 'pigeon_migrated', $migrated, admin_url( 'options-general.php?page=' . Admin::MENU_SLUG ) ) );
		exit;
	}

	/**
	 * Move every post's legacy access meta to the reserved tags.
	 *
	 * ## EXAMPLES
	 *
	 *     wp pigeon migrate-access
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function cli_migrate() {
		$migrated = self::migrate();

		\WP_CLI::success( sprintf( 'Migrated %d posts to Pigeon access tags.', $migrated ) );
	}

	/**
	 * Count posts that still carry the legacy access meta.
	 *
	 * @since 1.7.0
	 *
	 * @return int
	 */
	public static function count_legacy() {
		global $wpdb;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s", Access_Tags::LEGACY_META_KEY ) ); // @phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}

	/**
	 * Move legacy access meta to tags, in batches. Rules (0) just drops the meta.
	 *
	 * @since 1.7.0
	 *
	 * @return int Posts that ended up with a reserved tag.
	 */
	public static function migrate() {
		global $wpdb;

		$migrated = 0;

		do {
			// Each pass deletes the meta it reads, so the next batch starts again from the top.
			$rows = $wpdb->get_results( // @phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT %d",
					Access_Tags::LEGACY_META_KEY,
					self::MIGRATE_BATCH
				)
			);

			$row_count = count( $rows );

			foreach ( $rows as $row ) {
				$post_id = (int) $row->post_id;
				$access  = (int) $row->meta_value;

				// Revisions carry copies of the meta; a tag set by hand wins over the old meta.
				if ( wp_is_post_revision( $post_id ) || null !== Access_Tags::get_tag_access( $post_id ) || Access_Tags::ACCESS_RULES === $access || ! array_key_exists( $access, Access_Tags::get_labels() ) ) {
					delete_post_meta( $post_id, Access_Tags::LEGACY_META_KEY );
					continue;
				}

				if ( Access_Tags::set_access( $post_id, $access ) ) {
					++$migrated;
				} else {
					// Stop rather than loop forever on a post that can't be tagged.
					return $migrated;
				}
			}
		} while ( self::MIGRATE_BATCH === $row_count );

		return $migrated;
	}
}
