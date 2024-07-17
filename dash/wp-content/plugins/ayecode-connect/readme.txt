=== AyeCode Connect ===
Contributors: stiofansisland, paoltaia, ayecode, Ismiaini
Donate link: https://www.ko-fi.com/stiofan
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags:  ayecode, service, geodirectory, userswp, getpaid
Requires at least: 4.7
Requires PHP: 5.6
Tested up to: 6.5
Stable tag: 1.3.0


Use this service plugin to easily activate any of our products, open a support ticket and view documentation all from your wp-admin!

== Description ==

To take full advantage of this plugin you should have one of our plugins installed.

[GeoDirectory](https://wordpress.org/plugins/geodirectory/) | [UsersWP](https://wordpress.org/plugins/userswp/) | [WP Invoicing](https://wordpress.org/plugins/invoicing/)

AyeCode Connect is a service plugin, meaning that it will have no functionality until you connect your site to ours. This link allows us to provide extra services to your site such as live documentation search and submission of support tickets.
After connecting your site you can install our update plugin which will give you the ability to automatically sync license keys of purchases and also be able to remotely install and update purchased products.

You will be able to remotely manage your activated sites and licences all from your account area on our site.

You can also use our one click demo importer.

== Installation ==

= Minimum Requirements =

* WordPress 4.7 or greater
* PHP version 5.6 or greater
* MySQL version 5.0 or greater

= Automatic installation =

Automatic installation is the easiest option. To do an automatic install of AyeCode Connect, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "AyeCode Connect" and click Search Plugins. Once you've found our plugin you install it by simply clicking Install Now.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex will tell you more [here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should seamlessly work. We always suggest you backup up your website before performing any automated update to avoid unforeseen problems.


== Frequently Asked Questions ==

= Do you have T&C's and a Privacy Policy? =

Yes, please see our [terms & conditions](https://ayecode.io/terms-and-conditions/) and [privacy policy.](https://ayecode.io/privacy-policy/)

= Do i need to pay to use this? =

No, you can register a free account on our site which will provide you with live documentation search and the ability to get support directly from your wp-admin area.

= Is my connection to your site safe? =

Yes, our system will only connect via HTTPS ensuring all passed data is encrypted.
Additionally, we built our systems in such a way that;
Should your database or our database (or both) be compromised, this would not result in access to your site.
Should your files or our files (or both) be compromised, this would not result in access to your site.

= Your demo importer is not working? =

If your host runs "mod security" on your hosting and has some specific additional rules active, this can block some of our API calls. Please contact our support for help with this.

== Screenshots ==

1. Pre Connection.
2. Connection Screen.
3. Connected.

== Changelog ==

= 1.3.0 - 2024-07-02 =
* Import demo import - CHANGED
* Demo Import screen improvements - CHANGED

= 1.2.19 - 2024-06-06 =
* Function auto_detect_line_endings is deprecated - FIXED
* Support user auto expiry time extended from 3 > 7 days - CHANGED
* Added AyeNav block which allows GeoDirectory and UsesWP dynamic menu items to be added to the core WP Navigation block - ADDED

= 1.2.18 - 2024-01-29 =
* AUI Updated to latest version - CHANGED

= 1.2.17 - 2023-12-14 =
* Update textdomain - CHANGED

= 1.2.16 =
* Loco translate can cause connected site to disconnect - FIXED

= 1.2.15 =
* PHP deprecated notice "Creation of dynamic property" - FIXED

= 1.2.14 =
* Changes for upcoming Bootstrap v5 option - ADDED

= 1.2.12 =
* Spelling mistake error which could prevent GetPaid extensions showing correctly - FIXED

= 1.2.11 =
* Persistent Object cache plugins can make connection fail - FIXED
* Changes to allow for event tickets demo import - ADDED
* More debugging calls added - ADDED

= 1.2.10 =
* Added strip/replace functionality to demo content to prevent Mod Security blocking some demo imports - ADDED

= 1.2.9 =
* Added warning if coming soon plugin detected that connection might not work - ADDED
* Some servers limit the POST parameters which can cause some licenses not to sync - FIXED

= 1.2.8 =
* Added constant to be able to disable SSL verify for servers that fail this check - ADDED
* Better error debugging functionality - ADDED

= 1.2.7 =
* Some reports of 401 errors on connection for access keys with capital letters - FIXED

= 1.2.6 =
* get_plugins() might be undefined in sync_licenses call in some servers - FIXED
* Added the ability to debug remote calls if constant is defined - ADDED
* Removed double sanitization and extra sanitization in some functions - CHANGED/ADDED
* Readme text clarified at the request of the plugin review team - CHANGED

= 1.2.5 =
* Prevent GD Social Importer activation redirect on import - FIXED

= 1.2.4 =
* Fix PHP Non-static method error - FIXED
* Non-static method AyeCode_Demo_Content::prevent_redirects() should not be called statically - FIXED

= 1.2.3 =
* Demo import not always preventing plugin activation re-direct which can cause first import to fail - FIXED
* Demo import can now open demo import screen via direct URL link - ADDED

= 1.2.2 =
* Demo import might not create the menu if menu with same name already exists - FIXED
* Demo import category images not removed when new demo imported - FIXED
* Demo importer added support for elementor pro imports - ADDED
* Demo importer added support for Kadence theme imports - ADDED
* Demo importer now places old template pages in trash instead of fully deleting - CHANGED

= 1.2.1 =
* AyeCode UI now only loads on specified screen_ids so we add our screen ids - FIXED

= 1.2.0 =
* One click demo import option added for connected users - ADDED
* Licenses now auto sync when a new plugin or theme is installed - ADDED
* Settings now moved to their own admin settings item so we can have sub items - CHANGED

= 1.1.8 =
* Multisite undefined function wpmu_delete_user() issue - FIXED

= 1.1.7 =
* WP 5.5 requires API permissions callback param - ADDED

= 1.1.6 =
* CloudFlare can cause our server validation methods to fail resulting in licenses not being added - FIXED
* Stored keys will be cleared when deactivating 'One click addon installs' - CHANGED

= 1.1.5 =
* WPML dynamic URL change can disconnect a site - FIXED
* Warning added if another plugin may be calling the get_plugins() function too early in which case we can install a must use plugin to try and resolve - ADDED

= 1.1.4 =
* License sync now checks if site_id and site_url are correct and will work before syncing - CHANGED
* Detect and disconnect if site_url changes and invalidates licences - ADDED

= 1.1.3 =
* Support user on network not able to access all plugin settings - FIXED
* Max API timeout changed from 10 to 20 seconds - CHANGED
* When the website URL changes a notice will show asking to re-connect the new URL - ADDED

= 1.1.2 =
* WP_DEBUG being active can affect initial connection - FIXED

= 1.1.1 =
* If support user not set PHP Notice can show if debugging enabled - FIXED
* Remove support user if plugin deactivated - ADDED
* Remove support user immediately if site disconnected - CHANGED

= 1.1.0 =
* Support widget and live documentation search now available - ADDED
* Temporary Support User Access feature now available - ADDED

= 1.0.6 =
* Extensions screen can still request key if no addons installed on first sync - FIXED
* Small spelling mistakes - FIXED

= 1.0.5 =
* If switching a connected user account the license keys are not immediately updated - FIXED
* Deactivate and remove all licence keys when disconnecting a site - CHANGED

= 1.0.4 =
* Added connected notice when activating from product extensions page - ADDED
* Changes added to be able to detect if activating site is a network site - ADDED

= 1.0.3 =
* Added settings link to plugins page - ADDED

= 1.0.2 =
* First wp.org release - WOOHOO

= 1.0.1 =
* Warning added that localhost sites won't work - ADDED

= 1.0.0 =
* First release - YAY


= Resources used to build this plugin =

* Image for Demo Import Screen ( kadencewp-icon-dark.svg ), Copyright Kadence WP
  License: CC0 Public Domain
  Source: https://www.kadencewp.com/brand-assets/

* Image for Demo Import Screen ( blockstrap-logo.jpg ), Copyright AyeCode Ltd
  License: CC0 Public Domain
  Source: https://ayecode.io/

* Image for Connection Screen ( connect-site.png ), Copyright AyeCode Ltd
  License: CC0 Public Domain
  Source: https://ayecode.io/

* Image for Plugins screens ( ayeccode.png ), Copyright AyeCode Ltd
  License: CC0 Public Domain
  Source: https://ayecode.io/

* Image for Plugins screens ( ayeccode.svg ), Copyright AyeCode Ltd
  License: CC0 Public Domain
  Source: https://ayecode.io/