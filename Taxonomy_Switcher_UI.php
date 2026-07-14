<?php
/**
 * Class Taxonomy_Switcher_UI.
 */
class Taxonomy_Switcher_UI {

	const VERSION = '1.1.1';

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

		$this->admin_title = esc_html__( 'Taxonomy Switcher', 'taxonomy-switcher' );
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
		wp_enqueue_script( $this->admin_slug, $this->dir_url . 'js/' . $this->admin_slug . '.js', [], self::VERSION, true );

		$taxonomies = get_taxonomies( [
			'public' => true,
		], 'objects' );
		$tax_data = [];
		foreach ( $taxonomies as $tax ) {
			$tax_data[] = [
				'taxonomy'     => $tax->name,
				'hierarchical' => $tax->hierarchical ? 'true' : 'false',
			];
		}
		wp_add_inline_script(
			$this->admin_slug,
			'const tsTaxData = ' . json_encode( $tax_data ),
			'before'
		);
	}

	/**
	 * Taxonomy Switcher UI admin page.
	 *
	 * @since 1.0.0
	 */
	public function do_page() {

		$this->registered_taxonomies = get_taxonomies( [
			'public' => true,
		], 'objects' );
		?>
		<div id="wds-taxonomy-switcher" class="wrap <?php echo esc_attr( $this->admin_slug ); ?>">
			<h2><?php echo esc_html( $this->admin_title ); ?></h2>

			<form method="get">
				<?php wp_nonce_field( __FILE__, 'taxonomy_switcher_nonce' ); ?>
				<input type="hidden" name="taxonomy_switcher" value="1" />
				<input type="hidden" name="page" value="<?php echo esc_attr( $this->admin_slug ); ?>" />

				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><label for="from_tax"><?php esc_html_e( 'Taxonomy to switch from:', 'taxonomy-switcher' ); ?></label></th>
						<td>
							<select name="from_tax" id="from_tax">
								<?php $this->fill_options( 'from_tax' ); ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="to_tax"><?php esc_html_e( 'Taxonomy to switch to:', 'taxonomy-switcher' ); ?></label></th>
						<td>
							<select name="to_tax" id="to_tax">
								<?php $this->fill_options( 'to_tax' ); ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="taxonomy-switcher-terms"><?php esc_html_e( 'Comma separated list of term ids to switch', 'taxonomy-switcher' ); ?></label>
						</th>
						<td>
							<input placeholder="1,2,13" class="regular-text" type="text" id="taxonomy-switcher-terms" name="terms" value="<?php echo isset( $_GET[ 'terms' ] ) ? esc_attr( $_GET[ 'terms' ] ) : ''; ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="taxonomy-switcher-parent"><?php esc_html_e( 'Limit taxonomy switch for child terms of a specific parent. Use term slug.', 'taxonomy-switcher' ); ?></label>
						</th>
						<td>
							<input class="regular-text" type="text" id="taxonomy-switcher-parent" name="parent" value="<?php echo isset( $_GET['parent'] ) ? esc_attr( $_GET['parent'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Start typing to search for a term parent', 'taxonomy-switcher' ); ?>">

							<p class="taxonomy-switcher-spinner spinner"></p>

							<p class="taxonomy-switcher-ajax-results-help" style="display:none;"><?php esc_html_e( 'Select a term:', 'taxonomy-switcher' ); ?></p>

							<div class="taxonomy-switcher-ajax-results-posts"></div>
						</td>
					</tr>
					</tbody>
				</table>

				<?php submit_button( esc_attr__( 'Switch Taxonomies', 'taxonomy-switcher' ) ); ?>
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
			$this->send_error( __LINE__, esc_html__( 'Security check failed', 'taxonomy-switcher' ) );
		}

		$taxonomy = $_REQUEST['tax_name'] ?? 'category';

		$search_string = sanitize_text_field( $_REQUEST[ 'search' ] );

		if ( empty( $search_string ) ) {
			$this->send_error( __LINE__, esc_html__( 'Please Try Again', 'taxonomy-switcher' ) );
		}

		$terms = $this->get_terms( $search_string, $taxonomy );

		if ( ! $terms ) {
			$this->send_error( __LINE__, esc_html__( 'No terms found', 'taxonomy-switcher' ) );
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
			$this->send_error( __LINE__, esc_html__( 'No terms found with children.', 'taxonomy-switcher' ) );
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

		$msg = $msg ?: esc_html__( 'No Results Found', 'taxonomy-switcher' );

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

		$terms = get_terms(
			[
				'taxonomy'     => $taxonomy,
				'number'       => absint( $number ),
				'name__like'   => $search_string,
				'cache_domain' => 'taxonomy_switch_search2',
				'get'          => 'all',
				'hide_empty'   => false,
			]
		);

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
			$children = get_terms( [
				'taxonomy'   => $term->taxonomy,
				'parent'     => $term->term_id,
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
}
