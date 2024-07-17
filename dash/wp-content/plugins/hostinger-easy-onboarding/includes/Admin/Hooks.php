<?php

namespace Hostinger\EasyOnboarding\Admin;

use Hostinger\EasyOnboarding\Helper;
use Hostinger\WpHelper\Utils;

defined( 'ABSPATH' ) || exit;


class Hooks {

	private Helper $helper;

	public const DAY_IN_SECONDS = 86400;
	public const WOOCOMMERCE_PAGES = array(
		'/wp-admin/admin.php?page=wc-admin',
		'/wp-admin/edit.php?post_type=shop_order',
		'/wp-admin/admin.php?page=wc-admin&path=/customers',
		'/wp-admin/edit.php?post_type=shop_coupon&legacy_coupon_menu=1',
		'/wp-admin/admin.php?page=wc-admin&path=/marketing',
		'/wp-admin/admin.php?page=wc-reports',
		'/wp-admin/admin.php?page=wc-settings',
		'/wp-admin/admin.php?page=wc-status',
		'/wp-admin/admin.php?page=wc-admin&path=/extensions',
		'/wp-admin/edit.php?post_type=product',
		'/wp-admin/post-new.php?post_type=product',
		'/wp-admin/edit.php?post_type=product&page=product-reviews',
		'/wp-admin/edit.php?post_type=product&page=product_attributes',
		'/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product',
		'/wp-admin/edit-tags.php?taxonomy=product_tag&post_type=product',
		'/wp-admin/admin.php?page=wc-admin&path=/analytics/overview',
		'/wp-admin/admin.php?page=wc-admin',
		'/wp-admin/admin.php?page=wc-orders',
	);

    public const WOO_ONBOARDING_NOTICE_TRANS = 'hostinger_return_to_onboarding';

	public function __construct() {
		$this->helper = new Helper();

		// Admin footer actions
		add_action( 'admin_footer', array( $this, 'rate_plugin' ) );

		// Admin init actions
		add_action( 'admin_init', array( $this, 'admin_init_actions' ) );

		// Admin notices
		add_action( 'admin_notices', array( $this, 'omnisend_discount_notice' ) );

        // Return back to onboarding.
		add_action( 'admin_footer', array( $this, 'back_to_onboarding_notice' ) );
        add_filter( 'admin_body_class', array( $this, 'add_onboarding_notice_class' ) );

		// Admin Styles
		add_action( 'admin_head', array( $this, 'hide_notices' ) );

		// WooCommerce filters
		add_filter( 'woocommerce_prevent_automatic_wizard_redirect', function () {
			return true;
		}, 999 );
		add_filter( 'woocommerce_enable_setup_wizard', function () {
			return false;
		}, 999 );

		// Spectra
		add_filter( 'uagb_enable_redirect_activation', function () {
			return false;
		}, 999 );

        // Hooking up one action before and removing astra redirect to onboarding
        if ( function_exists( 'astra_sites_redirect_to_onboarding' ) ) {
            add_action('admin_menu', function () {
                remove_action('admin_init', 'astra_sites_redirect_to_onboarding');
            });
        }

        // Hook up one hook earlier and prevent stripe checkout redirect by setting option to false
        add_action( 'admin_menu', array( $this, 'prevent_stripe_checkout_onboarding' ), 0 );

        add_filter( 'get_edit_post_link', array( $this, 'change_shop_page_edit_url' ), 99, 3 );

        add_action( 'admin_menu', array( $this, 'disable_beaver_builder_redirect' ) );

        add_action( 'admin_menu', array( $this, 'disable_monsterinsights_redirect' ) );
	}

	public function hide_notices() {
		$helper = new Helper();
		?>
		<style>
			<?php if ( ! $helper->is_woocommerce_onboarding_completed() ) : ?>
            .post-php.post-type-product #wpadminbar .wpforms-menu-notification-counter,
            .post-php.post-type-product #wpadminbar .aioseo-menu-notification-counter,
            .post-php.post-type-product .woocommerce-layout__header-tasks-reminder-bar,
            .post-php.post-type-product .litespeed_icon.notice.is-dismissible,
            .post-php.post-type-product .monsterinsights-menu-notification-indicator,
            .post-php.post-type-product .aioseo-review-plugin-cta,
            .post-php.post-type-product .omnisend-connection-notice-hidden,
            .post-php.post-type-product #astra-upgrade-pro-wc {
                display: none !important;
            }

