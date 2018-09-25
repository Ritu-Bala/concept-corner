<?php

class Mathtools_Cmb2_Hookup extends CMB2_hookup {

	public function admin_hooks() {

		global $pagenow;

		if ( $pagenow !== 'edit.php' )
			return;

		var_dump( get_current_screen() );

		var_dump( $this->cmb );
		die();


		/*
				$this->object_type = $this->cmb->mb_object_type();

				if ( 'post' == $this->object_type && ) {

					// check to

					add_action( 'add_meta_boxes', array( $this, 'add_metaboxes' ) );
					add_action( 'add_attachment', array( $this, 'save_post' ) );
					add_action( 'edit_attachment', array( $this, 'save_post' ) );
					add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

					$this->once( 'admin_enqueue_scripts', array( $this, 'do_sipts' ) );

				}


			}

		/*	public function add_metaboxes() {

			}


		*/
	}
}