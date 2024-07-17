<?php

namespace Hostinger\Surveys;

use Hostinger\WpHelper\Requests\Client;

defined('ABSPATH') || exit;

class Rest
{
    public const SUBMIT_SURVEY             = '/v3/wordpress/survey/store';
    public const CLIENT_SURVEY_ELIGIBILITY = '/v3/wordpress/survey/client-eligible';
    public const CLIENT_SURVEY_IDENTIFIER  = 'customer_satisfaction_score';

    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function isClientEligible(): bool
    {
        $response = $this->client->get(
            self::CLIENT_SURVEY_ELIGIBILITY,
            [
                'identifier' => self::CLIENT_SURVEY_IDENTIFIER,
            ],
            [],
            10
        );

        $decoded_response = $this->decodeResponse($response);
        $response_data    = $decoded_response['response_data']['data'] ?? null;

        if ($response_data !== true) {
            return false;
        }

        return (bool) $this->getResult($response);
    }

    public function submitSurveyData(array $data): bool
    {
        $response = $this->client->post(self::SUBMIT_SURVEY, $data);
        return $this->getResult($response);
    }

    /**
     * @param array|WP_Error $response
     *
     * @return mixed
     */
    public function getResult($response)
    {
        $data = $this->decodeResponse($response);

        if (is_wp_error($data) || $data['response_code'] !== 200) {
            error_log('Error: ' . $data['response_body']);
            return false;
        }

        return $data['response_data']['data'];
    }

    /**
     * @param array|WP_Error $response
     *
     * @return array
     */
    public function decodeResponse($response): array
    {
        $response_body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        $response_data = json_decode($response_body, true);

        if (! is_array($response_data)) {
            $response_data = [ 'data' => null ];
        }

        return [
            'response_code' => $response_code,
            'response_data' => $response_data,
            'response_body' => $response_body,
        ];
    }
}
