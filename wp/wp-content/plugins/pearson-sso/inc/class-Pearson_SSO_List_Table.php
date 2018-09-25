<?php
/**
 * Pearson Single Sign On
 *
 * @package   Pearson_SSO
 * @author    Roger Los <roger@rogerlos.com>
 * @license   GPL-2.0+
 * @link      http://pearson.com
 * @copyright 2014 Pearson Education
 */

/**
 * Pearson SSO List Table
 * @package Pearson_SSO_List_Table
 * @author    Roger Los <roger@rogerlos.com>
 *
 */
class Pearson_SSO_List_Table extends WP_List_Table {
	
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Entry', 'sp' ), 
			'plural'   => __( 'Entriess', 'sp' ), 
			'ajax'     => true,
		) );
	}
	
	/**
	 * Prepare items
	 */
	public function prepare_items() {
		
		$this->process_bulk_action();

		$perPage     = $this->get_items_per_page( 'entries_per_page', 20 );
		$currentPage = $this->get_pagenum();

		$totalItems  = self::record_count();
		
		$this->set_pagination_args( array(
			'total_items' => $totalItems,
			'per_page'    => $perPage,
		) );
		
		$this->_column_headers = $this->get_column_info();
		
		$this->items = $this->table_data( $perPage, $currentPage );
	}
	
	/**
	 * User column
	 * 
	 * @param $item
	 *
	 * @return string
	 */
	public function column_sso_user( $item ) {

		$delete_nonce = wp_create_nonce( 'sp_delete_entry' );
		$user = isset( $item['sso_user'] ) && $item['sso_user'] ? $item['sso_user']->data->display_name : false;
		$role = $user && ! empty( $item['sso_user']->roles ) ? $item['sso_user']->roles : 'No role';
		if ( is_array( $role ) ) {
			$role = implode( ', ', $role );
		}
		
		$actions = array(
			'detail' => sprintf(
				'<a href="?page=%s&action=%s&id=%s">SSO Log Details</a>',
				$_REQUEST['page'],
				'details',
				$item['sso_id']
			),
			'delete' => sprintf(
				'<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Delete Log Entry</a>',
				$_REQUEST['page'],
				'delete',
				$item['sso_id'],
				$delete_nonce
			),
		);
		
		$display = $user ?  '<strong>' . $user . '</strong> (' . $role . ')': 'No user';
		
		return sprintf( '%1$s %2$s', $display, $this->row_actions( $actions ) );
	}

	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox">',
			'sso_user'    => 'WP User',
			'sso_date'    => 'Date',
			'sso_path'    => 'Path',
			'sso_via'     => 'Via',
			'sso_new'     => 'New User',
			'sso_success' => 'Status',
		);
	}

	/**
	 * Hidden columns...
	 *
	 * @return array
	 */
	public function get_hidden_columns() {
		return array( 'sso_id' => 'ID' );
	}

	/**
	 * Sortable columns
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		
		return array(
			'sso_user'    => array( 'sso_user', false ),
			'sso_date'    => array( 'sso_date', false ),
			'sso_path'    => array( 'sso_path', false ),
			'sso_via'     => array( 'sso_via', false ),
			'sso_new'     => array( 'sso_new', false ),
			'sso_success' => array( 'sso_success', false ),
		);
	}

	/**
	 * Get data
	 *
	 * @param int $per_page
	 * @param int $pg
	 *
	 * @return array|null|object
	 */
	private function table_data( $per_page = 20, $pg = 1 ) {
		
		global $wpdb;

		$sql = 'SELECT * FROM ' . Pearson_SSO_Log::log_table_name();
		
		$orderby = empty( $_REQUEST['orderby'] ) ? 'sso_date' : esc_sql( $_REQUEST['orderby'] );
		$order   = empty( $_REQUEST['order'] ) ?
			$orderby == 'sso_date' ? ' DESC' :' ASC' : ' ' . esc_sql( $_REQUEST['order'] );
		
		$sql .= ' ORDER BY ' . $orderby . $order;

		$wh = array();
		if ( isset( $_REQUEST['sso_success'] ) ){
			$wh[] = '`sso_success`=' . esc_sql( $_REQUEST['sso_success'] );
		}
		if ( isset( $_REQUEST['sso_via'] ) ){
			$wh[] = '`sso_via`=\'' . esc_sql( $_REQUEST['sso_via'] )  . '\'';
		}
		if ( isset( $_REQUEST['sso_new'] ) ){
			$wh[] = '`sso_new`=' . esc_sql( $_REQUEST['sso_new'] );
		}
		
		$sql .= empty( $wh ) ? '' : ' WHERE ' . implode(' AND ', $wh );

		$sql .= ' LIMIT ' . $per_page;
		$sql .= ' OFFSET ' . ( $pg - 1 ) * $per_page;

		$results = $wpdb->get_results( $sql, 'ARRAY_A' );
		
		$data = array();
		
		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				
				// get the user name from the id
				$this_user = get_user_by( 'id', $result['sso_user'] );
				
				$data[] = array(
					'sso_id'      => $result['sso_id'],
					'sso_user'    => $this_user,
					'sso_date'    => $result['sso_date'],
					'sso_path'    => $result['sso_path'],
					'sso_via'     => $result['sso_via'],
					'sso_new'     => $result['sso_new'],
					'sso_success' => $result['sso_success'],
				);
			}
		}
		
		return $data;
	}

	/**
	 * Column defaults
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return mixed|string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'sso_user' :
			case 'sso_date' :
			case 'sso_path' :
			case 'sso_via' :
				return $item[ $column_name ];
				break;
			case 'sso_new' :
				return ( $item[ $column_name ] == 1 ) ? 'Yes' : 'No';
				break;
			case 'sso_success' :
				if ( $item[ $column_name ] == 1 ) {
					return 'Success';
				}
				else if ( $item[ $column_name ] == 2 ) {
					return 'See Details';
				}
				else {
					return 'Fail';
				}
				break;
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk-delete' => 'Delete',
		);
	}

	/**
	 * Column callbacks
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['sso_id']
		);
	}

	public static function record_count() {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM " . Pearson_SSO_Log::log_table_name();
		return $wpdb->get_var( $sql );
	}

	public function process_bulk_action() {

		$action =  isset( $_POST['action2'] ) ? $_POST['action2'] : isset( $_POST['action'] ) ? $_POST['action'] : '';
		$bulk = $action == 'bulk-delete' ? true : false;
		
		if ( 'delete' === $this->current_action() && ! $bulk ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'sp_delete_entry' ) ) {
				die( 'Oops' );
			}
			else {
				self::delete_entry( absint( $_GET['id'] ) );
				self::delete_notification( $_GET['id'] );
			}

		} else if ( $bulk ) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_entry( $id );
			}
			
			self::delete_notification( $delete_ids );
		}
	}
	
	public static function delete_entry( $id ) {
		global $wpdb;
		$wpdb->delete(
			Pearson_SSO_Log::log_table_name(),
			array( 'sso_id' => $id ),
			array( '%d' )
		);
	}

	public static function delete_notification( $id ) {
		$e = is_array( $id ) ? 'entries ' . implode(', ', $id ) : 'entry ' . $id;
		echo '<div class="notice notice-success is-dismissible">';
		echo '<p>Log ' . $e . ' deleted.</p>';
		echo '</div>';
	}

	public function extra_tablenav( $which ) {
		if ( $which == "top" ){
			echo '<div class="alignleft actions bulkactions">';
			
			// by success
			$opts = array(
				array(
					'val'   => '',
					'label' => 'Filter by Status'
				),
				array(
					'val'   => '1',
					'label' => 'Status: Success'
				),
				array(
					'val'   => '0',
					'label' => 'Status: Failed'
				),
				array(
					'val'   => '2',
					'label' => 'Status: Other'
				),
			);
			echo '<select name="success-filter" data-key="sso_success" class="psso-log-filter">';
			foreach ( $opts as $opt ) {
				$sel = isset( $_REQUEST['sso_success'] ) && $_REQUEST['sso_success'] == $opt['val'] ? ' selected' : '';
				echo '<option value="' . $opt['val'] . '"' . $sel . '>'.$opt['label'].'</option>';
			}
			echo '</select>';
			
			// by via
			$opts = array(
				array(
					'val'   => '',
					'label' => 'Filter by Via'
				),
				array(
					'val'   => 'Login',
					'label' => 'Via: Login Form'
				),
				array(
					'val'   => 'Test',
					'label' => 'Via: Test Form'
				),
				array(
					'val'   => 'Payload',
					'label' => 'Via: Payload'
				),
			);
			echo '<select name="via-filter" data-key="sso_via" class="psso-log-filter">';
			foreach ( $opts as $opt ) {
				$sel = isset( $_REQUEST['sso_via'] ) && $_REQUEST['sso_via'] == $opt['val'] ? ' selected' : '';
				echo '<option value="' . $opt['val'] . '"' . $sel . '>'.$opt['label'].'</option>';
			}
			echo '</select>';
			
			// by new
			$opts = array(
				array(
					'val'   => '',
					'label' => 'Filter by New User'
				),
				array(
					'val'   => '0',
					'label' => 'Existing User'
				),
				array(
					'val'   => '1',
					'label' => 'New User'
				),
			);
			echo '<select name="via-new" data-key="sso_new" class="psso-log-filter">';
			foreach ( $opts as $opt ) {
				$sel = isset( $_REQUEST['sso_new'] ) && $_REQUEST['sso_new'] == $opt['val'] ? ' selected' : '';
				echo '<option value="' . $opt['val'] . '"' . $sel . '>'.$opt['label'].'</option>';
			}
			echo '</select>';
			
			echo '</div>';
		}
	}

}