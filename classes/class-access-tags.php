<?php
/**
 * Paywall access overrides stored as reserved post tags.
 *
 * @since 1.7.0
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class Access_Tags
 *
 * A post's Pigeon access override lives in one of two reserved post tags instead of
 * post meta, so it can be changed in bulk. No reserved tag means the Pigeon paywall
 * rules decide. The value sent to Pigeon as `free` keeps its legacy meaning:
 * 0 = rules, 1 = free, 2 = restricted.
 *
 * @since 1.7.0
 */
class Access_Tags {

	/**
	 * Access: the Pigeon paywall rules decide.
	 *
	 * @since 1.7.0
	 */
	const ACCESS_RULES = 0;

	/**
	 * Access: always free.
	 *
	 * @since 1.7.0
	 */
	const ACCESS_FREE = 1;

	/**
	 * Access: always restricted.
	 *
	 * @since 1.7.0
	 */
	const ACCESS_RESTRICTED = 2;

	/**
	 * Reserved tag slugs keyed by the access value they force.
	 *
	 * @since 1.7.0
	 */
	const TAG_SLUGS = array(
		self::ACCESS_FREE       => 'pigeon-free',
		self::ACCESS_RESTRICTED => 'pigeon-restricted',
	);

	/**
	 * The option holding the reserved term IDs.
	 *
	 * @since 1.7.0
	 */
	const TERMS_OPTION = 'wp_pigeon_access_terms';

	/**
	 * The legacy post meta key the override used to live in.
	 *
	 * @since 1.7.0
	 */
	const LEGACY_META_KEY = '_wp_pigeon_content_access';

	/**
	 * Reserved term IDs keyed by access value, cached for the request per blog (multisite).
	 *
	 * @since 1.7.0
	 *
	 * @var array
	 */
	protected static $term_ids = array();

	/**
	 * Set while the plugin reads terms itself, so the front-end hiding filters let them through.
	 *
	 * @since 1.7.0
	 *
	 * @var bool
	 */
	protected static $internal = false;

	/**
	 * Hooks.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function hooks() {
		// Pages and custom post types carry the override as a tag too.
		add_action( 'init', array( $this, 'register_tag_taxonomy' ), 20 );

		// Only the plugin may rename or delete the reserved tags.
		add_filter( 'map_meta_cap', array( $this, 'protect_terms' ), 10, 4 );

		// A post can't be both free and restricted; restricted wins.
		add_action( 'set_object_terms', array( $this, 'normalize_terms' ), 10, 6 );

		// Readers never see the reserved tags.
		add_filter( 'get_the_terms', array( $this, 'hide_object_terms' ), 10, 3 );
		add_filter( 'get_terms', array( $this, 'hide_terms' ), 10, 3 );
		add_filter( 'wp_sitemaps_taxonomies_query_args', array( $this, 'hide_from_sitemap' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'hide_archive' ) );
	}

	/**
	 * Give every Pigeon post type the post_tag taxonomy, so a page's override tag is a real,
	 * counted and cleaned-up relationship.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function register_tag_taxonomy() {
		foreach ( self::get_post_types() as $post_type ) {
			if ( post_type_exists( $post_type ) && ! is_object_in_taxonomy( $post_type, 'post_tag' ) ) {
				register_taxonomy_for_object_type( 'post_tag', $post_type );
			}
		}
	}

	/**
	 * Get the reserved term IDs.
	 *
	 * Reads trust the stored IDs. Writes pass $verify, which checks them and creates any
	 * missing term, so a front-end page view never inserts terms once they exist.
	 *
	 * @since 1.7.0
	 *
	 * @param bool $verify Check the stored IDs against the terms and repair them.
	 *
	 * @return array Term IDs keyed by access value.
	 */
	public static function get_term_ids( $verify = false ) {
		$blog_id = get_current_blog_id();

		if ( ! $verify && isset( self::$term_ids[ $blog_id ] ) ) {
			return self::$term_ids[ $blog_id ];
		}

		// Too early: nothing can be looked up or created yet, and nothing is cached.
		if ( ! taxonomy_exists( 'post_tag' ) ) {
			return array();
		}

		$stored = get_option( self::TERMS_OPTION );
		$stored = is_array( $stored ) ? array_map( 'intval', $stored ) : array();

		if ( ! $verify && count( $stored ) === count( self::TAG_SLUGS ) ) {
			self::$term_ids[ $blog_id ] = $stored;
			return $stored;
		}

		$ids            = array();
		self::$internal = true;

		foreach ( self::TAG_SLUGS as $access => $slug ) {
			$term_id = self::find_or_create_term( $slug, isset( $stored[ $access ] ) ? $stored[ $access ] : 0 );

			if ( $term_id ) {
				$ids[ $access ] = $term_id;
			}
		}

		self::$internal = false;

		if ( $ids !== $stored ) {
			update_option( self::TERMS_OPTION, $ids );
		}

		self::$term_ids[ $blog_id ] = $ids;

		return $ids;
	}

