<?php

class Pearson_SSO_Decrypt {
	
	/**
	 * This contains openssl and mcrypt ciphers in format (key matches openssl value):
	 * []['openssl']        = string
	 * []['mcrypt']['alg']  = string
	 * []['mcrypt']['mode'] = string
	 * 
	 * @var array
	 */
	protected static $ciphers = array();
	
	/**
	 * Corresponds to the 
	 * 
	 * @var array
	 */
	protected static $cipher_opts = array();
	
	/**
	 * Local copies of the main class options
	 * 
	 * @var array
	 */
	protected static $options = array();
	
	/**
	 * Original options for reference
	 * 
	 * @var array
	 */
	protected static $orig_options = array();
	
	/**
	 * Decrypt main function
	 * 
	 * @param array $Q   Query vars array
	 *
	 * @return array|bool|mixed|object|string
	 */
	public static function decrypt( $Q ) {

		Pearson_SSO_Debug::ds( 'Public decrypt gateway, expects array of query vars',
			array( 'param $Q' => $Q ) );

		if ( empty( self::$ciphers ) ) self::_setup();
		
		$ret = false;
		
		// query vars always has 'key' and 'payload' set.
		if ( ! ( empty( self::$options ) || ! $Q['key'] || ! $Q['payload'] ) ) {
			
			// see if the short-circuit var was sent
			$short = self::_shortcircuit( $Q );
			
			// limit the cipher arrays if so
			$try = $short ? array( self::$options[ $short ] ) : self::$options;
			
			// try the various ciphers
			foreach ( $try as $key => $opt ) {
				
				// send the info to the cipher
				$ret = self::_try( $opt, $Q['key'], $Q['payload'] );
				
				// make sure we got back actual info
				$ret = $ret ? self::_ok( $ret ) : false;
				
				// if we did, yay, we're done
				if ( $ret !== false ) {
					break;
				}
			}
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'Decryption successful' : 'Decryption failed',
			array( 'returning' => $ret ),
			$ret ? 'ok' : 'fail'
		);

		return $ret;
	}

	/**
	 * Returns options to CMS
	 * @return array
	 */
	public static function cipher_select() {
		
		if ( empty( self::$ciphers ) ) self::_setup();
		
		$c = array();
		foreach ( self::$ciphers as $k => $v ) {
			$val = $v['openssl'];
			if ( ! empty( $v['mcrypt']) ) {
				$val .= ' ( ' . $v['mcrypt']['alg'] . ', ' . $v['mcrypt']['mode'] . ' )';
			}
			$c[ $v['openssl'] ] = strtoupper( $val );
		}
		return $c;
	}

	/**
	 * Returns options for CMB to use
	 * @return array
	 */
	public static function cipher_options_select() {
		
		if ( empty( self::$ciphers ) ) self::_setup();
		
		$c = array();
		foreach ( self::$cipher_opts as $k => $v ) {
			$c[ $k ] = strtoupper( $v['label'] );
		}
		
		return $c;
	}
	
	/**
	 * Sets common vars and grabs options from SSO
	 */
	private static function _setup() {
		
		Pearson_SSO_Debug::ds( 'Decryption class setup, gets ciphers and other vars.');
		
		// set decryption ciphers
		self::$ciphers              = self::_cipher_methods();
		
		// set cipher options
		self::$cipher_opts          = self::_cipher_options();
		
		// set the decrypt options
		self::$options              = Pearson_SSO::get_option( 'psso_decryptgroup' );
		self::$orig_options['k']    = Pearson_SSO::get_option( 'psso_key' );
		self::$orig_options['kp']   = Pearson_SSO::get_option( 'psso_key_pass' );
		
		Pearson_SSO_Debug::de(
			'Decryption setup complete. Set properties under details.',
			array( 
				'self::$ciphers' => self::$ciphers,
				'self::$cipher_opts' => self::$cipher_opts,
				'self::$options' => self::$options,
				'self::$orig_options' => self::$orig_options,
			)
		);
	}

