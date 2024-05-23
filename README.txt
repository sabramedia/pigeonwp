=== Pigeon Paywall ===
Contributors: pigeonplatform, sabramedia, mattgeri
Tags: pigeon, paywall, restrict content, protect posts
Requires at least: 5.9
Tested up to: 6.5.3
Stable tag: 1.6.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The official Pigeon Paywall plugin for WordPress

== Description ==

Pigeon is paywall software that allows you to hide your content from users by specifying paywall rules. It offers account management and subscription based billing for your readers and subscribers. Enable this plugin to start using the Pigeon Paywall on your WordPress website.

An account on [Pigeon.io](https://pigeon.io) is required for this plugin to function. Your paywall rules and account management are setup and managed inside your Pigeon.io account. This plugin enables the JavaScript scripts and widgets required to block your content from non-subscribers based on the rules you've setup in your Pigeon.io account. Please read the [terms of service](https://pigeon.io/terms-of-service) and [privacy policy](https://pigeon.io/privacy-policy) for Pigeon.io.

== Installation ==

Follow the instructions below to install the plugin

= Installing and activating the plugin via your WordPress dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'pigeon'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Installing and activating the plugin via FTP =

1. Download `pigeon.zip`
2. Extract the `pigeon` directory to your computer
3. Upload the `pigeon` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= Why are PDFs not being blocked by the paywall? =

If you've enabled the PDF Paywall option in the settings but PDF's are still able to be downloaded when the paywall has run out of credits, then you could have one of the following issues:

1. If you're using Apache, make sure that your `.htaccess` file is writable. Our plugin needs to add a rule to this file. Some large and enterprise hosts don't allow updating of the .htaccess file. Contact your host to check if you're able to write to the `.htaccess` file.
2. If you're using nginx, then you need to add your own rule to the nginx config. Apply the following rule to hide PDF documents behind the paywall:

```
rewrite ^wp-content/uploads/(.*\.pdf)$ "index.php?pdf_download=$1" last;
```

== Changelog ==
= 1.6.4 =
* Add an item to the admin bar to show when the plugin is in demo mode

= 1.6.3 =
* Improve the connection UI for connection with Pigeon
* Fix bug when paywall was not active in demo mode, content would not render

= 1.6.2 =
* Added a connection user interface for connecting a site to Pigeon
* Introduces a demo mode for testing

= 1.6.1 =
* Fix plugin settings link on the plugin listings page
* Show steps to get setup with Pigeon if a domain has not been added yet
* Fixed a bug where the metered access metabox was not saving the first value

= 1.6 =
* Large refactor of codebase to be WordPress Coding Standards compliant
* Include correct .POT translation file
* Remove server paywall option
* Allow hiding of PDF documents behind the paywall
* Allow PDF documents to be excluding from search indexes with a robots.txt rule

= 1.5.13 =
* PHP8 minor version Compatibility updates

= 1.5.12 =
* Force https for api call that does bulk url paywall checking

= 1.5.11 =
* PHP8 Compatibility updates

= 1.5.10 =
* Pigeon API condition checks are made and feedback is given if connectivity fails. Dropped WP SSO in favor of Pigeon being the primary SSO identity provider.

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

