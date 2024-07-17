<?php

namespace Hostinger\Surveys;

use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Utils as Helper;

defined('ABSPATH') || exit;

class SurveyManager
{
    public const CACHE_THREE_HOURS = 3600 * 3;
    public const TIME_24_HOURS = 86400;
    public const CLIENT_SURVEY_IDENTIFIER = 'customer_satisfaction_score';
    public const CLIENT_WOO_COMPLETED_ACTIONS = 'woocommerce_task_list_tracked_completed_tasks';
    public const SUBMITTED_SURVEY_TRANSIENT = 'submitted_survey_transient';
    public const IS_CLIENT_ELIGIBLE_TRANSIENT_RESPONSE = 'client_eligibility_transient_response';
    public const IS_CLIENT_ELIGIBLE_TRANSIENT_REQUEST = 'survey_questions_transient_request';
    public const LOCATION_SLUG = 'location';
    public const HIDE_SURVEY_TRANSIENT = 'hts_hide_survey';
    public const WOOCOMMERCE_PAGES = [
        '/wp-admin/admin.php?page=wc-admin',
        '/wp-admin/edit.php?post_type=shop_order',
        '/wp-admin/admin.php?page=wc-admin&path=/customers',
        '/wp-admin/edit.php?post_type=shop_coupon&legacy_coupon_menu=1',
        '/wp-admin/admin.php?page=wc-admin&path=/marketing',
        '/wp-admin/admin.php?page=wc-reports',
        '/wp-admin/admin.php?page=wc-settings',
        '/wp-admin/admin.php?page=wc-status',
        '/wp-admin/admin.php?page=wc-admin&path=/extensions',
        '/wp-admin/edit.php?post_type=product',
        '/wp-admin/post-new.php?post_type=product',
        '/wp-admin/edit.php?post_type=product&page=product-reviews',
        '/wp-admin/edit.php?post_type=product&page=product_attributes',
        '/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product',
        '/wp-admin/edit-tags.php?taxonomy=product_tag&post_type=product',
        '/wp-admin/admin.php?page=wc-admin&path=/analytics/overview',
        '/wp-admin/admin.php?page=wc-admin',
    ];

    private Config $configHandler;
    private Helper $helper;
    private Rest $surveysRest;

    public function __construct(
        Helper $helper,
        Config $configHandler,
        Rest $surveysRest
    ) {
        $this->helper        = $helper;
        $this->configHandler = $configHandler;
        $this->surveysRest   = $surveysRest;
    }

    public function isWoocommerceAdminPage(): bool
    {
        if (! isset($_SERVER['REQUEST_URI'])) {
            return false;
        }

        $currentUri = sanitize_text_field($_SERVER['REQUEST_URI']);

        if (defined('DOING_AJAX') && \DOING_AJAX) {
            return false;
        }

        if (isset($currentUri) && strpos($currentUri, '/wp-json/') !== false) {
            return false;
        }

        foreach (self::WOOCOMMERCE_PAGES as $page) {
            if (strpos($currentUri, $page) !== false) {
                return true;
            }
        }

        return false;
    }

    public function isClientEligible(): bool
    {
        $transientRequestKey  = self::IS_CLIENT_ELIGIBLE_TRANSIENT_REQUEST;
        $transientResponseKey = self::IS_CLIENT_ELIGIBLE_TRANSIENT_RESPONSE;
        $cachedEligibility    = get_transient($transientResponseKey);

        // Check if a request is already in progress
        if (get_transient('hts_eligible_request')) {
            return false;
        }

        // Check if transient response exists
        if ($cachedEligibility && ( $cachedEligibility === 'eligible' || $cachedEligibility === 'not_eligible' )) {
            return $cachedEligibility === 'eligible';
        }

        // Attempt to set transient request
        if (! $this->helper->checkTransientEligibility($transientRequestKey, self::CACHE_THREE_HOURS)) {
            return false;
        }

        try {
            // Set transient flag to indicate request in progress
            set_transient('hts_eligible_request', 'in_progress', 60);

            $isEligible = $this->surveysRest->isClientEligible();

            // Clear the request transient flag
            delete_transient('hts_eligible_request');

            if (has_action('litespeed_purge_all')) {
                do_action('litespeed_purge_all');
            }

            if ($isEligible) {
                set_transient($transientResponseKey, 'eligible', self::CACHE_THREE_HOURS);

                return $isEligible;
            }

            set_transient($transientResponseKey, 'not_eligible', self::CACHE_THREE_HOURS);

            return false;
        } catch (\Exception $exception) {
            $this->helper->errorLog('Error checking eligibility: ' . $exception->getMessage());

            return false;
        }
    }

