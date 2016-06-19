=== Taxonomy Switcher ===
Contributors: webdevstudios, sc0ttkclark, jtsternberg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3084056
Tags: taxonomy, taxonomies, term, terms, category, categories, convert, converter, tag, tags, custom taxonomy, custom taxonomies, switch taxonomies
Requires at least: 3.5
Tested up to: 4.5.2
Stable tag: 1.0.2
License: GNU AGPLv3
License URI: http://www.gnu.org/licenses/agpl-3.0.html

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

= 1.0.2 =
* Update for xss vulnerability, https://make.wordpress.org/plugins/2015/04/20/fixing-add_query_arg-and-remove_query_arg-usage

= 1.0.1 =
* Add ability to switch comma-separated list of term IDs.

= 1.0.0 =
* Release

== Upgrade Notice ==

= 1.0.1 =
* Add ability to switch comma-separated list of term IDs.

= 1.0.0 =
* Release
