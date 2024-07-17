<?php

namespace Hostinger\EasyOnboarding;

use Hostinger\EasyOnboarding\Admin\Onboarding\WelcomeCards;
use Hostinger\EasyOnboarding\DefaultOptions;
use Hostinger\WpHelper\Utils;

defined( 'ABSPATH' ) || exit;

class Activator {

	public static function activate(): void {
		$options = new DefaultOptions();
		$options->add_options();

		self::update_installation_state_on_activation();
        self::update_onboarding_state_on_activation();
	}

	/**
	 * Saves installation state.
	 */
	public static function update_installation_state_on_activation(): void {
		$installation_state = get_option( 'hts_new_installation', false );

		if ( $installation_state !== 'old' ) {
			add_option( 'hts_new_installation', 'new' );
		}
	}

    /**
     * Enable onboarding
     *
     * @return void
     */
    public static function update_onboarding_state_on_activation(): void {
        $onboarding_choice_done = get_option( 'hostinger_onboarding_choice_done', null );

        if ( $onboarding_choice_done === null ) {
            // Hide WooCommerce task list and onboarding profile.

            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
                $hidden_tasks = get_option( 'woocommerce_task_list_hidden_lists', false );

                if($hidden_tasks === false) {
                    update_option( 'woocommerce_task_list_hidden_lists', array( 'setup' ) );
                }

                $onboarding_profile = get_option( 'woocommerce_onboarding_profile', false );

                if($onboarding_profile === false) {
                    update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true ) );
                }

                // Disable Flexible shipping activation redirect by setting value to true
                update_option( 'flexible-shipping-activation-redirected', 1 );
            }
        }
    }
}
