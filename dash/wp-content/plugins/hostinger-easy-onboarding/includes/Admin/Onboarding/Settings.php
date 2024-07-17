<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

use Hostinger\EasyOnboarding\Admin\Actions as Admin_Actions;

defined( 'ABSPATH' ) || exit;

class Settings {
	public static function all_steps_completed(): bool {
		$actions                = Admin_Actions::ACTIONS_LIST;
		$completed_steps        = get_option( 'hostinger_onboarding_steps', array() );
		$completed_step_actions = array_column( $completed_steps, 'action' );
		$completed_steps_count  = count( array_intersect( $completed_step_actions, $actions ) );

		return $completed_steps_count === count( $actions );
	}
}

new Settings();
