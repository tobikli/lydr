# WordPress Menu Manager PHP package

Package for managing Hostinger WordPress menus and pages.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
- [Code Testing](#code-testing)
- [Translation](#translation)
- [Support](#support)

## Installation

This is private package, adding it to composer is a bit different.

Add it to the composer.json:
```sh
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:hostinger/hostinger-wp-menu-manager.git"
    }
  ],
```

and:
```sh
"require": {
  "hostinger/hostinger-wp-menu-manager": "dev-main OR dev-branch", 
}
```

## Usage

First thing boot up package in main plugin file:

```sh
use Hostinger\WpMenuManager\Manager;

if( !function_exists('hostinger_load_menus') ) {
    function hostinger_load_menus(): void {
        $manager = Manager::getInstance();
        $manager->boot();
    }
}

if ( ! has_action( 'plugins_loaded', 'hostinger_load_menus' ) ) {
    add_action('plugins_loaded', 'hostinger_load_menus');
}
```

You are ready to go. There are several hooks for use.

**hostinger_menu_subpages** - add new subpage under Hostinger menu item

```sh
add_filter('hostinger_menu_subpages', function($submenus) {
    $example_submenu = array(
        'page_title' => 'Example Submenu',
        'menu_title' => 'Example Submenu',
        'capability' => 'manage_options',
        'menu_slug' => 'example-submenu',
        'callback' => [$this, 'test'],
        'menu_order' => 10
    );
    
    $submenus[] = $example_submenu;
    
    return $submenus;
});
```

**hostinger_admin_menu_bar_items** - add new sub menu items under Hostinger admin bar menu

```sh
add_filter('hostinger_admin_menu_bar_items', function($menu_items) {
    $menu_item = array(
        'id'     => 'billings',
        'title'  => 'Billings',
        'href'  => 'https://',
        'meta'  => [
          'target'  => '_blank'
        ]
    );
    
    $menu_items[] = $menu_item;
    
    return $menu_items;
});
```

You also need to render Hostinger navigation inside your pages:

```sh
use Hostinger\WpMenuManager\Menus;

echo Menus::renderMenuNavigation();
```

If you want to hide all menu items and only show one view you can:

1) Update **hostinger_hide_subpages** option with true

2) Hook into **hostinger_main_menu_content**

```sh
add_action('hostinger_main_menu_content', function() {
  echo 'I am main content';
});
```

## Code Testing

PHP_CodeSniffer package is used to check code against code standards. PSR-12 standard is used.

```sh
$ vendor/bin/phpcs -ps
```

Translation

## Generate .pot file with this command

```sh
$ wp i18n make-pot src/ languages/hostinger-wp-menu-package.pot --domain=hostinger-wp-menu-package
```

## Support

Package initially was written by Daniels Martinovs (daniels.martinovs@hostinger.com). You can ping him in Slack for support.