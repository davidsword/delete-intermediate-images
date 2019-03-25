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

//add_action( 'admin_init', function() {
	new Delete_Thumbnails();
//});

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
	 * Will store the list of original URLs from the Media Library.
	 *
	 * @var array
	 */
	public $library = [];

	/**
	 * Tiger cats go.
	 */
	public function __construct() {

		// setup vars.
		$this->init();

		// inject into WordPress.
		$this->hooks();
	}

	/**
	 * Plugin initialization
	 *
	 * @TODO load_plugin_textdomain.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function init() {

		// Load the library/
		$this->library = $this->get_library();

		// make paths more accessable.
		$dir       = wp_upload_dir();
		$this->dir = $dir['basedir'];
		$this->url = $dir['baseurl'];
	}

	/**
	 * Hook the plugin into WordPress
	 */
	public function hooks() {

		// Add in our JS resources.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

		// Add in our custom menu page.
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
	}

	/**
	 * Get the Media Library
	 *
	 * @TODO this all needs to be cached.
	 * @TODO this only needs to be run on the options page.
	 * @TODO this should be in it's own class
	 * @TODO should pageinate
	 *
	 * @return array of URLs of current media items.
	 */
	public function get_library() {
		$library = [];
		$args    = [
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'numberposts'    => -1, // Watch out bigger sites, this might slow ya down.
			'post_status'    => null,
			'post_parent'    => null, // Any parent.
		];

		$attachments = get_posts( $args );
		foreach ( $attachments as $post ) {
			$library[] = $this->fixslash( wp_get_attachment_url( $post->ID ) );
		}
		return $library;
	}

	/**
	 * Add Resources
	 *
	 * @since 2.0
	 */
	public function admin_scripts() {
		if ( 'tools_page_dlthumbs' === get_current_screen()->base ) {
			$version = SCRIPT_DEBUG ? time() : get_plugin_data( __FILE__ )['Version'];

			wp_enqueue_script(
				'css',
				plugins_url( 'style.css', __FILE__ ),
				false,
				$version
			);

			wp_enqueue_script(
				'js',
				plugins_url( 'dltumbs.js', __FILE__ ),
				[ 'jquery' ],
				$version,
				true
			);
		}
	}

}
