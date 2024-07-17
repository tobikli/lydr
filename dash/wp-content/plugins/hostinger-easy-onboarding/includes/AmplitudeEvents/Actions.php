<?php

namespace Hostinger\EasyOnboarding\AmplitudeEvents;

defined( 'ABSPATH' ) || exit;

class Actions {
	public const WOO_ITEM_COMPLETED    = 'wordpress.woocommerce.item_completed';

	public const WOO_READY_TO_SELL    = 'wordpress.woocommerce.store.ready_to_sell';

	public const WOO_SETUP_COMPLETED    = 'wordpress.woocommerce.store_setup.completed';
}
