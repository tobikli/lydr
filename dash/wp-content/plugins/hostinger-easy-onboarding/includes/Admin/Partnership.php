<?php
namespace Hostinger\EasyOnboarding\Admin;
class Partnership {
	const MONSTERINSIGHTS_PARTNER_ID = '3107422';
	const ASTRA_PARTNER_ID = '12425';
	const WPFORMS_PARTNER_LINK = 'https://shareasale.com/r.cfm?b=834775&u=3107422&m=64312&urllink=';
	const AIOSEO_PARTNER_LINK = 'https://shareasale.com/r.cfm?b=1491200&u=3107422&m=94778&urllink=';
	const HESTIA_AND_NEVE_PARTNER_LINK = 'https://www.shareasale.com/r.cfm?b=642802&u=3107422&m=55096';

	public function __construct() {
		if ( is_admin() ) {
			$this->define_admin_hooks();
		}

		add_action( 'init', array( $this, 'schedule_weekly_cron_job' ) );
	}

	public function partner_astra() {
		add_option( 'astra_partner_url_param', self::ASTRA_PARTNER_ID, '', 'no' );
	}

	public function partner_monsterinsights( $id ) {
		return self::MONSTERINSIGHTS_PARTNER_ID;
	}

	public function wpforms_upgrade_link( $link ) {
		return self::WPFORMS_PARTNER_LINK . rawurlencode( $link );
	}

	public function aioseo_upgrade_link( $link ) {
		return self::AIOSEO_PARTNER_LINK . rawurlencode( $link );
	}

	public function neve_or_hestia_upgrade_link( $utmify_url, $url ) {
		if ( strpos( $url, 'themes/neve/upgrade' ) !== false || strpos( $url, 'themes/hestia-pro/upgrade' ) !== false ) {
			return self::HESTIA_AND_NEVE_PARTNER_LINK;
		}

		return $utmify_url;
	}

	private function define_admin_hooks() {
		add_filter( 'optinmonster_sas_id', array( $this, 'partner_monsterinsights' ) );
		add_filter( 'monsterinsights_shareasale_id', array( $this, 'partner_monsterinsights' ) );
		add_filter( 'wpforms_upgrade_link', array( $this, 'wpforms_upgrade_link' ) );
		add_filter( 'aioseo_upgrade_link', array( $this, 'aioseo_upgrade_link' ) );
		add_filter( 'tsdk_utmify_url_neve', array( $this, 'neve_or_hestia_upgrade_link' ), 11, 2 );
		add_filter( 'tsdk_utmify_url_hestia-pro', array( $this, 'neve_or_hestia_upgrade_link' ), 11, 2 );
	}

	public function schedule_weekly_cron_job() {
		if ( ! wp_next_scheduled( 'run_weekly_partner_astra' ) ) {
			wp_schedule_event( time(), 'weekly', 'run_weekly_partner_astra' );
		}
		add_action( 'run_weekly_partner_astra', array( $this, 'run_weekly_partner_astra' ) );
	}

	public function run_weekly_partner_astra() {
		if ( ! get_option( 'astra_partner_url_param' ) ) {
			$this->partner_astra();
		}
	}
}
