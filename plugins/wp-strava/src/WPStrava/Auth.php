<?php

abstract class WPStrava_Auth {

	protected $auth_url = 'https://www.strava.com/oauth/authorize?response_type=code';
	protected $feedback = '';

	/**
	 * Factory method to get the correct Auth class based on specified string
	 * or by the options setting.
	 *
	 * @param string $auth 'refresh' or 'forever' (default 'refresh').
	 * @return WPStrava_Auth Instance of Auth
	 * @author Justin Foell <justin@foell.org>
	 */
	public static function get_auth( $auth = 'refresh' ) {
		if ( 'forever' === $auth ) {
			return new WPStrava_AuthForever();
		}
		// Default to refresh.
		return new WPStrava_AuthRefresh();
	}

	abstract protected function get_authorize_url( $client_id );

	public function hook() {
		if ( is_admin() ) {
			add_filter( 'pre_set_transient_settings_errors', array( $this, 'maybe_oauth' ) );
			add_action( 'admin_init', array( $this, 'init' ) );
		}
	}

	/**
	 * This runs after options are saved
	 */
	public function maybe_oauth( $value ) {
		$settings = WPStrava::get_instance()->settings;

		// User is clearing to start-over, don't oauth, ignore other errors.

		$input_args = array(
			'strava_id'            => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
				'flags'  => FILTER_REQUIRE_ARRAY,
			),
			'strava_client_id'     => array(
				'filter' => FILTER_SANITIZE_NUMBER_INT,
				'flags'  => FILTER_REQUIRE_SCALAR,
			),
			'strava_client_secret' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		);

		$input = filter_input_array( INPUT_POST, $input_args );

		if ( is_array( $input['strava_id'] ) && $settings->ids_empty( $input['strava_id'] ) ) {
			return array();
		}

		// Redirect only if all the right options are in place.
		if ( $settings->is_settings_updated( $value ) && $settings->is_options_page() ) {
			// Only re-auth if client ID and secret were saved.
			if ( ! empty( $input['strava_client_id'] ) && ! empty( $input['strava_client_secret'] ) ) {
				wp_redirect( $this->get_authorize_url( $input['strava_client_id'] ) );
				exit();
			}
		}
		return $value;
	}

	public function init() {
		$settings = WPStrava::get_instance()->settings;

		$input_args = array(
			'settings-updated' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'code'             => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		);

		$input = filter_input_array( INPUT_GET, $input_args );

		//only update when redirected back from strava
		if ( ! isset( $input['settings-updated'] ) && $settings->is_settings_page() ) {
			if ( isset( $input['code'] ) ) {
				$info = $this->token_exchange_initial( $input['code'] );
				if ( isset( $info->access_token ) ) {
					// Translators: New strava token
					add_settings_error( 'strava_token', 'strava_token', sprintf( __( 'New Strava token retrieved. %s', 'wp-strava' ), $this->feedback ), 'updated' );
				} else {
					add_settings_error( 'strava_token', 'strava_token', $this->feedback );
				}
			} elseif ( isset( $_GET['error'] ) ) {
				// Translators: authentication error mess
				add_settings_error( 'strava_token', 'strava_token', sprintf( __( 'Error authenticating at Strava: %s', 'wp-strava' ), str_replace( '_', ' ', $_GET['error'] ) ) );
			}
		}
	}

	protected function get_redirect_param() {
		$page_name = WPStrava::get_instance()->settings->get_page_name();
		return rawurlencode( admin_url( "options-general.php?page={$page_name}" ) );
	}

	// Was fetch_token();
	private function token_exchange_initial( $code ) {
		$settings      = WPStrava::get_instance()->settings;
		$client_id     = $settings->client_id;
		$client_secret = $settings->client_secret;

		$settings->delete_id_secret();

		if ( $client_id && $client_secret ) {

			$data = array(
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'code'          => $code,
			);

			$data = $this->add_initial_params( $data );

			try {
				$strava_info = $this->token_request( $data );
			} catch ( WPStrava_Exception $e ) {
				wp_die( $e->to_html() ); // phpcs:ignore -- Debug only.
			}

			if ( isset( $strava_info->access_token ) ) {
				$settings->add_id( $client_id );
				$settings->save_info( $client_id, $client_secret, $strava_info );

				$this->feedback .= __( 'Successfully authenticated.', 'wp-strava' );
				return $strava_info;
			}

			// Translators: error message from Strava
			$this->feedback .= sprintf( __( 'There was an error receiving data from Strava: <pre>%s</pre>', 'wp-strava' ), print_r( $strava_info, true ) ); // phpcs:ignore -- Debug output.
			return false;

		}

		$this->feedback .= __( 'Missing Client ID or Client Secret.', 'wp-strava' );
		return false;
	}

	protected function token_request( $data ) {
		$api = new WPStrava_API();
		return $api->post( 'oauth/token', $data );
	}

	protected function add_initial_params( $data ) {
		return $data;
	}

}
