Taxonomy Switcher
======================

Switch the taxonomy for terms by a specific parent of another taxonomy.

## Instructions

1. **Backup!**
2. Activate the plugin and browse to yourdomain.com/wp-admin/tools.php?page=taxonomy-switcher
3. Select your "From", and "To" Taxonomies.

**Optional**  

1. Select a parent term to limit terms to switch. Typing term names will do a live search of terms with the name you are typing AND possess child terms.
2. OR add a comma-separated list of term ids to switch.

## Notes

If parent isn't set, or you don't specify a comma-separated list of term ids to migrate, it will migrate *all* terms for that taxonomy to the new taxonomy.

Compatible with [wp-cli](http://wp-cli.org/). `wp taxonomy-switcher` for instructions.

## Changelog

#### 1.0.3
* Compatibility confirmation for WordPress 5.4

#### 1.0.2
* Update for xss vulnerability, https://make.wordpress.org/plugins/2015/04/20/fixing-add_query_arg-and-remove_query_arg-usage

#### 1.0.1
* Add ability to switch comma-separated list of term IDs.

#### 1.0.0
* Release
