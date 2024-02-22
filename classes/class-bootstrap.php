<?php
/**
 * Bootstraps the plugin
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

use PigeonWP\Traits\Singleton;

/**
 * Class Bootstrap
 *
 * Gets the plugin started and holds plugin objects.
 *
 * @since 1.6
 */
class Bootstrap {

	use Singleton;

	/**
	 * A container to hold objects.
	 *
	 * @since 1.6
	 *
	 * @var array Plugin objects.
	 */
	protected $container = array();

	/**
	 * Constructor.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function __construct() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'pigeon', false, PIGEONWP_DIR . 'languages' );
	}

	/**
	 * Loads the different parts of the plugin and intializes the objects. Also
	 * stores the object in a container.
	 *
	 * @since 1.6
	 */
	public function load() {
		$class_load_path = PIGEONWP_DIR . 'config/load.php';

		if ( file_exists( $class_load_path ) ) {
			require_once $class_load_path;
		}

		// Load classes.
		if ( ! empty( $classes ) && is_array( $classes ) ) {
			foreach ( $classes as $class ) {
				$this->load_class( $class );
			}
		}

		// Init container objects.
		foreach ( $this->container as $object ) {
			$this->maybe_call_hooks( $object );
		}
	}

	/**
	 * Takes a class name, creates an object and adds it
	 * to the container.
	 *
	 * @since 1.6
	 *
	 * @param string $class_name The class to instantiate.
	 */
	protected function load_class( $class_name ) {
		if ( class_exists( $class_name ) ) {
			$key = strtolower( str_replace( 'PigeonWP\\', '', $class_name ) );
			$key = str_replace( '_', '-', $key );

			// Add component to container.
			$this->container[ $key ] = new $class_name();
		}
	}

	/**
	 * Takes an object and call the hooks method if it is available.
	 *
	 * @since 1.6
	 *
	 * @param object $object_name The object to initiate.
	 */
	protected function maybe_call_hooks( $object_name ) {
		if ( is_callable( array( $object_name, 'hooks' ) ) ) {
			$object_name->hooks();
		}
	}

	/**
	 * Return the object container.
	 *
	 * @since 1.6
	 *
	 * @param string|bool|void $item The item identifier of the object to fetch.
	 *
	 * @return array|bool
	 */
	public function get_container( $item = false ) {
		if ( ! empty( $item ) ) {
			if ( ! empty( $this->container[ $item ] ) ) {
				return $this->container[ $item ];
			}

			return false;
		}

		return $this->container;
	}
}
