<?php
defined( 'ABSPATH' ) || exit;

/**
 * File service for storing, reading, and deleting `/wp-content/uploads/*` files.
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

	/**
	 * Houses a list of all files found in the `$dir`.
	 *
	 * @var array
	 */
	public $files = '';

	/**
	 * The total number of giles in `$files`
	 *
	 * @var int
	 */
	public $files_count = '';

	/**
	 * Startup.
	 */
	public function __construct() {

		// load the vars for this class.
		$this->load_vars();

		// this class is only used above the UI, so our listener can sit here.
		$this->delete_listener();
	}

	function load_vars() {
		$this->dir         = $this->get_upload_dir();
		$this->url         = $this->get_upload_url();
		$this->files       = $this->get_files_from_folder( $this->dir );
		$this->files_count = count( $this->files );
	}

	/**
	 * A quick method to retrieve the `basedir`.
	 *
	 * @return string basedir path (ie `/var/www/public_html/wordpress/wp-content/uploads/`)
	 */
	static function get_upload_dir() {
		$wp_upload_dir = wp_upload_dir();
		return $wp_upload_dir['basedir'];
	}

	/**
	 * A quick method to retrieve the `baseurl`.
	 *
	 * @return string baseurl URL (ie `https://example.com/wp-content/uploads/`)
	 */
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
	public function check_permission() {
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

		foreach ( $get_files as $file_name ) {
			if ( in_array( $file_name, [ '.DS_Store', '.', '..', '' ], true ) ) {
				continue; // common, get outta here.
			}

			// For directories, lets repeat ourselves, find files within folders. Inception style.
			$maybe_dir = DL_Helpers::fixslash( $folder . '/' . $file_name . '/' );
			if ( is_dir( $maybe_dir ) ) {
				$sub_files = $this->get_files_from_folder( $maybe_dir );
				if ( is_array( $sub_files ) && count( $sub_files ) > 0 ) {
					$files = array_merge( $files, $sub_files );
				}
			} else { // it's a file!
				$files[] = DL_Helpers::fixslash( $folder . '/' . $file_name );
			}
		}
		return $files;
	}

	/**
	 * Listener for deleting requested images.
	 *
	 * Only valid images will be deleted.
	 */
	public function delete_listener() {
		if ( ! isset( $_POST['dlthumbs_list'] ) || empty( $_POST['dlthumbs_list'] ) || '[]' === $_POST['dlthumbs_list'] ) {
			return;
		}

		// Check for nonce. No CSRF here.
		if ( ! isset( $_POST['_wpnonce_dlthumbs'] ) || ! wp_verify_nonce( $_POST['_wpnonce_dlthumbs'], 'dlthumbs_delete_form' ) ) {
			return;
		}

		$not_deleted   = [];
		$deleted       = [];
		// this value will be sanitized as it's parsed, hang tight.
		$files_to_delete = json_decode( str_replace( '\"', '"', $_POST['dlthumbs_list'] ) );

		foreach ( $files_to_delete as $delete_me ) {
			$delete_file = $this->verify_and_sanitize_path( $this->dir . $delete_me );
			if ( $delete_file ) {
				if ( unlink( $delete_file ) ) { // CYA LATER!
					$deleted[] = $delete_file;
				} else {
					$not_deleted[] = $delete_me . ' (' . esc_html__( 'could not delete', 'dlthumbs' ) . ')';
				}
			} else {
				$not_deleted[] = $delete_me . ' (' . esc_html__( 'could not verify path', 'dlthumbs' ) .')';
			}
		}
		if ( count( $deleted ) > 0 ) {
			$notice_class = 'notice-success is-dismissible';
			$notice_text  = count( $deleted ) . " ";
			$notice_text .= esc_html__( 'files successfully deleted.', 'dlthumbs' ); // @TODO change to print_f format
		}
		if ( count( $not_deleted ) > 0 ) {
			$notice_class = 'notice-error';
			$notice_text  = esc_html__( 'Yikes, files were marked to-delete but PHP was unable to delete them.', 'dlthumbs' ); // @TODO change to print_f format.
			$notice_text .= count( $not_deleted ) . implode( '<br /> - ', $not_deleted );
		}
		?>
		<div class="notice <?php echo esc_html( $notice_class ); ?>">
			<p><?php echo esc_html( $notice_text ); ?></p>
		</div>
		<?php

		// reload our values for the ui.
		$this->load_vars();
	}

	/**
	 * Ensure nothing naughty is happening. Clean out any bad behaviour.
	 *
	 * @param string $path the path of what should be an image.
	 * @return string clean path
	 */
	public function verify_and_sanitize_path( $path ) {
		$path_with_no_funny_business = str_replace( [ '../', '..', '/.' ], '', $path );
		// Eliminate any symbolic links or dot-dot'ery.
		$sanitized_path = realpath( $path_with_no_funny_business );
		// Make sure we're working in the uploads directory. Double check it.
		$has_dir = strpos( $sanitized_path, $this->dir );
		$starts_with_dir = $this->dir === substr($sanitized_path, 0, strlen( $this->dir ) );
		if ( 0 === $has_dir && $starts_with_dir) {
			return $path;
		} else {
			return false;
		}
	}

}
