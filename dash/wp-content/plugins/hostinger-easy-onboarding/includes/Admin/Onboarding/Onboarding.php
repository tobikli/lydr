<?php

namespace Hostinger\EasyOnboarding\Admin\Onboarding;

use Hostinger\EasyOnboarding\Admin\Actions as Admin_Actions;
use Hostinger\EasyOnboarding\AmplitudeEvents\Actions as AmplitudeActions;
use Hostinger\EasyOnboarding\AmplitudeEvents\Amplitude;
use Hostinger\EasyOnboarding\Settings;

defined( 'ABSPATH' ) || exit;

class Onboarding {
    private const HOSTINGER_ADD_DOMAIN_URL  = 'https://hpanel.hostinger.com/add-domain/';
    private const HOSTINGER_WEBSITES_URL    = 'https://hpanel.hostinger.com/websites';
    public const HOSTINGER_EASY_ONBOARDING_STEPS_OPTION_NAME    = 'hostinger_easy_onboarding_steps';
    public const HOSTINGER_EASY_ONBOARDING_WEBSITE_STEP_CATEGORY_ID   = 'website_setup';
    public const HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID   = 'online_store_setup';

    /**
     * @var array
     */
    private array $step_categories = array();

    /**
     * @return void
     */
    public function init(): void {
        $this->load_step_categories();
    }