    public function submitSurveyAnswers(array $answers, string $surveyId, string $surveyLocation): void
    {
        $data = [
            'identifier' => self::CLIENT_SURVEY_IDENTIFIER,
            'answers'    => [
                [
                    'question_slug' => self::LOCATION_SLUG,
                    'answer'        => $surveyLocation,
                ]
            ],
        ];

        $answers = $this->addUserAnswers($data, $answers);

        $success = $this->surveysRest->submitSurveyData($answers);

        set_transient(self::SUBMITTED_SURVEY_TRANSIENT, true, self::TIME_24_HOURS);

        if ($success) {
            delete_transient(self::IS_CLIENT_ELIGIBLE_TRANSIENT_RESPONSE);
            $settingKey = $surveyId . '_survey_completed';
        }

        update_option('hostinger_' . $settingKey, true);
        wp_send_json('Survey completed');

        if (has_action('litespeed_purge_all')) {
            do_action('litespeed_purge_all');
        }
    }

    public function getSpecifiedSurvey(array $activeSurvey): array
    {
        $specifiedSurveyQuestions = [
            'pages'               => [],
            'showQuestionNumbers' => 'off',
            'showTOC'             => false,
            'pageNextText'        => __('Next', 'hostinger-wp-surveys'),
            'pagePrevText'        => __('Previous', 'hostinger-wp-surveys'),
            'completeText'        => __('Submit', 'hostinger-wp-surveys'),
            'completedHtml'       => __('Thank you for completing the survey !', 'hostinger-wp-surveys'),
            'requiredText'        => '*',
        ];

        foreach ($activeSurvey['questions'] as $question) {
            $element = [
                'type'              => $question['type'],
                'name'              => $question['slug'],
                'title'             => $question['question'],
                'requiredErrorText' => __('Response required.', 'hostinger-wp-surveys'),
            ];

            if ($question['slug'] == 'comment') {
                $element['maxLength'] = 250;
            }

            if ($question['slug'] == 'score') {
                $element['rateMin']            = '1';
                $element['rateMax']            = '10';
                $element['minRateDescription'] = __('Poor', 'hostinger-wp-surveys');
                $element['maxRateDescription'] = __('Excellent', 'hostinger-wp-surveys');
            }

            if ($question['isRequired']) {
                $element['isRequired'] = true;
            }

            $questionData = [
                'name'     => $question['slug'],
                'elements' => [ $element ],
            ];

            $specifiedSurveyQuestions['pages'][] = $questionData;
        }

        return $specifiedSurveyQuestions;
    }

    public static function getHostingerSurveys(): array
    {
        return apply_filters('hostinger_add_surveys', []);
    }