	/**
	 * Returns legit OpenSSL ciphers
	 * @return array
	 */
	private static function _cipher_methods() {
		$ciphers = openssl_get_cipher_methods();
		$ciphers = array_map( 'strtolower', $ciphers );
		$ciphers = array_unique( $ciphers );
		$r = array();
		foreach ( $ciphers as $c ) {
			$r[$c] = array( 'openssl' => $c );
		}

		$r = self::_mcrypt_map( $r );
		return $r;
	}

	/**
	 * Returns cipher aliases corresponding to padding
	 * @return array
	 */
	private static function _cipher_options() {
		return array(
			'raw'  => array(
				'label' => 'Raw Data',
				'value' => OPENSSL_RAW_DATA,
			),
			'zero' => array(
				'label' => 'Zero Padding',
				'value' => OPENSSL_ZERO_PADDING,
			),
		);
	}

	/**
	 * Checks to see if a query var matches a short-circuit set in options
	 *
	 * @param $Q
	 *
	 * @return bool|int|string
	 */
	private static function _shortcircuit( $Q ) {
		
		Pearson_SSO_Debug::ds( 'Checks for "short circuit" query var' );
		
		$ret = false;
		
		if ( ! empty( $Q ) ) {
			
			foreach ( $Q as $sc => $val ) {
				
				if ( $sc == 'key' || $sc == 'payload' ) {
					continue;
				}
				
				foreach ( self::$options as $k => $o ) {
					if ( isset( $o['shortcircuit'] ) && $o['shortcircuit'] == $sc ) {
						$ret = $k;
						break;
					}
				}
			}
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'Found short-circuit' : 'No short-circuit',
			array( 'returning' => $ret ),
			$ret ? 'ok' : 'fail'
		);

		return $ret;
	}

	/**
	 * Wrapper for decrypting
	 *
	 * @param $opt
	 * @param $K
	 * @param $P
	 *
	 * @return bool|string
	 */
	private static function _try( $opt, $K, $P ) {
		
		Pearson_SSO_Debug::ds( 
			'Try decryption using options array', 
			array( 
				'param $opt' => $opt,
				'param $K' => $K,
				'param $P' => $P,
			) 
		);
		
		$ret = false;
		$payload_key = '';

		$key = $K;
		$pay = $P;

		// should we URL decode these?
		$key = $opt['rawurl'] ? rawurldecode( $key ) : $key;
		$pay = $opt['rawurl'] ? rawurldecode( $pay ) : $pay;
		
		// should we base64 decode them?
		$key = $opt['base64'] ? base64_decode( $key ) : $key;
		$pay = $opt['base64'] ? base64_decode( $pay ) : $pay;

		// convert key if necessary
		if ( isset( $opt['key_format'] ) && $opt['key_format'] == 'der' ) {
			$opt['key'] = self::_der2pem( $opt['key'] );
		}

		// prep the key via openssl function
		$sslkey = self::_get_private_key( $opt['key'], $opt['pass'] );
		
		// this decrypts the payload key
		openssl_private_decrypt( $key, $payload_key, $sslkey );
		
		// the key worked
		if ( $payload_key ) {
			
			// decrypt using mcrypt
			if ( isset( $opt['func'] ) && $opt['func'] == 'mcrypt' ) {
				$ret = self::_mcrypt(
					$pay,
					$payload_key,
					self::$ciphers[ $opt['cipher'] ],
					$opt['iv'],
					$opt['iv_trim']
				);
			}
			
			// decrypt using openssl
			else {
				$ret = self::_decrypt(
					$pay,
					$payload_key,
					self::$ciphers[ $opt['cipher'] ],
					self::$cipher_opts[ $opt['cipher_opts'] ]['value'],
					$opt['iv'],
					$opt['iv_trim']
				);
			}
		}

		Pearson_SSO_Debug::de(
			$ret ? 'Config successful' : 'Config not successful',
			array( ! $payload_key ? 'errors' : 'returning' => $ret ? $ret : ! $payload_key ? self::_getoerr() : false ),
			$ret ? 'ok' : 'fail'
		);
		
		return $ret;
	}
	
