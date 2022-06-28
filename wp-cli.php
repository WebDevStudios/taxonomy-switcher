<?php
/**
 * Implements Taxonomy Switcher command for WP-CLI.
 */
class Taxonomy_Switcher_Command extends WP_CLI_Command {

	/**
	 * Taxonomy_Switcher_Command constructor.
	 */
	public function __construct() {}

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
	 * [--terms=<terms>]
	 * : Comma separated list of term ids to switch.
	 *
	 * ## EXAMPLES
	 *
	 *     wp taxonomy-switcher convert --from=category --to=post_tag
	 *     wp taxonomy-switcher convert --from=category --to=post_tag --parent=123
	 *     wp taxonomy-switcher convert --from=category --to=post_tag --terms=1,2,13
	 *
	 * @synopsis --from=<taxonomy> --to=<taxonomy> [--parent=<parent>] [--terms=<terms>]
	 * @param array $args Args.
	 * @param array $assoc_args Args.
	 */
	public function convert( $args, $assoc_args ) {

		$args = $this->map_arg_names( $assoc_args );
		$tax_switcher = new Taxonomy_Switcher( $this->map_arg_names( $assoc_args ) );

		$count = $tax_switcher->count();

		if ( ! $count ) {
			WP_CLI::error( $tax_switcher->notices( 'no_terms' ) );
		}

		WP_CLI::log( $tax_switcher->notices( 'switching' ) );

		if ( 0 < $tax_switcher->parent ) {
			WP_CLI::log( $tax_switcher->notices( 'limit_by_parent' ) );
		}

		if ( ! empty( $tax_switcher->terms ) ) {
			WP_CLI::log( $tax_switcher->notices( 'limit_by_terms' ) );
		}

		set_time_limit( 0 );

		$tax_switcher->convert();

		WP_CLI::success( $tax_switcher->notices( 'switched' ) );

	}

	/**
	 * Map args to a new array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Array of args to map.
	 * @return array
	 */
	protected function map_arg_names( $args ) {
		$tomap = [
			'to' => 'to_tax',
			'from' => 'from_tax',
		];
		$newargs = [];
		foreach ( $args as $key => $value ) {
			if ( array_key_exists( $key, $tomap ) ) {
		 		$newargs[ $tomap[ $key ] ] = $value;
			} else {
				$newargs[ $key ] = $value;
			}
		}
		return $newargs;
	}
}

WP_CLI::add_command( 'taxonomy-switcher', 'Taxonomy_Switcher_Command' );
