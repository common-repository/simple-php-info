=== Simple PHP Info ===
Contributors: joshmckibbin
Donate link: https://joshmckibbin.com/donate
Tags: php, phpinfo, debug
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Displays the phpinfo() table in the WordPress dashboard and creates a shortcode for use in posts and pages.

== Description ==

The [Simple PHP Info](https://kibb.in/sphp) plugin allows you to view the phpinfo table in a dashboard widget. It also includes a shortcode that allows you to insert the phpinfo table into any post or page.

=== Simple PHP Info Settings ===

* **Enable the Dashboard Widget**: Whether or not to show the dashboard widget (Defaults to Yes)
* **Enable the Shortcode**: Whether or not to enable the shortcode (Default to Yes)

== Frequently Asked Questions ==

= How do I use the shortcode? =

Simply type `[phpinfo]` in the body of a shortcode field when creating a post or page. You can also set it to output without styles: `[phpinfo output=table-nocss]`

== Screenshots ==

1. Simple PHP Info Widget

== Upgrade Notice ==
none

== Changelog ==

= 1.0.4 =
* Compatibility with WordPress 6.3
* Converted LESS to SASS and recompiled CSS

= 1.0.3 =
* Compatibility with WordPress 6.1.1

= 1.0.2 =
* Added additional escaping
* Removed array output option from shortcode

= 1.0.1 =
* Added escaping
* Fixed text-domain declarations

= 1.0.0 =
* Initial commit
