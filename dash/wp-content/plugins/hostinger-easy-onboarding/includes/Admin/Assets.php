<?php

namespace Hostinger\EasyOnboarding\Admin;
use Hostinger\Admin\Menu;
use Hostinger\EasyOnboarding\Helper;
use Hostinger\WpHelper\Utils;
use Hostinger\WpMenuManager\Menus;

defined( 'ABSPATH' ) || exit;

/**
 * Class Hostinger_Admin_Assets
 *
 * Handles the enqueueing of styles and scripts for the Hostinger admin pages.
 */
class Assets {
	/**
	 * @var Helper Instance of the Hostinger_Helper class.
	 */
	private Helper $helper;

    /**
     * @var Utils
     */
    private Utils $utils;

	public function __construct() {
		$this->helper = new Helper();
        $this->utils = new Utils();

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueues styles for the Hostinger admin pages.
	 */
	public function admin_styles(): void {
        if ( $this->utils->isThisPage( 'wp-admin/admin.php?page=hostinger-get-onboarding' ) || $this->utils->isThisPage( 'wp-admin/admin.php?page=' . Menus::MENU_SLUG ) ) {
			wp_enqueue_style( 'hostinger_easy_onboarding_main_styles', HOSTINGER_EASY_ONBOARDING_ASSETS_URL . '/css/main.min.css', array(), HOSTINGER_EASY_ONBOARDING_VERSION );

            $hide_notices = '.notice { display: none !important; }';
            wp_add_inline_style('hostinger_easy_onboarding_main_styles', $hide_notices);
		}

		wp_enqueue_style( 'hostinger_easy_onboarding_global_styles', HOSTINGER_EASY_ONBOARDING_ASSETS_URL . '/css/global.min.css', array(), HOSTINGER_EASY_ONBOARDING_VERSION );

		if ( $this->helper->is_preview_domain() && is_user_logged_in() ) {
			wp_enqueue_style( 'hostinger_easy_onboarding_preview_styles', HOSTINGER_EASY_ONBOARDING_ASSETS_URL . '/css/hts-preview.min.css', array(), HOSTINGER_EASY_ONBOARDING_VERSION );
		}

        if( is_plugin_active( 'wpforms/wpforms.php' ) ) {
            $hide_wp_forms_counter = '.wp-admin #wpadminbar .wpforms-menu-notification-counter { display: none !important; }';
            wp_add_inline_style( 'hostinger_easy_onboarding_global_styles', $hide_wp_forms_counter );
        }
        if( is_plugin_active( 'googleanalytics/googleanalytics.php' ) ) {
            $hide_wp_forms_notification = '.wp-admin .monsterinsights-menu-notification-indicator { display: none !important; }';
            wp_add_inline_style( 'hostinger_easy_onboarding_global_styles', $hide_wp_forms_notification );
        }
	}

	/**
	 * Enqueues scripts for the Hostinger admin pages.
	 */
	public function admin_scripts(): void {
        if ( $this->utils->isThisPage( 'wp-admin/admin.php?page=hostinger-get-onboarding' ) || $this->utils->isThisPage( 'wp-admin/admin.php?page=' . Menus::MENU_SLUG ) ) {
			wp_enqueue_script(
				'hostinger_easy_onboarding_main_scripts',
				HOSTINGER_EASY_ONBOARDING_ASSETS_URL . '/js/main.min.js',
				array(
					'jquery',
					'wp-i18n',
				),
				HOSTINGER_EASY_ONBOARDING_VERSION,
				false
			);

            $localize_data = array(
                'promotional_link' => $this->helper->get_promotional_link_url( get_locale() ),
                'completed_steps' =>  get_option( 'hostinger_onboarding_steps', array() ),
                'site_url'     => get_site_url(),
                'plugin_url'   => HOSTINGER_EASY_ONBOARDING_PLUGIN_URL,
                'admin_url' => admin_url('admin-ajax.php'),
                'user_locale' => get_user_locale(),
                'translations' => array(
                    'hostinger_easy_onboarding_online_store_setup' => __( 'Online store setup', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_preview_website' => __( 'Preview website', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_continue' => __( 'Continue', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_market_your_business' => __( 'Market your business', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_manage_shipping' => __( 'Manage shipping', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_run_email_marketing_campaigns' => __( 'Run email marketing campaigns', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_run_email_marketing_campaigns_description' => __( 'Expand your audience with the help of Omnisend. Create email campaigns that drive sales.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_ship_products_with_ease' => __( 'Ship products with ease', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_shipping_methods' => __( 'Shipping methods', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_try_omnisend' => __( 'Try Omnisend', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_your_store_name' => __( 'Your store name', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_your_business_email' => __( 'Your business email', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_where_is_your_store' => __( 'Where is your store located?', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_what_products_what_do_you_sell' => __( 'What type of products or services will you sell?', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_ship_products_with_ease_description' => __( 'Choose the ways you\'d like to ship orders to customers. You can always add others later.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_getting_features_ready' => __( 'Getting your features ready', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_your_progress' => __( 'Your progress', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_store_info' => __( 'Store info', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_set_up_a_payment_method' => __( 'Set up a payment method', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_set_up_payment_method' => __( 'Set up payment method', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_set_up_a_payment_method_description' => __( 'Get ready to accept customer payments. Let them pay for your products and services with ease.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_first_product' => __( 'Add your first product', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_first_product_or_service' => __( 'Add your first product or service', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_first_product_or_service_description' => __( 'Sell products, services, and digital downloads. Set up and customize each item to fit your business needs.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_start_earning' => __( 'Start earning', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect_your_domain_to_hostinger' => __( 'Connect your domain to Hostinger', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_wait_for_domain_propagation' => __( 'Wait for domain propagation', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_nameserver' => __( 'Nameserver', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect_your_domain_to_hostinger' => __( 'Connect your domain to Hostinger', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect_your_domain_description_step_one' => __( 'You can connect domain to Hostinger by changing the nameservers. Different domain providers are have unique procedures for changing nameservers. Here are Hostinger\'s nameservers:', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect_your_domain_description_step_two' => __( ' Learn how to connect your domain to Hostinger by watching this tutorial on YouTube for a step-by-step guide:', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_how_to_point_domain_nameservers' => __( 'How to Point Domain Name to Web Hosting', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_play_on_youtube' => __( 'Play on YouTube', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_share_your_referral_link' => __( 'Share your referral link with friends and family and <strong>receive 20% commission</strong> for every successful referral.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_invite_friend' => __( 'Invite a Friend, Earn Up to $100', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_welcome_to_wordpress_description' => __( 'Set up a website that works for you.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_create_a_logo_description' => __( 'Adding a logo is a great way to personalize a website or add branding information. You can use your existing logo or create a new one using the <a href="https://logo.hostinger.com/?ref=wordpress-onboarding" style="text-decoration:none; font-weight:bold; color:#673de6">AI Logo Maker.</a>', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_upload_your_logo' => __( 'Upload your logo', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_welcome_to_wordpress_title' => __( '👋 Welcome to WordPress', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_website_url' => __( 'Website URL', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_get_domain_description_step_one' => __( 'Your website is already published and can be accessed using Hostinger free temporary subdomain right now. Here is the current URL of your website:', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_get_domain_description_step_two' => __( 'You need to purchase a domain for your website before the preview domain becomes inaccessible. Find a desired website name using a <a style="text-decoration:none; font-weight:bold; color:#673de6" target="_blank" href="https://hpanel.hostinger.com/domains/domain-checker" >domain name searcher.</a >', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_go_to_customize_page_description' => __( 'In the left sidebar, click Appearance to expand the menu. In the Appearance section, click Customize. The Customize page will open.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_post_description' => __( 'Edit post description', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_upload_an_image' => __( 'Upload an image', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_site_title' => __( 'Edit site title', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_a_new_page' => __( 'Add a new page', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect_your_domain' => __( 'Connect your domain', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_create_a_logo_title' => __( 'Create a logo', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_go_to_customize_page_title' => __( 'Go to the Customize page', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_upload_your_logo_title' => __( 'Upload your logo', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_upload_your_logo_description' => __( 'In the left sidebar, click Site Identity, then click on the Select Site Icon button. Here, you can upload your brand logo.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_take_me_there' => __( 'Take me there', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_get_started' => __( 'Get started!', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_skip' => __( 'Skip', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_setup_my_online_store' => __( 'Setup my online store', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_setup_my_online_store_description' => __( 'Enter your store details so we can help you set up your online store faster.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_your progress' => __( 'Your progress', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_complete' => __( 'Complete', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_website_setup' => __( 'Website setup', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_dismiss' => __( 'Dismiss', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_finish_setting_up_plugins' =>  __( 'Now <strong>finish setting up</strong> the plugins you’ve installed.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_view_plugins' => __( 'View plugins', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_done_setting_up_online_store' => __( 'You’re done setting up your online store!', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_view_completed' => __( 'View completed', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_hide_completed' => __( 'Hide completed', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect_domain_now' => __( 'Connect domain now', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_got_it' => __( 'Got it!', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_go_to_posts_title' => __( 'Go to Posts', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_go_to_posts_description' => __( 'In the left sidebar, find the Posts button. Click on the All Posts button and find the post for which you want to change the description.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_post_title' => __( 'Edit post', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_post_description' => __( 'Hover over the chosen post to see the options menu. Click on the Edit button to open the post editor.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_description_title' => __( 'Edit description', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_description_description' => __( 'You can see the whole post in the editor. Find the description part and change it to your preferences.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_find_the_media_page_title' => __( 'Find the Media page', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_find_the_media_page_description' => __( 'In the left sidebar, find the Media button. Click on the Library button to see all the images you have uploaded to your website.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_upload_an_image_title' => __( 'Upload an image', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_upload_an_image_description' => __( 'To upload a new image, click on Add New button on the Media Library page and select files.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_an_image_title' => __( 'Edit an image', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_edit_an_image_description' => __( 'If you wish to edit the image, click on the chosen image and click the Edit Image button. You can now crop, rotate, flip or scale the selected image.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_go_to_the_customize_page_title' => __( 'Go to the Customize page', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_go_to_the_customize_page_description' => __( 'In the left sidebar, click Appearance to expand the menu. In the Appearance section, click Customize. The Customize page will open.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_access_site_identity_and_edit_title_title' => __( 'Access site identity and edit title', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_access_site_identity_and_edit_title_description' => __( 'In the left sidebar, click Site Identity and edit your site title.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_a_new_page_title' => __( 'Add a new page', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_plugin' => __( 'Add plugin', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_plugin_added' => __( 'Added', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect' => __( 'Connect', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_dismiss' => __( 'Dismiss', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_generate_content' => __( 'Generate content', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_plugin_installed' => __( 'Installed', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_generate_content_with_ai' => __( 'Generate content with AI', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_generate_content_with_ai_description' => __( 'Get images, text, and SEO keywords created for you instantly – try <strong>AI Content Creator</strong>.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_run_amazon_affiliate_site' => __( 'Run an Amazon Affiliate site', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_run_amazon_affiliate_site_description' => __( 'Connect your <strong>Amazon Associate</strong> account to fetch API details.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_plugin_activate' => __( 'Activate', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_a_new_page_description' => __( 'In the left sidebar, find the Pages menu and click on Add New button. You will see the WordPress page editor. Each paragraph, image, or video in the WordPress editor is presented as a “block” of content.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_title_title' => __( 'Add a title', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_back' => __( 'Back', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboaring_payment_methods' => __( 'Payment methods', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboaring_shipping_methods' => __( 'Shipping methods', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_set_up_payments' => __( 'Set up payments', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_recommended_for_you' => __( 'Recommended for you', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_other' => __( 'Other', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_shipping_method' => __( 'Add shipping method', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_shipping_without_additional_plugins' => __( 'You can also set up a shipping method without installing additional plugins.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_set_up_payment_method_without_additional_plugins' => __( 'You can also set up a payment method without installing additional plugins.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_title_description' => __( 'Add the title of the page, for example, About. Click the Add Title text to open the text box where you will add your title. The title of your page should be descriptive of the information the page will have.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_content_title' => __( 'Add content', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_add_content_description' => __( 'Content can be anything you wish, for example, text, images, videos, tables, and lots more. Click on a plus sign and choose any block you want to add to the page.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_publish_the_page_title' => __( 'Publish the page', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_publish_the_page_description' => __( 'Before publishing, you can preview your created page by clicking on the Preview button. If you are happy with the result, click the Publish button.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_get_a_domain_title' => __( 'Get a domain', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_connect_your_domain_to_hostinger_title' => __( 'Connect your domain to Hostinger', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_wait_for_domain_propagation_description' => __( 'Domain propagation can take up to 24 hours. Your domain will propagate automatically, and you don\'t need to take any action during this time.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_how_to_add_wordpress_website_to_google_search_console' => __( 'How to Add Your WordPress Website to Google Search Console', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_how_to_create_wordpress_contact_us_page' => __( 'How to Create a WordPress Contact Us Page', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_how_to_clear_cache_in_wordpress_website' => __( 'How to Clear Cache in WordPress Website', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_how_to_edit_footer_in_wordpress' =>__(  'How to Edit the Footer in WordPress', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_how_to_get_maximum_wordpress_optimization' => __( 'LiteSpeed Cache: How to Get 100% WordPress Optimization', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_how_to_backup_wordpress_site' => __( 'How to Back Up a WordPress Site', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_how_to_import_images_to_wordpress_website' => __( 'How to Import Images Into WordPress Website', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_how_to_setup_wordpress_smtp' => __( 'How to Set Up WordPress SMTP', 'hostinger-easy-onboarding' ),
                    'hosinger_easy_onboarding_knowledge_base' => __( 'Knowledge Base', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_find_answers_in_knowledge_base' => __( 'Find the answers you need in our Knowledge Base', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_help_center' => __( 'Help Center', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_wordpress_tutorials' => __( 'WordPress tutorials', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_hostinger_academy' => __( 'Hostinger Academy', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_setup_page_confirmation_text' => __( 'Opt-in to receive tips, discounts, and recommendations from the WooCommerce team directly in your inbox.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_tell_us_about_your_business' => __( 'Tell us a bit about your business', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_tell_us_about_your_business_description' => __( 'We\'ll use this information to help you set up your store faster.', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_get_in_touch_with_live_specialists' => __( 'Get in touch with our live specialists', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_welcome_to_wordpress' => __( 'Welcome to WordPress!', 'hostinger-easy-onboarding' ),
                    'hostinger_easy_onboarding_follow_steps_complete_setup' => __( 'Follow these steps to complete your setup', 'hostinger-easy-onboarding' ),
                ),
                'rest_base_url' => esc_url_raw( rest_url() ),
                'nonce'         => wp_create_nonce( 'wp_rest' ),
                'ajax_nonce'         => wp_create_nonce( 'updates' ),
            );

            if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

                $omnisend_slug = 'omnisend-woocommerce';

                $all_plugins = get_plugins();

                $localize_data['woo'] = array(
                    'store_email' => get_bloginfo( 'admin_email' ),
                    'type_of_products' => array(
                        array(
                            'name' => 'clothing_and_accessories',
                            'label' => __('Clothing and accessories', 'woocommerce')
                        ),
                        array(
                            'name' => 'health_and_beauty',
                            'label' => __('Health and beauty', 'woocommerce')
                        ),
                        array(
                            'name' => 'food_and_drink',
                            'label' => __('Food and drink', 'woocommerce')
                        ),
                        array(
                            'name' => 'home_furniture_and_garden',
                            'label' => __('Home, furniture and garden', 'woocommerce')
                        ),
                        array(
                            'name' => 'education_and_learning',
                            'label' => __('Education and learning', 'woocommerce')
                        ),
                        array(
                            'name' => 'electronics_and_computers',
                            'label' => __('Electronics and computers', 'woocommerce')
                        ),
                        array(
                            'name' => 'other',
                            'label' => __('Other', 'woocommerce')
                        ),
                    ),
                    'omnisend_state' => array(
                      'is_installed' => array_key_exists( 'omnisend-connect/'.$omnisend_slug.'.php', $all_plugins),
                      'is_active' => is_plugin_active( 'omnisend-connect/'.$omnisend_slug.'.php' ),
                    ),
                    'store_countries' => $this->get_countries_and_states()
                );
            }

			wp_localize_script(
				'hostinger_easy_onboarding_main_scripts',
				'hostinger_easy_onboarding',
				$localize_data
			);
		}

		wp_enqueue_script(
			'hostinger_easy_onboarding_global_scripts',
			HOSTINGER_EASY_ONBOARDING_ASSETS_URL . '/js/global-scripts.min.js',
			array(
				'jquery',
				'wp-i18n',
			),
			HOSTINGER_EASY_ONBOARDING_VERSION,
			false
		);

        wp_localize_script(
            'hostinger_easy_onboarding_global_scripts',
            'hostinger_easy_onboarding_global',
            array(
                'rest_base_url' => esc_url_raw( rest_url() ),
                'nonce'         => wp_create_nonce( 'wp_rest' ),
                'hostinger_nonce'         => wp_create_nonce( 'hts-ajax-nonce' ),
            )
        );
	}

    private function get_countries_and_states() {
        $countries = WC()->countries->get_countries();
        if ( ! $countries ) {
            return array();
        }
        $output = array();
        foreach ( $countries as $key => $value ) {
            $states = WC()->countries->get_states( $key );

            if ( $states ) {
                foreach ( $states as $state_key => $state_value ) {
                    $output[ $key . ':' . $state_key ] = $value . ' - ' . $state_value;
                }
            } else {
                $output[ $key ] = $value;
            }
        }
        return $output;
    }
}
