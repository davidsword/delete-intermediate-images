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
	 * Will hold WordPress's `basedir` value.
	 *
	 * @var string
	 */
	private $dir = '';

	/**
	 * Will hold WordPress's `baseurl` value.
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * Tiger cats go!
	 */
	public function __construct() {
		$this->load();
		$this->hook();
	}

	/**
	 * Tiger cats go!
	 */
	public function hook() {
		// fire up the interface.
		new DL_UI();
	}

	/**
	 *
	 *
	 * @return void
	 */
	public function load() {

		include('inc/class-deletor.php');
		include('inc/class-helpers.php');
		include('inc/class-service.php');
		include('inc/class-ui.php');
		include('inc/class-library.php');

	}

}

// Self load.
//new Delete_Thumbnails();