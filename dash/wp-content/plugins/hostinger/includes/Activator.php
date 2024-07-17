<?php

namespace Hostinger;

defined( 'ABSPATH' ) || exit;

class Activator {

    /**
     * @return void
     */
	public static function activate(): void {
		$options = new DefaultOptions();
		$options->add_options();

		self::update_installation_state_on_activation();
	}

    /**
     * Saves installation state.
     *
     * @return void
     */
	public static function update_installation_state_on_activation(): void {
		$installation_state = get_option( 'hts_new_installation', false );

		if ( $installation_state !== 'old' ) {
			add_option( 'hts_new_installation', 'new' );
		}
	}
}