	/**
	 * The ID of a reserved tag: the stored one if it still matches, else found by slug, else created.
	 *
	 * @since 1.7.0
	 *
	 * @param string $slug      The reserved slug.
	 * @param int    $stored_id The ID stored for it, or 0.
	 *
	 * @return int The term ID, or 0.
	 */
	protected static function find_or_create_term( $slug, $stored_id ) {
		$term = $stored_id ? get_term( $stored_id, 'post_tag' ) : null;

		if ( ! $term instanceof \WP_Term || $term->slug !== $slug ) {
			$term = get_term_by( 'slug', $slug, 'post_tag' );
		}

		if ( ! $term ) {
			$created = wp_insert_term( $slug, 'post_tag', array( 'slug' => $slug ) );

			// A concurrent request may have just created it; the error carries its ID.
			$term_id = is_wp_error( $created ) ? $created->get_error_data( 'term_exists' ) : $created['term_id'];
			$term    = $term_id ? get_term( (int) $term_id, 'post_tag' ) : null;
		}

		return $term instanceof \WP_Term ? (int) $term->term_id : 0;
	}

	/**
	 * Check whether a term is one of the reserved tags.
	 *
	 * @since 1.7.0
	 *
	 * @param int $term_id The term ID.
	 *
	 * @return bool
	 */
	public static function is_reserved_term( $term_id ) {
		return in_array( (int) $term_id, self::get_term_ids(), true );
	}

	/**
	 * Get the access override for a post.
	 *
	 * Reads the reserved tags, falling back to the legacy post meta for posts that
	 * have not been migrated yet.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int One of the ACCESS_* constants.
	 */
	public static function get_access( $post_id ) {
		$tag_access = self::get_tag_access( $post_id );

		if ( null !== $tag_access ) {
			return $tag_access;
		}

		$legacy = (int) get_post_meta( $post_id, self::LEGACY_META_KEY, true );

		return in_array( $legacy, array( self::ACCESS_FREE, self::ACCESS_RESTRICTED ), true ) ? $legacy : self::ACCESS_RULES;
	}

	/**
	 * Get the access forced by the post's reserved tags.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @return int|null The access value, or null when no reserved tag is set.
	 */
	public static function get_tag_access( $post_id ) {
		$term_ids = self::get_term_ids();

		// The term cache is already primed for the main query and list tables.
		$post_terms = get_object_term_cache( $post_id, 'post_tag' );

		if ( false === $post_terms ) {
			self::$internal = true;
			$post_terms     = wp_get_object_terms( $post_id, 'post_tag', array( 'fields' => 'ids' ) );
			self::$internal = false;
		}

		if ( is_wp_error( $post_terms ) ) {
			return null;
		}

		$post_terms = array_map(
			function ( $term ) {
				return (int) ( $term instanceof \WP_Term ? $term->term_id : $term );
			},
			(array) $post_terms
		);

		// Restricted wins if both are somehow present.
		foreach ( array( self::ACCESS_RESTRICTED, self::ACCESS_FREE ) as $access ) {
			if ( ! empty( $term_ids[ $access ] ) && in_array( $term_ids[ $access ], $post_terms, true ) ) {
				return $access;
			}
		}

		return null;
	}

	/**
	 * Set the access override for a post.
	 *
	 * Replaces any reserved tag with the one for the new value. The legacy meta is removed
	 * for free/restricted (the tag is the truth) and left as 0 for rules, which is what the
	 * old "Metered" choice stored and what the RSS feed has always reported for it.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id The post ID.
	 * @param int $access  One of the ACCESS_* constants.
	 *
	 * @return bool
	 */
	public static function set_access( $post_id, $access ) {
		$access   = (int) $access;
		$term_ids = self::get_term_ids( true );

		if ( count( $term_ids ) !== count( self::TAG_SLUGS ) ) {
			return false;
		}

		$remove = array_values( array_diff_key( $term_ids, array( $access => true ) ) );
		wp_remove_object_terms( $post_id, $remove, 'post_tag' );

		if ( ! empty( $term_ids[ $access ] ) ) {
			$result = wp_set_object_terms( $post_id, array( $term_ids[ $access ] ), 'post_tag', true );

			if ( is_wp_error( $result ) ) {
				return false;
			}
		}

		if ( self::ACCESS_RULES === $access ) {
			update_post_meta( $post_id, self::LEGACY_META_KEY, self::ACCESS_RULES );
		} else {
			delete_post_meta( $post_id, self::LEGACY_META_KEY );
		}

		// The inline Pigeon.paywall({free:…}) is baked into the cached page.
		clean_post_cache( $post_id );

		return true;
	}

