<?php
/**
 * Protect PDFs with paywall.
 *
 * @since 1.6
 *
 * @package PigeonWP
 */

namespace PigeonWP;

/**
 * Class PDF
 *
 * @since 1.6
 */
class PDF {

	/**
	 * The nonce key.
	 */
	const NONCE_ACTION = 'pigeon_pdf_download';

	/**
	 * Hooks
	 *
	 * @since 1.6
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'download' ) );
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_filter( 'robots_txt', array( $this, 'disallow_pdfs' ), 0 );
		add_action( 'update_option_pigeon_content_pdf_paywall', array( $this, 'flush_rewrite_rules' ) );
		add_action( 'query_vars', array( $this, 'set_query_vars' ) );
	}

	/**
	 * Download the PDF file after passing the paywall.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function download() {
		if ( ! empty( $_GET['pdf_download'] ) ) {
			$attachment_id = $this->get_attachment_id();

			// Validate the PDF is legitimate.
			if ( empty( $attachment_id ) ) {
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				return;
			}

			// If we have a nonce, the paywall has been checked and we are ready to download.
			if ( empty( $_GET['download_key'] ) ) {
				add_filter(
					'template_include',
					function () {
						return PIGEONWP_DIR . 'templates/public/download-pdf.php';
					},
					10,
					0
				);

				add_action(
					'request',
					function ( $query ) {
						$attachment_id = $this->get_attachment_id();

						if ( ! empty( $attachment_id ) ) {
							$query['p']             = $attachment_id;
							$query['attachment_id'] = $attachment_id;
							$query['post_type']     = 'attachment';
							$query['pagename']      = get_post_field( 'post_name', $attachment_id );
						}

						return $query;
					}
				);

				// Avoid redirecting canonically.
				remove_action( 'template_redirect', 'redirect_canonical' );

				return;
			} else {
				$nonce = sanitize_text_field( wp_unslash( $_GET['download_key'] ) );
			}

			// Passed the paywall, and file is ready to download.
			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
				// Allow the file download.
				$file_path = sprintf( '%s/%s', WP_CONTENT_DIR, $this->get_download_uri() );
				header( 'Expires: -1' );
				header( 'Cache-Control: no-cache' );
				header( 'Content-Description: File Transfer' );
				header( 'Content-Length: ' . filesize( $file_path ) );
				header( 'Content-type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=' . basename( $file_path ) );
				readfile( $file_path ); // @phpcs:ignore
				exit;
			}
		}
	}

	/**
	 * Get the attachment ID from the request file url.
	 *
	 * @since 1.6
	 *
	 * @return string
	 */
	protected function get_attachment_id() {
		return attachment_url_to_postid( content_url( $this->get_download_uri() ) );
	}

	/**
	 * Get the file URI for the current request.
	 *
	 * @since 1.6
	 *
	 * @return string
	 */
	protected function get_download_uri() {
		if ( ! empty( $_GET['pdf_download'] ) ) { // @phpcs:ignore
			$uri = urldecode( sanitize_text_field( wp_unslash( $_GET['pdf_download'] ) ) ); // @phpcs:ignore
			return sprintf( 'uploads/%s', $uri );
		}

		return '';
	}

	/**
	 * Protect PDF's from search engine indexes.
	 *
	 * @since 1.6
	 *
	 * @param string $robots_txt The current robots.txt content.
	 *
	 * @return string
	 */
	public function disallow_pdfs( $robots_txt ) {
		$settings = get_plugin_settings();

		if ( ! empty( $settings['pigeon_content_pdf_index'] ) && 1 === (int) $settings['pigeon_content_pdf_index'] ) {
			$robots_txt .= "Disallow: *.pdf\n";
		}

		return $robots_txt;
	}

	/**
	 * Add a rewrite rule for PDF documents.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function add_rewrite_rule() {
		$settings = get_plugin_settings();

		if ( ! empty( $settings['pigeon_content_pdf_paywall'] ) && 1 === (int) $settings['pigeon_content_pdf_paywall'] ) {
			global $wp_rewrite;
			$wp_rewrite->add_external_rule( 'wp-content/uploads/(.*\.pdf)', 'index.php?pdf_download=$1' );
		}
	}

	/**
	 * Flush rewrite rules when our settings page is updated.
	 *
	 * @since 1.6
	 *
	 * @return void
	 */
	public function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Set Query Variables.
	 *
	 * @since 1.6
	 *
	 * @param array $query_vars Existing query variables.
	 *
	 * @return array
	 */
	public function set_query_vars( $query_vars ) {
		$query_vars[] = 'pdf_download';
		$query_vars[] = 'download_key';
		return $query_vars;
	}
}
