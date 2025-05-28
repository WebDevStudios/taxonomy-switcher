<?php
/**
 * Class Taxonomy_Switcher_UI.
 */
class Taxonomy_Switcher_UI {

	const VERSION = '1.0.8';

	/**
	 * Whether or not we are on WordPress 3.7.
	 *
	 * @var bool
	 */
	public bool $not_37 = false;

	/**
	 * Directory URL.
	 *
	 * @var string
	 */
	public string $dir_url = '';

	/**
	 * Admin title.
	 *
	 * @var string
	 */
	public string $admin_title = '';

	/**
	 * Admin slug.
	 *
	 * @var string
	 */
	public string $admin_slug = '';

	/**
	 * Options page.
	 *
	 * @var string
	 */
	public string $options_page = '';

	/**
	 * Array of registered taxonomies.
	 *
	 * @var array
	 */
	public array $registered_taxonomies = [];

	/**
	 * Setup some vars.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		global $wp_version;

		$this->not_37  = ! version_compare( $wp_version, '3.7' ) >= 0;
		$this->dir_url = plugins_url( '/', __FILE__ );

	}

	/**
	 * Peform our UI admin page hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'admin_menu', [ $this, 'add_page' ] );
		add_action( 'wp_ajax_taxonomy_switcher_search_term_handler', [ $this, 'ajax_term_results' ] );

	}

	/**
	 * Add menu item/ui page.
	 *
	 * @since 1.0.0
	 */
	public function add_page() {

		$this->admin_title = esc_html__( 'Taxonomy Switcher', 'wds' );
		$this->admin_slug  = 'taxonomy-switcher';

		$this->options_page = add_management_page( $this->admin_title, $this->admin_title, 'manage_options', $this->admin_slug, [
			$this,
			'do_page',
		] );

		add_action( 'admin_head-' . $this->options_page, [ $this, 'js' ] );

	}

	/**
	 * JS for UI page.
	 *
	 * @since 1.0.0
	 */
	public function js() {
		wp_enqueue_script( $this->admin_slug, $this->dir_url . 'js/' . $this->admin_slug . '.js', [ 'jquery' ], self::VERSION, true );
	}

	/**
	 * Taxonomy Switcher UI admin page.
	 *
	 * @since 1.0.0
	 */
	public function do_page() {

		$this->registered_taxonomies = get_taxonomies( [], 'objects' );
		?>
		<div class="wrap <?php echo esc_attr( $this->admin_slug ); ?>">
			<h2><?php echo esc_html( $this->admin_title ); ?></h2>

			<form method="get">
				<?php wp_nonce_field( __FILE__, 'taxonomy_switcher_nonce' ); ?>
				<input type="hidden" name="taxonomy_switcher" value="1" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->admin_slug ); ?>" />

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="from_tax"><?php esc_html_e( 'Taxonomy to switch from:', 'wds' ); ?></label></th>
						<td>
							<select name="from_tax" id="from_tax">
								<?php $this->fill_options( 'from_tax' ); ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="to_tax"><?php esc_html_e( 'Taxonomy to switch to:', 'wds' ); ?></label></th>
						<td>
							<select name="to_tax" id="to_tax">
								<?php $this->fill_options( 'to_tax' ); ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="taxonomy-switcher-terms"><?php esc_html_e( 'Comma separated list of term ids to switch', 'wds' ); ?></label>
						</th>
						<td>
							<input placeholder="1,2,13" class="regular-text" type="text" id="taxonomy-switcher-terms" name="terms" value="<?php echo isset( $_GET[ 'terms' ] ) ? esc_attr( $_GET[ 'terms' ] ) : ''; ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="taxonomy-switcher-parent"><?php esc_html_e( 'Limit taxonomy switch for child terms of a specific parent', 'wds' ); ?></label>
						</th>
						<td>
							<input class="regular-text" type="text" id="taxonomy-switcher-parent" name="parent" value="<?php echo isset( $_GET['parent'] ) ? esc_attr( $_GET['parent'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Start typing to search for a term parent', 'wds' ); ?>">

							<p class="taxonomy-switcher-spinner spinner"></p>

							<p class="taxonomy-switcher-ajax-results-help" style="display:none;"><?php esc_html_e( 'Select a term:', 'wds' ); ?></p>

							<div class="taxonomy-switcher-ajax-results-posts"></div>
						</td>
					</tr>
					</tbody>
				</table>

