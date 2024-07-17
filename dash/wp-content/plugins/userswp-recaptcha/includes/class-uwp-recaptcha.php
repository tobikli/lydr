<?php

if(!class_exists('UsersWP_Recaptcha')) {

    class UsersWP_Recaptcha
    {

        private static $instance;

        /**
         * Plugin Version
         */
        private $version = UWP_RECAPTCHA_VERSION;


        public static function get_instance()
        {
            if (!isset(self::$instance) && !(self::$instance instanceof UsersWP_Recaptcha)) {
                self::$instance = new UsersWP_Recaptcha;
                self::$instance->setup_globals();
                self::$instance->includes();
                self::$instance->setup_actions();
            }

            return self::$instance;
        }

        private function __construct()
        {
            self::$instance = $this;
        }

        private function setup_globals()
        {

        }

        private function setup_actions()
        {
            add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
            add_action('login_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            add_action('wp_authenticate_user', array($this, 'login_authenticate'));
            add_action('registration_errors', array($this, 'registration_errors'));
            add_action('init', array($this, 'load_textdomain'));
            add_action('uwp_template_fields', array($this, 'add_captcha_for_uwp_forms'), 10, 1);
            add_action('uwp_validate_result', array($this, 'validate_recaptcha'), 10, 3);
            add_action('register_form', array($this, 'add_recaptcha_wp_register_form'), 10, 1);
            add_action('login_form', array($this, 'add_recaptcha_wp_login_form'), 10, 1);
	        add_action('uwp_options_for_translation', array($this, 'uwp_add_options_for_translation'));

            if (is_admin()) {
                add_action('admin_init', array($this, 'activation_redirect'));
                add_action('admin_notices', array($this, 'recaptcha_key_notices'));
            }

            do_action('uwp_recaptcha_setup_actions');
        }

        /**
         * Load the textdomain.
         */
        public function load_textdomain()
        {
            load_plugin_textdomain('uwp-recaptcha', false, basename(UWP_RECAPTCHA_PATH) . '/languages');
        }

        private function includes()
        {

            if (class_exists('UsersWP')) {
                require_once UWP_RECAPTCHA_PATH . '/includes/functions.php';
            }

            do_action('uwp_recaptcha_include_files');

            if (!is_admin())
                return;

            require_once UWP_RECAPTCHA_PATH . '/admin/settings.php';
            do_action('uwp_recaptcha_include_admin_files');

        }

        /**
         * Redirect to the registration settings page on activation.
         *
         * @since 1.0.0
         */
        public function activation_redirect()
        {
            // Bail if no activation redirect
            if (!get_transient('_uwp_recaptcha_activation_redirect')) {
                return;
            }

            // Delete the redirect transient
            delete_transient('_uwp_recaptcha_activation_redirect');

            // Bail if activating from network, or bulk
            if (is_network_admin() || isset($_GET['activate-multi'])) {
                return;
            }

            wp_safe_redirect(admin_url('admin.php?page=userswp&tab=uwp-addons&section=uwp_recaptcha'));
            exit;
        }

        public function admin_enqueue_scripts(){
            if(1 == uwp_get_option('enable_recaptcha_in_wp_login_form') || 1 == uwp_get_option('enable_recaptcha_in_wp_register_form')){
                $this->enqueue_scripts();
            }
        }

        public function enqueue_scripts()
        {
	        $captcha_version = uwp_get_option( 'recaptcha_version', 'default' );

	        if (!wp_script_is('uwp_recaptcha_js_api', 'registered')) {

	            $api_domain = apply_filters('uwp_recaptcha_js_domain', 'www.recaptcha.net');

		        if ( $captcha_version == 'v3' ) {
			        $site_key = uwp_get_option( 'recaptcha_api_key', '' );
			        wp_register_script( 'uwp_recaptcha_js_api', 'https://'.$api_domain.'/recaptcha/api.js?render=' . $site_key, array( 'jquery' ), $this->version, true );
			        wp_enqueue_script( 'uwp_recaptcha_js_api' );
		        } else {
			        $language = uwp_recaptcha_language();
			        wp_register_script( 'uwp_recaptcha_js_api', 'https://'.$api_domain.'/recaptcha/api.js?onload=uwp_init_recaptcha&hl=' . $language . '&render=explicit', array( 'jquery' ), $this->version, true );
			        wp_enqueue_script( 'uwp_recaptcha_js_api' );
		        }

		        wp_add_inline_script( 'uwp_recaptcha_js_api', $this->inline_script() );

		        $localize_data = $this->get_localize_data();

		        wp_localize_script('uwp_recaptcha_js_api', 'uwp_recaptcha_data', $localize_data);
	        }
        }

        public function inline_script(){
            ob_start();
            ?>
            <script>
                function uwp_init_recaptcha() {
                    if ( jQuery('.uwp-captcha-render').length) {
                        jQuery('.uwp-captcha-render').each(function() {
                            if(jQuery(this).html()==''){
                                var container = jQuery(this).attr('id');
                                if (container) {
                                    try {
                                        eval(container + '()');
                                    } catch(err) {
                                        console.log(err);
                                    }
                                }
                            }
                        });
                    }
                }

                function uwp_reset_captcha(element){
                    if(uwp_recaptcha_data.captcha_version == 'v3') {
                        if (typeof grecaptcha != 'undefined') {
                            holderId = grecaptcha.execute(uwp_recaptcha_data.site_key, {action: 'uwp_captcha'}).then(function (token) {
                                document.getElementById(element).value = token;
                            });
                        }

                    } else {
                        if (typeof grecaptcha != 'undefined') {
                            grecaptcha.reset();
                        }
                    }
                }
            </script>
            <?php
            $output = ob_get_clean();

            /*
			 * We only add the <script> tags for code highlighting, so we strip them from the output.
			 */
            return str_replace( array(
                '<script>',
                '</script>'
            ), '', $output );
        }

        public function get_localize_data(){
	        $localize_data = array(
		        'site_key' => uwp_get_option('recaptcha_api_key', ''),
		        'captcha_version' => uwp_get_option( 'recaptcha_version', 'default' ),
		        'captcha_theme' => uwp_get_option( 'recaptcha_theme', 'light' ),
		        'ajaxurl' => admin_url('admin-ajax.php'),
	        );

	        return apply_filters('uwp_recaptcha_localize_data', $localize_data);
        }

        public function recaptcha_key_notices() {

            $site_key = uwp_get_option('recaptcha_api_key');
            $secret_key = uwp_get_option('recaptcha_api_secret');

            if (empty($site_key) && empty($secret_key)) {
                echo '<div class="notice-error notice is-dismissible"><p><strong>' . sprintf(__('UsersWP ReCaptcha addon: API Key and API Secret not set. %sclick here%s to set one.', 'uwp-recaptcha'), '<a href=\'' . admin_url('admin.php?page=userswp&tab=uwp-addons&section=uwp_recaptcha') . '\'>', '</a>') . '</strong></p></div>';
            } elseif (empty($site_key)) {
                echo '<div class="notice-error notice is-dismissible"><p><strong>' . sprintf(__('UsersWP ReCaptcha addon: API Key not set. %sclick here%s to set one.', 'uwp-recaptcha'), '<a href=\'' . admin_url('admin.php?page=userswp&tab=uwp-addons&section=uwp_recaptcha') . '\'>', '</a>') . '</strong></p></div>';
            } elseif (empty($secret_key)) {
                echo '<div class="notice-error notice is-dismissible"><p><strong>' . sprintf(__('UsersWP ReCaptcha addon: API Secret not set. %sclick here%s to set one.', 'uwp-recaptcha'), '<a href=\'' . admin_url('admin.php?page=userswp&tab=uwp-addons&section=uwp_recaptcha') . '\'>', '</a>') . '</strong></p></div>';
            }

        }

        public function add_captcha_for_uwp_forms($type){
            if(!uwp_recaptcha_enabled()){
                return;
            }
            $enable_register_form = uwp_get_option('enable_recaptcha_in_register_form');
            $enable_login_form = uwp_get_option('enable_recaptcha_in_login_form');
            $enable_forgot_form = uwp_get_option('enable_recaptcha_in_forgot_form');
            $enable_account_form = uwp_get_option('enable_recaptcha_in_account_form');

            // registration form
            if ( $enable_register_form == '1' && $type == 'register') {
                uwp_recaptcha_display( 'register' );
            }

            // login form
            if ( $enable_login_form == '1' && $type == 'login' ) {
                uwp_recaptcha_display( 'login' );
            }

            // forgot form
            if ( $enable_forgot_form == '1' && $type == 'forgot') {
                uwp_recaptcha_display( 'forgot' );
            }

            // account form
            if ( $enable_account_form == '1' && $type == 'account') {
                uwp_recaptcha_display( 'account' );
            }
        }

        public function add_recaptcha_wp_login_form() {
            // WP login form
            $enable_wp_login_form = uwp_get_option('enable_recaptcha_in_wp_login_form', false);
            if ( $enable_wp_login_form == '1' ) {
                uwp_recaptcha_display('wp_login');
            }
        }

        public function add_recaptcha_wp_register_form() {
            // WP register form
            $enable_wp_register_form = uwp_get_option('enable_recaptcha_in_wp_register_form', false);
            if ( $enable_wp_register_form == '1' ) {
                uwp_recaptcha_display('wp_register');
            }
        }

        public function validate_recaptcha($result, $type, $data) {

            if(empty($type) && ! isset( $data['uwp_'.$type.'_nonce'] )){
                return $result;
            }

            if ( uwp_recaptcha_check_role() ) { // disable captcha as per user role settings
                return $result;
            }

            if(!uwp_recaptcha_enabled() || 1 !=  uwp_get_option('enable_recaptcha_in_'.$type.'_form') || is_wp_error($result)){
                return $result;
            }

            if ( $type ) {
                switch( $type ) {
                    case 'register':
                    case 'login':
                    case 'forgot':
                    case 'account':
                    case 'frontend':

                        $response = uwp_recaptcha_check($type);
                        if(is_wp_error($response)){
                            return $response;
                        }

                        break;
                }
            }

            return $result;
        }

        public function login_authenticate($user){

            if(isset( $_POST['uwp_login_nonce'] ) || isset( $_POST['uwp_register_nonce'] )){  // ignore UWP login/register form submission
                return $user;
            }

            if(1 != uwp_get_option('enable_recaptcha_in_wp_login_form') || !uwp_recaptcha_enabled() || is_user_logged_in()){
                return $user;
            }

            if ( is_wp_error( $user ) && isset( $user->errors["empty_username"] ) && isset( $user->errors["empty_password"] ) ){
                return $user;
            }

            $response = uwp_recaptcha_check('wp_login_form');
            if(is_wp_error($response)){
                return $response;
            }

            return $user;
        }

        public function registration_errors($errors){

            if(isset( $_POST['uwp_login_nonce'] ) || isset( $_POST['uwp_register_nonce'] )){  // ignore UWP login/register form submission
                return $errors;
            }

            if(1 != uwp_get_option('enable_recaptcha_in_wp_register_form') || !uwp_recaptcha_enabled()){
                return $errors;
            }

            $response = uwp_recaptcha_check('wp_register_form');
            if(is_wp_error($response)){
                return $response;
            }

            return $errors;
        }

	    /**
	     * Get the settings texts for translation.
	     *
	     * @param  array $uwp_options Array of text strings.
	     * @return array Translation texts.
	     */
	    public function uwp_add_options_for_translation($uwp_options) {
		    $uwp_options = !empty( $uwp_options ) && is_array( $uwp_options ) ? $uwp_options : array();

		    $add_uwp_options = array(
			    'recaptcha_title',
		    );

		    return array_merge($uwp_options, $add_uwp_options);
	    }
    }
}