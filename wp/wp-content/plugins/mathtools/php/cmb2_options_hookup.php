<?php

class Cmb2_Options_Hookup {

	protected $cmb;

	public function __construct( CMB2 $cmb ) {
		$this->cmb = $cmb;
	}

	public function admin_hooks() {








		// hook in our save notices
		add_action( "cmb2_save_options-page_fields_{$box['id']}", array( $this, 'settings_notices' ), 10, 2 );




	}


	public function settings_notices( $object_id, $updated ) {
		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		add_settings_error( $this->key . '-notices', '', __( 'Settings updated.', 'mathtools' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}




	public function save_settings() {

	}




}