	/**
	 * Checks to see if the decrypted values are JSON
	 *
	 * @param $check
	 *
	 * @return array|bool|mixed|object
	 */
	private static function _ok( $check ) {

		Pearson_SSO_Debug::ds( 'Checks if decrypted values are actually JSON', array( 'param $check' => $check ) );
		
		$ret = false;

		// check for a closing bracket, as decoded payload should be JSON
		$end_pos = strpos( $check, "}" );

		if ( $end_pos !== false ) {

			// eliminate trailing whitespace
			$end_pos = $end_pos + 1;
			$temp = substr( $check, 0, $end_pos );

			// JSON decode it
			$json = json_decode( $temp );

			// if there is a JSON error, report it
			if ( json_last_error() ) {
				Pearson_SSO_Debug::d(
					'JSON Parsing Error',
					array( 'error' => json_last_error_msg() ),
					'fail'
				);
			} else {
				$ret = $json;
			}
			
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'JSON returned successfully' : 'Decrypted string was not JSON',
			array( 'returning' => $ret ),
			$ret ? 'ok' : 'fail'
		);

		return $ret;
	}

	/**
	 * @param        $pay
	 * @param        $key
	 * @param        $method
	 * @param        $options
	 * @param string $iv
	 * @param string $iv_trim
	 *
	 * @return bool|string
	 */
	private static function _decrypt( $pay, $key, $method, $options, $iv = '', $iv_trim = 'yes' ) {

		Pearson_SSO_Debug::ds(
			'OpenSSL Decryption',
			array(
				'param $pay' => $pay,
				'param $key' => $key,
				'param $method' => $method,
				'param $options' => $options,
				'param $iv' => $iv,
				'param $iv_trim' => $iv_trim,
			)
		);
		
		$OPEN = $method['openssl'];
		
		// get the IV and its size
		$iv = self::_prepareIV( $iv, $OPEN, $pay );
		
		// remove the IV from front of payload
		$pay = $iv_trim == 'yes' ? mb_substr( $pay, $iv['size'], null ) : $pay;
		
		// decrypt using openssl
		$de = openssl_decrypt( $pay, $OPEN, $key, $options, $iv['iv'] );
		
		Pearson_SSO_Debug::de(
			$de ? 'Decryption successful' : 'Decryption failed',
			array( $de ? 'returning' : 'errors' => $de ? $de : self::_getoerr() ),
			$de ? 'ok' : 'fail'
		);
		
		return $de;
	}

	/**
	 * Olde School, in case OpenSSL s-t-b
	 *
	 * @param        $pay
	 * @param        $key
	 * @param        $method
	 * @param string $iv
	 * @param string $iv_trim
	 *
	 * @return string
	 */
	private static function _mcrypt( $pay, $key, $method, $iv = '', $iv_trim = 'yes' ) {
		
		Pearson_SSO_Debug::ds( 
			'Mcrypt decryption', 
			array( 
				'param $pay' => $pay,
				'param $key' => $key,
				'param $method' => $method,
				'param $iv' => $iv,
				'param $iv_trim' => $iv_trim,
			)
		);
		
		$ret = false;
		
		if ( ! empty( $method['mcrypt']['alg'] ) ) {
			
			$ALG  = $method['mcrypt']['alg'];
			$MODE = $method['mcrypt']['mode'];
			$rtrim = "\0";
			
			$iv = self::_prepareIV( $iv, $method['openssl'], $pay );
			
			$this_iv = $iv['iv'] ? $iv['iv'] : self::_nullpad( $iv['size'] );
			
			$pay = $iv_trim == 'yes' ? mb_substr( $pay, $iv['size'], null ) : $pay;

			$ret = rtrim( mcrypt_decrypt( $ALG, $key, $pay, $MODE, $this_iv ), $rtrim );
		}
		
		Pearson_SSO_Debug::de(
			$ret ? 'Decrypted successfully' : 'Failed decryption',
			array( empty( $method['mcrypt']['alg'] ) ? 'error' : 'returning' => $ret ? 
				$ret : empty( $method['mcrypt']['alg'] ) ? 'No mCrypt algorythm for this method' : false ),
			$ret ? 'ok' : 'fail'
		);
		
		return $ret;
	}
	
