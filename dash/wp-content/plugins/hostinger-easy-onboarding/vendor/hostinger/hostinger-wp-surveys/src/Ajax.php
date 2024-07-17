<?php

namespace Hostinger\Surveys;

use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Constants;
use Hostinger\Surveys\Rest as SurveysRest;
use Hostinger\Surveys\SurveyManager as HostingerSurveys;

defined('ABSPATH') || exit;

class Ajax
{
    private const HIDE_SURVEY_TRANSIENT = 'hts_hide_survey';
    private const THIRTY_DAYS = 86400 * 30;

    private Config $configHandler;
    private Helper $helper;
    private SurveysRest $surveysRest;

    public function __construct(
        Helper $helper,
        Config $configHandler,
        Rest $surveysRest
    ) {
        $this->helper          = $helper;
        $this->configHandler   = $configHandler;
        $this->surveysRest     = $surveysRest;

        add_action('init', [ $this, 'defineAjaxEvents' ], 0);
    }

    public function defineAjaxEvents(): void
    {
        $events = [
            'submitSurvey',
            'hideSurvey',
        ];

        foreach ($events as $event) {
            add_action('wp_ajax_hostinger_' . $event, [ $this, $event ]);
        }
    }

    public function submitSurvey(): void
    {
        $nonce           = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        $survey_results  = isset($_POST['survey_results']) ? sanitize_text_field($_POST['survey_results']) : '';
        $survey_location = isset($_POST['survey_location']) ? sanitize_text_field($_POST['survey_location']) : '';
        $survey_id = isset($_POST['survey_id']) ? sanitize_text_field($_POST['survey_id']) : '';
        $surveys         = new HostingerSurveys($this->helper, $this->configHandler, $this->surveysRest);

        $security_check = $this->requestSecurityCheck($nonce);

        if (! empty($security_check)) {
            wp_send_json_error($security_check);
        }

        $decoded_json = json_decode(stripslashes($survey_results), true);
        $surveys->submitSurveyAnswers($decoded_json, $survey_id, $survey_location);
    }

    public function hideSurvey(): void
    {
        $nonce          = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        $transient_key  = self::HIDE_SURVEY_TRANSIENT;
        $security_check = $this->requestSecurityCheck($nonce);

        if (! empty($security_check)) {
            wp_send_json_error($security_check);
        }

        if (false === get_transient($transient_key)) {
            set_transient($transient_key, time(), self::THIRTY_DAYS);
        }

        wp_send_json_success([]);
    }

    public function requestSecurityCheck($nonce)
    {
        if (! wp_verify_nonce($nonce, 'hts-ajax-nonce')) {
            return 'Invalid nonce';
        }

        if (! current_user_can('manage_options')) {
            return 'Lack of permissions';
        }

        return false;
    }
}
