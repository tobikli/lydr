<?php

namespace Hostinger\Surveys;

use Hostinger\Surveys\Rest;
use Hostinger\Surveys\SurveyManager;
use Hostinger\WpHelper\Requests\Client as RequestClient;
use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpHelper\Constants;

class SurveyLoader
{
    public function run(): void
    {
        if (is_admin()) {
            $this->showSurvey();
        }
    }

    private function showSurvey(): void
    {
        $helper           = new Helper();
        $configHandler   = new Config();
        $client           = new RequestClient(
            $configHandler->getConfigValue('base_rest_uri', Constants::HOSTINGER_REST_URI),
            [
                Config::TOKEN_HEADER  => $helper::getApiToken(),
                Config::DOMAIN_HEADER => $helper->getHostInfo(),
            ]
        );
        $rest             = new Rest($client);
        $surveys          = new SurveyManager($helper, $configHandler, $rest);

        add_action('admin_footer', [ $surveys, 'activeSurveyHtml' ], 10);
    }
}
