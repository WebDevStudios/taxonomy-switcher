<?php
/**
 * Class Taxonomy_Switcher
 */
class Taxonomy_Switcher {

	/**
	 * Taxonomy to switch from
	 *
	 * @var string
	 */
	public $from = '';

	/**
	 * Taxonomy to switch to
	 *
	 * @var string
	 */
	public $to = '';

	/**
	 * Parent term_id to limit by
	 *
	 * @var int
	 */
	public $parent = 0;

	/**
	 * Array of Term IDs to convert
	 *
	 * @var array
	 */
	public $term_ids = array();

	/**
	 * Setup the object
	 *
	 * @param null|int $from Taxonomy to switch from
	 * @param null|int $to Taxonomy to switch to
	 * @param int $parent Parent term_id to limit by
	 *
	 * @since 1.0.0
	 */
	public function __construct( $from = null, $to = null, $parent = 0 ) {

		if ( null !== $from && null !== $to ) {
			$this->from = absint( $from );
			$this->to = absint( $to );

			if ( !empty( $parent ) ) {
				$this->parent = absint( $parent );
			}
		}

	}

	/**
	 * Convert taxonomy of terms from the Admin
	 *
	 * @since 1.0.0
	 */
	public function admin_convert() {

		$count = $this->count();

		echo sprintf( 'Switching %d terms with the taxonomy \'%s\' to the taxonomy \'%s\'', $count, $this->from, $this->to );

		if ( 0 < $this->parent ) {
			echo sprintf( 'Limiting the switch by the parent term_id of %d', $this->parent );
		}

		set_time_limit( 0 );

		$this->convert();

		echo 'Taxonomies switched!';

		die();

	}

	/**
	 * Get term ids based on $from and $parent
	 *
	 * @return array An array of term ids
	 * @since 1.0.0
	 */
	public function get_term_ids() {

		$args = array(
			'hide_empty' => false,
			'fields' => 'ids',
			'child_of' => $this->parent
		);

		$args = apply_filters( 'taxonomy_switcher_get_terms_args', $args, $this->from, $this->to, $this->parent );

		$terms = get_terms( $this->from, $args );

		$this->term_ids = array();

		if ( !is_wp_error( $terms ) && !empty( $terms ) ) {
			$this->term_ids = $terms;
		}

		return $this->term_ids;

	}

	/**
	 * Return the total count of terms found
	 *
	 * @return int Total count of terms found
	 * @since 1.0.0
	 */
	public function count() {

		if ( empty( $this->term_ids ) ) {
			$this->get_term_ids();
		}

		return count( $this->term_ids );

	}

	/**
	 * Convert taxonomy of terms
	 *
	 * @return bool Whether the conversion was successful
	 * @since 1.0.0
	 */
	public function convert() {

		if ( empty( $this->term_ids ) ) {
			$this->get_term_ids();
		}

		if ( empty( $this->term_ids ) ) {
			return false;
		}

		global $wpdb;

		$term_ids = array_map( 'absint', $this->term_ids );
		$term_ids = implode( ', ', $term_ids );

		$wpdb->query( $wpdb->prepare( "
			UPDATE `{$wpdb->term_taxonomy}`
			SET `taxonomy` = %s
			WHERE `taxonomy` = %s AND `term_id` IN ( {$term_ids} )
		", $this->to, $this->from ) );

		if ( 0 < $this->parent ) {
			$wpdb->query( $wpdb->prepare( "
				UPDATE `{$wpdb->term_taxonomy}`
				SET `parent` = 0
				WHERE `parent` = %d AND `term_id` IN ( {$term_ids} )
			", $this->parent ) );
		}

		return true;

	}

}