<?php
/**
 * Plugin Name:    Delete Thumbnails & Intermediate Images
 * Plugin URI:     https://davidsword.ca/projects/delete-thumbnails/
 * Description:    Find and delete thumbnails & the other resized images from your Media Library
 * Version:        2.3.0
 * Author:         davidsword
 * Author URI:     https://davidsword.ca/
 * License:        GPLv3
 * License URI:    https://www.gnu.org/licenses/quick-guide-gplv3.en.html
 * Text Domain:    dlthumbs
 *
 * @package delete-thumbnails
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Main and only Class for plugin.
 */
class Delete_Thumbnails {

	/**
	 * Init class.
	 */
	public function __construct() {
		$this->load();
		$this->init();
	}

	/**
	 * Tiger cats go! Fire up the UI.
	 */
	public function init() {
		new DL_UI();
	}

	/**
	 * Load in classes.
	 *
	 * @return void
	 */
	public function load() {
		include('inc/class-helpers.php');
		include('inc/class-service.php');
		include('inc/class-ui.php');
		include('inc/class-library.php');
		include('inc/class-file.php');
	}

}

// Self load.
new Delete_Thumbnails();