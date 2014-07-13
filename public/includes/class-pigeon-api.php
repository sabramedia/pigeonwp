<?php
/**
 * WP Pigeon
 *
 * @package   WP_Pigeon
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://pigeonpaywall.com/
 * @copyright 2014 Sabramedia
 */

/**
 * The core class for the plugin
 *
 * @package WP_Pigeon
 * @author  Your Name <email@example.com>
 */
class WP_Pigeon_Api {

	protected $pigeon_settings = array();
	protected $pigeon_data = array();
	protected $pigeon_uri = '';
	protected $pigeon_api = '';
	protected $pigeon_session = '';

	protected function setup( $parameters ) {

		// Get the content access setting
		$this->pigeon_settings['content_access'] = $parameters['content_access'];

		// @Todo: allow the user to set this
		$this->pigeon_settings['redirect'] = $parameters['redirect'];

		// @Todo: set this in the settings page
		$pigeon_subdomain = $parameters['subdomain'];

		$this->pigeon_uri = $pigeon_subdomain . '.' . str_replace( 'www.', '', $_SERVER["HTTP_HOST"] );
		$this->pigeon_api = 'http://' . $this->pigeon_uri . '/action/public/vo/pigeon-server';
		$this->pigeon_session = md5( $this->pigeon_uri );

		// @Todo: set this in the settigs page
		$this->pigeon_data['secret'] = $parameters['secret'];
		$this->pigeon_data['pigeon_version'] = '1.7';
		$this->pigeon_data['ip'] = $_SERVER['REMOTE_ADDR'];
		$this->pigeon_data['uri'] = urlencode( 'http://' . $domain . $_SERVER['REQUEST_URI'] );

	}

	protected function send( $post_fields ) {

		$ch = curl_init();
		
		curl_setopt_array(
			$ch,
			array(
				CURLOPT_URL => $this->pigeon_api,
				CURLOPT_TIMEOUT => 20,
				CURLOPT_VERBOSE => 1,
				CURLOPT_COOKIE => 1,
				CURLOPT_SSL_VERIFYPEER => FALSE,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_POST => 1,
				CURLOPT_USERAGENT => ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ? $_SERVER['HTTP_USER_AGENT'] : '' ),
				CURLOPT_POSTFIELDS => $post_fields
			)
		);

		return curl_exec( $ch );

	}

	public function exec( $parameters ) {

		// Avoid sending assets to the Pigeon server. Will reduce impression load and client cost.
		foreach ( array( ".css", ".js", ".woff", ".eot", ".ttf", ".svg", ".png", ".jpg" ) as $asset ) {
			if ( strpos( basename( $_SERVER["REQUEST_URI"] ), $asset ) !== FALSE ) {
				return false;
			}
		}

		// Setup our settings and data
		$this->setup( $parameters );

		$special_access = FALSE;

		if ( array_key_exists( $this->pigeon_session . "_id", $_COOKIE ) && array_key_exists( $this->pigeon_session . "_hash", $_COOKIE ) ) {
			$session_data = array(
				'session_id' => $_COOKIE[$unique_session . '_id'],
				'session_hash' => $_COOKIE[$unique_session . '_hash']
			);

			$this->pigeon_data = array_merge( $this->pigeon_data, $this->pigeon_settings, $session_data );
		} else {
			// Check with Pigeon Server if there are allowed USER AGENTS or IP ADDRESSES
			// Before we go through the Cookie process
			$response = json_decode( $this->send( 'action=get_special&json=' . json_encode( $this->pigeon_data ) ), TRUE );

			// Receive routing response
			if (
				$response['special_access'] !== TRUE &&
				! array_key_exists( 'HTTP_X_MOZ', $_SERVER ) ||
				(
					array_key_exists( 'HTTP_X_MOZ', $_SERVER ) &&
					$_SERVER['HTTP_X_MOZ'] != 'prefetch'
				)
			){
				// reroute to set cookies
				header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
				header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
				
				// HTTP/1.1
				header( 'Cache-Control: no-store, no-cache, must-revalidate' );
				header( 'Cache-Control: post-check=0, pre-check=0', false );
				
				// HTTP/1.0
				header( 'Pragma: no-cache' );
				
				$cookie_not_setting = '';

				if ( array_key_exists( 'pgnc', $_GET ) && $_GET['pgnc'] ){
					// This is sent in order to ensure redirect redundancy doesn't happen if Cookies are turned off.
					// If we are here and the pgnc is set then one attempt to set the cookies has been unsuccessful.
					$cookie_not_setting = "&cookies_disabled=1";
				}

				header( 'Location: ' . $this->pigeon_api . '?action=validate&set_cookie=1' . $cookie_not_setting . '&redirect_url=' . urlencode( 'http://' . $this->pigeon_uri . $_SERVER['REQUEST_URI'] ) );
				exit();
			} else {
				$special_access = TRUE;
				$pigeon['user_status'] = 1;
				$pigeon['special_access'] = 1;
			}
		}

		if (
			! $special_access &&
			! array_key_exists( 'HTTP_X_MOZ', $_SERVER ) ||
			(
				array_key_exists( 'HTTP_X_MOZ', $_SERVER ) &&
				$_SERVER[ 'HTTP_X_MOZ'] != 'prefetch'
			)
		) {
			// POST current page data to pigeon server
			$response = json_decode( $this->send( 'action=validate&json=' . json_encode( $this->pigeon_data ) ), TRUE );

			if ( array_key_exists( 'reset_cookie', $response ) ){
				header( 'Location: ' . $this->pigeon_api . '?action=validate&set_cookie=1&reset=1&redirect_url=' . urlencode( 'http://' . $this->pigeon_uri . $_SERVER['REQUEST_URI'] ) );
				exit();
			}

			$pigeon = array(
				'user_status' => 0,
				'meter_limit' => $response['meter_limit'],
				'meter' => $response['meter'] >= $response['meter_limit'] ? $response['meter_limit'] : $response['meter'], // Accommodate for interval
				'profile' => $response['profile'] // User profile
			);

			if ( $response['status'] == 'redirect' && $user_variables['pigeon_redirect'] ){
				header( 'Location: ' . $response['redirect'] );
				exit();
			} else {
				if ( $response['status'] == 'logged in' ) {
					$pigeon['user_status'] = 1;
				}
			}

			return $pigeon;
		}

		return array();

	}

}