<?php
/**
 * Singleton trait to be used by classes.
 *
 * @since 1.0
 *
 * @package PigeonWP
 */

namespace PigeonWP\Traits;

/**
 * Trait Singleton
 *
 * @since 1.0
 *
 * @codeCoverageIgnore
 *
 * @package PigeonWP\Traits
 */
trait Singleton {

	/**
	 * The object instance.
	 *
	 * @since 1.0
	 *
	 * @var object|null Instance of object.
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since 1.0
	 */
	protected function __construct() {}

	/**
	 * No cloning of the object.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since 1.0
	 */
	final protected function __clone() {}

	/**
	 * Get an instance of the object.
	 *
	 * @since 1.0
	 *
	 * @codeCoverageIgnore
	 *
	 * @return object
	 */
	final public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
