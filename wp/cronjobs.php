<?php
include('wp-load.php');

$cron = get_option( 'cron' );

echo '<p>Cron jobs. Server time is ' . date( 'r', time() ) . '.</p>';

echo '<table border="1" cellpadding="5">';
echo '<tr><th>Scheduled</th><th>Hook</th><th>Run</th><th>Arguments</th><th>Interval (hours)</th></tr>';

foreach ( $cron as $timestamp => $task ) {

	echo '<tr>';
	
	echo '<td>'. date( 'r', intval($timestamp) ) .'</td>';
	
	if ( ! is_array( $task ) ) {
	
		echo '<td colspan="4">Error, not array: ' . $task . '</td>';
		echo '</tr>';
		
	} else {
	
		$counter = 0;
	
		foreach ( $task as $hook => $keys ) {

			if ( $counter > 0 ) {
				echo '<tr><td></td>';
			}
		
			if ( isset( $hook ) ) {
				echo '<td>' . $hook . '</td>';
			} else {
				echo '<td>' . key($task) . '</td>';
			}
		
			
		
			foreach ( $keys as $k => $v ) {
				
				echo '<td>' . $v['schedule'] . '</td>';
				
				echo '<td>' . implode( '<br>', $v['args'] ) . '</td>';
				
				echo '<td>' . ( ( $v['interval'] / 60 ) / 60 ) . '</td>';
			
			}
			
			echo '</tr>';
			
			$counter++;
			
		}
	
	} 

}


echo '</table>';