<?php


defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Helpers {
	public function __construct() {

	}

    /**
	 * Determine if a file is a thumbnail or not
	 *
	 * @since 2.0
	 * @param string $file file to find out if thumbnail or not.
	 * @return boolean true if really a thumbnail, false if not.
	 */
	public function is_thumbnail( $file ) {
		/*
		If it's in the media library as a main file, it's defnitantly not a thumbnail
		It could of been mistaken as one if it's source was a downloaded thumbnail from
		Another WordPress blog
		*/
		if ( in_array( str_replace( get_home_path(), '', $file ), $this->library, true ) ) {
			return false;
		}

		// If it has the thumbnail suffix, lets concider it.
		preg_match( '"-([0-9]*x[0-9]*)."', $file, $matches );
		if ( count( $matches ) > 0 ) {
			return true;
		}

		// Not sure what it is, just send it back as not a thumbnail.
		return false;
	}

	/**
	 * Helper, replace double slash.
	 *
	 * This shouldn't happen, but it does.
	 * Until I clean up the cause, I'll clean up the symptom.
	 *
	 * @since 2.0.0
	 *
	 * @param string $str string to replace double slashes on.
	 * @return string hopefully without double slashes.
	 */
	public function fixslash( $str ) {
		return str_replace( '//', '/', $str );
    }

    /**
	 * Ensure nothing naughty is happening. Clean out any bad behaviour.
	 *
	 * @param string $path the path of what should be an image.
	 * @return clean path
	 */
	public function verify_and_sanatize_path( $path ) {
		$path_no_funny_business = str_replace( [ '../', '..', '/.' ], '', $path );
		// Eliminate any symbolic links or dot-dot'ery.
		$realpath = realpath( $path_no_funny_business );
		// Make sure we're in the uploads directory.
		$good = strpos( $realpath, $this->dir );
		if ( 0 === $good ) {
			return $path;
		} else {
			return false;
		}
    }

}