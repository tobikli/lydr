<?php

namespace Hostinger\WpHelper;

defined( 'ABSPATH' ) || exit;

class Constants {
	public const TOKEN_HEADER  = 'X-Hpanel-Order-Token';
	public const DOMAIN_HEADER = 'X-Hpanel-Domain';
	public const HOSTINGER_REST_URI = 'https://rest-hosting.hostinger.com';
	public const CONFIG_PATH = ABSPATH . '.private/config.json';

}
