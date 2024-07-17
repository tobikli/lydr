<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

use Hostinger\EasyOnboarding\AmplitudeEvents\Amplitude;
use Hostinger\EasyOnboarding\AmplitudeEvents\Actions as AmplitudeActions;
use Hostinger\EasyOnboarding\Helper;
use Hostinger\EasyOnboarding\Settings;
use Hostinger\EasyOnboarding\Admin\Actions as Admin_Actions;
use WP_Post;

defined( 'ABSPATH' ) || exit;

class AutocompleteSteps {
    /**
     * @var Helper
     */
	private Helper $helper;

    /**
     * @var Onboarding
     */
    private Onboarding $onboarding;

	public function __construct() {
		$this->onboarding          = new Onboarding();
        $this->onboarding->init();
		$this->helper          = new Helper();

		add_action( 'customize_save', array( $this, 'logo_upload' ) );
		add_action( 'wp_handle_upload', array( $this, 'image_upload' ) );
		add_action( 'post_updated', array( $this, 'post_content_change' ), 10, 3 );
		add_action( 'customize_save', array( $this, 'edit_site_title' ) );
		add_action( 'publish_page', array( $this, 'new_page_creation' ), 10, 3 );
		add_action( 'save_post_product', array( $this, 'new_product_creation' ), 10, 3 );
		add_action( 'publish_post', array( $this, 'new_post_creation' ), 10, 3 );
		add_action( 'updated_option', array( $this, 'check_option_change' ), 10, 3 );
        add_action( 'added_option', array( $this, 'omnisend_connect'), 10, 2 );
        add_action( 'woocommerce_shipping_zone_method_added', array( $this, 'shipping_zone_added'), 10, 3 );

		if ( $this->helper->is_hostinger_admin_page() ) {
			add_action( 'admin_init', array( $this, 'domain_is_connected' ) );
		}
	}

    /**
     * @return void
     */
	public function domain_is_connected(): void {
		$action = Admin_Actions::DOMAIN_IS_CONNECTED;

        $category_id = $this->find_category_from_actions($action);

        if(empty($category_id)) {
            return;
        }

		if ( $this->onboarding->is_completed( $category_id, $action ) ) {
			return;
		}

		if ( ! $this->helper->is_free_subdomain() && ! $this->helper->is_preview_domain() ) {
			if ( ! did_action( 'hostinger_domain_connected' ) ) {
                $this->onboarding->complete_step( $category_id, $action );
				do_action( 'hostinger_domain_connected' );
			}
		}
	}

    /**
     * @param \WP_Customize_Manager $data
     *
     * @return void
     */
	public function logo_upload( \WP_Customize_Manager $data ): void {
        $this->onboarding->init();

		$action = Admin_Actions::LOGO_UPLOAD;

		$logo_updated = array_filter(
			$data->changeset_data(),
			function ( $key ) {
				return strpos( $key, 'custom_logo' ) !== false;
			},
			ARRAY_FILTER_USE_KEY
		);

		$has_logo     = reset( $logo_updated )['value'] ?? false;
		$cookie_value = isset( $_COOKIE[ $action ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $action ] ) ) : '';

        $category_id = $this->find_category_from_actions($action);

        if(empty($category_id)) {
            return;
        }

		if ( $this->onboarding->is_completed( $category_id, $action ) || $logo_updated && ! $has_logo ) {
			return;
		}