    public function generateSurveyHtml(): string
    {
        $allSurveys   = SurveyManager::getHostingerSurveys();

        if (empty($allSurveys)) {
            return '';
        }

        $activeSurvey = $this->getHighestPrioritySurvey($allSurveys);
        ob_start();
        ?>
        <div class="hts-survey-wrapper"
             data-survey-id="<?php echo esc_attr($activeSurvey['id']) ?>"
             data-location="<?php echo esc_attr($activeSurvey['location']) ?>"
             data-surveys="<?php echo esc_attr(json_encode($this->getSpecifiedSurvey($activeSurvey))) ?>">
            <div class="close-btn">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 24 24">
                    <path d="M 4.9902344 3.9902344 A 1.0001 1.0001 0 0 0 4.2929688 5.7070312 L 10.585938 12 L 4.2929688 18.292969 A 1.0001 1.0001 0 1 0 5.7070312 19.707031 L 12 13.414062 L 18.292969 19.707031 A 1.0001 1.0001 0 1 0 19.707031 18.292969 L 13.414062 12 L 19.707031 5.7070312 A 1.0001 1.0001 0 0 0 18.980469 3.9902344 A 1.0001 1.0001 0 0 0 18.292969 4.2929688 L 12 10.585938 L 5.7070312 4.2929688 A 1.0001 1.0001 0 0 0 4.9902344 3.9902344 z"></path>
                </svg>
            </div>
            <div id="hostinger-feedback-survey"></div>
            <div id="hts-questionsLeft">
                <span id="hts-currentQuestion">1</span>
                <?php
                echo esc_html(
                    __(
                        'Question',
                        'hostinger-wp-surveys'
                    )
                );
                ?>
                <?php echo esc_html(__('of ', 'hostinger-wp-surveys')); ?>
                <span id="hts-allQuestions"></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function defaultWoocommerceSurveyCompleted(): bool
    {
        $completedActions         = get_option(self::CLIENT_WOO_COMPLETED_ACTIONS, []);
        $requiredCompletedActions = [ 'products', 'payments' ];

        return empty(array_diff($requiredCompletedActions, $completedActions));
    }

    public function getOldestProductDate(): int
    {
        global $wpdb;

        $getProductDate = $wpdb->prepare(
            "
	        SELECT MIN(post_date) 
	        FROM {$wpdb->posts} 
	        WHERE post_type = %s
	        AND post_status = %s
        ",
            'product',
            'publish'
        );

        $oldestProductDate = $wpdb->get_var($getProductDate);

        if ($oldestProductDate !== null) {
            $oldestProductDateTimestamp = strtotime($oldestProductDate);

            if ($oldestProductDateTimestamp !== false) {
                return $oldestProductDateTimestamp;
            }
        }

        return strtotime('-1 year');
    }

    public function isTimeElapsed(string $firstLoginAt, int $timeInSeconds): bool
    {
        $currentTime = time();
        $timeElapsed = $currentTime - $timeInSeconds;

        return $timeElapsed >= $firstLoginAt;
    }

    public function activeSurveyHtml(): void
    {
        if (! did_action('activeSurvey')) {
            $survey_html = $this->generateSurveyHtml('');
            echo $survey_html;
            do_action('activeSurvey');
        }
    }

    public static function addSurvey($surveyId, $scoreQuestion, $commentQuestion, $location, $priority)
    {
        $surveyData = [
            'id'        => $surveyId,
            'questions' => [
                [
                    'type'       => 'rating',
                    'slug'       => 'score',
                    'question'   => $scoreQuestion,
                    'isRequired' => true
                ],
                [
                    'type'       => 'comment',
                    'slug'       => 'comment',
                    'question'   => $commentQuestion,
                    'isRequired' => false
                ],
            ],
            'location'  => $location,
            'priority'  => $priority,
        ];

        $surveys[] = $surveyData;

        return $surveys;
    }

    public function getHighestPrioritySurvey(array $allSurveys): array
    {
        $surveys = array_merge([], ...$allSurveys);

        return array_reduce($surveys, function ($highestPrioritySurvey, $currentSurvey) {
            return ( ! isset($highestPrioritySurvey['priority']) ||
                     ( isset($currentSurvey['priority']) && $currentSurvey['priority'] > $highestPrioritySurvey['priority'] ) )
                ? $currentSurvey : $highestPrioritySurvey;
        }, []);
    }

    /**
     * Checks if a specific survey is not completed
     * @param string $surveyId
     * @return bool
     */
    public function isSurveyNotCompleted(string $surveyId): bool
    {
        $optionKey = 'hostinger_' . $surveyId . '_survey_completed';
        $notCompleted = ! get_option($optionKey, '');
        return $notCompleted;
    }

    private function addUserAnswers(array $data, array $answers): array
    {
        foreach ($answers as $answerSlug => $answer) {
            $data['answers'][] = [
                'question_slug' => $answerSlug,
                'answer'        => $answer,
            ];
        }

        return $data;
    }

    public function isSurveyHidden(): bool
    {
        $surveyVisibility = get_transient(self::HIDE_SURVEY_TRANSIENT);
        return $surveyVisibility !== false;
    }
}