	/**
	 * Prepare the IV
	 * 
	 * @param $iv
	 * @param $lookup
	 * @param $pay
	 *
	 * @return array|bool
	 */
	private static function _prepareIV( $iv, $lookup, $pay ) {

		Pearson_SSO_Debug::ds(
			'Prepares IV',
			array(
				'param $iv' => $iv,
				'param $lookup' => $lookup,
				'param $pay' => $pay
			)
		);

		$ret = array();
		
		// get the IV size
		$ret['size'] = self::_getIVsize( $lookup );
		
		// if an IV string was sent
		if ( $iv ) {
			// remove escapes
			$ret['iv'] = stripslashes( $iv );
			
			// check that its size matches the method's
			if ( mb_strlen( $ret['iv'] ) != $ret['size'] ) {
				$ret = false;
			}
		}

		// no IV was sent, so we need to get it from front of string
		else {
			$ret['iv'] =  mb_substr( $pay, 0, $ret['size'] );
		}
		
		Pearson_SSO_Debug::de(
			empty( $ret ) ? 'Could not return IV' : 'IV prep successful',
			array( 'returning' => $ret ),
			empty( $ret ) ? 'fail' : 'ok'
		);
		
		return $ret;
	}
	
	/**
	 * Creates a null IV of appropriate length
	 * 
	 * @param $length
	 *
	 * @return string
	 */
	private static function _nullpad( $length ) {
		Pearson_SSO_Debug::d( 'Creating null-padding IV of bytes: ' . $length );
		$r = "";
		for ( $a = 0; $a < $length; $a++ ) {
			$r .= "\0";
		}
		return $r;
	}

	/**
	 * Returns prepared private key
	 *
	 * @param        $key
	 * @param string $pass
	 *
	 * @return bool|resource
	 */
	private static function _get_private_key( $key, $pass = '' ) {
		return openssl_pkey_get_private( $key, $pass );
	}

	/**
	 * Returns cipher IV length
	 *
	 * @param $method
	 *
	 * @return int
	 */
	private static function _getIVsize( $method ) {
		$s = openssl_cipher_iv_length( $method );
		Pearson_SSO_Debug::d( $method . ': ' . $s );
		return $s;
	}
	
	/**
	 * Get openssl errors. Does not seem to consistently work
	 * @return string
	 */
	private static function _getoerr() {
		$ret = 'Starting error read<ul>';
		while ( $msg = openssl_error_string() ) {
			$ret .= '<li>' . $msg . '</li>';
		}
		$ret .= '</ul>';
		return $ret;
	}
	
	/**
	 * Thanks: http://php.net/manual/en/ref.openssl.php#74188
	 *
	 * @param $der_data
	 *
	 * @return string
	 */
	private static function _der2pem( $der_data ) {
		$pem = chunk_split( base64_encode( $der_data ), 64, "\n" );
		$pem = "-----BEGIN CERTIFICATE-----\n" . $pem . "-----END CERTIFICATE-----\n";
		return $pem;
	}