		if ( $logo_updated && $cookie_value === $action ) {
			$this->onboarding->complete_step( $category_id, $action );
		}
	}

    /**
     * @param array $data
     *
     * @return array
     */
	public function image_upload( array $data ): array {
        $this->onboarding->init();

		$action       = Admin_Actions::IMAGE_UPLOAD;
		$file_type    = $data['type'] ?? '';
		$cookie_value = isset( $_COOKIE[ $action ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $action ] ) ) : '';

        $category_id = $this->find_category_from_actions($action);

        if(empty($category_id)) {
            return $data;
        }

		if ( $this->onboarding->is_completed( $category_id, $action ) || strpos( $file_type, 'image' ) !== 0 ) {
			return $data;
		}

		if ( $cookie_value === $action ) {
            $this->onboarding->complete_step( $category_id, $action );
		}

		return $data;
	}

    /**
     * @param int     $post_id
     * @param WP_Post $post_after
     * @param WP_Post $post_before
     *
     * @return void
     */
	public function post_content_change( int $post_id, WP_Post $post_after, WP_Post $post_before ) {
		$action         = Admin_Actions::EDIT_DESCRIPTION;
		$post_date      = get_the_date( 'Y-m-d H:i:s', $post_id );
		$modified_date  = get_the_modified_date( 'Y-m-d H:i:s', $post_id );
		$post_type      = get_post_type( $post_id );
		$cookie_value   = isset( $_COOKIE[ $action ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $action ] ) ) : '';
		$content_before = $post_before->post_content;
		$content_after  = $post_after->post_content;

        $category_id = $this->find_category_from_actions($action);

        if(empty($category_id)) {
            return;
        }

		if ( $this->onboarding->is_completed( $category_id, $action ) || $post_date === $modified_date ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( $post_type === 'post' && $content_before !== $content_after && $cookie_value === $action ) {
            $this->onboarding->complete_step( $category_id, $action );
		}
	}

    /**
     * @param \WP_Customize_Manager $data
     *
     * @return void
     */
	public function edit_site_title( \WP_Customize_Manager $data ): void {
		$action        = Admin_Actions::EDIT_SITE_TITLE;
		$changed_title = $data->changeset_data()['blogname']['value'] ?? '';
		$cookie_value  = isset( $_COOKIE[ $action ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $action ] ) ) : '';

        $category_id = $this->find_category_from_actions($action);

        if(empty($category_id)) {
            return;
        }

		if ( $this->onboarding->is_completed( $category_id, $action ) ) {
			return;
		}

		if ( $cookie_value === $action && $changed_title !== '' && get_bloginfo( 'name' ) !== $changed_title ) {
            $this->onboarding->complete_step( $category_id, $action );
		}
	}

    /**
     * @param int    $post_id
     * @param bool   $update
     * @param string $action
     *
     * @return void
     */
	public function new_post_item_creation( int $post_id, bool $update, string $action ): void {
		$cookie_value = isset( $_COOKIE[ $action ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $action ] ) ) : '';

        $category_id = $this->find_category_from_actions($action);

        if(empty($category_id)) {
            return;
        }

		if ( $this->onboarding->is_completed( $category_id, $action ) || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		if ( $update && $cookie_value === $action ) {
            $this->onboarding->complete_step( $category_id, $action );
		}
	}

    /**
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     *
     * @return void
     */
	public function new_page_creation( int $post_id, WP_Post $post, bool $update ): void {
		$this->new_post_item_creation( $post_id, $update, Admin_Actions::ADD_PAGE );
	}

    /**
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     *
     * @return void
     */
	public function new_product_creation( int $post_id, WP_Post $post, bool $update ): void {
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if( $post->post_status != 'publish' ) {
            return;
        }

        if( empty( $post->post_author ) ) {
            return;
        }

        if ( $this->onboarding->is_completed( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_PRODUCT ) ) {
            return;
        }

        $this->onboarding->complete_step( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_PRODUCT );

        $add_product_event_sent = get_option( 'hostinger_add_product_event_sent', false );

        if ( !empty( $add_product_event_sent ) ) {
            return;
        }

        $amplitude = new Amplitude();

        $params = array(
            'action' => AmplitudeActions::WOO_ITEM_COMPLETED,
            'step_type' => Admin_Actions::ADD_PRODUCT,
        );

        $amplitude->send_event($params);

        update_option( 'hostinger_add_product_event_sent', true );
	}

    /**
     * @param int     $post_id
     * @param WP_Post $post
     * @param bool    $update
     *
     * @return void
     */
	public function new_post_creation( int $post_id, WP_Post $post, bool $update ): void {
		$this->new_post_item_creation( $post_id, $update, Admin_Actions::ADD_POST );
	}

    /**
     * @param string $option_name
     * @param        $old_value
     * @param        $new_value
     *
     * @return void
     */
	public function check_option_change( string $option_name, $old_value, $new_value ): void {
		$action       = Admin_Actions::EDIT_SITE_TITLE;
		$cookie_value = isset( $_COOKIE[ $action ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $action ] ) ) : '';

        $category_id = $this->find_category_from_actions($action);

        if(empty($category_id)) {
            return;
        }

        if ( $this->onboarding->is_completed( $category_id, $action ) ) {
			return;
		}

		if ( $cookie_value === $action && $new_value !== '' && $option_name === Settings::SITE_TITLE_OPTION && $old_value !== $new_value ) {
            $this->onboarding->complete_step( $category_id, $action );
		}
	}

    /**
     * @param $option_name
     * @param $value
     *
     * @return bool
     */
    public function omnisend_connect($option_name, $value) {
        if($option_name != 'omnisend_api_key') {
            return false;
        }

        if(empty($value)) {
            return false;
        }

        // Omnisend API key is set and we can conclude that it is connected.
        if ( $this->onboarding->is_completed( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_MARKETING ) ) {
            return false;
        }

        $this->onboarding->complete_step( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_MARKETING );

        $amplitude = new Amplitude();

        $params = array(
            'action' => AmplitudeActions::WOO_ITEM_COMPLETED,
            'step_type' => Admin_Actions::ADD_MARKETING,
        );

        $amplitude->send_event($params);

        return true;
    }

    /**
     * @param $instance_id
     * @param $type
     * @param $zone_id
     *
     * @return void
     */
    public function shipping_zone_added($instance_id, $type, $zone_id) {
        if ( $this->onboarding->is_completed( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_SHIPPING ) ) {
            return;
        }

        $this->onboarding->complete_step( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, Admin_Actions::ADD_SHIPPING );

        $amplitude = new Amplitude();

        $params = array(
            'action' => AmplitudeActions::WOO_ITEM_COMPLETED,
            'step_type' => Admin_Actions::ADD_SHIPPING,
        );

        $amplitude->send_event($params);
    }

    /**
     * @param $action
     *
     * @return string
     */
    private function find_category_from_actions($action): string {
        foreach (Admin_Actions::CATEGORY_ACTION_LISTS as $category => $actions) {
            if (in_array($action, $actions)) {
                return $category;
            }
        }
        return '';
    }
}
