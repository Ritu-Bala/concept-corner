<?php

// @todo: consider using window.sessionStorage to record all SSO ops, assuming that's possible

class Pearson_SSO_Debug {
	
	protected static $debug_messages  = array();
	protected static $debug           = array();

	protected static $groups          = array();
	protected static $all_groups      = array();
	protected static $close_flag      = false;
	
	protected static $open            = 0;
	
	protected static $timer           = 0;
	protected static $last_time       = 0;
	protected static $qtime           = 0;
	
	protected static $count           = 0;
	protected static $last_class      = '';
	
	protected static $qbase           = 0;
	protected static $qsso            = 0;
	
	/**
	 * @return array
	 */
	public static function get_debug() {

		self::$debug[] = '<tr><td style="border-top: 0 solid transparent !important;"><div id="pssomule" data-groups="'
			. implode( ',', self::$all_groups ) . '"></div></td></tr>';
		
		return self::$debug;
	}
	
	/**
	 * Debug message gateway
	 * 
	 * @param string $message
	 * @param array  $vals
	 * @param string $type
	 * @param string $note
	 * @param bool   $more
	 */
	public static function d( $message, $vals = array(), $type = 'ok', $note = '', $more = true ) {
		self::_d( $message, $vals, $type, $note, $more );
	}
	
	/**
	 * Debug message gateway, used at start of methods
	 * 
	 * @param string $message
	 * @param array  $vals
	 * @param string $type
	 * @param string $note
	 * @param bool   $more
	 */
	public static function ds( $message, $vals = array(), $type = 'start', $note = 'Method start', $more = true ) {
		// reset the query base so we don't count non-sso queries, and the timer so we don't count non-sso code
		if ( self::$open < 1 ) {
			self::$qbase = get_num_queries();
			self::$last_time = microtime( true );
		}
		self::$open++;
		self::_d( $message, $vals, $type, $note, $more, 'open' );
	}
	
	/**
	 * Debug message gateway, used at end of methods
	 * 
	 * @param string $message
	 * @param array  $vals
	 * @param string $type
	 * @param string $note
	 * @param bool   $more
	 */
	public static function de( $message, $vals = array(), $type = 'ok', $note = 'Method complete', $more = true ) {
		self::$open = self::$open > 0 ? self::$open - 1 : 0;
		self::_d( $message, $vals, $type, $note, $more, 'close' );
	}
	
	/**
	 * Debug message gateway, used when retrieving values via getter
	 * 
	 * @param string $message
	 * @param array  $vals
	 * @param string $type
	 * @param string $note
	 * @param bool   $more
	 */
	public static function dopt( $message, $vals = array(), $type = 'get', $note = 'Returned value', $more = true ) {
		self::_d( $message, $vals, $type, $note, $more );
	}
	
	/**
	 * @param string $message
	 * @param array  $vals
	 * @param string $type
	 * @param string $note
	 * @param bool   $more
	 */
	public static function dset( $message, $vals = array(), $type = 'set', $note = 'Set property', $more = true ) {
		self::_d( $message, $vals, $type, $note, $more );
	}

	/**
	 * Main debug function
	 *
	 * @param string $message
	 * @param array  $vals
	 * @param string $type
	 * @param string $note
	 * @param bool   $more
	 * @param string $group
	 */
	private static function _d( $message, $vals, $type, $note, $more, $group = '' ) {
		
		// avoid a constructor
		if ( self::$timer === 0 ) {
			self::$timer = microtime( true );
			self::$last_time = self::$timer;
		}
		
		$m   = '';
		$tag = 'td';
		
		self::$count++;
		
		$data = self::prep_values( $vals );
		
		// process the passed values
		$data = $more ? self::more( $data ) : $data;
		
		// controller() allows possibility of adding special code to "tree"
		$m .= self::tag( self::controller(), 'psso-debug-controller', $tag, 'div' );
		
		// this will be an open/shut controller for the group
		$m .= self::tag( self::$count, 'psso-debug-num', $tag );
		
		// add time
		$m .= self::tag( self::add_time(), 'psso-debug-time', $tag );
		
		// add code
		$m .= self::tag( self::add_code( $group ), 'psso-debug-code', $tag );
		
		// add note
		$message = $note ? self::note( $note ) . $message : '<br>' . $message;
		
		// add message
		$m .= self::tag( $message . $data, 'psso-debug-message', $tag );
		
		// add query count
		$m .= self::tag( self::query_count(), 'psso-debug-queries', $tag );
		
		// status
		$m .= self::tag( self::success( $type ), 'psso-debug-result', $tag );
		
		// surround with row, add classes for each group
		$gc = empty( self::$groups ) ? '' : ' ' . implode( ' ',  self::$groups );
		$gc .= ( $type == 'get' || $type == 'set' ) ? ' psso-debug-getset' : '';
		$m = '<tr class="' . self::$last_class . $gc . '">' . $m . '</tr>';
		
		// save into the debug array
		self::$debug[] = $m;
	}
	
	
	/**
	 * Adds italicized note on its own line above message
	 *
	 * @param $note
	 *
	 * @return string
	 */
	private static function note( $note ) {
		return '<em>' . $note . '</em><br>';
	}
	
