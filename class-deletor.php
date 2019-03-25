<?php

defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Deletor {
	public function __construct() {

	}

	/**
	 * Form Submit Actions
	 *
	 * @since 2.0
	 */
	public function dltthumbs_form_submit() {
		if (
			isset( $_POST['list'] ) &&
			! empty( $_POST['list'] ) &&
			'[]' !== $_POST['list']
		) {
			if ( check_admin_referer( 'submit' ) ) {
				$not_deleted   = [];
				$deleted       = [];
				$filestodelete = json_decode( str_replace( '\"', '"', $_POST['list'] ) ); // this value will be sanitized later.

				foreach ( $filestodelete as $deleteme ) {
					$delete_file = verify_and_sanatize_path( $this->dir . $deleteme );
					if ( $delete_file ) {
						// CYA LATER!
						if ( unlink( $delete_file ) ) { // yeah unlink.
							$deleted[] = $delete_file;
						} else {
							$not_deleted[] = $deleteme . ' (' . esc_html__( 'could not delete', 'dlthumbs' ) . ')';
						}
					} else {
						$not_deleted[] = $deleteme . ' (' . esc_html__( 'could not verify path', 'dlthumbs' ) .')';
					}
				}
				if ( count( $deleted ) > 0 ) {
					$error_class = 'notice-success is-dismissible';
					$error_text  = count( $deleted );
					$error_text .= esc_html_e( 'files successfully deleted.', 'dlthumbs' ); // @TODO change to print_f format
				}
				if ( count( $not_deleted ) > 0 ) {
					$error_class = 'notice-error';
					$error_text  = esc_html_e( 'Yikes, files were marked to-delete but PHP was unable to delete them.', 'dlthumbs' ); // @TODO change to print_f format
					$error_text .= count( $not_deleted ) . implode( '<br /> - ', $not_deleted );
				}
			} else {
				$error_class = 'notice-error';
				$error_text  = esc_html_e( 'Something went wrong with the', 'dlthumbs' );
				$error_text .= '<code>wp_nonce_field()</code>.';
			}
			?>
			<div class="notice <?php echo $error_class; ?>">
				<p><?php echo $error_text; ?></p>
			</div>
			<?php
		}
	}

}