	/**
	 * Labels for each access value.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	public static function get_labels() {
		return array(
			self::ACCESS_RULES      => __( 'Pigeon rules', 'pigeon' ),
			self::ACCESS_FREE       => __( 'Free', 'pigeon' ),
			self::ACCESS_RESTRICTED => __( 'Restricted', 'pigeon' ),
		);
	}

	/**
	 * Post types that carry Pigeon settings.
	 *
	 * @since 1.7.0
	 *
	 * @return array
	 */
	public static function get_post_types() {
		$settings = get_plugin_settings();
		$custom   = ! empty( $settings['pigeon_content_post_types'] ) ? (array) $settings['pigeon_content_post_types'] : array();

		return array_unique( array_merge( array( 'post', 'page' ), $custom ) );
	}

	/**
	 * Deny editing or deleting the reserved tags.
	 *
	 * @since 1.7.0
	 *
	 * @param array  $caps    Primitive caps the user needs.
	 * @param string $cap     The meta cap being checked.
	 * @param int    $user_id The user ID.
	 * @param array  $args    Extra arguments; the term ID comes first.
	 *
	 * @return array
	 */
	public function protect_terms( $caps, $cap, $user_id, $args ) {
		if ( in_array( $cap, array( 'edit_term', 'delete_term' ), true ) && ! empty( $args[0] ) && self::is_reserved_term( $args[0] ) ) {
			return array( 'do_not_allow' );
		}

		return $caps;
	}

