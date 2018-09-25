<?php
/**
 * Created by PhpStorm.
 * User: roger
 * Date: 9/24/2014
 * Time: 2:25 AM
 */

// load wordpress

define('WP_USE_THEMES', false);
include( '../../../../wp-load.php' );

$student_base = 'student';
$teacher_base = 'teacher';

$start = 101;
$end = 200;

if ( isset( $_GET['start'] ) && is_numeric( $_GET['start'] ) ) {
	$start = (int) $_GET['start'];
}
if ( isset( $_GET['total'] ) && is_numeric( $_GET['total'] ) ) {
	$end = (int) $_GET['total'] + $start;
}

for ( $a = $start; $a <= $end; $a++ ) {

	$user = $teacher_base . $a;
	$userrole = 'cc_teacher';

	$bullshit = username_exists( $user );

	if ( $bullshit !== null ) {
		echo $bullshit; die();
	}

	$user_id = wp_create_user( $user, $user . 'test', 'ccsocuser+' . $user . '@gmail.com' );
	if ( is_wp_error( $user_id ) ) {

		echo $user_id->get_error_message();
		die(' teacher');

	}
	$new_user = new WP_User( $user_id );
	$new_user->set_role( $userrole );


	$user2 = $student_base . $a;
	$userrole2 = 'cc_student';

	$bullshit = username_exists( $user2 );

	if ( $bullshit !== null ) {
		echo $bullshit; die();
	}

	$user_id2 = wp_create_user( $user2, $user2 . 'test', 'ccsocuser+' . $user2 . '@gmail.com' );
	if ( is_wp_error( $user_id2 ) ) {

		echo $user_id2->get_error_message();
		die(' student');

	}
	$new_user2 = new WP_User( $user_id2 );
	$new_user2->set_role( $userrole2 );

}



echo 'done';