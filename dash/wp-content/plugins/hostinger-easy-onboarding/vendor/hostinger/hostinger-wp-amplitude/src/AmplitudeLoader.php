<?php

namespace Hostinger\Amplitude;

use Hostinger\WpHelper\Config;
use Hostinger\WpHelper\Constants;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Utils as Helper;

class AmplitudeLoader
{
	/**
	 * @var AmplitudeLoader instance.
	 */
	private static ?AmplitudeLoader $instance = null;

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

		return true;
	}

	/**
	 * @return void
	 */
	public function registerModules(): void
	{
		// Amplitude Manager.
		$this->objects['amplitude_rest'] = new Rest();
		$this->objects['amplitude_rest']->init();

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
			if (property_exists($object, 'amplitude')) {
				$object->setAmplitude($this);
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
}