	/**
	 * Keep at most one reserved tag on a post after any tag change.
	 *
	 * @since 1.7.0
	 *
	 * @param int    $object_id  The object ID.
	 * @param array  $terms      Terms passed in.
	 * @param array  $tt_ids     Term taxonomy IDs now set.
	 * @param string $taxonomy   The taxonomy.
	 * @param bool   $append     Whether terms were appended.
	 * @param array  $old_tt_ids Previous term taxonomy IDs.
	 *
	 * @return void
	 */
	public function normalize_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) { // @phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		if ( 'post_tag' !== $taxonomy ) {
			return;
		}

		$term_ids = self::get_term_ids();

		if ( count( $term_ids ) !== count( self::TAG_SLUGS ) ) {
			return;
		}

		$restricted = get_term( $term_ids[ self::ACCESS_RESTRICTED ], 'post_tag' );
		$free       = get_term( $term_ids[ self::ACCESS_FREE ], 'post_tag' );

		if ( ! $restricted instanceof \WP_Term || ! $free instanceof \WP_Term ) {
			return;
		}

		// On an append $tt_ids holds only the added terms and core leaves $old_tt_ids empty, so read
		// what the post now has.
		if ( $append ) {
			self::$internal = true;
			$tt_ids         = wp_get_object_terms( $object_id, 'post_tag', array( 'fields' => 'tt_ids' ) );
			self::$internal = false;
		}

		$tt_ids = is_wp_error( $tt_ids ) ? array() : array_map( 'intval', (array) $tt_ids );

		$has_restricted = in_array( (int) $restricted->term_taxonomy_id, $tt_ids, true );
		$has_free       = in_array( (int) $free->term_taxonomy_id, $tt_ids, true );

		if ( $has_restricted && $has_free ) {
			wp_remove_object_terms( $object_id, array( (int) $free->term_id ), 'post_tag' );
		}

		// Once a reserved tag is set, an old free/restricted meta must not come back if the tag is removed.
		if ( ( $has_restricted || $has_free ) && in_array( (int) get_post_meta( $object_id, self::LEGACY_META_KEY, true ), array( self::ACCESS_FREE, self::ACCESS_RESTRICTED ), true ) ) {
			delete_metadata( 'post', $object_id, self::LEGACY_META_KEY );
		}
	}

	/**
	 * Whether reserved tags should be hidden from the current request.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	protected function should_hide() {
		if ( self::$internal ) {
			return false;
		}

		// Front-end AJAX (infinite scroll, "load more") runs through admin-ajax.php, where is_admin()
		// is true; only editors see the tags there.
		if ( wp_doing_ajax() ) {
			return ! current_user_can( 'edit_posts' );
		}

		if ( is_admin() ) {
			return false;
		}

		// REST stays unfiltered: the block editor reads tags there, and so does Pigeon's paywall
		// editor (tag search, reserved-tag checks).
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		return true;
	}

	/**
	 * Strip reserved tags from a post's term list on the front end.
	 *
	 * Covers get_the_tags(), the_tags(), get_the_term_list() and post classes.
	 *
	 * @since 1.7.0
	 *
	 * @param array|\WP_Error $terms    The post's terms.
	 * @param int             $post_id  The post ID.
	 * @param string          $taxonomy The taxonomy.
	 *
	 * @return array|\WP_Error
	 */
	public function hide_object_terms( $terms, $post_id, $taxonomy ) {
		if ( 'post_tag' !== $taxonomy || ! is_array( $terms ) || ! $this->should_hide() ) {
			return $terms;
		}

		return $this->strip_reserved( $terms );
	}

	/**
	 * Strip reserved tags from term queries on the front end, such as tag clouds.
	 *
	 * @since 1.7.0
	 *
	 * @param array $terms      The found terms, shaped by the query's `fields` arg.
	 * @param array $taxonomies The queried taxonomies.
	 * @param array $args       The query args.
	 *
	 * @return array
	 */
	public function hide_terms( $terms, $taxonomies, $args ) {
		if ( ! is_array( $terms ) || ! in_array( 'post_tag', (array) $taxonomies, true ) || ! $this->should_hide() ) {
			return $terms;
		}

		// A single post's terms (wp_get_post_tags) still include them, so they reach Pigeon;
		// theme output goes through get_the_terms, filtered above.
		if ( ! empty( $args['object_ids'] ) ) {
			return $terms;
		}

		return $this->strip_by_fields( $terms, ! empty( $args['fields'] ) ? $args['fields'] : 'all' );
	}

	/**
	 * Remove reserved tags from a term query result of any `fields` shape.
	 *
	 * @since 1.7.0
	 *
	 * @param array  $terms  The found terms.
	 * @param string $fields The query's `fields` arg.
	 *
	 * @return array
	 */
	protected function strip_by_fields( $terms, $fields ) {
		$term_ids = self::get_term_ids();

		switch ( $fields ) {
			case 'all':
			case 'all_with_object_id':
				return $this->strip_reserved( $terms );

			case 'ids':
				return array_values( array_diff( array_map( 'intval', $terms ), $term_ids ) );

			case 'names':
			case 'slugs':
				return array_values( array_diff( $terms, array_values( self::TAG_SLUGS ) ) );

			case 'id=>name':
			case 'id=>slug':
			case 'id=>parent':
				return array_diff_key( $terms, array_flip( $term_ids ) );
		}

		return $terms;
	}

	/**
	 * Remove reserved tags from a list of term objects.
	 *
	 * @since 1.7.0
	 *
	 * @param array $terms Term objects.
	 *
	 * @return array
	 */
	protected function strip_reserved( $terms ) {
		$term_ids = self::get_term_ids();

		$kept = array_filter(
			$terms,
			function ( $term ) use ( $term_ids ) {
				return ! ( $term instanceof \WP_Term && in_array( (int) $term->term_id, $term_ids, true ) );
			}
		);

		return array_values( $kept );
	}

	/**
	 * Leave the reserved tags out of the tag sitemap.
	 *
	 * @since 1.7.0
	 *
	 * @param array  $args     The term query args.
	 * @param string $taxonomy The taxonomy.
	 *
	 * @return array
	 */
	public function hide_from_sitemap( $args, $taxonomy ) {
		if ( 'post_tag' === $taxonomy ) {
			$exclude         = ! empty( $args['exclude'] ) ? wp_parse_id_list( $args['exclude'] ) : array();
			$args['exclude'] = array_merge( $exclude, array_values( self::get_term_ids() ) );
		}

		return $args;
	}

	/**
	 * 404 the archive pages of the reserved tags.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	public function hide_archive() {
		if ( ! is_tag( array_values( self::TAG_SLUGS ) ) ) {
			return;
		}

		$is_feed = is_feed();

		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();

		// set_404() keeps is_feed, and the template loader would still render the tag's feed.
		if ( $is_feed ) {
			exit;
		}
	}
}
