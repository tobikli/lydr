=== Hostinger ===
Tags: onboarding, hostinger
Requires at least: 5.5
Version: 2.0.16
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 2.0.16
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

== Description ==

Hostinger's Easy Onboarding Plugin transforms the way you [launch your WordPress site](https://www.hostinger.com/tutorials/launch-a-wordpress-site). Crafted for an effortless user experience, it guides you through the essential steps of website setup and personalization. Whether you're adjusting settings or exploring new tools, a single click is all it takes to guide you to the right place.

Dive into a hassle-free WordPress setup by installing our plugin. Here's what our plugin guides you to get started:

# **Upload Site Logo**
Elevate your brand identity and online presence with ease using our plugin. It guides you through the process of uploading your site's logo, reinforcing your brand's visual impact. Plus, gain instant access to our innovative  [AI Logo Maker](https://logo.hostinger.com) with just a click, simplifying the creation of a unique and professional logo tailored to your brand.

# **Editing Post Description**
Learn to navigate the post editor seamlessly. Our plugin helps you to open the WordPress post dashboard in a single click, letting you start editing your post description and creating captivating content.

# **Uploading Images**
Discover the simplicity of adding and managing images. Our step-by-step guide leads you to the [Media Library](https://www.hostinger.com/tutorials/wordpress-media-library-tutorial), making it easy to incorporate visuals that break up text and illustrate your points, thereby enhancing the reader's experience.

# **Editing Site Title**
Attract visitors with a compelling site title. Our tool simplifies the process of finding and using the Customizer page, enabling you to craft a title that truly represents your website's identity and purpose.

# **Adding a New Page**
Add vital static pages to your site, like About, Contact, or Services, with our user-friendly guide. Our tool breaks down the process into manageable steps, helping you enrich your site with essential information.

# **Connecting Your Domain**
Link your domain name effortlessly. Our plugin offers a straightforward guide, complete with video tutorials, to connect your domain to the correct nameservers, making what seems technical a breeze.

# **Previewing Your Website**
Check how your website looks on a temporary subdomain with just a click. This feature is especially useful for making sure everything is perfect before your site goes live.

# **Easy Access to Learning Resources**
[Hostinger Academy's](https://www.youtube.com/@HostingerAcademy) WordPress video tutorials are now at your fingertips, right within your dashboard in our onboarding plugin. Learn and set up your site with ease without ever leaving the dashboard.

# **Want to Accelerate Your Content Creation?**
Unlock the power of AI for your content with the WordPress AI Assistant plugin, an exclusive extension plugin for [Hostinger's managed WordPress hosting](https://www.hostinger.com/wordpress-hosting) customers. Just provide a prompt and let the AI generate blog posts and custom post type content in seconds.

Upgrade to our managed WordPress hosting plan and embrace the future of website building with AI at your side.

[youtube https://www.youtube.com/watch?v=-94Fjj6GsIE]

== Frequently Asked Questions ==

= How Do I Install the Plugin =

If you set up a WordPress site on Hostinger's managed WordPress hosting account, this plugin will be automatically installed.

= Is There a Cost Associated With Using the Hostinger Onboarding Plugin? =

The Hostinger Onboarding Plugin is available for free. However, the AI Assistant features for content creation are distributed with a different plugin exclusive to our Business and Cloud hosting plans customers.

= Are There Any Specific Hosting Requirements for This Plugin? =

The Hostinger Onboarding Plugin is designed to work seamlessly on any hosting platform, ensuring wide compatibility and ease of use. However, the plugin requires these minimum requirements:
* PHP 7.4 or greater
* MySQL 5.6 or greater

= Can I Access Educational Resources Outside This Plugin? =

Yes, the educational resources you can find in this plugin are available on our [Hostinger Academy YouTube channel](https://www.youtube.com/@HostingerAcademy) and [Hostinger Tutorials website](https://www.hostinger.com/tutorials/wordpress).

== Installation ==

# **Minimum Requirements**

* PHP 7.4 or greater is recommended
* MySQL 5.6 or greater is recommended

# **Automatic installation**

If you set up a WordPress site on Hostinger's managed WordPress hosting account, this plugin will be automatically installed.

Alternatively, you can use the automatic installation from the dashboard, and WordPress will handle the file transfer - you won't need to leave your web browser. To do an automatic install of the Hostinger plugin, log in to your WordPress dashboard, navigate to the Plugins menu, and click **Add New**.

In the search field, type **Hostinger**, then click **Search Plugins**. Once you find us, you can view details about it, such as the point release, rating, and description. Most importantly, of course, you can install it by clicking **Install Now**, and WordPress will take it from there.

# **Manual installation**

The manual installation method requires downloading the Hostinger plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains instructions on how to do this here.

# **Updating**

Automatic updates should work smoothly, but we still recommend you back up your site.

# **Development**

Here's how to use the Hostinger Easy Onboarding Plugin's Asset Builder to compile your assets:

Installation: Start by navigating to your project's root directory in your command line interface. Run npm install to install all the necessary project dependencies.

Configuration: Make sure your project's "scripts" section in your package.json file includes the necessary commands for asset compilation. As shown in your example, these commands define how assets are processed during development and production phases.

Development Mode: Execute "npm run dev" or "npm run development" to initiate the development environment. This command will use Node.js and Webpack to build your assets while displaying progress.

Production Mode: When you're ready to launch your project, run "npm run prod" or "npm run production" to build assets optimized for production, again utilizing Node.js and Webpack.

Watching for Changes: During development, you can use "npm run watch" to continuously monitor your assets and rebuild them when changes are detected. This command is invaluable for real-time asset management.


== Changelog ==

2.0.16 (2024-07-11)
- Fixed cached options issue

2.0.15 (2024-07-10)
- Added product events limiter

2.0.14 (2024-07-08)
- Added different edit URL for shop page
- Added hooks to prevent hijacking of onboarding redirect (Monster insights, Beaver builder)
- Added update URL for canary servers

2.0.13 (2024-07-04)
- Refactored Woocommerce Onboarding events
- Added hooks to prevent hijacking of onboarding redirect (Stripe checkout, Starter templates)
- Added return to Woocommerce Onboarding CTA

2.0.12 (2024-07-03)
- Woocommerce Onboarding Fixes

2.0.11 (2024-07-01)
- Added preview event

2.0.10 (2024-07-01)
- Initial settings for hiding Gutenberg panels

2.0.9 (2024-06-27)
- Woocommerce Onboarding Adjustments

2.0.8 (2024-06-26)
- Improved detection of Woo onboarding steps

2.0.7 (2024-06-21)
- Design fixes, added missing amplitude event

2.0.6 (2024-06-20)
- Added partnership

2.0.5 (2024-06-20)
- Fix omnisend step bug and amplitude event logic fix

2.0.4 (2024-06-19)
- Disable Spectra activation redirect

2.0.3 (2024-06-19)
- Fix amplitude events for website setup steps

2.0.2 (2024-06-13)
- Fix woocommerce onboarding plugin activation

2.0.1 (2024-06-12)
- Hide 3rd party plugin metaboxes and notices

2.0.0 (2024-06-05)
- Add WooCommerce onboarding

1.1.11 (2024-06-04)
- Fixed Amazon affiliate step CTA destination

1.1.10 (2024-05-21)
- Added translations

1.1.9 (2024-05-21)
- Styling fix

1.1.8 (2024-05-17)
- Hide notices in all hostinger pages

1.1.7 (2024-05-17)
- Add asset compatibility with older Hostinger plugins

1.1.6 (2024-05-16)
- Add low PHP version notice

1.1.5 (2024-05-15)
- Hide Notices

1.1.4 (2024-05-14)
- Hide MonsterInsights metabox

1.1.3 (2024-05-13)
- Disable composer platform check
- Disable hiding of elementor in astra templates

1.1.1 (2024-05-10)
- Added Amplitude

1.1.0 (2024-05-07)
- Added surveys

1.0.0 (2024-04-17)
- First release
