Taxonomy Switcher
======================

Switch the taxonomy for terms by a specific parent of another taxonomy.

## Instructions

1. **Backup!**
2. Activate the plugin and browse to yourdomain.com/wp-admin/?taxonomy_switch=1&from_tax={old_taxonomy_name}&to_tax={new_taxonomy_name}&parent=123

## Notes

If parent isn't set, it will default to zero, which will migrate *all* terms for that taxonomy to the new taxonomy.

Compatible with [wp-cli](http://wp-cli.org/).