	/**
	 * Maps mcrypt methos to openssl methods
	 *
	 * @param $ciphers
	 *
	 * @return mixed
	 */
	private static function _mcrypt_map( $ciphers ) {

		$mmap = array(
			'aes-128-cbc'   => array( 'alg' => 'rijndael-128', 'mode' => 'cbc' ),
			'aes-128-cfb'   => array( 'alg' => 'rijndael-128', 'mode' => 'ncfb' ),
			'aes-128-cfb8'  => array( 'alg' => 'rijndael-128', 'mode' => 'cfb' ),
			'aes-128-ecb'   => array( 'alg' => 'rijndael-128', 'mode' => 'ecb' ),
			'aes-128-ofb'   => array( 'alg' => 'rijndael-128', 'mode' => 'nofb' ),
			'aes-192-cbc'   => array( 'alg' => 'rijndael-128', 'mode' => 'cbc' ),
			'aes-192-cfb'   => array( 'alg' => 'rijndael-128', 'mode' => 'ncfb' ),
			'aes-192-cfb8'  => array( 'alg' => 'rijndael-128', 'mode' => 'cfb' ),
			'aes-192-ecb'   => array( 'alg' => 'rijndael-128', 'mode' => 'ecb' ),
			'aes-192-ofb'   => array( 'alg' => 'rijndael-128', 'mode' => 'nofb' ),
			'aes-256-cbc'   => array( 'alg' => 'rijndael-128', 'mode' => 'cbc' ),
			'aes-256-cfb'   => array( 'alg' => 'rijndael-128', 'mode' => 'ncfb' ),
			'aes-256-cfb8'  => array( 'alg' => 'rijndael-128', 'mode' => 'cfb' ),
			'aes-256-ecb'   => array( 'alg' => 'rijndael-128', 'mode' => 'ecb' ),
			'aes-256-ofb'   => array( 'alg' => 'rijndael-128', 'mode' => 'nofb' ),
			'bf-cbc'        => array( 'alg' => 'blowfish', 'mode' => 'cbc' ),
			'bf-cfb'        => array( 'alg' => 'blowfish', 'mode' => 'ncfb' ),
			'bf-ecb'        => array( 'alg' => 'blowfish', 'mode' => 'ecb' ),
			'bf-ofb'        => array( 'alg' => 'blowfish', 'mode' => 'nofb' ),
			'cast5-cbc'     => array( 'alg' => 'cast-128', 'mode' => 'cbc' ),
			'cast5-cfb'     => array( 'alg' => 'cast-128', 'mode' => 'ncfb' ),
			'cast5-ecb'     => array( 'alg' => 'cast-128', 'mode' => 'ecb' ),
			'cast5-ofb'     => array( 'alg' => 'cast-128', 'mode' => 'nofb' ),
			'des-cbc'       => array( 'alg' => 'des', 'mode' => 'cbc' ),
			'des-cfb'       => array( 'alg' => 'des', 'mode' => 'ncfb' ),
			'des-cfb8'      => array( 'alg' => 'des', 'mode' => 'cfb' ),
			'des-ecb'       => array( 'alg' => 'des', 'mode' => 'ecb' ),
			'des-ede'       => array( 'alg' => 'tripledes', 'mode' => 'ecb' ),
			'des-ede-cbc'   => array( 'alg' => 'tripledes', 'mode' => 'cbc' ),
			'des-ede-cfb'   => array( 'alg' => 'tripledes', 'mode' => 'ncfb' ),
			'des-ede-ofb'   => array( 'alg' => 'tripledes', 'mode' => 'nofb' ),
			'des-ede3'      => array( 'alg' => 'tripledes', 'mode' => 'ecb' ),
			'des-ede3-cbc'  => array( 'alg' => 'tripledes', 'mode' => 'cbc' ),
			'des-ede3-cfb'  => array( 'alg' => 'tripledes', 'mode' => 'ncfb' ),
			'des-ede3-cfb8' => array( 'alg' => 'tripledes', 'mode' => 'cfb' ),
			'des-ede3-ofb'  => array( 'alg' => 'tripledes', 'mode' => 'nofb' ),
			'des-ofb'       => array( 'alg' => 'des', 'mode' => 'nofb' ),
			'rc4-40'        => array( 'alg' => 'arcfour', 'mode' => 'stream' ),
		);

		foreach( $ciphers as $ckey => $c ) {
			foreach ( $mmap as $mkey => $m ) {
				if ( $mkey == $c['openssl'] ) {
					$ciphers[ $ckey ]['mcrypt'] = $m;
					continue;
				}
			}
		}

		return $ciphers;
	}
}