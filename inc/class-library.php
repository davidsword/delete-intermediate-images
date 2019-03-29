<?php


defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Library {
	public function __construct() {

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
	static function get() {
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
			$library[] = DL_Helpers::fixslash( wp_get_attachment_url( $post->ID ) );
		}
		return $library;
    }
}