	/**
	 * Creates tag, allowing for tables or UL or ...
	 * 
	 * @param        $content
	 * @param string $class
	 * @param string $tag
	 * @param string $inner
	 *
	 * @return string
	 */
	private static function tag( $content, $class = '', $tag = 'td', $inner = '' ) {
		$ino = $inner ? '<' . $inner . ' class="inner">' : '';
		$inc = $inner ? '</' . $inner . '>' : '';
		return '<' . $tag . ' class="' . $class . '">' . $ino . $content . $inc . '</' . $tag . '>';
	}

	/**
	 * Makes sure the values array sent is in the proper format
	 *
	 * @param array $vals
	 *
	 * @return array
	 */
	private static function prep_values( $vals ) {;
		
		// cast the value to an array if it's a single item
		$vals = ! is_array( $vals ) ? array ( $vals ) : $vals;

		// if it's empty return
		if ( empty( $vals ) ) return '';

		// turn remaining arrays into lists with key/value pairs
		foreach ( $vals as $key => $val ) {
			if ( is_array( $val ) ) {
				$vals[ $key ] = Pearson_SSO_Utilities::array2ul( $val );
			}
		}
		
		return Pearson_SSO_Utilities::array2ul( $vals );
	}

	/**
	 * Hides values behind "more" button
	 *
	 * @param string $values
	 * 
	 * @return string
	 */
	private static function more( $values ) {
		$re = '';
		if ( ! empty( $values ) ) {
			$rand = rand(100000,999999);
			$re = '<a href="#psso' . $rand . '" class="button button-small psso-log-more-button" '
				. 'data-hide="Hide" data-show="Details">Details</a>';
			$re .= '<div id="psso' . $rand . '" class="psso-log-more-value">' . $values . '</div>';
		}
		return $re;
	}
	
	/**
	 * Potential future server-side processing of tree view
	 * 
	 * @return string
	 */
	private static function controller() {
		return '';
	}
	
	/**
	 * Get current query count
	 * 
	 * @return int
	 */
	private static function query_count() {
		$q = get_num_queries();
		
		// if a method is open (ie, sso is working), check if the query count has changed
		if ( self::$open > 0 && $q > self::$qbase ) {
			
			// if it has, add the difference to the total count, and reset the base
			self::$qsso = self::$qsso + ( $q - self::$qbase );
			self::$qbase = $q;
		}
		return self::$qsso;
	}
	
	/**
	 * Returns dash icon to represent success, failure, exit, whatever
	 * 
	 * @param $type
	 *
	 * @return string
	 */
	private static function success( $type ) {
		
		// default to OK
		$ret = 'yes';
		
		// other types
		if ( $type == 'fail' )
			$ret = 'no-alt';
		if ( $type == 'bail' )
			$ret = 'thumbs-down';
		if ( $type == 'success')
			$ret = 'thumbs-up';
		if ( $type == 'bug')
			$ret = 'flag';
		if ( $type == 'get' )
			$ret = 'download';
		if ( $type == 'set' )
			$ret = 'upload';
		if ( $type == 'start' )
			$ret = 'clock';

		// return icon
		return '<span class="dashicons dashicons-' . $ret . '"></span>';
	}

	/**
	 * returns a string of time taken and changes self::$last_time
	 * @return string
	 */
	private static function add_time() {
		
		$time_post       = microtime( true );
		$since_last      = round( ( $time_post - self::$last_time ), 4 );
		
		self::$last_time = $time_post;
		self::$qtime = self::$qtime + $since_last;
		
		return $since_last . '<br>' . self::$qtime;
	}

	/**
	 * Adds debug backtrace to show current calling class and function
	 * 
	 * @param string $group
	 * @return string
	 */
	private static function add_code( $group ) {

		$dbt = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 4 );
		$ret = '';
		$use = 3; 
		
