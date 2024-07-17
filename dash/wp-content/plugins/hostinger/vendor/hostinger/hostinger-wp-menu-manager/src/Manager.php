<?php

namespace Hostinger\WpMenuManager;

class Manager
{
    /**
     * @var Manager instance.
     */
    private static ?Manager $instance = null;

    /**
     * @var array
     */
    private array $objects = [];

    /**
     * @var array
     */
    public array $old_plugins = [
        [
            'name' => 'Hostinger',
            'slug' => 'hostinger',
            'version' => '3.0.0',
        ],
        [
            'name' => 'Hostinger Affiliate Plugin',
            'slug' => 'hostinger-affiliate-plugin',
            'version' => '2.0.0',
        ],
        [
            'name' => 'Hostinger AI Assistant',
            'slug' => 'hostinger-ai-assistant',
            'version' => '2.0.0',
        ]
    ];

    /**
     * @var array
     */
    public array $outdated_plugins = [];

    /**
     * @var array
     */
    public array $affected_plugins = [
        'hostinger' => 'Hostinger Tools',
        'hostinger-affiliate-plugin' => 'Hostinger Affiliate Connector',
        'hostinger-ai-assistant' => 'Hostinger AI',
        'hostinger-easy-onboarding' => 'Hostinger Easy Onboarding',
    ];

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
     * @return array|string[]
     */
    public function getOutdatedPlugins(): array
    {
        return $this->outdated_plugins;
    }

    /**
     * @return array
     */
    public function getAffectedActivePlugins(): array
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugins = [];

        if (empty($this->getAffectedPlugins())) {
            return [];
        }

        foreach ($this->getAffectedPlugins() as $plugin_slug => $plugin_name) {
            if (\is_plugin_active($plugin_slug . DIRECTORY_SEPARATOR . $plugin_slug . '.php')) {
                $plugins[$plugin_slug] = $plugin_name;
            }
        }

        return $plugins;
    }

    /**
     * @return array|string[]
     */
    public function getAffectedPlugins(): array
    {
        return $this->affected_plugins;
    }

    /**
     * @return void
     */
    public function boot(): bool
    {
        // Locale.
        $this->loadTextDomain();

        // Modules.
        $this->registerModules();

        return true;
    }

    /**
     * @return void
     */
    public function registerModules(): void
    {
        // Assets.
        $this->objects['assets'] = new Assets();

        // Menus.
        $this->objects['menus'] = new Menus();

        $this->addContainer();

        // Init after container is added.
        $this->objects['assets']->init();
        $this->objects['menus']->init();
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
            if (property_exists($object, 'manager')) {
                $object->setManager($this);
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
            'hostinger-wp-menu-package',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * @return bool
     */
    public function checkCompatibility(): bool
    {
        $outdated_plugins = false;

        if (empty($this->old_plugins)) {
            return false;
        }

        foreach ($this->old_plugins as $plugin_data) {
            if ($this->checkOutdatedPluginVersion($plugin_data['slug'], $plugin_data['version'])) {
                $outdated_plugins = true;
                $this->outdated_plugins[$plugin_data['slug']] = $plugin_data['name'];
            }
        }

        return $outdated_plugins;
    }

    /**
     * If main hostinger plugin is outdated
     *
     * @return false
     */
    public function maybeDoCompatibilityRedirect(): bool
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_name = 'hostinger';
        $current_uri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

        $plugin = current(array_filter($this->old_plugins, fn($p) => $p['slug'] === $plugin_name));

        if (str_contains($current_uri, '/wp-admin/admin.php?page=hostinger')) {
            if (!$this->checkOutdatedPluginVersion($plugin['slug'], $plugin['version'])) {
                wp_redirect(get_admin_url());
                die();
            }
        }

        return true;
    }

    /**
     * @param $plugin_name
     *
     * @return string
     */
    private function getPluginVersion($plugin_name): string
    {
        $version = get_file_data(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin_name . DIRECTORY_SEPARATOR . $plugin_name . '.php', array('Version'), 'plugin');

        if (empty($version[0])) {
            return '';
        }

        return $version[0];
    }

    /**
     * @param $plugin_name
     * @param $compare_version
     *
     * @return bool
     */
    private function checkOutdatedPluginVersion($plugin_name, $compare_version): bool
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (!\is_plugin_active($plugin_name . DIRECTORY_SEPARATOR . $plugin_name . '.php')) {
            return false;
        }

        $version = $this->getPluginVersion($plugin_name);

        if (empty($version)) {
            return false;
        }

        return version_compare($version, $compare_version, '<');
    }
}
