Taxonomy Switcher
======================

Switch the taxonomy for terms by a specific parent of another taxonomy.

## Instructions

1. **Backup!**
2. Activate the plugin and browse to yourdomain.com/wp-admin/options-general.php?page=taxonomy-switcher
3. Select your "From", and "To" Taxonomies.
4. Optionally select a parent term to limit terms to switch. Typing term names will do a live search of terms with the name you are typing AND possess child terms.

## Notes

If parent isn't set, it will default to zero, which will migrate *all* terms for that taxonomy to the new taxonomy.

Compatible with [wp-cli](http://wp-cli.org/). `wp taxonomy-switcher` for instructions.