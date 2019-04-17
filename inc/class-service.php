<?php


defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Service {

	/**
	 *
	 *
	 * @var
	 */
	public $dir = '';

	/**
	 *
	 *
	 * @var
	 */
	public $url = '';


	public function __construct() {
		$dir       = wp_upload_dir();
		$this->dir = $dir['basedir'];
		$this->url = $dir['baseurl'];
	}

    /**
	 * Check the file permission of a folder.
	 *
	 * @param  string $folder path to the folder to check permissions on.
	 * @return boolean true if it's writeable, false if not
	 */
	public function check_permission( $folder ) {
		$check = substr( sprintf( '%o', fileperms( $this->dir ) ), -4 );
		return ( $check >= 755 );
    }

	/**
	 * Function for indexing directory
	 *
	 * Using itself from within to get sub-folder's indexed
	 * also used to delete thumbnails as indexing when instructed to
	 *
	 * @since 2.0
	 *
	 * @param string  $folder folder to retrive files from.
	 * @return array of files.
	 */
	public function get_files_from_folder( $folder ) {

		$files = [];

		$files = scandir( $folder );
		if ( false === $files) {
			return "ERROR running scandir on {$folder}`";
		}

		foreach ( $files as $filename ) {
			if ( in_array( $filename, [ '.DS_Store', '.', '..', '' ], true ) ) {
				continue; // common, get outta here.
			}

			// For directories, lets repeat ourselves, find files within folders. Inception style.
			$maybe_dir = DL_Helpers::fixslash( $folder . '/' . $filename . '/' );
			if ( is_dir( $maybe_dir ) ) {
				$subfiles = $this->get_files_from_folder( $maybe_dir );
				if ( is_array( $subfiles ) && count( $subfiles ) > 0 ) {
					$files = array_merge( $files, $subfiles );
				}
			} else { // it's a file!
				$files[] = DL_Helpers::fixslash( $folder . '/' . $filename );
			}
		}
		return $files;
	}

	public function delete() {
		if (
			isset( $_POST['list'] ) &&
			! empty( $_POST['list'] ) &&
			'[]' !== $_POST['list']
		) {
			if ( check_admin_referer( 'submit' ) ) { // @TODO no.
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