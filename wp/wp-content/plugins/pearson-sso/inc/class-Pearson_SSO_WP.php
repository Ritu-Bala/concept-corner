<?php

class Pearson_SSO_WP {
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide
	 */
	public static function activate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
			$blog_ids = self::get_blog_ids();
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::single_activate();
				restore_current_blog();
			}
		}
		else {
			self::single_activate();
		}
	}
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean $network_wide
	 */
	public static function deactivate( $network_wide ) {
		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
			$blog_ids = self::get_blog_ids();
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::single_deactivate();
				restore_current_blog();
			}
		}
		else {
			self::single_deactivate();
		}
	}
	
	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int $blog_id ID of the new blog.
	 */
	public static function activate_new_site( $blog_id ) {
		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}
		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}
	
	/**
	 * Get all blog ids of blogs in the current network.
	 *
	 * @since    1.0.0
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {
		global $wpdb;
		$sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";
		return $wpdb->get_col( $sql );
	}
	
	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		Pearson_SSO_Log::create_log_table();
	}
	
	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() { }
	
	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public static function load_plugin_textdomain() {
		$domain = Pearson_SSO::slug();
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, PSSO_URL . '/languages/' );
	}
	
	/**
	 * Register JavaScript files.
	 *
	 * @since 1.5.0
	 */
	public static function register_scripts() {
		wp_register_style(
			Pearson_SSO::slug() . '-ac-css',
			PSSO_URL . 'css/public.css',
			array(),
			Pearson_SSO::get_const( 'VERSION' )
		);
		wp_register_script(
			Pearson_SSO::slug() . '-districts-script',
			PSSO_URL . 'js/districts.js',
			array( 'jquery', 'jquery-ui-autocomplete', ),
			Pearson_SSO::get_const( 'VERSION' ),
			true
		);
		wp_register_script(
			Pearson_SSO::slug() . '-plugin-script',
			PSSO_URL .  'js/public.js',
			array( 'jquery', 'jquery-ui-autocomplete', Pearson_SSO::slug() . '-districts-script', ),
			Pearson_SSO::get_const( 'VERSION' ),
			true
		);
	}
	
	/**
	 * Enqueues JavaScript files.
	 *
	 * @since 1.2.0
	 */
	public static function enqueue_scripts() {
		// only enqueue these if login capture is on or in admin
		if ( Pearson_SSO::get_option('psso_auth_login') == 'on' || is_admin() ) {
			wp_enqueue_style( Pearson_SSO::slug() . '-ac-css' );
			wp_enqueue_script( Pearson_SSO::slug() . '-districts-script' );
			wp_enqueue_script( Pearson_SSO::slug() . '-plugin-script' );
		}
	}
	
	/**
	 * Localize the public JS
	 */
	public static function localize_js() {
		
		Pearson_SSO_Debug::ds( 'Localizing JS with districts' );
		
		$dist = Pearson_SSO::get_option('auth_login_district_require') ? Pearson_SSO::get_option('auth_login_district_require') : 'off';
		
		wp_localize_script(
			Pearson_SSO::slug() . '-plugin-script',
			'pssoOption',
			array( 'dist' => $dist )
		);
		
		Pearson_SSO_Debug::de( '', array( 'pssoOption[dist]' => $dist ) );
	}
	
	/**
	 * Adds SSO to admin menu
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	public static function add_sso_to_admin_menu( $wp_admin_bar ) {
		
		if ( ! is_super_admin()
			|| ! is_object( $wp_admin_bar )
			|| ! function_exists( 'is_admin_bar_showing' )
			|| ! is_admin_bar_showing()
			|| is_admin()
		) {
			return;
		}
		
		$args = array(
			'id'    => Pearson_SSO::slug(),
			'title' => 'Pearson SSO Logs',
			'href'  => network_admin_url( 'admin.php?page=' . Pearson_SSO::slug() . '-logs' ),
			'meta'  => array( 'class' => 'psso-wpadminbar' ),
			'parent' => 'site-name',
		);
		$wp_admin_bar->add_node( $args );
	}
	
	
}