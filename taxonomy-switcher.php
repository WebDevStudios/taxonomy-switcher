<?php
/*
Plugin Name: Taxonomy Switcher
Description: Switches the Taxonomy of terms to a different Taxonomy
Version: 1.0.0
Author: WebDevStudios
Author URI: http://webdevstudios.com
*/

// WP-CLI integration
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/Taxonomy_Switcher.php' );
	require_once( __DIR__ . '/wp-cli.php' );
}

/**
 * Include Taxonomy_Switcher if being run
 */
function taxonomy_switcher_init() {

	if ( isset( $_GET[ 'taxonomy_switcher' ] ) && 1 == $_GET[ 'taxonomy_switcher' ] && current_user_can( 'install_plugins' ) ) {
		if ( isset( $_GET[ 'from_tax' ] ) && !empty( $_GET[ 'from_tax' ] ) && isset( $_GET[ 'to_tax' ] ) && !empty( $_GET[ 'to_tax' ] ) ) {
			require_once( __DIR__ . '/Taxonomy_Switcher.php' );

			$from = absint( $_GET[ 'from_tax' ] );
			$to = absint( $_GET[ 'to_tax' ] );
			$parent = 0;

			if ( isset( $_GET[ 'parent' ] ) && !empty( $_GET[ 'parent' ] ) ) {
				$parent = absint( $_GET[ 'parent' ] );
			}

			$taxonomy_switcher = new Taxonomy_Switcher( $from, $to, $parent );

			$taxonomy_switcher->admin_convert();
		}
	}

}
add_action( 'admin_init', 'taxonomy_switcher_init' );