<?php
/**
 * ADMIN OPTIONS
 */
function S3MSAdminMenu() {
	add_options_page( 'S3 Media Storage', 'S3 Media Storage', 'edit_posts', 'S3MSAdminMenu', 'S3MSAdminContent' );
}

add_action( 'admin_menu', 'S3MSAdminMenu' );

if ( ! class_exists( 'S3' ) ) {
	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'S3.php';
}

function S3MSAdminContent() {

	global $wpdb;

	if ( isset( $_POST['submit'] ) ) {
		$errors = array();

		if ( ! isset( $_POST['s3_bucket'] ) || trim( $_POST['s3_bucket'] ) == '' ) {
			$errors[] = "S3 Bucket Name Missing!";
		}

		if ( ! isset( $_POST['s3_access_key'] ) || trim( $_POST['s3_access_key'] ) == '' ) {
			$errors[] = "S3 Access Key Missing!";
		}

		if ( ! isset( $_POST['s3_secret_key'] ) || trim( $_POST['s3_secret_key'] ) == '' ) {
			$errors[] = "S3 Secret Key Missing!";
		}

		$bucket     = trim( $_POST['s3_bucket'] );
		$access_key = trim( $_POST['s3_access_key'] );
		$secret_key = trim( $_POST['s3_secret_key'] );
		$ssl        = isset( $_POST['s3_ssl'] ) ? 1 : 0;

		// Test connectivity
		require_once dirname( __FILE__ ) . '/S3.php';
		$s3 = new S3( $access_key, $secret_key );
		$s3->setSSL( (bool) $ssl );
		$s3->setExceptions( true );

		try {
			$s3->getBucketLocation( $bucket );
		}
		catch ( Exception $e ) {
			$errors[] = "Could not connect to bucket with the provided credentials!";
			$errors[] = $e->getMessage();
		}

		if ( ! empty( $errors ) ) {

			$msg = implode( '<br>', $errors );
			echo '<div class="error"><p><strong>' . _e( $msg, 'S3MS' ) . '</strong></p></div>';

		} else {

			// No errors!

			$settings = array(
				's3_bucket'       => trim( $_POST['s3_bucket'] ),
				's3_bucket_path'  => isset( $_POST['s3_bucket_path'] ) ? ltrim( rtrim( trim( $_POST['s3_bucket_path'] ), '/' ), '/' ) : '',
				's3_access_key'   => trim( $_POST['s3_access_key'] ),
				's3_secret_key'   => trim( $_POST['s3_secret_key'] ),
				's3_ssl'          => isset( $_POST['s3_ssl'] ) ? 1 : 0,
				's3_delete_local' => isset( $_POST['s3_delete_local'] ) ? 1 : 0,
				's3_delete'       => isset( $_POST['s3_delete'] ) ? 1 : 0,
				's3_expires'      => trim( $_POST['s3_expires'] ),
				's3_cloudfront'   => trim( $_POST['s3_cloudfront'] ),
				's3_protocol'     => in_array( trim( $_POST['s3_protocol'] ), array(
					'http',
					'https',
					'relative'
				) ) ? trim( $_POST['s3_protocol'] ) : 'relative',
				'valid'           => 1,
				's3_table_limit'  => trim( $_POST['s3_table_limit'] ),
				's3_jwpmeta'      => trim( $_POST['s3_jwpmeta'] ),
			);

			$settings = json_encode( $settings );
			update_option( 'S3MS_settings', $settings );

			echo '<div class="updated"><p><strong>' . _e( "Settings Saved!", "S3MS" ) . '</strong></p></div>';
		}
	}

	if ( isset( $_POST['purge'] ) ) {
		$wpdb->query( "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key LIKE 'S3MS_%'" );
	}

	if ( isset( $_POST['db_s3_sync'] ) ) {

		$label = 'Updated';

		if ( isset( $_POST['selected'] ) && is_array( $_POST['selected'] ) ) {

			$success_count = 0;
			$error_count   = 0;
			foreach ( $_POST['selected'] as $key => $id ) {

				// if file exists but DB doesn't, update DB

				if ( $_POST['s3sync'][ $key ] == 'file' ) {
					update_post_meta( $id, "S3MS_bucket", $_POST['s3s3_bucket'] );
					update_post_meta( $id, "S3MS_bucket_path", $_POST['s3s3_bucket_path'] );
					update_post_meta( $id, "S3MS_file", $_POST['s3file'][ $key ] );
					update_post_meta( $id, "S3MS_cloudfront", $_POST['s3s3_cloudfront'] );

					$success_count ++;

					// if DB exists but file doesn't, delete DB

				} else if ( $_POST['s3sync'][ $key ] == 'nofile' ) {

					$wpdb->query( "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key LIKE 'S3MS_%' AND post_id='" . $id . "'" );
					$success_count ++;
				}
			}

			echo '<div class="updated"><p><strong>' . _e( number_format( $success_count ) . ' File(s) ' . $label . '!', 'S3MS' ) . '</strong></p></div>';

			if ( $error_count > 0 ) {
				echo '<div class="error"><p><strong>' . _e( number_format( $error_count ) . ' File(s) Could Not Be ' . $label . '!', 'S3MS' ) . '</strong></p></div>';
			}
		}
	}

	if ( isset( $_POST['move_files'] ) || isset( $_POST['copy_files'] ) ) {

		$move  = isset( $_POST['move_files'] ) ? true : false;
		$label = isset( $_POST['move_files'] ) ? 'Moved' : 'Copied';

		if ( isset( $_POST['selected'] ) && is_array( $_POST['selected'] ) ) {

			$success_count = 0;
			$error_count   = 0;

			foreach ( $_POST['selected'] as $id ) {

				$ret = s3_update_attachment_metadata( array( 'S3MS_move' => $move ), $id );

				if ( $ret ) {
					$success_count ++;
				} else {
					$error_count ++;
				}
			}

			echo '<div class="updated"><p><strong>' . _e( number_format( $success_count ) . ' File(s) ' . $label . '!', 'S3MS' ) . '</strong></p></div>';

			if ( $error_count > 0 ) {
				echo '<div class="error"><p><strong>' . _e( number_format( $error_count ) . ' File(s) Could Not Be ' . $label . '!', 'S3MS' ) . '</strong></p></div>';
			}
		}
	}

	// Get existing/POST options

	$settings = json_decode( get_option( 'S3MS_settings' ), true );

	$s3_bucket = isset( $_POST['s3_bucket'] ) ? trim( $_POST['s3_bucket'] ) : null;
	if ( ! $s3_bucket && is_array( $settings ) && isset( $settings['s3_bucket'] ) ) {
		$s3_bucket = $settings['s3_bucket'];
	}

	$s3_bucket_path = isset( $_POST['s3_bucket_path'] ) ? trim( $_POST['s3_bucket_path'] ) : null;
	if ( ! $s3_bucket_path && is_array( $settings ) && isset( $settings['s3_bucket_path'] ) ) {
		$s3_bucket_path = $settings['s3_bucket_path'];
	}

	$s3_access_key = isset( $_POST['s3_access_key'] ) ? trim( $_POST['s3_access_key'] ) : null;
	if ( ! $s3_access_key && is_array( $settings ) && isset( $settings['s3_access_key'] ) ) {
		$s3_access_key = $settings['s3_access_key'];
	}

	$s3_secret_key = isset( $_POST['s3_secret_key'] ) ? trim( $_POST['s3_secret_key'] ) : null;
	if ( ! $s3_secret_key && is_array( $settings ) && isset( $settings['s3_secret_key'] ) ) {
		$s3_secret_key = $settings['s3_secret_key'];
	}

	$s3_ssl = isset( $_POST['s3_ssl'] ) ? (int) $_POST['s3_ssl'] : null;
	if ( ! $s3_ssl && is_array( $settings ) && isset( $settings['s3_ssl'] ) ) {
		$s3_ssl = (int) $settings['s3_ssl'];
	}

	$s3_delete_local = isset( $_POST['s3_delete_local'] ) ? (int) $_POST['s3_delete_local'] : null;
	if ( ! $s3_delete_local && is_array( $settings ) && isset( $settings['s3_delete_local'] ) ) {
		$s3_delete_local = (int) $settings['s3_delete_local'];
	}

	$s3_delete = isset( $_POST['s3_delete'] ) ? (int) $_POST['s3_delete'] : null;
	if ( ! $s3_delete && is_array( $settings ) && isset( $settings['s3_delete'] ) ) {
		$s3_delete = (int) $settings['s3_delete'];
	}

	$s3_expires = isset( $_POST['s3_expires'] ) ? trim( $_POST['s3_expires'] ) : null;
	if ( ! $s3_expires && is_array( $settings ) && isset( $settings['s3_expires'] ) ) {
		$s3_expires = $settings['s3_expires'];
	}

	$s3_cloudfront = isset( $_POST['s3_cloudfront'] ) ? trim( $_POST['s3_cloudfront'] ) : null;
	if ( ! $s3_cloudfront && is_array( $settings ) && isset( $settings['s3_cloudfront'] ) ) {
		$s3_cloudfront = $settings['s3_cloudfront'];
	}

	$s3_protocol = isset( $_POST['s3_protocol'] ) ? trim( $_POST['s3_protocol'] ) : null;
	if ( ! $s3_protocol && is_array( $settings ) && isset( $settings['s3_protocol'] ) ) {
		$s3_protocol = $settings['s3_protocol'];
	}

	$s3_table_limit = isset( $_POST['s3_table_limit'] ) ? trim( $_POST['s3_table_limit'] ) : null;
	if ( ! $s3_table_limit && is_array( $settings ) && isset( $settings['s3_table_limit'] ) ) {
		$s3_table_limit = $settings['s3_table_limit'];
	}

	$s3_jwpmeta = isset( $_POST['s3_jwpmeta'] ) ? trim( $_POST['s3_jwpmeta'] ) : '';
	if ( ! $s3_jwpmeta && is_array( $settings ) && isset( $settings['s3_jwpmeta'] ) ) {
		$s3_jwpmeta = $settings['s3_jwpmeta'];
	}
	?>
	<div class="wrap">
	<h2>S3 Media Storage Options</h2>

	<div id="poststuff">
		<div class="postbox">
			<h3><?php _e( 'Settings' ); ?></h3>

			<div class="inside">
				<form id="S3MS-config" method="post" action="" enctype="multipart/form-data">

					<table class="form-table">
						<tbody>
						<tr>
							<th><label for="key"><?php _e( "S3 Bucket Name:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:300px;" type="text" name="s3_bucket" value="<?php echo $s3_bucket; ?>" placeholder="Enter S3 Bucket Name e.g. media.myblog" />
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "S3 Bucket Path:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:300px;" type="text" name="s3_bucket_path" value="<?php echo $s3_bucket_path; ?>" placeholder="Enter Additional S3 Bucket Path e.g. blog or blog/assets" />

								<p class="description">If 'blog' is entered, uploads go to https://bucketname.s3.amazonaws.com/blog/YYYY/MM/ file.ext </p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "S3 Access Key:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:400px;" type="text" name="s3_access_key" value="<?php echo $s3_access_key; ?>" placeholder="Enter S3 Access Key" />
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "S3 Secret Key:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:400px;" type="text" name="s3_secret_key" value="<?php echo $s3_secret_key; ?>" placeholder="Enter S3 Secret Key" />
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "Use SSL:", 'S3MS' ); ?></label></th>
							<td>
								<input type="checkbox" name="s3_ssl" value="1" <?php echo ( $s3_ssl ) ? 'checked="checked"' : ''; ?>/>

								<p class="description">Encrypt traffic for data sent to S3?</p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "Delete Local Files:", 'S3MS' ); ?></label></th>
							<td>
								<input type="checkbox" name="s3_delete_local" value="1" <?php echo ( $s3_delete_local ) ? 'checked="checked"' : ''; ?>/>

								<p class="description">Whether or not to keep files uploaded locally</p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "DELETE FROM S3:", 'S3MS' ); ?></label></th>
							<td>
								<input type="checkbox" name="s3_delete" value="1" <?php echo ( $s3_delete ) ? 'checked="checked"' : ''; ?>/>

								<p class="description">Deleting from Media Library deletes from S3?</p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "Expires:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:400px;" type="text" name="s3_expires" value="<?php echo $s3_expires; ?>" placeholder="Enter expires format" />

								<p class="description">To set far reaching expires for assets, enter it in a
									<a href="http://us1.php.net/manual/en/datetime.formats.php" target="_blank">valid strtotime format</a> e.g. +15 years
								</p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "CloudFront Domain Name:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:400px;" type="text" name="s3_cloudfront" value="<?php echo $s3_cloudfront; ?>" placeholder="Enter CloudFront Domain Name" />

								<p class="description">e.g.abcslfn3kg17h.cloudfront.net</p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "Protocol:", 'S3MS' ); ?></label></th>
							<td>
								<input type="radio" name="s3_protocol" value="http" <?php echo ( $s3_protocol == 'http' ) ? 'checked="checked"' : ''; ?>/> Always serve from HTTP.<br />
								<input type="radio" name="s3_protocol" value="https" <?php echo ( $s3_protocol == 'https' ) ? 'checked="checked"' : ''; ?>/> Always serve from HTTPS.<br />
								<input type="radio" name="s3_protocol" value="relative" <?php echo ( $s3_protocol == 'relative' ) ? 'checked="checked"' : ''; ?>/> Serve from same protocol as requested page.<br />
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "JW Player Poster Image Meta Key:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:400px;" type="text" name="s3_jwpmeta" value="<?php echo $s3_jwpmeta; ?>" placeholder="jwplayermodule_thumbnail" />

								<p class="description">This is the postmeta key JWPlayer uses to save poster images.If you set this option, when jwplayer requests the meta field, it will be given the S3 URL instead of an attachment ID if the S3 file exists.</p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "Purge Database:", 'S3MS' ); ?></label></th>
							<td>
								<input type="submit" name="purge" id="purge" class="button button-primary" value="<?php _e( 'Purge Database' ); ?>"><br>

								<p class="description">This will remove all S3 Media Storage data from the postmeta.If you moved your files, your media library will be broken!</p>
							</td>
						</tr>
						<tr>
							<th><label for="key"><?php _e( "Limit for table, below:", 'S3MS' ); ?></label></th>
							<td>
								<input style="width:100px;" type="text" name="s3_table_limit" value="<?php echo $s3_table_limit; ?>" placeholder="Number" />

								<p class="description">The check to be really sure the file exists on S3 is expensive, so you may want to limit the rows shown below.</p>
							</td>
						</tr>
						</tbody>
					</table>

					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>">
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php

	echo '<div id="poststuff">';
	echo '<div class="postbox">';

	echo '<h3>'. _e( 'Library Files' ).'</h3>';

	echo '<div class="inside">';
	echo '<form id="S3MS-transfer" method="post" action="" enctype="multipart/form-data">';
	echo '<input type="hidden" name="transfer" value="1" />';

	// While we could use get_posts and get_post_meta instead of a custom query, it would mean more queries/data than necessary
	// So lets just do our own query.

	$page   = isset( $_GET['s3ms_page'] ) ? (int) $_GET['s3ms_page'] : 1;
	$limit  = $s3_table_limit;
	$offset = ( $limit * $page ) - $limit;

	$sql= "
		SELECT COUNT(1) as count
		FROM {$wpdb->posts}
		LEFT JOIN {$wpdb->postmeta} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID AND {$wpdb->postmeta}.meta_key = 'S3MS_file'
		WHERE 1=1
		AND {$wpdb->posts}.post_type = 'attachment'
		AND ({$wpdb->posts}.post_status = 'inherit')
		";

	$r     = $wpdb->get_row( $sql );
	$total = $r->count;

	$page_action = s3ms_pagination_url();

	$sql = "
		SELECT p.ID, n.meta_value as 'attachURL', p.guid, m.meta_value as 'S3MS_file'
		FROM {$wpdb->posts} p
		LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = 'S3MS_file'
		LEFT JOIN {$wpdb->postmeta} n ON n.post_id = p.ID AND n.meta_key = '_wp_attached_file'
		WHERE 1=1
		AND p.post_type = 'attachment'
		AND p.post_status = 'inherit'
		ORDER BY p.post_date DESC
		LIMIT $offset, $limit
		";

	$files = $wpdb->get_results( $sql );

	$ud       = wp_upload_dir();

	echo '<table class="wp-list-table widefat">';
	echo '<thead><tr>';
	echo '<th scope="col"><input type="checkbox" id="select-all" /></th>';
	echo '<th scope="col">ID</span></th>';

	echo '<th scope="col">Attachment</span></th>';
	echo '<th scope="col">Exists Locally?</th>';
	echo '<th scope="col">S3 File (no entry: serving local file)</span></th>';
	echo '<th scope="col">Exists On S3?</span></th>';
	echo '</tr></thead><tfoot>';
	echo '<tr><th colspan="5" scope="row"><p style="float:left">' . s3ms_pagination( $total, $limit, $page, $page_action ) . '</p>&nbsp;</th></tr></tfoot><tbody>';

	$keycounter = 0;

	foreach ( $files as $file ) {

		$attachment_url  = '';
		$attachment_path = '';


		if ( strstr( $file->attachURL, 'amazonaws.com' ) ) {
			$attachment_url = s3_attachment_url( $file->attachURL, $file->ID );
		} else {
			$attachment_url  = $ud['baseurl'] . '/' . $file->attachURL;
			$attachment_path = $ud['basedir'] . '/' . $file->attachURL;
		}

		echo '<tr><td>';
		echo '<input type="checkbox" class="files" name="selected[' . $keycounter . ']" value="' . $file->ID . '">';
		echo '</td><td>';
		echo '<a href="' . admin_url() . 'post.php?post=' . $file->ID . '&action=edit">' . $file->ID . '</a>';
		echo '</td><td>';
		echo '<a href="' . $attachment_url . '" target="_blank">' . $attachment_url . '</a>';
	//	var_dump( $file );
		echo '</td><td>';

		$local_exists = file_exists( $attachment_path );
		echo $local_exists ? '&#10003;' : '';

		echo '</td><td>';

		// returns url, file, missing
		$s3_url = s3_attachment_url( $attachment_url, $file->ID, true );

		$aws_link = '<a href="' . $s3_url['url'] . '" target="_blank">' . $s3_url['url'] . '</a>';

		echo $aws_link;
		echo '</td><td>';

		$around = $s3_url['missing'] ? false : true;

		echo $around ? '&#10003;' : '';

		echo( '<input type="hidden" name="s3sync[' . $keycounter . ']" value="' . $around . '"/>' );
		echo( '<input type="hidden" name="s3file[' . $keycounter . ']" value="' . $s3_url['file'] . '"/>' );

		$keycounter ++;

		echo '</td></tr>';

	}
						?>
						</tbody>
					</table>

					<p class="submit">With selected:<br>
						<input type="hidden" name="s3s3_bucket" value="<?php echo( $s3_bucket ); ?>" />
						<input type="hidden" name="s3s3_bucket_path" value="<?php echo( $s3_bucket_path ); ?>" />
						<input type="hidden" name="s3s3_cloudfront" value="<?php echo( $s3_cloudfront ); ?>" />
						<input type="submit" name="copy_files" id="s3_form_copy_button" class="button button-primary" value="<?php _e( 'Copy Files To S3' ); ?>">
						<input type="submit" name="move_files" class="button button-primary" value="<?php _e( 'Move Files To S3' ); ?>">
						<input type="submit" name="db_s3_sync" class="button button-primary" value="<?php _e( 'Sync DB &amp; S3' ); ?>">
					</p>
				</form>
			</div>
		</div>
	</div>
	</div>
	<script>
		jQuery('#select-all').click(function (e) {
			if (jQuery(this).attr('checked') == 'checked') {
				jQuery(':input[type=checkbox].files').attr('checked', 'checked');
			} else {
				jQuery(':input[type=checkbox].files').attr('checked', null);
			}
		});
	</script>
<?php
}