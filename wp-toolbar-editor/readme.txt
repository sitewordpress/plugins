=== WordPress Toolbar Editor ===
Contributors: whiteshadow
Tags: admin, toolbar, menu, security, wpmu
Requires at least: 3.5
Tested up to: 5.3
Stable tag: 1.3.3

Lets you edit the WordPress Toolbar (a.k.a. Admin Bar).

== Description ==

This add-on lets you edit the WordPress Toolbar (a.k.a. the Admin Bar). Requires _Admin Menu Editor Pro_. 

*Credits*
Uses some icons from the ["Silk" set by Mark James](http://www.famfamfam.com/lab/icons/silk/).

== Installation ==

1. Install and activate Admin Menu Editor Pro if you haven't done it already.
2. Upload and activate the add-on like a normal WordPress plugin.
3. Go to "Settings -> Toolbar Editor" and start customizing the Toolbar. 
4. (Optional) Click the small "Settings" button on the "Toolbar Editor" page to change access permissions and other add-on settings.

== Changelog ==

= 1.3.3 =
* Fixed a warning about get_magic_quotes_gpc() being deprecated in PHP 7.4.

= 1.3.2 =
* Fixed a bug where certain Toolbar items like "Edit with Elementor" could end up in the wrong place after activating the plugin.

= 1.3.1 =
* Fixed some layout issues in RTL environments.
* Changed the position of new and unrecognized Toolbar nodes. Now the add-on will attempt to keep them near their initial location instead of moving them to the end of the Toolbar. 
* Tested up to WP 5.3.

= 1.3 =

* The "new menu visibility" settings from the menu editor now also apply to the Toolbar Editor. You can use this to automatically hide new toolbar items.
* The add-on will now automatically re-select the previously selected role or user after saving toolbar settings.
* Fixed the PHP notice "screen_icon is deprecated since version 3.8.0".
* Tested up to WP 5.0.3.

= 1.2.2 =
* Added the current user and selected users from AME to the role list.
* Fixed a rare issue where the plugin would throw an error if toolbar item title was not a string.

= 1.2.1 =
* Added basic WPML support.

= 1.2 =
* Added a white background to the toolbar item list on the settings page.

= 1.1 =
* Added a way to hide items from specific roles.
* You can now use shortcodes in the "Title", "URL" and "HTML content" fields.
* Tested with WordPress 3.9 and an early alpha version of 4.0.

= 1.0 =
* Initial release.