    /**
     * @return void
     */
    private function load_step_categories(): void {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $website_type = Settings::get_setting( 'survey.website.type' );

        $website_step_category = new StepCategory(
            self::HOSTINGER_EASY_ONBOARDING_WEBSITE_STEP_CATEGORY_ID,
            __( 'Website setup', 'hostinger-easy-onboarding' )
        );

        // Add logo.
        if ( get_theme_support( 'custom-logo' ) ) {
            $website_step_category->add_step( $this->get_add_logo_step() );
        }

        // Add post or description.
        if ( $website_type === Settings::WEBSITE_TYPE_BLOG ) {
            $website_step_category->add_step( $this->get_add_post_step() );
        } else {
            $website_step_category->add_step( $this->get_add_description_step() );
        }

        // Add image.
        $website_step_category->add_step( $this->get_add_image_step() );

        // Add heading.
        $website_step_category->add_step( $this->get_add_heading_step() );

        // Add page.
        $website_step_category->add_step( $this->get_add_page_step() );

        // Connect domain.
        $website_step_category->add_step( $this->get_add_domain_step() );

        // Add category.
        $this->step_categories[] = $website_step_category;

        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            $store_step_category = new StepCategory(
                self::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID,
                __('Online store setup', 'hostinger-easy-onboarding')
            );

            // Setup online store
            $store_step_category->add_step( $this->get_setup_online_store() );

            // Add product.
            $store_step_category->add_step( $this->get_add_product_step() );

            // Add payment method.
            $store_step_category->add_step( $this->get_payment_method_step() );

            // Add shipping method.
            $store_step_category->add_step( $this->get_shipping_method_step() );

            // Add marketing method.
            $store_step_category->add_step( $this->get_marketing_step() );

            $this->step_categories[] = $store_step_category;
        }
    }

    /**
     * @return array
     */
    public function get_step_categories(): array {
        return array_map(
            function ( $item ) {
                return $item->to_array();
            },
            $this->step_categories
        );
    }

    /**
     * @param string $step_category_id
     * @param string $step_id
     *
     * @return bool
     */
    public function complete_step( string $step_category_id, string $step_id ): bool {
        if ( !$this->validate_step( $step_category_id, $step_id ) ) {
            return false;
        }

        $onboarding_steps = $this->get_saved_steps();

        if(empty($onboarding_steps[$step_category_id])) {
            $onboarding_steps[$step_category_id] = array();
        }

        $onboarding_steps[$step_category_id][$step_id] = true;

        $this->maybe_send_store_events( $onboarding_steps );

        return update_option( self::HOSTINGER_EASY_ONBOARDING_STEPS_OPTION_NAME, $onboarding_steps );
    }

    /**
     * @param string $step_category_id
     * @param string $step_id
     *
     * @return bool
     */
    public function validate_step( string $step_category_id, string $step_id ): bool {
        $step_categories = $this->get_step_categories();

        if(empty($step_categories)) {
            return false;
        }

        // Try to match step category id.
        $found = false;
        foreach($step_categories as $step_category) {
            if($step_category['id'] == $step_category_id) {
                if(!empty($step_category['steps'])) {
                    foreach($step_category['steps'] as $step) {
                        if($step['id'] == $step_id) {
                            $found = true;
                            break;
                        }
                    }
                }
                break;
            }
        }

        if(empty($found)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $step_category_id
     * @param string $step_id
     *
     * @return bool
     */
    public function is_completed( string $step_category_id, string $step_id ) : bool {
        $onboarding_steps = $this->get_saved_steps();

        if(empty($onboarding_steps[$step_category_id][$step_id])) {
            return false;
        }

        return (bool)$onboarding_steps[$step_category_id][$step_id];
    }

    /**
     * @return array
     */
    private function get_saved_steps(): array {
        return get_option( self::HOSTINGER_EASY_ONBOARDING_STEPS_OPTION_NAME, array() );
    }

    /**
     * @return Step
     */
    private function get_add_logo_step(): Step
    {
        $step = new Step('add_logo');

        $step->set_title( __( 'Upload your logo', 'hostinger-easy-onboarding' ) );

        $bullet_points = array(
            array(
                'title'       => __( 'Create a logo', 'hostinger-easy-onboarding' ),
                'description' => __( 'Adding a logo is a great way to personalize a website or add branding information. You can use your existing logo or create a new one using the <a href="https://logo.hostinger.com/?ref=wordpress-onboarding" target="_blank">AI Logo Maker</a>.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Go to the Customize page', 'hostinger-easy-onboarding' ),
                'description' => __( 'In the left sidebar, click Appearance to expand the menu. In the Appearance section, click Customize. The Customize page will open. ', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Upload your logo', 'hostinger-easy-onboarding' ),
                'description' => __( 'In the left sidebar, click Site Identity, then click on the Select Site Icon button. Here, you can upload your brand logo. ', 'hostinger-easy-onboarding' ),
            )
        );

        $step->set_bullet_points( $bullet_points );

        $step->set_url( admin_url( 'customize.php' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_add_post_step(): Step
    {
        $step = new Step('add_post');

        $step->set_title( __( 'Create your first blog post', 'hostinger-easy-onboarding' ) );

        $bullet_points = array(
            array(
                'title'       => __( 'Create a catchy headline', 'hostinger-easy-onboarding' ),
                'description' => __( 'Create a headline that grabs your visitors attention and accurately represents the content of your post.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Draft your post', 'hostinger-easy-onboarding' ),
                'description' => __( 'Write your content, making sure to include relevant keywords and images. You can use different blocks to create headings, paragraphs, lists, and other types of content.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Proofread and publish', 'hostinger-easy-onboarding' ),
                'description' => __( 'Once you have finished drafting your post, read it over to check for errors, make any necessary revisions, and then publish it to your blog.', 'hostinger-easy-onboarding' ),
            ),
        );

        $step->set_bullet_points( $bullet_points );

        $step->set_url( admin_url( 'post-new.php' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_add_description_step(): Step
    {
        $step = new Step('edit_description');

        $step->set_title( __( 'Edit post description', 'hostinger-easy-onboarding' ) );

        $bullet_points = array(
            array(
                'title'       => __( 'Go to Posts', 'hostinger-easy-onboarding' ),
                'description' => __( 'In the left sidebar, find the Posts button. Click on the All Posts button and find the post for which you want to change the description.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Edit post', 'hostinger-easy-onboarding' ),
                'description' => __( 'Hover over the chosen post to see the options menu. Click on the Edit button to open the post editor.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Edit description', 'hostinger-easy-onboarding' ),
                'description' => __( 'You can see the whole post in the editor. Find the description part and change it to your preferences.', 'hostinger-easy-onboarding' ),
            ),
        );

        $step->set_bullet_points( $bullet_points );

        $step->set_url( admin_url( 'edit.php' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_add_image_step(): Step
    {
        $step = new Step('image_upload');

        $step->set_title( __( 'Upload an image', 'hostinger-easy-onboarding' ) );

        $bullet_points = array(
            array(
                'title'       => __( 'Find the Media page', 'hostinger-easy-onboarding' ),
                'description' => __( 'In the left sidebar, find the Media button. The Media Library page allows you to edit, view, and delete media previously uploaded to your website.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Upload an image', 'hostinger-easy-onboarding' ),
                'description' => __( 'To upload a new image, click on Add New button on the Media Library page and select files.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Edit an image', 'hostinger-easy-onboarding' ),
                'description' => __( 'If you wish to edit the image, click on the chosen image and click the Edit Image button. You can now crop, rotate, flip or scale the selected image.', 'hostinger-easy-onboarding' ),
            ),
        );

        $step->set_bullet_points( $bullet_points );

        $step->set_url( admin_url( 'media-new.php' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_add_heading_step(): Step
    {
        $step = new Step('edit_site_title');

        $step->set_title( __( 'Edit site title', 'hostinger-easy-onboarding' ) );

        $bullet_points = array(
            array(
                'title'       => __( 'Go to the Customize page', 'hostinger-easy-onboarding' ),
                'description' => __( 'In the left sidebar, click Appearance to expand the menu. In the Appearance section, click Customize. The Customize page will open.', 'hostinger-easy-onboarding' ),
            ),
            array(
                'title'       => __( 'Access the Site identity and edit title', 'hostinger-easy-onboarding' ),
                'description' => __( 'In the left sidebar, click Site Identity and edit your site title.', 'hostinger-easy-onboarding' ),
            ),
        );

        $step->set_bullet_points( $bullet_points );

        $step->set_url( admin_url( 'customize.php' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_add_page_step(): Step
    {
        $website_type = Settings::get_setting( 'survey.website.type' );

        $step = new Step('add_page');

        switch ( $website_type ) {
            case Settings::WEBSITE_TYPE_BUSINESS:
                $title = __( 'Create a page describing your services', 'hostinger-easy-onboarding' );
                break;
            case Settings::WEBSITE_TYPE_PORTFOLIO:
                $title = __( 'Upload your portfolio projects', 'hostinger-easy-onboarding' );
                break;
            default:
                $title = __( 'Add a new page', 'hostinger-easy-onboarding' );
        }

        $step->set_title( $title );

        switch ( $website_type ) {
            case Settings::WEBSITE_TYPE_BUSINESS:
                $bullet_points = array(
                    array(
                        'title'       => __( 'Give a page a short title such as “Our Services”', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Come up with a clear and concise name for the page that accurately reflects the services you provide.', 'hostinger-easy-onboarding' ),
                    ),
                    array(
                        'title'       => __( 'Add the content and images that represent your services', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Write a brief introduction to your business, provide an overview of your services with descriptions and benefits, and use relevant images to support your content. Keep it simple and straightforward.', 'hostinger-easy-onboarding' ),
                    ),
                    array(
                        'title'       => __( 'Finalize the page', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Make the page easy to read and navigate by using headings and bullet points. Include a clear call-to-action (CTA) to encourage visitors to take the next step for example, to contact you via email or contact form.', 'hostinger-easy-onboarding' ),
                    ),
                );
                break;
            case Settings::WEBSITE_TYPE_PORTFOLIO:
                $bullet_points = array(
                    array(
                        'title'       => __( 'Create a new page called “My projects”', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Give your page a name (e.g. "My Portfolio"), then click the "+" button in the editor to add a new block.', 'hostinger-easy-onboarding' ),
                    ),
                    array(
                        'title'       => __( 'Add a gallery block', 'hostinger-easy-onboarding' ),
                        'description' => __( 'In the block menu, search for "Gallery" and select the Gallery block. Upload the images you want to showcase in your portfolio, then adjust the layout and add captions if needed.', 'hostinger-easy-onboarding' ),
                    ),
                    array(
                        'title'       => __( 'Publish your page', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Once you\'re happy with your portfolio post, click the "Publish" button to make it live on your website. You can add a link to your main navigation menu or other relevant posts to make it easy for visitors to find.', 'hostinger-easy-onboarding' ),
                    ),
                );
                break;
            default:
                $bullet_points = array(
                    array(
                        'title'       => __( 'Add a new page', 'hostinger-easy-onboarding' ),
                        'description' => __( 'In the left sidebar, find the Pages menu and click on Add New button. You will see the WordPress page editor. Each paragraph, image, or video in the WordPress editor is presented as a “block” of content.', 'hostinger-easy-onboarding' ),
                    ),
                    array(
                        'title'       => __( 'Add a title', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Add the title of the page, for example, About. Click the Add Title text to open the text box where you will add your title. The title of your page should be descriptive of the information the page will have.', 'hostinger-easy-onboarding' ),
                    ),
                    array(
                        'title'       => __( 'Add content', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Content can be anything you wish, for example, text, images, videos, tables, and lots more. Click on a plus sign and choose any block you want to add to the page.', 'hostinger-easy-onboarding' ),
                    ),
                    array(
                        'title'       => __( 'Publish the page', 'hostinger-easy-onboarding' ),
                        'description' => __( 'Before publishing, you can preview your created page by clicking on the Preview button. If you are happy with the result, click the Publish button.', 'hostinger-easy-onboarding' ),
                    ),
                );
        }

        $step->set_bullet_points( $bullet_points );

        $step->set_url( admin_url( 'post-new.php?post_type=page' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_add_domain_step(): Step
    {
        $step = new Step('connect_domain');

        $step->set_title( __( 'Connect your domain', 'hostinger-easy-onboarding' ) );

        $step->set_component_name( 'ConnectDomain' );

        $site_url   = preg_replace( '#^https?://#', '', get_site_url() );
        $hpanel_url = self::HOSTINGER_ADD_DOMAIN_URL . $site_url . '/options';

        $query_parameters = array(
            'websiteType' => 'wordpress',
            'redirectUrl' => self::HOSTINGER_WEBSITES_URL,
        );

        $step->set_url( $hpanel_url . '?' . http_build_query( $query_parameters ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_setup_online_store(): Step
    {
        $step = new Step('setup_store');

        $step->set_component_name( 'SetupOnlineStore' );

        // $step->set_image_url( 'setup-online-store.png' ); // TODO: set image url

        $step->set_title( __( 'Setup my online store', 'hostinger-easy-onboarding' ) );

        $step->set_description( __( 'Enter your store details so we can help you set up your online store faster.', 'hostinger-easy-onboarding' ) );

        $step->set_button_name( __( 'Get Started', 'hostinger-easy-onboarding' ) );

        $step->set_url( admin_url( 'admin.php?page=hostinger-get-onboarding&subPage=hostinger-store-setup-information' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_add_product_step(): Step
    {
        $step = new Step('add_product');

        $step->set_image_url( '' ); // TODO: set image url

        $step->set_title( __( 'Add your first product', 'hostinger-easy-onboarding' ) );

        $step->set_component_name( 'AddFirstProduct' );

        $step->set_description( __( 'Sell products, services, and digital downloads. Set up and customize each item to fit your business needs.', 'hostinger-easy-onboarding' ) );

        $step->set_button_name( __( 'Add product', 'hostinger-easy-onboarding' ) );

        $step->set_url( admin_url( 'post-new.php?post_type=product' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_payment_method_step(): Step
    {
        $step = new Step('add_payment_method');

        $step->set_image_url( '' ); // TODO: set image url

        $step->set_component_name( 'SetupPaymentMethod' );

        $step->set_title( __( 'Set up a payment method', 'hostinger-easy-onboarding' ) );

        $step->set_description( __( 'Get ready to accept customer payments. Let them pay for your products and services with ease.', 'hostinger-easy-onboarding' ) );

        $step->set_button_name( __( 'Set up payment method', 'hostinger-easy-onboarding' ) );

        $step->set_url( admin_url( 'admin.php?page=hostinger-get-onboarding&subPage=hostinger-store-add-payment-method' ) );


        return $step;
    }

    /**
     * @return Step
     */
    private function get_shipping_method_step(): Step
    {
        $step = new Step('add_shipping_method');

        $step->set_image_url( '' ); // TODO: set image url

        $step->set_component_name( 'ShipProducts' );

        $step->set_title( __( 'Ship products with ease', 'hostinger-easy-onboarding' ) );

        $step->set_description( __( 'Choose the ways you\'d like to ship orders to customers. You can always add others later.', 'hostinger-easy-onboarding' ) );

        $step->set_button_name( __( 'Shipping methods', 'hostinger-easy-onboarding' ) );

        $step->set_url( admin_url( 'admin.php?page=hostinger-get-onboarding&subPage=hostinger-store-add-shipping-method' ) );

        return $step;
    }

    /**
     * @return Step
     */
    private function get_marketing_step(): Step
    {
        $step = new Step('add_marketing');

        $step->set_image_url( '' ); // TODO: set image url

        $step->set_title( __( 'Market your business', 'hostinger-easy-onboarding' ) );

        $step->set_component_name( 'MarketBusiness' );

        $step->set_description( __( 'Expand your audience with the help of Omnisend. Create email campaigns that drive sales.', 'hostinger-easy-onboarding' ) );

        if ( is_plugin_active( 'omnisend-connect/omnisend-woocommerce.php' ) ) {
            $button_name = __('Configure', 'hostinger-easy-onboarding');
        } else {
            $button_name = __('Try Omnisend', 'hostinger-easy-onboarding');
        }

        $step->set_button_name($button_name);

        $step->set_url( admin_url( 'admin.php?page=omnisend-woocommerce' ) );

        return $step;
    }

    public function maybe_send_store_events( array $steps ) : void {
        if ( $this->is_store_ready( $steps ) ) {
            $this->send_event( AmplitudeActions::WOO_READY_TO_SELL, true );
        }

        if ( $this->is_store_completed( $steps ) ) {
            $this->send_event( AmplitudeActions::WOO_SETUP_COMPLETED, true );
        }
    }

    private function is_store_ready( array $steps ): bool {
        $store_steps = $steps[Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID] ?? array();
        return !empty( $store_steps[Admin_Actions::ADD_PAYMENT] ) && !empty( $store_steps[Admin_Actions::ADD_PRODUCT] );
    }

    private function is_store_completed( $steps ): bool {
        $all_woo_steps = Admin_Actions::CATEGORY_ACTION_LISTS[ Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID ];
        $completed_woo_steps = $steps[Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID];

        foreach ( $all_woo_steps as $step_key ) {
            if ( empty( $completed_woo_steps[ $step_key ] ) ) {
                return false;
            }
        }

        return true;
    }

    private function send_event( string $action, bool $once = false ): bool {
        if ( $once ) {
            $option_name = 'hostinger_amplitude_' . $action;

            $event_sent = get_option( $option_name, false );

            if ( $event_sent ) {
                return false;
            }
        }

        $amplitude = new Amplitude();

        $params = array( 'action' => $action );

        $event = $amplitude->send_event( $params );

        if( $once ) {
            update_option( $option_name, true );
        }

        return !empty( $event );
    }
}
