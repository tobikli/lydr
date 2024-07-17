<?php
/**
 * Rest API Routes
 *
 * @package HostingerAffiliatePlugin
 */

namespace Hostinger\Amplitude;

use Hostinger\Amplitude\AmplitudeManager;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;


/**
 * Avoid possibility to get file accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class for handling Rest Api Routes
 */
class Rest {
	/**
	 * Init rest routes
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'registerRoutes' ] );
	}

	/**
	 * @return void
	 */
	public function registerRoutes() {
		$this->registerAmplitudeRoute();
	}

	public function registerAmplitudeRoute(): void {
		register_rest_route(
			'hostinger-amplitude/v1',
			'hostinger-amplitude-event',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'sendAmplitudeEvent' ),
				'permission_callback' => array( $this, 'permissionCheck' ),
			)
		);
	}

	public function sendAmplitudeEvent( $request ) {
		$params          = $request->get_param( 'params' );
		$helper          = new Helper();
		$configHandler   = new Config();
		$client          = new Client(
			$configHandler->getConfigValue( 'base_rest_uri', Constants::HOSTINGER_REST_URI ),
			array(
				Config::TOKEN_HEADER  => $helper::getApiToken(),
				Config::DOMAIN_HEADER => $helper->getHostInfo()
			)
		);


		$amplitudeManger = new AmplitudeManager( $helper, $configHandler, $client );
		$status = $amplitudeManger->sendRequest( $amplitudeManger::AMPLITUDE_ENDPOINT, !empty($params) ? $params : [] );

		$response = new \WP_REST_Response( array( 'status' => $status ) );
		$response->set_headers(array('Cache-Control' => 'no-cache'));
		$response->set_status( \WP_Http::OK );

		return $response;
	}

	/**
	 * @param WP_REST_Request $request WordPress rest request.
	 *
	 * @return bool
	 */
	public function permissionCheck( $request ): bool {
		if ( empty( is_user_logged_in() ) ) {
			return false;
		}

		// Implement custom capabilities when needed.
		return current_user_can( 'manage_options' );
	}
}
