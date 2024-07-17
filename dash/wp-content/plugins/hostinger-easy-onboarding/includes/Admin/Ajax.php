<?php

namespace Hostinger\EasyOnboarding\Admin;

use Hostinger\EasyOnboarding\Admin\Onboarding\Onboarding;
use Hostinger\EasyOnboarding\AmplitudeEvents\Amplitude;
use Hostinger\EasyOnboarding\AmplitudeEvents\Actions as AmplitudeActions;
use Hostinger\EasyOnboarding\Admin\Actions as Admin_Actions;
use Hostinger\EasyOnboarding\Admin\Hooks as Admin_Hooks;

defined( 'ABSPATH' ) || exit;

class Ajax {
    /**
     * @var Onboarding
     */
    private Onboarding $onboarding;

    public function __construct() {
        $this->onboarding = new Onboarding();
        $this->onboarding->init();

		add_action( 'init', array( $this, 'define_ajax_events' ), 0 );
	}

    /**
     * @return void
     */
	public function define_ajax_events(): void {
		$events = array(
            'identify_action',
            'payment_gateway_enabled',
            'hide_woo_onboarding_notice',
		);

		foreach ( $events as $event ) {
			add_action( 'wp_ajax_hostinger_' . $event, array( $this, $event ) );
		}
	}

    /**
     * @return void
     */
    public function identify_action(): void {
        $nonce          = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        $security_check = $this->request_security_check( $nonce );

        if ( ! empty( $security_check ) ) {
            wp_send_json_error( $security_check );
        }

        $action = sanitize_text_field( $_POST['action_name'] ) ?? '';

        if ( in_array( $action, Admin_Actions::ACTIONS_LIST, true ) ) {
            setcookie( $action, $action, time() + ( 86400 ), '/' );
            wp_send_json_success( $action );
        } else {
            wp_send_json_error( 'Invalid action' );
        }
    }

    /**
     * @return void
     */
    public function payment_gateway_enabled(): void {
        $payment_gateway_slug = isset( $_POST['payment_gateway_slug'] ) ? sanitize_text_field( $_POST['payment_gateway_slug'] ) : '';
        $nonce          = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        $security_check = $this->request_security_check( $nonce );

        if ( ! empty( $security_check ) ) {
            wp_send_json_error( $security_check );
        }

        if ( $this->onboarding->is_completed( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_PAYMENT ) ) {
            return;
        }

        $this->onboarding->complete_step( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_PAYMENT );

        $payment_gateways      = WC()->payment_gateways->payment_gateways();
        $payment_gateway_found = false;

        foreach ( $payment_gateways as $gateway ) {
            if ( in_array($payment_gateway_slug, [$payment_gateway_slug, sanitize_title(get_class($gateway))], true)) {
                $payment_gateway_found = true;
                break;
            }
        }

        if ( empty( $payment_gateway_found ) ) {
            wp_send_json_error( __( 'Payment gateway not found.', 'hostinger-easy-onboarding' ) );
        }

        $amplitude = new Amplitude();

        $params = array(
            'action' => AmplitudeActions::WOO_ITEM_COMPLETED,
            'step_type' => Admin_Actions::ADD_PAYMENT,
        );

        $response = $amplitude->send_event($params);

        wp_send_json_success($response);
    }

    public function hide_woo_onboarding_notice(): void {
        $nonce          = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        $transient_key  = Admin_Hooks::WOO_ONBOARDING_NOTICE_TRANS;
        $security_check = $this->request_security_check( $nonce );

        if ( ! empty( $security_check ) ) {
            wp_send_json_error( $security_check );
        }

        if ( false === get_transient( $transient_key ) ) {
            set_transient( $transient_key, time(), Admin_Hooks::DAY_IN_SECONDS );
        }

        wp_send_json_success( array() );
    }

    /**
     * @param $nonce
     *
     * @return false|string
     */
	public function request_security_check( $nonce ) {
		if ( ! wp_verify_nonce( $nonce, 'hts-ajax-nonce' ) ) {
			return 'Invalid nonce';
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return 'Lack of permissions';
		}

		return false;
	}
}
