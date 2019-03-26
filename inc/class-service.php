<?php


defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Service {
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
		return ( $check >= 755 ) ? true : false;
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
			$maybe_dir = $this->fixslash( $folder . '/' . $filename . '/' );
			if ( is_dir( $maybe_dir ) ) {
				$subfiles = $this->get_files_from_folder( $maybe_dir );
				if ( is_array( $subfiles ) && count( $subfiles ) > 0 ) {
					$files = array_merge( $files, $subfiles );
				}
			} else { // it's a file!
				$files[] = $this->fixslash( $folder . '/' . $filename );
			}
		}
		return $files;
    }
}