<?php

class Pearson_SSO_Admin_Views {
	
	/**
	 * Main options page
	 */
	public static function general() {
		
		$ret = '<div class="psso_admin_general">';
		$ret .= '<p>Pearson Single Sign-On (SSO) allows your site to process visits from apps which deliver encrypted '
			. 'payloads, capture WordPress logins and authorize the user against the '
			. 'Pearson Schoolnet API, and authorize users via Schoolnet tokens.</p>';
		$ret .= '</div>';
		
		return $ret;
	}
	
	/**
	 * Test page
	 */
	public static function test() {
		
		$ret = '<div class="wrap psso_admin_page psso_admin_tests">';
		
		$ret .= '<p>Please save options first! By entering a URL below, complete with query string sent by the SSO '
			. 'app, you can SSO to see what the results are. If the login attempt fails, the default action is '
			. 'for the user to be forwarded to the URL they arrived at, without the query string present. '
			. 'They will not be logged in.</p>';
		
		$ret .= '<form id="sso-test" class="cmb-form"><table class="form-table cmb_metabox"><tbody>';
		
		$ret .= '<tr class="cmb-type-textarea_code">';
		$ret .= '<th><label for="ssoaddress">Enter URL with query string</label></th>';
		$ret .= '<td>';
		$ret .= '<textarea id="ssoaddress" class="cmb_textarea_code" rows="4" cols="80" name="ssoaddress">';
		
		$ret .= self::payload();
		
		$ret .= '</textarea><p><input class="button-primary" type="submit" value="Test" name="submit-test"></p>';
		$ret .= '</td>';
		$ret .= '</tr>';
		
		$ret .= '<tr class="cmb-type-textarea_code">';
		$ret .= '<th><label for="ssoresult">Results:</label></th>';
		$ret .= '<td><div id="ssoresult">...will appear here...</div></td>';
		$ret .= '</tr>';
		
		$ret .= '</tbody></table></form>';
		
		$ret .= '</div>';
		return $ret;
	}
	
	/**
	 * Logs page
	 *
	 * @var Pearson_SSO_List_Table $ccsocListTable
	 * @return string
	 */
	public static function logs() {
		
		global $wpdb, $ccsocListTable;
		
		$table_name     = Pearson_SSO_Log::log_table_name();
		$ccsocListTable->prepare_items();
		
		$ACTION = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : false;
		
		$ret = '<div class="wrap psso_admin_page psso_admin_logs">';
		
		// show table of log entries
		if ( $ACTION != 'details' ) {
			
			$ret .= '<form method="post"><input type="hidden" name="page" value="pearson-sso-log">';
			
			ob_start();
			
			$ccsocListTable->display();
			
			$ret .= ob_get_clean();
			
			$ret .= '</form>';
		}
		
		// show details of specific log entry
		else {
			
			$item = $wpdb->get_results( "SELECT * FROM " . $table_name . " WHERE sso_id='" . $_REQUEST['id'] .
				"'", ARRAY_A );
			
			$stat = array(
				'Failed, user not logged in',
				'Success',
				'User logged in, but routing failed',
			);
			
			$delete_nonce = wp_create_nonce( 'sp_delete_entry' );
			
			$username     = 'No User';
			$userrole     = '';
			
			if ( isset( $item[0]['sso_user'] ) && intval( $item[0]['sso_user'] > 0 ) ) {
				
				$user = get_user_by( 'id', $item[0]['sso_user'] );
				
				$username = $user->data->display_name;
				$userrole = empty( $user->roles ) ? 'no role' : $user->roles;
				
				if ( is_array( $userrole ) ) {
					$userrole = implode( ', ', $userrole );
				}
			}
			
			
			
			$ret .= '<h3 class="psso-log-h3">' . $username .
				( ( $item[0]['sso_new'] == 1 ) ? '( new user )' : '' ) . '</h3>';
			$ret .= '<p class="psso-tight-p"><strong>' . $item[0]['sso_date'] . '</strong> ( ' . $stat[ $item[0]['sso_success'] ] . ' )</p>';
			$ret .= '<p class="psso-tight-p">';
			$ret .= '<em>Role:</em> ' . $userrole;
			$ret .= '</p>';
			
			$ret .= '<p>';
			$ret .= '#' . $item[0]['sso_id'] . ' (via ' . $item[0]['sso_via'] . ') | ';
			$ret .= '<a href="admin.php?page=pearson-sso-logs">Back to Log Table</a> | ';
			$ret .= '<a href="admin.php?page=pearson-sso-logs&amp;action=delete&amp;_wpnonce='
				. $delete_nonce . '&amp;id=' . $_REQUEST['id'] . '">Delete This Entry</a>';
			$ret .= '</p>';
			
			$ret .= '<table class="form-table cmb2_metabox">';
			
			if ( $item[0]['sso_path'] ) {
				
				// url they came in on
				$ret .= '<tr class="cmb-type-title">';
				$ret .= '<td>';
				
				$pay = ( isset( $item[0]['sso_payload'] ) && $item[0]['sso_payload'] ) ? '?payload=' .
					$item[0]['sso_payload'] : '';
				$kee = ( isset( $item[0]['sso_key'] ) && $item[0]['sso_key'] ) ? '&amp;key=' . $item[0]['sso_key'] : '';
				$linker = $item[0]['sso_path'] . $pay . $kee;
				
				$ret .= '<a href="#pssoarrival" class="button button-small psso-arrival-button" '
					. 'data-hide="Hide" data-show="Arrival URL">Arrival URL</a>';
				$ret .= '<div id="pssoarrival" class="psso-arrival-value">';
				
				$ret .= '<span id="sso-long-url">' . wordwrap( $linker, 80, "<br>", true ) . '</span>';
				$ret .= '<p><button class="button-secondary" id="sso-copy-url" data-copy="' . $linker
					. '">Remove Breaks (for copying)</button></p></div>';
				$ret .= '</td>';
				$ret .= '</tr>';
			}
			
			// Details
			$ret .= '<tr class="cmb-type-title">';
			$ret .= '<td><div id="ssoresult">';
			
			$debug = maybe_unserialize( base64_decode( $item[0]['sso_debug'] ) );
			
			$ret .= Pearson_SSO_Debug::html( 'open', false );
			
			foreach ( $debug as $d ) {
				$ret .= $d;
			}
			
			$ret .= Pearson_SSO_Debug::html( 'close', false );
			
			$ret .= '</div></td>';
			$ret .= '</tr>';
			$ret .= '</table>';
		}
		
		$ret .= '</div>';
		
		return $ret;
	}
	
