<?php

namespace Hostinger\EasyOnboarding\AmplitudeEvents;

defined( 'ABSPATH' ) || exit;

use Hostinger\Amplitude\AmplitudeManager;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;

class Amplitude {
    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var Config
     */
    private Config $configHandler;

    public function __construct()
    {
        $this->helper          = new Helper();
        $this->configHandler   = new Config();
        $this->client          = new Client(
            $this->configHandler->getConfigValue( 'base_rest_uri', Constants::HOSTINGER_REST_URI ),
            array(
                Config::TOKEN_HEADER  => Helper::getApiToken(),
                Config::DOMAIN_HEADER => $this->helper->getHostInfo()
            )
        );
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function send_event( array $params ): array
    {
        $amplitudeManger = new AmplitudeManager( $this->helper, $this->configHandler, $this->client );

        return $amplitudeManger->sendRequest( $amplitudeManger::AMPLITUDE_ENDPOINT, $params );
    }
}