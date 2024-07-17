<?php
use Hostinger\EasyOnboarding\EasyOnboarding;
use Hostinger\EasyOnboarding\Activator;
use Hostinger\EasyOnboarding\Deactivator;
use Hostinger\WpMenuManager\Manager;
use Hostinger\Surveys\Loader;
use Hostinger\Amplitude\AmplitudeLoader;

$vendor_file = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if ( file_exists( $vendor_file ) ) {
	require_once $vendor_file;
} else {
    return;
}

/**
 * Plugin activation hook.
 */
function hostinger_easy_onboarding_activate(): void {
	Activator::activate();
}

/**
 * Plugin deactivation hook.
 */
function hostinger_easy_onboarding_deactivate(): void {
	Deactivator::deactivate();
}

if ( ! function_exists( 'hostinger_load_menus' ) ) {
	function hostinger_load_menus(): void {
		$manager = Manager::getInstance();
		$manager->boot();
	}
}
if ( ! function_exists( 'hostinger_add_surveys' ) ) {
	function hostinger_add_surveys(): void {
		$surveys = Loader::getInstance();
		$surveys->boot();
	}
}

if ( ! function_exists( 'hostinger_load_amplitude' ) ) {
	function hostinger_load_amplitude(): void {
		$amplitude = AmplitudeLoader::getInstance();
		$amplitude->boot();
	}
}

if ( ! has_action( 'plugins_loaded', 'hostinger_load_menus' ) ) {
	add_action( 'plugins_loaded', 'hostinger_load_menus' );
}
if ( ! has_action( 'plugins_loaded', 'hostinger_add_surveys' ) ) {
	add_action( 'plugins_loaded', 'hostinger_add_surveys' );
}

if ( ! has_action( 'plugins_loaded', 'hostinger_load_amplitude' ) ) {
	add_action( 'plugins_loaded', 'hostinger_load_amplitude' );
}

register_activation_hook( HOSTINGER_EASY_ONBOARDING_PLUGIN_FILE, 'hostinger_easy_onboarding_activate' );
register_deactivation_hook( HOSTINGER_EASY_ONBOARDING_PLUGIN_FILE, 'hostinger_easy_onboarding_deactivate' );


$hostingerEasyOnboarding = new EasyOnboarding();
$hostingerEasyOnboarding->run();
