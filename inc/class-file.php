<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class for handling a file.
 *
 * Has methods to modify the file into various forms.
 */
class DL_File {

	/**
	 * The file to be passed into the class.
	 *
	 * @var string should be path of image relative to wp_uploads base dir.
	 */
	public $file = '';

	/**
	 * Init class.
	 *
	 * @param string $file_path should be path of image relative to wp_uploads base dir.
	 */
	public function __construct( $file_path ) {
		$this->file = $file_path;
	}

	/**
	 * Get the link of an image.
	 *
	 * @return string URL link to image.
	 */
	public function get_image_link() {
		return str_replace( DL_Service::get_upload_dir(), DL_Service::get_upload_url(), $this->file );
	}

	/**
	 * Get the value of the file for the processing form.
	 *
	 * @return string the image path without the upload_dir path.
	 */
	public function get_form_value() {
		return str_replace( DL_Service::get_upload_dir(), '', $this->file );
	}

	/**
	 * Return the path that was init passed into the class.
	 *
	 * Just tidier while using this class to call this method instead of using the value put into it.
	 *
	 * @return string path to file.
	 */
	public function get_file_path() {
		return $this->file;
	}


}