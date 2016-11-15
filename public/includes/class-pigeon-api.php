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
	protected $pigeon_current_page = '';
	protected $pigeon_subdomain = '';
	protected $pigeon_api = '';
	protected $pigeon_session = '';

	protected function setup( $parameters ) {

		// Get the content access setting
		$this->pigeon_settings['content_id'] = isset($parameters['content_id']) ? $parameters['content_id'] : 0;
		$this->pigeon_settings['content_access'] = $parameters['content_access'];
		$this->pigeon_settings['content_title'] = empty($parameters['content_title']) ? "" : urlencode($parameters['content_title']);
		$this->pigeon_settings['content_date'] = empty($parameters['content_date']) ? "" : urlencode($parameters['content_date']);
		$this->pigeon_settings['content_price'] = empty($parameters['content_price']) ? 0 : $parameters['content_price'];
		$this->pigeon_settings['content_value'] = empty($parameters['content_value']) ? 0 : $parameters['content_value'];
		$this->pigeon_settings['content_prompt'] = isset($parameters['content_prompt']) ? $parameters['content_prompt'] : 0;

		$this->pigeon_settings['redirect'] = $parameters['redirect'];

		$this->pigeon_current_page = 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'];
		$this->pigeon_subdomain = $parameters['subdomain'];
		$this->pigeon_api = 'http://' . $this->pigeon_subdomain . '/action/public/vo/pigeon-server';
		$this->pigeon_session = md5( $this->pigeon_subdomain );

		$this->pigeon_data['user'] = $parameters['user'];
		$this->pigeon_data['secret'] = $parameters['secret'];
		$this->pigeon_data['pigeon_version'] = '1.7';
		$this->pigeon_data['ip'] = array_key_exists('HTTP_CLIENT_IP',$_SERVER) ? $_SERVER['HTTP_CLIENT_IP'] : ( array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
		$this->pigeon_data['uri'] = urlencode( 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI'] );
		$this->pigeon_data['referrer'] = array_key_exists("HTTP_REFERER", $_SERVER ) ? urlencode( $_SERVER["HTTP_REFERER"] ) : "";
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
		foreach ( array( ".css", ".js", ".woff", ".eot", ".ttf", ".svg", ".png", ".jpg", ".gif", ".cur" ) as $asset ) {
			if ( strpos( basename( $_SERVER["REQUEST_URI"] ), $asset ) !== FALSE ) {
				return false;
			}
		}

		// Setup our settings and data
		$this->setup( $parameters );

		$special_access = FALSE;
		$pigeon = array();

		if ( array_key_exists( $this->pigeon_session . "_id", $_COOKIE ) && array_key_exists( $this->pigeon_session . "_hash", $_COOKIE ) ) {
			$session_data = array(
				'session_id' => $_COOKIE[$this->pigeon_session . '_id'],
				'session_hash' => $_COOKIE[$this->pigeon_session . '_hash']
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

				header( 'Location: ' . $this->pigeon_api . '?action=validate&set_cookie=1' . $cookie_not_setting . '&redirect_url=' . urlencode( $this->pigeon_current_page ) );
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
				header( 'Location: ' . $this->pigeon_api . '?action=validate&set_cookie=1&reset=1&redirect_url=' . urlencode( $this->pigeon_current_page ) );
				exit();
			}

			$pigeon = array(
				'allowed' => $response['status'] == "redirect" ? 0 : 1,
				'paywalled' => isset($response['paywalled']) ? $response['paywalled'] : 0,
				'user_status' => 0,
				'meter_limit' => $response['meter_limit'],
				'meter' => $response['meter'] >= $response['meter_limit'] ? $response['meter_limit'] : $response['meter'], // Accommodate for interval
				'profile' => $response['profile'], // User profile
				'ssl' => $response["ssl"], // Need to know if subdomain is secure or not.
				'force_content_modal' => array_key_exists("force_content_modal",$response) ? $response["force_content_modal"] : 0 // Force content modal even if a content value isn't turned on
			);

			if( array_key_exists("credits", $response) )
				$pigeon['credits'] = $response['credits'];

			if( array_key_exists("content_expires", $response) )
				$pigeon['content_expires'] = $response['content_expires'];


			$pigeon['content_price'] = $this->pigeon_settings['content_price'];
			$pigeon['content_value'] = $this->pigeon_settings['content_value'];

			if ( $response['status'] == 'redirect' && $this->pigeon_settings['redirect'] ) {
				header( 'Location: ' . $response['redirect'] );
				exit();
			} else {
				if ( $response['status'] == 'logged in' ) {
					$pigeon['user_status'] = 1;
				}
			}
		}

		return $pigeon;

	}

}