		// remove final group from array
		if ( self::$close_flag ) {
			array_pop( self::$groups );
			self::$close_flag = false;
		}
		
		if ( isset( $dbt[$use]['function'] ) ) {

			$file = 'WordPress';
			if ( isset( $dbt[$use]['file'] ) ) {
				$file = explode( DIRECTORY_SEPARATOR, $dbt[$use]['file'] );
				$file = array_pop( $file );
			}

			$line = isset( $dbt[$use]['line'] ) ? $dbt[$use]['line'] : current_filter();
			
			$ret .= $file . ' (' . $line  . ')<br>';
			
			if ( isset( $dbt[$use]['class'] ) ) {
				$ret .= '<code>' . $dbt[$use]['class'] . '</code>&nbsp;' . $dbt[$use]['type'] . '&nbsp;';
			}
			
			$ret .= '<code>' . $dbt[$use]['function'] . '</code>';

			// put current class and function into var for adding class to row
			self::$last_class = '';
			if ( isset( $dbt[$use]['class'] ) ) {
				self::$last_class = $dbt[ $use ]['class'] . '--';
			}
			self::$last_class .= $dbt[ $use ]['function'];
				
			// adds group to groups array
			if ( $group == 'open' ) {
				$grp = 'psssog-' . rand(1000,9999);
				self::$groups[] = $grp;
				self::$all_groups[] = $grp;
			}

			// set flag so next code call closes the group
			if ( $group == 'close' ) {
				self::$close_flag = true;
			}
		}

		return $ret;
	}
	
	/**
	 * Provides opening and closing HTML for log and test pages.
	 * 
	 * @param string $pos
	 * @param bool   $echo
	 *
	 * @return bool|string
	 */
	public static function html( $pos = 'open', $echo = true ) {

		$hiders = array(
			array(
				'Pearson_SSO--get_option',
				'Pearson_SSO--get_errors',
				'Pearson_SSO--getter',
				'Pearson_SSO--setter',
				'Pearson_SSO--set_user',
				'Pearson_SSO--set_error',
			),
			array(
				'Pearson_SSO_User--get',
				'Pearson_SSO_User--set',
				'Pearson_SSO_User--get_user_roles',
				'Pearson_SSO_User--get_psso_role',
				'Pearson_SSO_User--check_object',
			),
			array(
				'Pearson_SSO_Auth--getter',
				'Pearson_SSO_Auth--setter',
				'Pearson_SSO_Payload--routing_rules_check',
				'Pearson_SSO_Utilities--replace_values',
				'Pearson_SSO_Login--placeholder_districts',
			),
		);
		
		$ret  = '';
		
		if ( $pos == 'open' ) {

			$ret .= '<div id="psso-debug-showhide">';
			$ret .= '<p class="common-calls common-line"><em>Hide Getters/Setters/Iterators</em><span class="psso-showhide-all-span">'
				. '<input type="checkbox" id="psso-showhide-all" checked> Select/Unselect all</span></p>';
			
			$ret .=  '<table class="common-calls"><tr>';
			
			foreach( $hiders as $hider ) {
				$ret .= '<td>';
				foreach ( $hider as $hide ) {
					$ret .= '<span class="psso-showhide-span"><input type="checkbox" data-what="' . $hide
						. '" class="psso-showhide-check" checked><code>'
						. str_replace('--', '</code>::<code>', $hide ) . '</code></span><br>';
				}
				$ret .= '</td>';
			}
			
			$ret .= '</tr></table>';
			
			$ret .= '</div>';

			$ret .= '<table class="psso-log-debug-table">';
			$ret .= '<tr>';
			$ret .= '<th class="psso-debug-control">&nbsp;</th>';
			$ret .= '<th class="psso-debug-num">&nbsp;</th>';
			$ret .= '<th class="psso-debug-time">Time</th>';
			$ret .= '<th class="psso-debug-code">Code Execution</th>';
			$ret .= '<th class="psso-debug-message">Message<span class="psso-mess-all">'
				. '<span>Details:</span> <a href="#all" class="button button-small psso-mess-group">Show All</a> '
				. '<a href="#none" class="button button-small psso-mess-group">Hide All</a>'
				. '</span></th>';
			$ret .= '<th class="psso-debug-queries">Queries</th>';
			$ret .= '<th class="psso-debug-result">OK?</th>';
			$ret .= '</tr>';
		}
		
		else {
			$ret .= '</table>';
		}
		
		if ( $echo ) echo $ret;
		
		return $echo ? false : $ret;
	}
}