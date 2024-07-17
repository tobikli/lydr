<?php

namespace Hostinger\Surveys;

class Assets
{
    public Loader $surveys;
    public function init(): void
    {
        add_action('admin_enqueue_scripts', [ $this, 'enqueueAdminAssets' ]);
    }

    /**
     * @return void
     */
    public function enqueueAdminAssets(): void
    {
        $composerJsonPath = __DIR__ . '/../composer.json';
        if (file_exists($composerJsonPath)) {
            $composerConfig = json_decode(file_get_contents($composerJsonPath), true);
            $version = $composerConfig['version'] ?? '1';
        }

        $pluginInfo = $this->surveys->getPluginInfo();
        $jsScriptPath = $pluginInfo . '/vendor/hostinger/hostinger-wp-surveys/assets/js/hostinger-surveys.min.js';
        $cssStylePath = $pluginInfo . '/vendor/hostinger/hostinger-wp-surveys/assets/css/style.min.css';

        wp_enqueue_script(
            'hostinger_surveys_scripts',
            $jsScriptPath,
            ['jquery'],
            $version,
            ['strategy' => 'defer']
        );
        wp_localize_script(
            'hostinger_surveys_scripts',
            'hostingerContainer',
            [
                'url'   => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('hts-ajax-nonce'),
            ]
        );
        wp_enqueue_style(
            'hostinger_surveys_styles',
            $cssStylePath,
            [],
            $version
        );
    }
}
