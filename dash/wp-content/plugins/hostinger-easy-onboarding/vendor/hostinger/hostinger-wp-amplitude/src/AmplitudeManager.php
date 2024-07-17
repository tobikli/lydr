<?php

namespace Hostinger\Amplitude;

use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Utils as Helper;

class AmplitudeManager {
    public const AMPLITUDE_ENDPOINT = '/v3/wordpress/plugin/trigger-event';
    private const CACHE_ONE_DAY = 86400;

    private Config $configHandler;
    private Client $client;
    private Helper $helper;

    public function __construct(
        Helper $helper,
        Config $configHandler,
        Client $client
    ) {
        $this->helper        = $helper;
        $this->configHandler = $configHandler;
        $this->client        = $client;
    }

    public function sendRequest( string $endpoint, array $params ): array {
        try {
            if ( !$this->shouldSendAmplitudeEvent( $params ) ) {
                return array();
            }

            $response = $this->client->post( $endpoint, array( 'params' => $params ) );
            return $response;
        } catch ( \Exception $exception ) {
            $this->helper->errorLog( 'Error sending request: ' . $exception->getMessage() );
            return ['status' => 'error', 'message' => $exception->getMessage()];
        }
    }

    // Events which firing once per day
    public static function getSingleAmplitudeEvents(): array {
        return apply_filters( 'hostinger_once_per_day_events', [] );
    }

    public function shouldSendAmplitudeEvent( array $params ): bool {
        $oneTimePerDay = self::getSingleAmplitudeEvents();

        $eventAction = sanitize_text_field( !empty( $params['action'] ) ? $params['action'] : '' );

        if ( in_array( $eventAction, $oneTimePerDay ) && get_transient( $eventAction ) ) {
            return false;
        }

        if ( in_array( $eventAction, $oneTimePerDay ) ) {
            set_transient( $eventAction, true, self::CACHE_ONE_DAY );
        }

        return true;
    }
}
