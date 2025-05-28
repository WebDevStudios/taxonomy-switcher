<?php
/*
 * Plugin Name: Taxonomy Switcher
 * Plugin URI: https://github.com/WebDevStudios/taxonomy-switcher
 * Description: Switch terms from one taxonomy to another
 * Version: 1.0.8
 * Author: WebDevStudios
 * Author URI: http://webdevstudios.com
 */

/**
 * Class Taxonomy_Switcher_Init
 */
class Taxonomy_Switcher_Init {

	/**
	 * @var Taxonomy_Switcher_UI
	 */
	private Taxonomy_Switcher_UI $ui;

	/**
	 * @var mixed
	 */
	private $notices;

	/**
	 * Setup the object.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( dirname( __FILE__ ) . '/Taxonomy_Switcher.php' );
			require_once( dirname( __FILE__ ) . '/wp-cli.php' );
		} else {
			require_once( dirname( __FILE__ ) . '/Taxonomy_Switcher_UI.php' );
			$this->ui = new Taxonomy_Switcher_UI();
			$this->ui->hooks();
		}

		add_action( 'admin_init', [ $this, 'taxonomy_switcher_init' ] );

		$this->notices = get_option( 'taxonomy-switcher-notices' );
		if ( $this->notices ) {
			add_action( 'all_admin_notices', [ $this, 'do_admin_notice' ] );
		}

	}

	/**
	 * Include Taxonomy_Switcher if being run.
	 *
	 * @since 1.0.0
	 */
	public function taxonomy_switcher_init() {

		if (
			! isset( $_GET[ 'taxonomy_switcher' ] )
			|| 1 != $_GET[ 'taxonomy_switcher' ]
			|| ! current_user_can( 'manage_options' )
			|| ! isset( $_GET[ 'from_tax' ] )
			|| empty( $_GET[ 'from_tax' ] )
			|| ! isset( $_GET[ 'to_tax' ] )
			|| empty( $_GET[ 'to_tax' ] )
		) {
			return;
		}

		require_once( dirname( __FILE__ ) . '/Taxonomy_Switcher.php' );

		$taxonomy_switcher = new Taxonomy_Switcher( $_GET );

		$success_notices = $taxonomy_switcher->admin_convert();

		if ( empty( $success_notices ) ) {
			return;
		}

		add_option( 'taxonomy-switcher-notices', $success_notices, null, 'no' );
		wp_redirect( esc_url_raw( add_query_arg( 'page', $this->ui->admin_slug, admin_url( '/tools.php' ) ) ) );

	}

	/**
	 * Show Notices for taxonomy switch.
	 *
	 * @since 1.0.0
	 */
	public function do_admin_notice() {
		echo '<div id="message" class="updated"><p>'. implode( '</p><p>', $this->notices ) .'</p></div>';
		delete_option( 'taxonomy-switcher-notices' );
	}
}
$Taxonomy_Switcher_Init = new Taxonomy_Switcher_Init();
