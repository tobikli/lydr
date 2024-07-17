<?php

namespace Hostinger\Admin;

use Hostinger\Admin\PluginSettings;
use Hostinger\Admin\Onboarding\Onboarding;
use Hostinger\WpHelper\Utils;
use Hostinger\WpMenuManager\Menus;

defined( 'ABSPATH' ) || exit;

class Menu {
	public const MENU_SLUG = 'hostinger-tools';

	public function __construct() {
        add_filter('hostinger_admin_menu_bar_items', array( $this, 'add_admin_bar_items' ) );
        add_filter('hostinger_menu_subpages', array( $this, 'sub_menu' ) );
	}

    public function add_admin_bar_items($menu_items): array {
        $menu_items[] = array(
            'id'     => 'hostinger-tools-admin-bar',
            'title'  => __( 'Tools', 'hostinger' ),
            'href'   => admin_url( 'admin.php?page=' . self::MENU_SLUG )
        );

        return $menu_items;
    }

    public function sub_menu($submenus): array {
        $tools_submenu = array(
            'page_title' => __( 'Tools', 'hostinger' ),
            'menu_title' => __( 'Tools', 'hostinger' ),
            'capability' => 'manage_options',
            'menu_slug' => self::MENU_SLUG,
            'callback' => [$this, 'render_tools_menu_page'],
            'menu_order' => 10
        );

        $submenus[] = $tools_submenu;

        return $submenus;
    }

    public function render_tools_menu_page(): void {
        echo Menus::renderMenuNavigation();
        ?>
		<div id="hostinger-tools-vue-app"/>


        <?php
    }

	public function render(): void {
		$onboarding = new Onboarding();
		include_once __DIR__ . '/Views/Onboarding.php';
	}
}
