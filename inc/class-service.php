<?php


defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Service {

	/**
	 * Will hold WordPress's `basedir` value.
	 *
	 * @var string
	 */
	public $dir = '';

	/**
	 * Will hold WordPress's `baseurl` value.
	 *
	 * @var string
	 */
	public $url = '';

	public $files = '';

	public $files_count = '';

	public $wp_upload_dir = '';

	public function __construct() {
		$this->dir = $this->get_upload_dir();
		$this->url = $this->get_upload_url();
		$this->files = $this->get_files_from_folder( $this->dir );
		$this->files_count = count( $this->files );
	}

	static function get_upload_dir() {
		$wp_upload_dir = wp_upload_dir();
		return $wp_upload_dir['basedir'];
	}

	static function get_upload_url() {
		$wp_upload_dir = wp_upload_dir();
		return $wp_upload_dir['baseurl'];
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
	 * @param string  $folder folder to retrieve files from.
	 * @return array of files.
	 */
	public function get_files_from_folder( $folder ) {

		$files = [];

		$get_files = scandir( $folder );
		if ( false === $get_files) {
			return "ERROR running scandir on {$folder}`";
		}

		foreach ( $get_files as $filename ) {
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
			! isset( $_POST['list'] ) ||
			empty( $_POST['list'] ) ||
			'[]' === $_POST['list']
		) {
			return;
		}
		if ( ! check_admin_referer( 'submit' ) ) { // @TODO no.
			return;
		}
		$not_deleted   = [];
		$deleted       = [];
		$files_to_delete = json_decode( str_replace( '\"', '"', $_POST['list'] ) ); // this value will be sanitized later.

		foreach ( $files_to_delete as $delete_me ) {
			$delete_file = $this->verify_and_sanitize_path( $this->dir . $delete_me );
			if ( $delete_file ) {
				// CYA LATER!
				if ( unlink( $delete_file ) ) { // yeah unlink.
					$deleted[] = $delete_file;
				} else {
					$not_deleted[] = $delete_me . ' (' . esc_html__( 'could not delete', 'dlthumbs' ) . ')';
				}
			} else {
				$not_deleted[] = $delete_me . ' (' . esc_html__( 'could not verify path', 'dlthumbs' ) .')';
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
		?>
		<div class="notice <?php echo $error_class; ?>">
			<p><?php echo $error_text; ?></p>
		</div>
		<?php
	}

	/**
	 * Ensure nothing naughty is happening. Clean out any bad behaviour.
	 *
	 * @param string $path the path of what should be an image.
	 * @return clean path
	 */
	public function verify_and_sanitize_path( $path ) {
		$path_with_no_funny_business = str_replace( [ '../', '..', '/.' ], '', $path );
		// Eliminate any symbolic links or dot-dot'ery.
		$sanitized_path = realpath( $path_with_no_funny_business );
		// Make sure we're working in the uploads directory. Double check it.
		$has_dir = strpos( $sanitized_path, $this->dir );
		$starts_with_dir = $this->dir === substr($sanitized_path, 0, count( $this->dir ) );
		if ( 0 === $has_dir && $starts_with_dir) {
			return $path;
		} else {
			return false;
		}
	}

}
