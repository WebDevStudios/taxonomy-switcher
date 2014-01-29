<?php
/**
 * Implements Taxonomy Switcher command for WP-CLI
 *
 */
class Taxonomy_Switcher_Command extends WP_CLI_Command {

	public function __construct() {

	}

    /**
     * Switch terms from one taxonomy to another.
     *
     * ## OPTIONS
     *
     * --from=<taxonomy>
     * : The Taxonomy to switch from.
     *
     * --to=<taxonomy>
     * : The Taxonomy to switch to.
     *
     * [--parent=<parent>]
     * : The term parent to limit by.
     *
     * ## EXAMPLES
     *
     *     wp taxonomy-switcher convert --from=category --to=region
     *     wp taxonomy-switcher convert --from=category --to=region --parent=123
     *
     * @synopsis --from=<taxonomy> --to=<taxonomy> [--parent=<parent>]
     */
	public function convert( $args, $assoc_args ) {

		$from = $assoc_args[ 'from' ];
		$to = $assoc_args[ 'to' ];

		$parent = 0;

		if ( isset( $assoc_args[ 'parent' ] ) ) {
			$parent = absint( $assoc_args[ 'parent' ] );
		}

		$taxonomy_switcher = new Taxonomy_Switcher( $from, $to, $parent );

		$count = $taxonomy_switcher->count();

		WP_CLI::log( sprintf( 'Switching %d terms with the taxonomy \'%s\' to the taxonomy \'%s\'', $count, $from, $to ) );

		if ( 0 < $parent ) {
			WP_CLI::log( sprintf( 'Limiting the switch by the parent term_id of %d', $parent ) );
		}

		set_time_limit( 0 );

		$taxonomy_switcher->convert();

		WP_CLI::log( 'Taxonomies switched!' );

	}

}

WP_CLI::add_command( 'taxonomy-switcher', 'Taxonomy_Switcher_Command' );