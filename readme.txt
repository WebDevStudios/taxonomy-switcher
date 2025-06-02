=== Taxonomy Switcher ===
Contributors: webdevstudios, pluginize
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3084056
Tags: taxonomy, term, category, tag, switch
Requires at least: 5.2
Tested up to: 6.8.1
Stable tag: 1.0.8
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html
Requires PHP: 7.4

Switch the taxonomy for all terms or only child terms of a specified parent term.

== Description ==

This plugin allows you to select your "From", and "To" Taxonomies, to convert all terms. Optionally select a parent term to limit terms to switch. Typing term names will do a live search of terms with the name you are typing AND possess child terms.

Plugin also has built-in support for [WP-CLI](http://wp-cli.org/). In the command line, type in `wp taxonomy-switcher` for instructions.

[Plugin is on GitHub](https://github.com/WebDevStudios/taxonomy-switcher). Pull Requests and Forks welcome.

### Notes

Please keep in mind, if parent isn't set, or you don't specify a comma-separated list of term ids to migrate, it will migrate *all* terms for that taxonomy to the new taxonomy.

[Pluginize](https://pluginize.com/?utm_source=taxonomy-switcher&utm_medium=text&utm_campaign=wporg) was launched in 2016 by [WebDevStudios](https://webdevstudios.com/) to promote, support, and house all of their [WordPress products](https://pluginize.com/shop/?utm_source=taxonomy-switcher&utm_medium=text&utm_campaign=wporg). Pluginize is not only creating new products for WordPress all the time, but also provides [ongoing support and development for WordPress community favorites like CPTUI](https://wordpress.org/plugins/custom-post-type-ui/), [CMB2](https://wordpress.org/plugins/cmb2/), and more.

== Installation ==

1. **Backup!**
2. Upload 'taxonomy-switcher' to the '/wp-content/plugins/' directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to the 'Taxonomy Switcher' admin page. You'll find the menu item under the 'Tools' menu item on the left.
5. Select your "From", and "To" Taxonomies.
6. **a)** Optionally select a parent term to limit terms to switch. Typing term names will do a live search of terms with the name you are typing AND possess child terms. **OR** **b)** Add a comma-separated list of term ids to switch.
7. Switch them!

== Frequently Asked Questions ==


== Screenshots ==

1. Admin page
2. Live-searching for a parent term

== Changelog ==

= 1.0.8 =
* Updated: Confirmed WP 6.8 compatibility.
* Updated: Misc little code cleanups that should not affect anyone.

= 1.0.7 =
* Updated: Confirmed WP 6.5 compatibility.

= 1.0.6 =
* Updated: Confirmed WP 6.4 compatibility.
* Updated: Moved capability back to manage_options to sync with options page.
* Updated: Clear caches after conversion.
* Fixed: PHP8 deprecation notices.

= 1.0.5 =
* Updated: Confirmed WP 6.2.1 compatibility.

= 1.0.4 =
* Updated: changed required capability to manage_categories

= 1.0.3 =
* Confirmed compatibility with WordPress 5.4.0

= 1.0.2 =
* Update for xss vulnerability, https://make.wordpress.org/plugins/2015/04/20/fixing-add_query_arg-and-remove_query_arg-usage

= 1.0.1 =
* Add ability to switch comma-separated list of term IDs.

= 1.0.0 =
* Release

== Upgrade Notice ==

= 1.0.5 =
* Updated: Confirmed WP 6.2.1 compatibility.
