<?php
/**
 * Pigeon for WordPress
 *
 * The Pigeon Paywall plugin for WordPress
 *
 * @package   Pigeon for WordPress
 * @author    Pigeon <support@pigeon.io>
 * @license   GPL-2.0+
 * @link      http://pigeon.io
 * @copyright 2014-2019 Sabramedia
 *
 * @wordpress-plugin
 * Plugin Name:       Pigeon for WordPress
 * Plugin URI:        http://pigeon.io
 * Description:       The Pigeon Paywall plugin for WordPress
 * Version:           1.6
 * Author:            Sabramedia
 * Text Domain:       pigeonwp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

namespace PigeonWP;

// Config.
require_once 'config/config.php';

// Autoload classes.
require_once 'helpers/autoloader.php';

// Load helper functions.
require_once 'helpers/functions.php';

$bootstrap = Bootstrap::get_instance();
$bootstrap->load();
