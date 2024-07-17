=== UsersWP - ReCaptcha ===
Contributors: stiofansisland, paoltaia, ayecode
Donate link: http://userswp.io/
Tags: recaptcha, frontend, login, register, account, users, restrict, userswp
Requires at least: 4.9
Tested up to: 6.4
Stable tag: 1.3.19
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

ReCaptcha addon for UsersWP.

== Description ==

ReCaptcha addon for [UsersWP](https://wordpress.org/plugins/userswp/).

The UsersWP ReCaptcha plugin allows you to implement a super security captcha into forms like
registration, login forms. UsersWP Re-Captcha protects your site against spam, malicious registrations
and other forms of attacks where computers try to disguise themselves as a human.

UsersWP Re-Captcha provides some of the smartest anti-spam security by protecting your site against spam and
malicious registrations available today for WordPress.

[Learn how reCAPTCHA works](https://www.google.com/recaptcha/)

== Requirements ==

* You need the Google reCAPTCHA keys from [here](https://www.google.com/recaptcha/admin#whyrecaptcha).

== Installation ==

= Minimum Requirements =

= Automatic installation =

Automatic installation is the easiest option. To do an automatic install of UsersWP - ReCaptcha, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.
Search for "UsersWP ReCaptcha" and click Install.

= Manual installation =

The manual installation method involves downloading UsersWP and uploading it to your webserver via your favourite FTP application. The WordPress codex will tell you more [here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should seamlessly work. We always suggest you backup up your website before performing any automated update to avoid unforeseen problems.

== Frequently Asked Questions ==

No questions so far, but don't hesitate to ask!

== Screenshots ==

1. UWP Login
2. WP Login

== Changelog ==

= 1.3.19 =
* Tested up to WP version 6.4 - CHANGED

= 1.3.18 =
* Update readme file for WordPress version 6.2

= 1.3.17 =
* BS5 compatibility changes - CHANGED

= 1.3.16 =
* Changes regarding bots faking the action name could sometimes bypass ReCaptcha v3 - FIXED

= 1.3.15 =
* Warning showing due to ReCaptcha V3 changes - FIXED

= 1.3.14 =
* Bots faking the action name could sometimes bypass ReCaptcha v3 - FIXED

= 1.3.13 =
* Update readme file for WordPress version 6.1

= 1.3.12 =
* Add options into db-language.php for translation compatibility - FIXED

= 1.3.11 =
* Update readme file for WordPress version 5.9

= 1.3.10 =
* Update readme file for WordPress version 5.8

= 1.3.9 =
* Recaptcha V3 giving error for auto approve and auto login - FIXED

= 1.3.8 =
* Update readme file for WordPress version 5.7

= 1.3.7 =
* Update readme file for WordPress version 5.6

= 1.3.6 =
* Loading google api.js from recaptcha.net domain to use recaptcha globally - ADDED

= 1.3.5 =
* Update readme file for tested up to version 5.4

= 1.3.4 =
* ReCaptcha V3 issue regarding loading API key - FIXED

= 1.3.3 =
* ReCaptcha add on runs before UsersWP on some sites causing issue - FIXED

= 1.3.2 =
* Settings reset to default all the time we activate plugin - FIXED
* First wp.org release - YAY

= 1.2.0 =
* Compatibility with new UWP core style updates - ADDED
* Changes for new login via lightbox - ADDED
* Icons changed to Font Awesome - ADDED

= 1.0.5 =
* reCaptcha not working on login widget on pages other than UWP pages - FIXED
* Removed reCaptcha library - CHANGED
* reCaptcha V3 support - ADDED

= 1.0.4 =
* Integrate new settings interface - CHANGED
* Recaptcha size compact for mobile compatibility - ADDED
* Allow recaptcha for default WordPress admin login and registration forms - ADDED
* Load JS on UsersWP and admin login and registration forms only - CHANGED

= 1.0.3 =
* Uninstall functionality - ADDED

= 1.0.2 =
Multiple recaptcha on same page breaks form - FIXED

= 1.0.1 =
Textdomain not being loaded correctly - FIXED

= 1.0.1 =
* Renamed some files - CHANGED
* Class names renamed from Users_WP to UsersWP for better naming and consistency - CHANGED
* Major code refactoring - CHANGED
* Docblocks added - ADDED

= 1.0.0 =
* First release.

== Upgrade Notice ==

TBA