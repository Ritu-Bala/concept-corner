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
 * Pearson SSO Log functions
 * @package Pearson_SSO_Log
 * @author    Roger Los <roger@rogerlos.com>
 */
class Pearson_SSO_Log {
	
	/**
	 * Log table name, minus the wpdb prefix
	 * 
	 * @var string
	 */
	protected static $table_name = "pearson_sso";

	/**
	 * Accumulated log entry
	 *
	 * @since    1.1.0
	 * @var      object
	 */
	protected static $record = null;

	/**
	 * Set if the log table has been cleaned this visit
	 *
	 * @since    1.1.0
	 * @var array
	 */
	protected static $cleaner = null;

	/**
	 * Updates the log table if needed
	 *
	 * @since 1.3.0
	 */
	public static function update_db() {
		
		// current DB version
		$db_version = Pearson_SSO::get_const( 'DB_VERSION' );

		// installed version
		$current_version = get_option( "psso_db_version" );

		if ( $db_version != $current_version ) {
			self::create_log_table();
		}
	}
	
	/**
	 * Add log table on init
	 *
	 * @since    1.1.0
	 */
	public static function create_log_table() {
		
		$db_version = Pearson_SSO::get_const( 'DB_VERSION' );

		global $charset_collate;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = self::log_table_name();

		$sql = "CREATE TABLE $table_name (
			sso_id mediumint(9) NOT NULL AUTO_INCREMENT,
			sso_date timestamp DEFAULT CURRENT_TIMESTAMP,
			sso_via text NOT NULL,
			sso_path text NOT NULL,
			sso_user mediumint(9) NOT NULL,
			sso_payload text NOT NULL,
			sso_key text NOT NULL,
			sso_debug longtext NOT NULL,
			sso_new tinyint(1) DEFAULT 0 NOT NULL,
			sso_success tinyint(1) DEFAULT 0 NOT NULL,
			PRIMARY KEY  (sso_id)
			) $charset_collate ";

		dbDelta( $sql );

		update_option( 'psso_db_version', $db_version );
	}

	/**
	 * Write data to log table
	 *
	 * @since    1.1.0
	 * @param $data
	 * @return bool|int
	 */
	private static function write_log( $data ) {
		
		global $wpdb;
		
		$table_name = self::log_table_name();
		$row_id     = isset( $data['sso_id'] ) && $data['sso_id'] ? intval( $data['sso_id'] ) : 0;
		$row        = $row_id;
		$return     = 'oops';

		$fields = array(
			'sso_via'     => ( ( ! isset( $data['sso_via'] ) ) ? '' : $data['sso_via'] ),
			'sso_path'    => ( ( ! isset( $data['sso_path'] ) ) ? '' : $data['sso_path'] ),
			'sso_user'    => ( ( ! isset( $data['sso_user'] ) ) ? 0 : $data['sso_user'] ),
			'sso_payload' => ( ( ! isset( $data['sso_payload'] ) ) ? '' : $data['sso_payload'] ),
			'sso_key'     => ( ( ! isset( $data['sso_key'] ) ) ? '' : $data['sso_key'] ),
			'sso_debug'   => base64_encode( maybe_serialize( $data['sso_debug'] ) ),
			'sso_new'     => ( ( ! isset( $data['sso_new'] ) ) ? 0 : $data['sso_new'] ),
			'sso_success' => ( ( ! isset( $data['sso_success'] ) ) ? 0 : $data['sso_success'] ),
		);
		$fields_format = array(
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
			'%d'
		);

		$where = array(
			'sso_id' => $row
		);
		$where_format = array(
			'%d'
		);

		if ( $row < 1 ) {
			$wpdb->insert( $table_name, $fields, $fields_format );
			$return = "insert: " .  $wpdb->insert_id;
		}

		if ( $row > 0 ) {
			$wpdb->update( $table_name, $fields, $where, $fields_format, $where_format );
			$return = 'update: ' . $row;
		}

		return $return;
	}

	/**
	 * Returns log table name, properly prefixed for multisite
	 *
	 * @since    1.1.0
	 * @return string
	 */
	public static function log_table_name() {
		global $wpdb;
		return is_multisite() ? $wpdb->base_prefix . self::$table_name : $wpdb->prefix . self::$table_name;
	}

	/**
	 * Adds data to record
	 *
	 * @since 1.1.0
	 * @param $key
	 * @param $data
	 * @return bool
	 */
	public static function log( $key, $data ) {
		self::$record[ $key ] = $data;
		return true;
	}

	/**
	 * Writes log to DB
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public static function write() {
		return Pearson_SSO::get_option('psso_logs', false ) == 'on'
		&& Pearson_SSO::get_option('writelog', false ) !== false ?
			self::write_log( self::$record ) : false;
	}
	
	/**
	 * Cleans log table of older entries
	 */
	public static function clean_log() {

		if ( self::$cleaner !== null || ! is_admin() ) {
			return;
		}

		global $wpdb;
		
		$cookie  = 'ssologclean';
		$table   = self::log_table_name();
		$expires = intval( Pearson_SSO::get_option( 'psso_log_expire', false ) );
		
		if ( $expires > 0 && ! isset( $_COOKIE[ $cookie ] ) ) {
			
			$wpdb->query( "DELETE FROM $table  WHERE DATEDIFF(NOW(), sso_date) > " . $expires );
			
			$expires = $expires * 24 * ( time() + 3600 );
			setcookie( $cookie, 'cleaned', $expires );

			Pearson_SSO_Debug::d( 'Cleaned Log Table' );
		}

		// set flag so this is not called again
		self::$cleaner = 1;
	}
}