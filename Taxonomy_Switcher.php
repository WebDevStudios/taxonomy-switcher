<?php

/**
 * Class Taxonomy_Switcher.
 */
class Taxonomy_Switcher {

	/**
	 * Taxonomy to switch from.
	 *
	 * @var string
	 */
	public string $from = '';

	/**
	 * Taxonomy to switch to.
	 *
	 * @var string
	 */
	public string $to = '';

	/**
	 * Parent term_id to limit by.
	 *
	 * @var int
	 */
	public int $parent = 0;

	/**
	 * Array of term IDs to convert.
	 *
	 * @var array
	 */
	public array $terms = [];

	/**
	 * Array of term IDs to convert.
	 *
	 * @var array
	 */
	public array $term_ids = [];

	/**
	 * Array of notices from conversion.
	 *
	 * @var array
	 */
	public array $notices = [];

	/**
	 * Array of error/success messages.
	 *
	 * @var array
	 */
	public array $messages = [];

	/**
	 * Whether or not is our option page.
	 *
	 * @var bool
	 */
	public bool $is_ui;

	/**
	 * Setup the object.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments containing from taxonomy, to taxonomy,
	 *                    and additional optional params.
	 */
	public function __construct( array $args = [] ) {

		$args = wp_parse_args( $args, [
			'from_tax' => '',
			'to_tax'   => '',
			'parent'   => '',
			'terms'    => '',
		] );

		if ( ! $args['from_tax'] || ! $args['to_tax'] ) {
			return;
		}

		if ( ! empty( $args['parent'] ) ) {
			$this->parent = absint( $args['parent'] );
		}

		if ( ! empty( $args['terms'] ) ) {
			$this->terms = wp_parse_id_list( $args['terms'] );
		}

		$this->is_ui = ( isset( $_GET['page'] ) && 'taxonomy-switcher' == $_GET['page'] );

		$this->from = sanitize_text_field( $args['from_tax'] );
		$this->to   = sanitize_text_field( $args['to_tax'] );

	}

	/**
	 * Convert taxonomy of terms from the Admin.
	 *
	 * @since 1.0.0
	 */
	public function admin_convert() {

		$count = $this->count();

		if ( ! $count && $this->is_ui ) {
			return $this->notice( $this->notices( 'no_terms' ) );
		}

		$this->notice( $this->notices( 'switching' ) );

		if ( 0 < $this->parent ) {
			$this->notice( $this->notices( 'limit_by_parent' ) );
		} elseif ( ! empty( $this->terms ) ) {
			$this->notice( $this->notices( 'limit_by_terms' ) );
		}

		set_time_limit( 0 );

		$this->convert();

		$this->notice( $this->notices( 'switched' ) );

		if ( $this->is_ui ) {
			return $this->notices;
		}

		die();
	}

	/**
	 * Stores and (maybe) displays notices.
	 *
	 * @since 1.0.0
	 *
	 * @param string $notice Notice to store and/or display.
	 *
	 * @return array
	 */
	public function notice( string $notice ) {
		// Add to our notices array.
		$this->notices[] = $notice;
		if ( ! $this->is_ui ) {
			echo $notice;
		}

		return $this->notices;
	}

	/**
	 * Compile our notices.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Array key to retrieve.
	 *
	 * @return mixed
	 */
	public function notices( string $key ) {
		if ( ! empty( $this->messages ) ) {
			return $this->messages[ $key ];
		}

		$count          = $this->count();
		$count_name     = sprintf( _n( '1 term', '%d terms', $count, 'wds' ), $count );
		$this->messages = [
			'no_terms'        => __( 'No terms to be switched. Check if the term exists in your "from" taxonomy.', 'wds' ),
			'switching'       => sprintf( __( 'Switching %s with the taxonomy \'%s\' to the taxonomy \'%s\'', 'wds' ), $count_name, $this->from, $this->to ),
			'limit_by_parent' => sprintf( __( 'Limiting the switch by the parent term_id of %d', 'wds' ), $this->parent ),
			'limit_by_terms'  => sprintf( __( 'Limiting the switch to these terms: %s', 'wds' ), implode( ', ', $this->terms ) ),
			'switched'        => sprintf( __( 'Taxonomies switched for %s!', 'wds' ), $count_name ),
		];

		return $this->messages[ $key ];
	}

	/**
	 * Get term ids based on $from and $parent.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of term ids.
	 */
	public function get_term_ids() : array {

		$args = [
			'hide_empty' => false,
			'fields'     => 'ids',
			'child_of'   => $this->parent,
			'include'    => $this->terms,
		];

		$args = apply_filters( 'taxonomy_switcher_get_terms_args', $args, $this->from, $this->to, [
			'parent' => $this->parent,
			'terms'  => $this->terms
		] );

		$terms = get_terms( $this->from, $args );

		$this->term_ids = [];

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$this->term_ids = $terms;
		}

		return $this->term_ids;

	}

	/**
	 * Return the total count of terms found.
	 *
	 * @since 1.0.0
	 *
	 * @return int Total count of terms found.
	 */
	public function count() : int {

		if ( empty( $this->term_ids ) ) {
			$this->get_term_ids();
		}

		return count( $this->term_ids );

	}

	/**
	 * Convert taxonomy of terms.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the conversion was successful.
	 */
	public function convert() : bool {

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

		$post_ids = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_menu_item_object_id' AND meta_value IN ( {$term_ids} );" );
		update_postmeta_cache( $post_ids );
		foreach ( $post_ids as $post_id ) {
			$type   = get_post_meta( $post_id, '_menu_item_type', true );
			$object = get_post_meta( $post_id, '_menu_item_object', true );
			if ( 'taxonomy' !== $type ) {
				continue;
			}
			if ( $this->from !== $object ) {
				continue;
			}
			update_post_meta( $post_id, '_menu_item_object', $this->to );
			clean_post_cache( $post_id );
		}

		// Clean term caches
		clean_term_cache( $term_ids, $this->from );
		clean_term_cache( $term_ids, $this->to );

		return true;
	}
}
