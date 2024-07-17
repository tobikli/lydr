<?php

namespace Hostinger\Surveys;

use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Utils;
use Hostinger\Surveys\Ajax as SurveysAjax;

class Loader
{
    /**
     * @var Loader instance.
     */
    private static ?Loader $instance = null;

    /**
     * @var array
     */
    private array $objects = [];

    /**
     * Allow only one instance of class
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public function boot(): bool
    {
        $this->registerModules();
        $this->loadTextDomain();

        return true;
    }

    /**
     * @return void
     */
    public function registerModules(): void
    {
        // Surveys.
        $helper                = new Utils();
        $configHandler         = new Config();
        $surveyRest            = new Rest(
            new Client(
                $configHandler->getConfigValue('base_rest_uri', Constants::HOSTINGER_REST_URI),
                [
                    Config::TOKEN_HEADER  => $helper::getApiToken(),
                    Config::DOMAIN_HEADER => $helper->getHostInfo(),
                ]
            )
        );

        $this->objects['ajax'] = new SurveysAjax($helper, $configHandler, $surveyRest);

        // Assets.
        $this->objects['assets'] = new Assets();
        $this->objects['assets']->init();

        $this->objects['survey_loader'] = new SurveyLoader();
        $this->objects['survey_loader']->run();

        $this->addContainer();
    }

    /**
     * @return bool
     */
    public function addContainer(): bool
    {
        if (empty($this->objects)) {
            return false;
        }

        foreach ($this->objects as $object) {
            if (property_exists($object, 'surveys')) {
                $object->surveys = $this;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getPluginInfo(): string
    {
        $plugin_url = '';

        $plugins = get_plugins();
        foreach ($plugins as $plugin_path => $plugin_info) {
            if (str_contains(__FILE__, 'plugins/' . dirname($plugin_path) . '/')) {
                $plugin_dir = dirname($plugin_path);

                return plugins_url($plugin_dir);
            }
        }

        return $plugin_url;
    }

    /**
     * @return void
     */
    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'hostinger-wp-surveys',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
