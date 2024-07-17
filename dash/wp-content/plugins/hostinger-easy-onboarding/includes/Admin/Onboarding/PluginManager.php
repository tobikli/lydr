<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

use Hostinger\EasyOnboarding\Admin\Onboarding\Plugin;
use Hostinger\WpHelper\Utils;
use Hostinger\EasyOnboarding\Helper;

defined( 'ABSPATH' ) || exit;

class PluginManager {
    /**
     * @var array
     */
    private array $plugins;

    public function __construct() {
        $this->load_plugins();
    }

    /**
     * @return void
     */
    private function load_plugins(): void {
        // Payments.
        $locales = array( 'US', 'UK', 'ES', 'FR', 'MX', 'CO', 'DE', 'IT', 'NL' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-gateway-stripe/assets/icon-128x128.png', 'WooCommerce Stripe Gateway', 'woocommerce-gateway-stripe', __( 'Take credit card payments on your store using Stripe.', 'hostinger-easy-onboarding' ), 'payment', $locales, true );

        $locales = array( 'US', 'UK', 'IN', 'BR', 'ES', 'FR', 'MX', 'CO', 'DE', 'IT', 'NL' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-paypal-payments/assets/icon-128x128.png', 'WooCommerce PayPal Payments', 'woocommerce-paypal-payments', __( 'PayPal\'s latest complete payments processing solution. Accept PayPal, Pay Later, credit/debit cards, alternative digital wallets local payment types and bank accounts.', 'hostinger-easy-onboarding' ), 'payment', $locales, true );

        $locales = array( 'US', 'BR', 'ES', 'FR', 'DE', 'IT', 'NL' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-payments/assets/icon-128x128.png','WooPayments','woocommerce-payments', __( 'Accept payments via credit card. Manage transactions within WordPress.', 'hostinger-easy-onboarding' ), 'payment', $locales, true );

        $locales = array( 'BR', 'MX', 'CO' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-mercadopago/assets/icon-128x128.png', 'Mercado Pago', 'woocommerce-mercadopago', __( 'Configure the payment options and accept payments with cards, ticket and money of Mercado Pago account.', 'hostinger-easy-onboarding' ), 'payment', $locales, false );

        $locales = array( 'US', 'UK', 'ES', 'FR' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-square/assets/icon-128x128.png', 'WooCommerce Square', 'woocommerce-square', __( 'Securely accept payments, synchronize sales, and seamlessly manage inventory and product data between WooCommerce and Square POS.', 'hostinger-easy-onboarding' ), 'payment', $locales, false );

        $locales = array( 'US', 'UK', 'IN', 'ES', 'FR', 'DE', 'IT', 'NL' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-gateway-amazon-payments-advanced/assets/icon-128x128.png', 'WooCommerce Amazon Pay', 'woocommerce-gateway-amazon-payments-advanced', __( 'Amazon Pay is embedded directly into your existing web site, and all the buyer interactions with Amazon Pay and Login with Amazon take place in embedded widgets so that the buyer never leaves your site. ', 'hostinger-easy-onboarding' ),'payment', $locales, true );

        $locales = array( 'IN' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/upi-qr-code-payment-for-woocommerce/assets/icon-128x128.png', 'UPI QR Code Payment Gateway', 'upi-qr-code-payment-for-woocommerce', __( 'It enables a WooCommerce site to accept payments through UPI apps like BHIM, Google Pay, Paytm, PhonePe or any Banking UPI app. Avoid payment gateway charges.', 'hostinger-easy-onboarding' ), 'payment', $locales, false );

        $locales = array( 'ES' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woo-redsys-gateway-light/assets/icon-128x128.png', 'WooCommerce Redsys Gateway Light', 'woo-redsys-gateway-light', __( 'Extends WooCommerce with a RedSys gateway.', 'hostinger-easy-onboarding' ), 'payment', $locales, false );

        $locales = array( 'BR' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woo-pagseguro-rm/assets/icon.svg', 'Módulo PagSeguro', 'woo-pagseguro-rm', __( 'Adiciona PagSeguro como meio de pagamento (com desconto nas taxas oficiais).', 'hostinger-easy-onboarding' ), 'payment', $locales, false );

        // Shipping.
        $locales = array( 'US' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-services/assets/icon-128x128.png', 'WooCommerce Shipping & Tax', 'woocommerce-services', __( 'Hosted services for WooCommerce: automated tax calculation, shipping label printing, and smoother payment setup.', 'hostinger-easy-onboarding' ), 'shipping', $locales, false );

        $locales = array( );
        $this->plugins[] = new Plugin( 'https://ps.w.org/flexible-shipping/assets/icon.svg', 'Flexible Shipping', 'flexible-shipping', __( 'Create additional shipment methods in WooCommerce and enable pricing based on cart weight or total.', 'hostinger-easy-onboarding' ), 'shipping', $locales, true );

        $locales = array( 'BR' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woocommerce-correios/assets/icon-128x128.png', 'Correios for WooCommerce', 'woocommerce-correios', __( 'Adds Correios shipping methods to your WooCommerce store.', 'hostinger-easy-onboarding' ), 'shipping', $locales, false );

        $locales = array( 'FR' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/colissimo-shipping-methods-for-woocommerce/assets/icon.svg', 'Colissimo shipping methods', 'colissimo-shipping-methods-for-woocommerce', __( 'This extension gives you the possibility to use the Colissimo shipping methods in WooCommerce', 'hostinger-easy-onboarding' ), 'shipping', $locales, false );

        $locales = array( 'US' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/woo-usps-simple-shipping/assets/icon-128x128.png', 'USPS Simple Shipping', 'woo-usps-simple-shipping', __( 'The USPS Simple plugin calculates rates for domestic shipping dynamically using USPS API during checkout.', 'hostinger-easy-onboarding' ), 'shipping', $locales, false );

        $locales = array( 'FR' );
        $this->plugins[] = new Plugin( 'https://ps.w.org/wc-multishipping/assets/icon-128x128.png', 'Chronopost & Mondial relay', 'wc-multishipping', __( 'Create Chronopost & Mondial relay shipping labels and send them easily.', 'hostinger-easy-onboarding' ), 'shipping', $locales, false );
    }

    /**
     * @param \Hostinger\EasyOnboarding\Admin\Onboarding\Plugin $plugin
     *
     * @return bool
     */
    private function check_recommended( Plugin $plugin ): bool {
        $locale = get_option('woocommerce_default_country', '');

        if(empty($locale)) {
            return false;
        }

        if( in_array($locale, $plugin->get_locale_supported())) {
            return true;
        }

        return false;
    }

    /**
     * Get plugins by type and/or locale.
     *
     * @param string|null $type
     * @param string|null $locale
     * @return array
     */

	public function get_plugins_by_criteria( string $type = null, string $locale = null ): array {
		// Get by type first.
		$filtered_by_type = array_filter( $this->plugins, function ( $plugin ) use ( $type ) {
			return $plugin->get_type() === $type;
		} );

		$all_plugins = get_plugins();

		// Filter by supported locale or global available
		$filter_by_locale = array_filter( $filtered_by_type, function ( Plugin $plugin ) use ( $locale, $all_plugins ) {
			// Set if plugin is active and/or recommended
			$plugin_slug = $plugin->get_slug();
			$helper = new Helper();
			$plugin_path = $helper->get_plugin_main_file( $plugin_slug );

			if ( ! is_wp_error( $plugin_path ) ) {
				$plugin->set_is_active( is_plugin_active( $plugin_path ) );
				$plugin->set_is_installed( array_key_exists( $plugin_path, $all_plugins ) );
			} else {
				$plugin->set_is_active( false );
				$plugin->set_is_installed( false );
			}

			$plugin->set_is_recommended( $this->check_recommended( $plugin ) );

			return in_array( $locale, $plugin->get_locale_supported() ) || $plugin->get_global();
		} );

		return array_map(
			function ( $item ) {
				return $item->to_array();
			},
			$filter_by_locale
		);
	}
}
