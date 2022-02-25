=== WP Pigeon ===
Contributors: 
Tags: pigeon, paywall
Requires at least: 3.5.1
Tested up to: 5.4.9
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Pigeon Paywall plugin for WordPress

== Description ==

Enable this plugin to start using the Pigeon Paywall on your WordPress website.

== Installation ==

Follow the instructions below to install the plugin

= Installing and activating the plugin via your WordPress dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'wp-pigeon'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Installing and activating the plugin via FTP =

1. Download `wp-pigeon.zip`
2. Extract the `wp-pigeon` directory to your computer
3. Upload the `wp-pigeon` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Changelog ==

= 1.5.9 =
* Categories can be sent to Pigeon for registered users to set content preferences by category from the Pigeon user account

= 1.4.5 =
* Encoded content title properly for special characters.

= 1.4.4 =
* Added get_pigeon_post_meta() method for accessing pigeon-created metadata on the post level in the WP loop.

= 1.4.0 =
* On-demand value can now be set on each post/page. New settings control. Improved uniqueness in JS browser fingerprinting.

= 1.3.1 =
* Added the pigeon_set_access() method to override access at the template level.

= 1.3.0 =
* More control is given over which technology is used for the paywall, server or browser (JavaScript).
* Streamlined the admin settings a bit.

= 1.2.0 =
* Added Pigeon JavaScript plugin and Soundcloud support.

= 1.1.0 =
* Add plugin options to the settings screen

= 1.0.1 =
* Look for other variables that reference the user's IP address
* Bugfixes

= 1.0.0 =
* First version of the plugin