            .notice.hts-notice {
                display: block !important;
            }

			<?php endif; ?>

			<?php if ( $this->is_woocommerce_admin_page() && ! $helper->is_woocommerce_onboarding_completed() ) : ?>
            #wpadminbar .wpforms-menu-notification-counter,
            #wpadminbar .aioseo-menu-notification-counter,
            .woocommerce-layout__header-tasks-reminder-bar,
            .litespeed_icon.notice.is-dismissible,
            .monsterinsights-menu-notification-indicator,
            .aioseo-review-plugin-cta,
            .omnisend-connection-notice-hidden,
            #astra-upgrade-pro-wc,
            .notice {
                display: none !important;
            }

            .notice.hts-notice {
                display: block !important;
            }

			<?php endif; ?>
		</style>
		<?php
	}

	public function admin_init_actions(): void {
		$this->hide_astra_builder_selection_screen();
		$this->hide_metaboxes();
		$this->hide_monsterinsight_notice();
	}

	public function hide_metaboxes(): void {
		$this->hide_plugin_metabox( 'google-analytics-for-wordpress/googleanalytics.php', 'monsterinsights-metabox', 'metaboxhidden_product' );
		$this->hide_plugin_metabox( 'all-in-one-seo-pack/all_in_one_seo_pack.php', 'aioseo-settings', 'aioseo-settings' );
		$this->hide_plugin_metabox( 'litespeed-cache/litespeed-cache.php', 'litespeed_meta_boxes', 'litespeed_meta_boxes' );
		$this->hide_astra_theme_metabox();
		$this->hide_custom_fields_metabox();

        // Hide panels in Gutenberg editor
        $this->hide_plugin_panel('all-in-one-seo-pack/all_in_one_seo_pack.php', 'meta-box-aioseo-settings');
	}

	public function hide_astra_theme_metabox(): void {
		if ( ! $this->is_astra_theme_active() ) {
			return;
		}
		$this->hide_metabox( 'astra_settings_meta_box', 'astra_metabox' );
	}

	public function hide_custom_fields_metabox(): void {
		$this->hide_metabox( 'postcustom', 'custom_fields_metabox' );
	}

	public function hide_metabox( string $metabox_id, string $transient_suffix ): void {
		$helper        = new Utils();
		$user_id       = get_current_user_id();
		$transient_key = $transient_suffix . '_' . $user_id;
		$hide_metabox  = get_transient( $transient_key );

		if ( $hide_metabox ) {
			return;
		}

		$hide_metabox = get_user_meta( $user_id, 'metaboxhidden_product', true );

		if ( ! is_array( $hide_metabox ) ) {
			$hide_metabox = array();
		}

		if ( $helper->isThisPage( 'post-new.php?post_type=product' ) ) {
			if ( ! in_array( $metabox_id, $hide_metabox ) ) {
				array_push( $hide_metabox, $metabox_id );
			}

			update_user_meta( $user_id, 'metaboxhidden_product', $hide_metabox );
			set_transient( $transient_key, 'hidden', self::DAY_IN_SECONDS );
		}
	}

	public function is_astra_theme_active(): bool {
		$theme = wp_get_theme();

		return $theme->get( 'Name' ) === 'Astra';
	}

	public function hide_monsterinsight_notice(): void {
		if ( is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ) {
			define( 'MONSTERINSIGHTS_DISABLE_TRACKING', true );
		}
	}

	public function rate_plugin(): void {
		$promotional_banner_hidden = get_transient( 'hts_hide_promotional_banner_transient' );
		$two_hours_in_seconds      = 7200;

		if ( $promotional_banner_hidden && time() > $promotional_banner_hidden + $two_hours_in_seconds ) {
			require_once HOSTINGER_EASY_ONBOARDING_ABSPATH . 'includes/Admin/Views/Partials/RateUs.php';
		}
	}

	public function omnisend_discount_notice(): void {
		$omnisend_notice_hidden = get_transient( 'hts_omnisend_notice_hidden' );

		if ( $omnisend_notice_hidden === false && $this->helper->is_this_page( '/wp-admin/admin.php?page=omnisend' ) && ( Helper::is_plugin_active( 'class-omnisend-core-bootstrap' ) || Helper::is_plugin_active( 'omnisend-woocommerce' ) ) ) : ?>
			<div class="notice notice-info hts-admin-notice hts-omnisend">
				<p><?php echo wp_kses( __( 'Use the special discount code <b>ONLYHOSTINGER30</b> to get 30% off on Omnisend for 6 months when you upgrade.', 'hostinger-easy-onboarding' ), array( 'b' => array() ) ); ?></p>
				<div>
					<a class="button button-primary"
					   href="https://your.omnisend.com/LXqyZ0"
					   target="_blank"><?= esc_html__( 'Get Discount', 'hostinger-easy-onboarding' ); ?></a>
					<button type="button" class="notice-dismiss"></button>
				</div>
			</div>
		<?php endif;
		wp_nonce_field( 'hts_close_omnisend', 'hts_close_omnisend_nonce', true );
	}

	public function hide_astra_builder_selection_screen(): void {
		add_filter( 'st_enable_block_page_builder', '__return_true' );
	}

	public function hide_plugin_metabox( string $plugin_slug, string $metabox_id, string $transient_suffix ): void {
		$helper        = new Utils();
		$user_id       = get_current_user_id();
		$transient_key = $transient_suffix . '_' . $user_id;
		$hide_metabox  = get_transient( $transient_key );

		if ( $hide_metabox ) {
			return;
		}

		$hide_metabox = get_user_meta( $user_id, 'metaboxhidden_product', true );

		if ( ! is_plugin_active( $plugin_slug ) ) {
			return;
		}

		if ( ! is_array( $hide_metabox ) ) {
			$hide_metabox = array();
		}

		if ( $helper->isThisPage( 'post-new.php?post_type=product' ) ) {
			if ( ! in_array( $metabox_id, $hide_metabox ) ) {
				array_push( $hide_metabox, $metabox_id );
			}

			update_user_meta( $user_id, 'metaboxhidden_product', $hide_metabox );
			set_transient( $transient_key, 'hidden', self::DAY_IN_SECONDS );
		}
	}

    public function hide_plugin_panel( string $plugin_slug, $panel_id ) {
        if ( ! is_plugin_active( $plugin_slug ) ) {
            return;
        }

        $user_id   = get_current_user_id();
        $flag_name = 'hostinger_' . $panel_id . '_changed';

        $hidden_once = get_user_meta( $user_id, $flag_name, true );

        if( $hidden_once ) {
            return;
        }

        $persisted_preferences = get_user_meta( $user_id, 'wp_persisted_preferences', true );

        if ( empty( $persisted_preferences ) ) {
            $persisted_preferences = array(
                    'core/edit-post' => array(
                        'welcomeGuide' => '',
                        'isComplementaryAreaVisible' => 1,
                        'inactivePanels' => array(
                            $panel_id
                        )
                    ),
                    'core/edit-site' => array(
                        'welcomeGuide' => '',
                        'isComplementaryAreaVisible' => 1
                    ),
                    '_modified' => wp_date( 'c' ),
            );
        } else {
            if( !empty( $persisted_preferences['core/edit-post']['inactivePanels'] ) && !in_array( $panel_id, $persisted_preferences['core/edit-post']['inactivePanels'] ) ) {
                $persisted_preferences['core/edit-post']['inactivePanels'][] = $panel_id;
            }
        }

        update_user_meta( $user_id, 'wp_persisted_preferences', $persisted_preferences );
        update_user_meta( $user_id, $flag_name, 1 );
    }

	private function is_woocommerce_admin_page(): bool {
        // Product edit etc.
        if ( get_post_type() === 'product' ) {
            return true;
        }

		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$current_uri = sanitize_text_field( $_SERVER['REQUEST_URI'] );

		if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
			return false;
		}

		if ( isset( $current_uri ) && strpos( $current_uri, '/wp-json/' ) !== false ) {
			return false;
		}

		foreach ( self::WOOCOMMERCE_PAGES as $page ) {
			if ( strpos( $current_uri, $page ) !== false ) {
				return true;
			}
		}

		return false;
	}

    public function prevent_stripe_checkout_onboarding(): bool {
        if ( !is_plugin_active( 'checkout-plugins-stripe-woo/checkout-plugins-stripe-woo.php') ) {
            return false;
        }

        $onboarding_status = get_option( 'cpsw_start_onboarding', false );

        if ( empty( $onboarding_status ) ) {
            return false;
        }

        update_option( 'cpsw_start_onboarding', false );

        return true;
    }

    public function back_to_onboarding_notice() : void {
        $return_to_onboarding = get_transient( self::WOO_ONBOARDING_NOTICE_TRANS );

        if ( $return_to_onboarding === false && $this->is_onboarding_reminder_visible() ) {
            ?>
            <div class="hostinger-onboarding-reminder">
                <div class="hostinger-onboarding-reminder__wrap">
                    <div class="hostinger-onboarding-reminder__logo">
                        <svg width="93" height="108" viewBox="0 0 93 108" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M66.5145 0V32.2221L92.8421 47.4468V13.2693L66.5145 0ZM0.000915527 0.00183105V50.5657H85.6265L59.5744 36.4074L25.6372 36.3911V13.6097L0.000915527 0.00183105ZM66.5145 94.0237V71.4387L32.3155 71.4152C32.3474 71.5655 5.83099 57.0306 5.83099 57.0306L92.8421 57.4371V108L66.5145 94.0237ZM0.000912095 60.9814L0 94.0237L25.6372 107.292V75.8458L0.000912095 60.9814Z" fill="#673DE6"/>
                        </svg>
                    </div>
                    <div class="hostinger-onboarding-reminder__text">
                        <?php echo __( 'Just a few more steps to launch your online store', 'hostinger-easy-onboarding' ); ?>
                    </div>
                    <div class="hostinger-onboarding-reminder__cta">
                        <a href="<?php echo admin_url( 'admin.php?page=hostinger' ); ?>">
                            <?php echo __( 'Finish setup', 'hostinger-easy-onboarding' ); ?>
                        </a>
                    </div>
                    <div class="hostinger-onboarding-reminder__close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M17.9571 7.45711C18.3476 7.06658 18.3476 6.43342 17.9571 6.04289C17.5666 5.65237 16.9334 5.65237 16.5429 6.04289L12 10.5858L7.45711 6.04289C7.06658 5.65237 6.43342 5.65237 6.0429 6.04289C5.65237 6.43342 5.65237 7.06658 6.0429 7.45711L10.5858 12L6.04289 16.5429C5.65237 16.9334 5.65237 17.5666 6.04289 17.9571C6.43342 18.3476 7.06658 18.3476 7.45711 17.9571L12 13.4142L16.5429 17.9571C16.9334 18.3476 17.5666 18.3476 17.9571 17.9571C18.3476 17.5666 18.3476 16.9334 17.9571 16.5429L13.4142 12L17.9571 7.45711Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    public function add_onboarding_notice_class( $classes ) {

        if ( $this->is_onboarding_reminder_visible() ) {
            $classes .= ' hostinger-onboarding-reminder-visible';
        }

        return $classes;
    }

    private function is_onboarding_reminder_visible() {
        return is_plugin_active( 'woocommerce/woocommerce.php' ) && !$this->helper->is_woocommerce_onboarding_completed() && $this->is_woocommerce_admin_page();
    }

    public function change_shop_page_edit_url( $link, $post_id, $context ): string {
        if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return $link;
        }

        $shop_page_id = wc_get_page_id( 'shop' );

        if( $shop_page_id != $post_id ) {
            return $link;
        }

        if( current_theme_supports( 'block-templates' ) ) {
            return admin_url( 'site-editor.php?postType=wp_template&postId=woocommerce/woocommerce//archive-product' );
        }

        return admin_url( 'customize.php?autofocus[section]=woocommerce_product_catalog' );
    }

    public function disable_beaver_builder_redirect() {
        if ( !is_plugin_active( 'ultimate-addons-for-beaver-builder-lite/bb-ultimate-addon.php' ) ) {
            return false;
        }

        $redirect = get_option( 'uabb_lite_redirect', false );

        if ( empty( $redirect ) ) {
            return false;
        }

        update_option( 'uabb_lite_redirect', false );

        $this->flush_object_cache();
    }

    public function disable_monsterinsights_redirect() {
        if ( !is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ) {
            return false;
        }

        if ( ! get_transient( '_monsterinsights_activation_redirect' ) ) {
            return false;
        }

        // Delete the redirect transient.
        delete_transient( '_monsterinsights_activation_redirect' );

        $this->flush_object_cache();
    }

    private function flush_object_cache(): void {
        if ( has_action( 'litespeed_purge_all_object' ) ) {
            do_action('litespeed_purge_all_object');
        }
    }
}

