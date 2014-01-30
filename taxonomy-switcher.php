<?php
/*
Plugin Name: Taxonomy Switcher
Plugin URI: https://github.com/WebDevStudios/taxonomy-switcher
Description: Switches the Taxonomy of terms to a different Taxonomy
Version: 1.0.0
Author: WebDevStudios
Author URI: http://webdevstudios.com
*/

/**
 * Class Taxonomy_Switcher_Init
 */
class Taxonomy_Switcher_Init {

	/**
	 * Setup the object
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// WP-CLI integration
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( dirname( __FILE__ ) . '/Taxonomy_Switcher.php' );
			require_once( dirname( __FILE__ ) . '/wp-cli.php' );
		} else {
			// No WP-CLI? Ok, let's add our UI
			require_once( dirname( __FILE__ ) . '/Taxonomy_Switcher_UI.php' );
			$this->ui = new Taxonomy_Switcher_UI();
			$this->ui->hooks();
		}

		add_action( 'admin_init', array( $this, 'taxonomy_switcher_init' ) );

		if ( $this->notices = get_option( 'taxonomy-switcher-notices' ) ) {
			add_action( 'all_admin_notices', array( $this, 'do_admin_notice' ) );
		}

	}

	/**
	 * Include Taxonomy_Switcher if being run
	 *
	 * @since 1.0.0
	 */
	public function taxonomy_switcher_init() {
		if ( isset( $_GET[ 'taxonomy_switcher' ] ) && 1 == $_GET[ 'taxonomy_switcher' ] && current_user_can( 'install_plugins' ) ) {
			if ( isset( $_GET[ 'from_tax' ] ) && !empty( $_GET[ 'from_tax' ] ) && isset( $_GET[ 'to_tax' ] ) && !empty( $_GET[ 'to_tax' ] ) ) {

				require_once( dirname( __FILE__ ) . '/Taxonomy_Switcher.php' );

				$from = sanitize_text_field( $_GET[ 'from_tax' ] );
				$to = sanitize_text_field( $_GET[ 'to_tax' ] );
				$parent = 0;

				if ( isset( $_GET[ 'parent' ] ) && !empty( $_GET[ 'parent' ] ) ) {
					$parent = absint( $_GET[ 'parent' ] );
				}

				$taxonomy_switcher = new Taxonomy_Switcher( $from, $to, $parent );

				$success_notices = $taxonomy_switcher->admin_convert();

				if ( ! empty( $success_notices ) ) {
					// Save notices
					update_option( 'taxonomy-switcher-notices', $success_notices );
					// Redirect and strip query string
					wp_redirect( add_query_arg( 'page', $this->ui->admin_slug, admin_url( '/options-general.php' ) ) );
				}
			}
		}

	}

	/**
	 * Show Notices for taxonomy switch
	 *
	 * @since 1.0.0
	 */
	public function do_admin_notice() {
		echo '<div id="message" class="updated"><p>'. implode( '</p><p>', $this->notices ) .'</p></div>';
		delete_option( 'taxonomy-switcher-notices' );
	}

}

$Taxonomy_Switcher_Init = new Taxonomy_Switcher_Init();
