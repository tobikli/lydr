<?php

namespace Hostinger\EasyOnboarding\Admin;

use Hostinger\Surveys\SurveyManager;
use Hostinger\WpHelper\Utils as Helper;

class Surveys {
    public const TIME_15_MINUTES = 900;
    public const WOO_SURVEY_ID = 'woo_survey';
    public const PREBUILD_WEBSITE_SURVEY_ID = 'prebuild_website';
    public const AI_ONBOARDING_SURVEY_ID = 'ai_onboarding';
    public const PREBUILD_WEBSITE_SURVEY_LOCATION = 'wordpress_prebuild_website';
    public const WOO_SURVEY_LOCATION = 'wordpress_woocommerce_onboarding';
    public const AI_ONBOARDING_SURVEY_LOCATION = 'wordpress_ai_onboarding';
    public const WOO_SURVEY_PRIORITY = 100;
    public const PREBUILD_WEBSITE_SURVEY_PRIORITY = 90;
    public const AI_ONBOARDING_SURVEY_PRIORITY = 80;
    public const SUBMITTED_SURVEY_TRANSIENT = 'submitted_survey_transient';
    private SurveyManager $surveyManager;

    public function __construct( SurveyManager $surveyManager ) {
        $this->surveyManager = $surveyManager;
    }

    public function init() {
        add_filter( 'hostinger_add_surveys', [ $this, 'createSurveys' ] );
    }

    public function createSurveys( $surveys ) {
        if ( $this->isWoocommerceSurveyEnabled() ) {
            $scoreQuestion   = esc_html__( 'How would you rate your experience setting up a WooCommerce site on our hosting?', 'hostinger-easy-onboarding' );
            $commentQuestion = esc_html__( 'Do you have any comments/suggestions to improve our WooCommerce onboarding?', 'hostinger-easy-onboarding' );
            $wooSurvey       = SurveyManager::addSurvey( self::WOO_SURVEY_ID, $scoreQuestion, $commentQuestion, self::WOO_SURVEY_LOCATION, self::WOO_SURVEY_PRIORITY );
            $surveys[]       = $wooSurvey;
        }

        if ( $this->isPrebuildWebsiteSurveyEnabled() ) {
            $scoreQuestion         = esc_html__( 'How would you rate your experience building a website based on a pre-built template? (Score 1-10)', 'hostinger-easy-onboarding' );
            $commentQuestion       = esc_html__( 'How could we make it easier to create a new WordPress website?', 'hostinger-easy-onboarding' );
            $prebuildWebsiteSurvey = SurveyManager::addSurvey( self::PREBUILD_WEBSITE_SURVEY_ID, $scoreQuestion, $commentQuestion, self::PREBUILD_WEBSITE_SURVEY_LOCATION, self::PREBUILD_WEBSITE_SURVEY_PRIORITY );
            $surveys[]             = $prebuildWebsiteSurvey;
        }

        if ( $this->isAiOnboardingSurveyEnabled() ) {
            $scoreQuestion         = esc_html__( 'How would you rate your experience using our AI content generation tools in onboarding? (Scale 1-10)', 'hostinger-easy-onboarding' );
            $commentQuestion       = esc_html__( 'Do you have any comments/suggestions to improve our AI tools?', 'hostinger-easy-onboarding' );
            $prebuildWebsiteSurvey = SurveyManager::addSurvey( self::AI_ONBOARDING_SURVEY_ID, $scoreQuestion, $commentQuestion, self::AI_ONBOARDING_SURVEY_LOCATION, self::AI_ONBOARDING_SURVEY_PRIORITY );
            $surveys[]             = $prebuildWebsiteSurvey;
        }

        return $surveys;
    }

    public function isWoocommerceSurveyEnabled(): bool {

        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }

        $notSubmitted                = ! get_transient( self::SUBMITTED_SURVEY_TRANSIENT );
        $notCompleted                = $this->surveyManager->isSurveyNotCompleted( self::WOO_SURVEY_ID );
        $isWoocommercePage           = $this->surveyManager->isWoocommerceAdminPage();
        $defaultWoocommerceCompleted = $this->surveyManager->defaultWoocommerceSurveyCompleted();
        $oldestProductDate           = $this->surveyManager->getOldestProductDate();
        $sevenDaysAgo                = strtotime( '-7 days' );
        $isClientEligible            = $this->surveyManager->isClientEligible();

        if ( $oldestProductDate < $sevenDaysAgo ) {
            return false;
        }

        return $notSubmitted && $notCompleted && $isWoocommercePage && $defaultWoocommerceCompleted && $isClientEligible;
    }

    public function isPrebuildWebsiteSurveyEnabled(): bool {

        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }

        $helper                 = new Helper();
        $notSubmitted           = ! get_transient( self::SUBMITTED_SURVEY_TRANSIENT );
        $notCompleted           = $this->surveyManager->isSurveyNotCompleted( self::PREBUILD_WEBSITE_SURVEY_ID );
        $isHostingerAdminPage   = $helper->isThisPage( 'hostinger' );
        $isClientEligible       = $this->surveyManager->isClientEligible();
        $astra_templates_active = $helper->isPluginActive( 'astra-sites' );

        if ( ! $isHostingerAdminPage ) {
            return false;
        }

        return $notSubmitted && $notCompleted && $isClientEligible && $astra_templates_active;
    }

    public function isAiOnboardingSurveyEnabled(): bool {
        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }
        $helper               = new Helper();
        $firstLoginAt         = strtotime( get_option( 'hostinger_first_login_at', time() ) );
        $notSubmitted         = ! get_transient( self::SUBMITTED_SURVEY_TRANSIENT );
        $notCompleted         = $this->surveyManager->isSurveyNotCompleted( self::AI_ONBOARDING_SURVEY_ID );
        $isClientEligible     = $this->surveyManager->isClientEligible();
        $isAiOnboardingPassed = get_option( 'hostinger_ai_onboarding', '' );
        $isHostingerAdminPage = $helper->isThisPage( 'hostinger' );

        if ( ! $isAiOnboardingPassed ) {
            return false;
        }

        if ( $firstLoginAt && ! $this->isTimeElapsed( $firstLoginAt, self::TIME_15_MINUTES ) ) {
            return false;
        }

        return $notSubmitted && $notCompleted && $isClientEligible && $isHostingerAdminPage;
    }

    public function isTimeElapsed( string $firstLoginAt, int $timeInSeconds ): bool {
        $currentTime = time();
        $timeElapsed = $currentTime - $timeInSeconds;

        return $timeElapsed >= $firstLoginAt;
    }

}