	public static function table_options() {

		global $ccsocListTable;

		$option = 'per_page';
		$args   = [
			'label'   => 'Entries',
			'default' => 20,
			'option'  => 'entries_per_page'
		];
		
		add_screen_option( $option, $args );

		$ccsocListTable = new Pearson_SSO_List_Table();
	}
	
	/**
	 * Payloads, allows SSO to store some sample payloads
	 *
	 * @param int $key
	 *
	 * @return string
	 */
	private static function payload( $key = 0 ) {
		$p = array(
			'payload='
			. 'Gbn57xF%2F6gJUI5kwBDWalv7zqrnvRxpqTc%2BJ4auBvV15fofamVEbaNTFTtfqFixgdU8QQvI7NeFnAtNGw'
			. '5nMJ55Jl58SOOqiLvJZhVstaeSEqzA5ZN%2FSiGbX%2FkYjmXh%2BOwf7tiyU8BzV8ywosvl1e7ROyX1H1jzYuR1oUkjJ6Fi'
			. 'dKGbkgpyZgNjZ4aqEeb%2FGaT9vwb9WHpFB57gH6pjms0YP8JX9xdiS4ik4Z2pRhPpa71Cq9%2BLlT%2BsSnhgFidKXh7pwF'
			. 'KaYhYJuMVTT2Dt%2BrJhBocZ2PrSrkqgbycPDpzU3OHUCp8RwejMO4fpSRT6uC%2F1B30bxdy%2FRgLnj3z0EDFwba9RV%2F'
			. 'faAE6kxVYS6OI7eDui7nAZZum1mHFqPlntYotV5Z6cFkHXQIF7tU0UHu3BMQMd52iczhq8bhLttP2a3Eb2O%2Bm7SKbogtFk'
			. 'h6N00ozKRMh0YnVZswl8XW5HBP%2B10GEabYe6xV8SS1EjWwV5YbLe8XDLYk5G5M2lnes2o9d4%2B86ePHLkrnEgidkT5thg'
			. 'zsa1ZM9t6%2BfJr%2Fmf4IpE8Mnux8ZoDLOAua9ujkCbMIZ8rXMuGsKoNAR9J%2B%2BhAtlBERk4gS%2BtqHWqdpRQEWbGuB'
			. 'xSboiCF0JVOFJ7pBGFgCCm7%2BBg9gDu6bpnyDs92p%2BX3X%2BgGWhjTGLKUFxn6AF0R3iM6uox0aUvh%2FaKz5SG0oRlrh'
			. 'qmWBY8FOG5iD%2BegIjx92xsqFE%2BlnrSNzbIECG8aHAF7DI%2Fhl1T7mvgtPkmoorR0F7wanZ3ZvXHYonihY0LqLhrLkFN'
			. '6gktcMkOCztcuPAAlAv5D4bVDrpDkvuoej%2BeXCBAVCJx5XlRDZPy4zA5M0z7sLixqBL1fIpkBCZwOER06gyAA7ByJN%2B5'
			. '%2FW%2FazJ8JpLF3PXK0Z4AJfGQF6E9pmlW61k8xD0P%2Fe6blP9dhALmIHP6b%2FO4I7O%2BfcwUquaYCWjCUng37F4w9Cb'
			. 'JWz3Nlkkf5MAB3aYH%2B4aavyqw2KAnjVguOke4X93aL67Z%2FlGmtvPv8zhDz%2Bo7s2ddP0JurNQLyMKpT8r23GVChIHxt'
			. 'ApAt34hdGlYBtKquk3mCkM2fi%2BeRAfil1dc4O1u3o9Qdaf4KpEst5bLugjF6%2Bah8%3D'
			. '&key='
			. '3kFL3JdHDDTmhX2VU6OBlaEbst2jgXh9KZva3V2SPQqxH%2Bd8I%2F71neGH8FCLk3%2B5ju2krlCaImWGRyLRw4j3TQ%3D%3D',
		);
		
		return '/?' . ( isset( $p[ $key ] ) ? $p[ $key ] : $p[0] );
	}
}