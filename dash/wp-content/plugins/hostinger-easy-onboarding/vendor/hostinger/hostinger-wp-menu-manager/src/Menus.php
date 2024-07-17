<?php

namespace Hostinger\WpMenuManager;

use WP_Admin_Bar;

class Menus
{
    /**
     * @var Manager
     */
    private Manager $manager;

    public const MENU_SLUG = 'hostinger';

    /**
     * @return void
     */
    public function init(): void
    {
        if (!$this->manager->checkCompatibility()) {
            add_action('admin_bar_menu', [$this, 'modifyAdminBar'], 999);
            add_filter('admin_body_class', [$this, 'addMenuClass']);
            add_action('admin_menu', [$this, 'registerAdminMenu']);
        } else {
            $this->manager->maybeDoCompatibilityRedirect();
            add_action('admin_notices', [$this, 'compatibilityMessage'], 0);
        }
    }

    /**
     * @param Manager $manager
     *
     * @return void
     */
    public function setManager(Manager $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * @param WP_Admin_Bar $bar
     *
     * @return void
     */
    public function modifyAdminBar(WP_Admin_Bar $bar): void
    {
        $menu_items = apply_filters('hostinger_admin_menu_bar_items', []);

        if (!empty($menu_items)) {
            $hostinger_icon = '<svg width="28" height="29" viewBox="0 0 28 29" fill="#9ca1a7" style="margin-right: 6px; max-height: 22px; float: left; margin-top: 4px;" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M1.8669 13.6096V0.500465L8.48322 4.02842V9.93472L17.2419 9.93895L23.9655 13.6096H1.8669ZM19.033 8.85388V0.5L25.8277 3.94018V12.801L19.033 8.85388ZM19.033 24.8765V19.0211L10.2067 19.015C10.215 19.054 3.37149 15.2857 3.37149 15.2857L25.8277 15.3911V28.5L19.033 24.8765ZM1.86667 24.8765L1.8669 16.31L8.48322 20.1637V28.3164L1.86667 24.8765Z" fill="" />
			</svg>';

            $bar->add_menu([
                'id'     => 'hostinger_admin_bar',
                'parent' => null,
                'group'  => null,
                'title'  => $hostinger_icon . esc_html__('Hostinger', 'hostinger-wp-menu-package'),
            ]);

	        foreach ($menu_items as $menu_item) {
		        $menu_item_data = [
			        'id'     => $menu_item['id'],
			        'parent' => 'hostinger_admin_bar',
			        'group'  => null,
			        'title'  => $menu_item['title'],
			        'href'   => $menu_item['href'],
		        ];

		        if( isset( $menu_item['meta'] ) ){
			        $menu_item_data['meta'] = $menu_item['meta'];
		        }

		        $bar->add_menu($menu_item_data);
	        }
        }
    }

    /**
     * @param string $classes
     *
     * @return string
     */
    public function addMenuClass(string $classes): string
    {
        $classes .= ' hostinger-hide-main-menu-item';

        if (!empty(self::isSubmenuItemsHidden())) {
            $classes .= ' hostinger-hide-all-menu-items';
        }

        return $classes;
    }

    /**
     * @return bool
     */
    public function registerAdminMenu(): bool
    {
        $submenus = self::getMenuSubpages();

        if (empty($submenus)) {
            return false;
        }

        $icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjEiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyMSAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBjbGlwLXJ1bGU9ImV2ZW5vZGQiIGQ9Ik0wLjAwMDE5OTY1MyAxMS4yMzY4VjAuMDAwMzk4MjM1TDUuNjcxMzMgMy4wMjQzNlY4LjA4NjkxTDEzLjE3ODggOC4wOTA1M0wxOC45NDE5IDExLjIzNjhIMC4wMDAxOTk2NTNaTTE0LjcxNCA3LjE2MDQ3VjBMMjAuNTM4IDIuOTQ4NzJWMTAuNTQzN0wxNC43MTQgNy4xNjA0N1pNMTQuNzE0IDIwLjg5NDJWMTUuODc1M0w3LjE0ODYyIDE1Ljg3QzcuMTU1NjggMTUuOTAzNCAxLjI4OTg0IDEyLjY3MzUgMS4yODk4NCAxMi42NzM1TDIwLjUzOCAxMi43NjM4VjI0TDE0LjcxNCAyMC44OTQyWk0wIDIwLjg5NDFMMC4wMDAyMDE3NjkgMTMuNTUxNEw1LjY3MTMzIDE2Ljg1NDZWMjMuODQyN0wwIDIwLjg5NDFaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K';

        add_menu_page(
            __('Hostinger', 'hostinger-wp-menu-package'),
            __('Hostinger', 'hostinger-wp-menu-package'),
            'manage_options',
            self::MENU_SLUG,
            [$this, 'render'],
            $icon,
            1
        );

        $this->registerSubMenus();

        return true;
    }

    /**
     * @return void
     */
    public function render(): void
    {
        if ($this->hasLoadedMainContent() && !empty(self::isSubmenuItemsHidden())) {
            do_action('hostinger_main_menu_content');
        } else {
            $submenus = self::getMenuSubpages();

            if (!empty($submenus)) {
                call_user_func($submenus[0]['callback']);
            }
        }
    }

    /**
     * @return void
     */
    public static function renderMenuNavigation(): string
    {
        ob_start();

        require_once __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'menu.php';

        $content = ob_get_contents();

        ob_end_clean();

        return $content;
    }

    /**
     * @return array
     */
    public static function getMenuSubpages(): array
    {
        return apply_filters('hostinger_menu_subpages', []);
    }

    /**
     * @return bool
     */
    public static function isSubmenuItemsHidden(): bool
    {
        return !empty(get_option('hostinger_hide_subpages'));
    }

    /**
     * @return bool
     */
    private function hasLoadedMainContent(): bool
    {
        return has_action('hostinger_main_menu_content');
    }

    /**
     * @return bool
     */
    private function registerSubMenus(): bool
    {
        $submenus = self::getMenuSubpages();

        if (empty($submenus)) {
            return false;
        }

        foreach ($submenus as $submenu) {
            add_submenu_page(
                self::MENU_SLUG,
                $submenu['page_title'],
                $submenu['menu_title'],
                $submenu['capability'],
                $submenu['menu_slug'],
                $submenu['callback'],
                $submenu['menu_order']
            );
        }

        return true;
    }

    /**
     * @return void
     */
    public function compatibilityMessage(): void
    {
        ?>
        <div class="notice notice-error is-dismissible hts-theme-settings">
            <p>
                <strong><?php echo __('Attention! Outdated Plugins Detected', 'hostinger-wp-menu-package') ?></strong>
            </p>
            <p>
                <strong><?php echo __('Action Required:', 'hostinger-wp-menu-package') ?></strong> <?php echo __('Your website has some outdated plugins that might prevent new features from working properly.', 'hostinger-wp-menu-package') ?>
            </p>

                <ul style="list-style: circle;margin-left: 18px;">
                <?php

                if (!empty($this->manager->getOutdatedPlugins())) {
                    ?>
                    <li>
                        <p><?php echo __('Outdated plugins:', 'hostinger-wp-menu-package') ?> <?php echo implode(', ', $this->manager->getOutdatedPlugins()); ?></p>
                    </li>
                    <?php
                }

                if (!empty($this->manager->getAffectedActivePlugins())) {
                    ?>
                    <li>
                        <p><?php echo __('Affected plugins:', 'hostinger-wp-menu-package') ?> <?php echo implode(', ', $this->manager->getAffectedActivePlugins()); ?></p>
                    </li>
                    <?php
                }

                ?>
            </ul>
            <p>
                <a href="/wp-admin/update-core.php" class="button-primary">
                    <?php echo __('Update plugins', 'hostinger-wp-menu-package') ?>
                </a>
            </p>
        </div>
        <?php
    }
}
