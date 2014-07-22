<?php
/**
 * Class Taxonomy_Switcher_UI
 */
class Taxonomy_Switcher_UI {

	const VERSION = '1.0.0';

	/**
	 * Setup some vars
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		global $wp_version;

		$this->not_37 = !version_compare( $wp_version, '3.7' ) >= 0;
		$this->dir_url = plugins_url( '/', __FILE__ );

	}

	/**
	 * Peform our UI admin page hooks
	 * @since  1.0.0
	 */
	public function hooks() {

		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'wp_ajax_taxonomy_switcher_search_term_handler', array( $this, 'ajax_term_results' ) );

	}

	/**
	 * Add menu item/ui page
	 * @since 1.0.0
	 */
	public function add_page() {

		$this->admin_title = __( 'Taxonomy Switcher', 'wds' );
		$this->admin_slug = 'taxonomy-switcher';

		$this->options_page = add_management_page( $this->admin_title, $this->admin_title, 'manage_options', $this->admin_slug, array(
			$this,
			'do_page'
		) );

		add_action( 'admin_head-' . $this->options_page, array( $this, 'js' ) );

	}

	/**
	 * JS for UI page
	 * @since 1.0.0
	 */
	public function js() {

		wp_enqueue_script( $this->admin_slug, $this->dir_url . 'js/' . $this->admin_slug . '.js', array( 'jquery' ), self::VERSION, true );

	}

	/**
	 * Taxonomy Switcher UI admin page
	 * @since 1.0.0
	 */
	public function do_page() {

		$this->registered_taxonomies = get_taxonomies( array(), 'objects' );
?>
	<div class="wrap <?php echo $this->admin_slug; ?>">
		<h2><?php echo $this->admin_title; ?></h2>

		<form method="get">
			<?php wp_nonce_field( __FILE__, 'taxonomy_switcher_nonce' ); ?>
			<input type="hidden" name="taxonomy_switcher" value="1" />
			<input type="hidden" name="page" value="<?php echo $this->admin_slug; ?>" />

			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"><label for="from_tax"><?php _e( 'Taxonomy to switch from:', 'wds' ); ?></label></th>
					<td>
						<select name="from_tax" id="from_tax">
							<?php $this->fill_options( 'from_tax' ); ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="to_tax"><?php _e( 'Taxonomy to switch to:', 'wds' ); ?></label></th>
					<td>
						<select name="to_tax" id="to_tax">
							<?php $this->fill_options( 'to_tax' ); ?>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="taxonomy-switcher-terms"><?php _e( 'Comma separated list of term ids to switch', 'wds' ); ?></label>
					</th>
					<td>
						<input placeholder="1,2,13" class="regular-text" type="text" id="taxonomy-switcher-terms" name="terms" value="<?php echo isset( $_GET[ 'terms' ] ) ? esc_attr( $_GET[ 'terms' ] ) : ''; ?>">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="taxonomy-switcher-parent"><?php _e( 'Limit taxomoy switch for child terms of a specific parent', 'wds' ); ?></label>
					</th>
					<td>
						<input class="regular-text" type="text" id="taxonomy-switcher-parent" name="parent" value="<?php echo isset( $_GET[ 'parent' ] ) ? esc_attr( $_GET[ 'parent' ] ) : ''; ?>" placeholder="<?php _e( 'Type to Search for Term Parent', 'wds' ); ?>">

						<p class="taxonomy-switcher-spinner spinner"></p>

						<p class="taxonomy-switcher-ajax-results-help" style="display:none;"><?php _e( 'Select a term:', 'wds' ); ?></p>

						<div class="taxonomy-switcher-ajax-results-posts"></div>
					</td>
				</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Switch Taxonomies', 'wds' ) ); ?>
		</form>
<?php
	}

	/**
	 * Fill select <option>s with all taxonomies
	 * @since  1.0.0
	 *
	 * @param  string $name name of select
	 */
	public function fill_options( $name ) {

		$current = isset( $_GET[ $name ] ) ? $_GET[ $name ] : false;

		foreach ( $this->registered_taxonomies as $slug => $tax_object ) {
			echo '<option value="' . $slug . '" ' . selected( $slug, $current, false ) . '>' . $tax_object->labels->name . '</option>';
		}

	}

	/**
	 * Ajax handler for term search
	 * @since  1.0.0
	 */
	public function ajax_term_results() {

		// verify our nonce, and required data
		if ( !( isset( $_REQUEST[ 'nonce' ], $_REQUEST[ 'search' ] ) && wp_verify_nonce( $_REQUEST[ 'nonce' ], __FILE__ ) ) ) {
			$this->send_error( __LINE__, __( 'security check failed', 'wds' ) );
		}

		// set taxonomy
		$taxonomy = isset( $_REQUEST[ 'tax_name' ] ) ? $_REQUEST[ 'tax_name' ] : 'category';

		// sanitize our search string
		$search_string = sanitize_text_field( $_REQUEST[ 'search' ] );

		// No search string, bail
		if ( empty( $search_string ) ) {
			$this->send_error( __LINE__, __( 'Please Try Again', 'wds' ) );
		}

		// do term search
		$terms = $this->get_terms( $search_string, $taxonomy );

		// No terms, bail
		if ( !$terms ) {
			$this->send_error( __LINE__ );
		}

		// loop found terms and concatenate list items
		$items = $this->get_list_items( $terms );

		if ( !$items ) {
			// do more extensive term search
			$terms = $this->get_terms( $search_string, $taxonomy, 30 );

			// loop found terms and concatenate list items
			$items = $this->get_list_items( $terms );
		}

		// No items, bail
		if ( !$items ) {
			$this->send_error( __LINE__, __( 'No terms found with children.', 'wds' ) );
		}

		// Build our ordered list
		$return = sprintf( '<ol>%s</ol>', $items );

		// send back our encoded data
		wp_send_json_success( array( 'html' => $return ) );

	}

	/**
	 * wp_send_json_error wrapper method
	 * @since  1.0.0
	 *
	 * @param  string $line Line number of error
	 * @param  string $msg Message to send
	 */
	public function send_error( $line, $msg = '' ) {

		$msg = $msg ? $msg : __( 'No Results Found', 'wds' );

		wp_send_json_error( array(
			'html' => '<ul><li>' . $msg . '</li></ul>',
			'line' => $line,
			'$_REQUEST' => $_REQUEST
		) );

	}

	/**
	 * Get the terms for our query
	 * @since  1.0.0
	 *
	 * @param  string $search_string Search query
	 * @param  string $taxonomy Taxonomy slug
	 * @param  integer $number Number of results to grab
	 *
	 * @return mixed                  Array of terms or false
	 */
	public function get_terms( $search_string, $taxonomy, $number = 10 ) {

		if ( $this->not_37 ) {
			// add our term clause filter for this iteration (if < than 3.7)
			add_filter( 'terms_clauses', array( $this, 'wilcard_term_name' ) );
		}
		// do term search
		$terms = get_terms( $taxonomy, array(
			'number' => absint( $number ),
			'name__like' => $search_string,
			'cache_domain' => 'taxonomy_switch_search2',
			'get' => 'all'
		) );

		remove_filter( 'terms_clauses', array( $this, 'wilcard_term_name' ) );

		return empty( $terms ) || is_wp_error( $terms ) ? false : $terms;

	}

	/**
	 * Loops terms and builds list item strings (if terms have children)
	 * @since  1.0.0
	 *
	 * @param  array $terms Array of term objects
	 *
	 * @return string        List item markup on success
	 */
	public function get_list_items( $terms ) {

		// loop found terms and concatenate list items
		$items = '';

		foreach ( $terms as $term ) {
			// Check if this term has child terms
			$children = get_terms( $term->taxonomy, array(
				'parent' => $term->term_id,
				'hide_empty' => false
			) );

			// If no child terms to convert, bail
			if ( !$children ) {
				continue;
			}

			// Add parent term for clarity
			$parent_term = $term->parent ? get_term_by( 'id', $term->parent, $term->taxonomy ) : false;
			$parent_name = $parent_term ? $parent_term->name . ' &rarr; ' : '';

			$items .= '<li><a data-slug="' . esc_attr( $term->slug ) . '" data-termid="' . esc_attr( $term->term_id ) . '" href="#">' . esc_html( $parent_name . $term->name ) . '</a></li>';
		}

		return $items;

	}

	/**
	 * Make term search wildcard on front as well as back
	 * @since  1.0.0
	 *
	 * @param  string $clauses Query clauses
	 *
	 * @return string           Modified query clauses
	 */
	public function wilcard_term_name( $clauses ) {

		// add wildcard flag to beginning of term
		$clauses[ 'where' ] = str_replace( "name LIKE '", "name LIKE '%", $clauses[ 'where' ] );

		return $clauses;

	}

}