				<?php submit_button( __( 'Switch Taxonomies', 'wds' ) ); ?>
			</form>
		</div>
	<?php
	}

	/**
	 * Fill select <option>s with all taxonomies.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Name of select.
	 */
	public function fill_options( string $name ) {

		$current = $_GET[ $name ] ?? false;

		foreach ( $this->registered_taxonomies as $slug => $tax_object ) {
			echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $slug, $current, false ) . '>' . $tax_object->labels->name . '</option>';
		}

	}

	/**
	 * Ajax handler for term search.
	 *
	 * @since 1.0.0
	 */
	public function ajax_term_results() {

		if ( ! ( isset( $_REQUEST[ 'nonce' ], $_REQUEST[ 'search' ] ) && wp_verify_nonce( $_REQUEST[ 'nonce' ], __FILE__ ) ) ) {
			$this->send_error( __LINE__, __( 'Security check failed', 'wds' ) );
		}

		$taxonomy = $_REQUEST['tax_name'] ?? 'category';

		$search_string = sanitize_text_field( $_REQUEST[ 'search' ] );

		if ( empty( $search_string ) ) {
			$this->send_error( __LINE__, __( 'Please Try Again', 'wds' ) );
		}

		$terms = $this->get_terms( $search_string, $taxonomy );

		if ( ! $terms ) {
			$this->send_error( __LINE__ );
		}

		// Loop found terms and concatenate list items.
		$items = $this->get_list_items( $terms );

		if ( ! $items ) {
			// Do more extensive term search.
			$terms = $this->get_terms( $search_string, $taxonomy, 30 );

			// Loop found terms and concatenate list items.
			$items = $this->get_list_items( $terms );
		}

		if ( ! $items ) {
			$this->send_error( __LINE__, __( 'No terms found with children.', 'wds' ) );
		}

		$return = sprintf( '<ol>%s</ol>', $items );

		wp_send_json_success( [ 'html' => $return ] );

	}

	/**
	 * The wp_send_json_error wrapper method.
	 *
	 * @since 1.0.0
	 *
	 * @param string $line Line number of error.
	 * @param string $msg  Message to send.
	 */
	public function send_error( string $line, string $msg = '' ) {

		$msg = $msg ?: esc_html__( 'No Results Found', 'wds' );

		wp_send_json_error( [
			'html' => '<ul><li>' . $msg . '</li></ul>',
			'line' => $line,
			'$_REQUEST' => $_REQUEST,
		] );

	}

	/**
	 * Get the terms for our query.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $search_string Search query.
	 * @param string  $taxonomy      Taxonomy slug.
	 * @param integer $number        Number of results to grab.
	 * @return mixed Array of terms or false.
	 */
	public function get_terms( string $search_string, string $taxonomy, int $number = 10 ) {

		if ( $this->not_37 ) {
			// Add our term clause filter for this iteration (if < than 3.7).
			add_filter( 'terms_clauses', [ $this, 'wilcard_term_name' ] );
		}

		$terms = get_terms( $taxonomy, [
			'number'       => absint( $number ),
			'name__like'   => $search_string,
			'cache_domain' => 'taxonomy_switch_search2',
			'get'          => 'all',
		] );

		remove_filter( 'terms_clauses', [ $this, 'wilcard_term_name' ] );

		return empty( $terms ) || is_wp_error( $terms ) ? false : $terms;

	}

	/**
	 * Loops terms and builds list item strings (if terms have children).
	 *
	 * @since 1.0.0
	 *
	 * @param array $terms Array of term objects.
	 * @return string List item markup on success.
	 */
	public function get_list_items( array $terms ) : string {

		$items = '';

		foreach ( $terms as $term ) {
			$children = get_terms( $term->taxonomy, [
				'parent' => $term->term_id,
				'hide_empty' => false,
			] );

			if ( ! $children ) {
				continue;
			}

			// Add parent term for clarity.
			$parent_term = $term->parent ? get_term_by( 'id', $term->parent, $term->taxonomy ) : false;
			$parent_name = $parent_term ? $parent_term->name . ' &rarr; ' : '';

			$items .= '<li><a data-slug="' . esc_attr( $term->slug ) . '" data-termid="' . esc_attr( $term->term_id ) . '" href="#">' . esc_html( $parent_name . $term->name ) . '</a></li>';
		}

		return $items;

	}

	/**
	 * Make term search wildcard on front as well as back.
	 *
	 * @since 1.0.0
	 *
	 * @param array $clauses Query clauses.
	 * @return array Modified query clauses.
	 */
	public function wilcard_term_name( $clauses ) {

		// Add wildcard flag to beginning of term.
		$clauses['where'] = str_replace( "name LIKE '", "name LIKE '%", $clauses['where'] );

		return $clauses;
	}
}
