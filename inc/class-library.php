<?php


defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Library {

	public $files_count = '';
	public $files = [];

	public function __construct() {
		$this->files = $this->get();
		$this->files_count = count( $this->files );
	}

	const LIBRARY_CACHE_IN_SECONDS = 60*5;

	/**
	 * Get the Media Library
	 *
	 * @return array of URLs of current media items.
	 */
	static function get() {

		$result = wp_cache_get( 'dlthumbs_library' );
		if ( true === $result ) {
			return $result;
		}

		$library = [];

		$args    = [
			'suppress_filters'    => true,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => false,
			'post_type'           => 'attachment',
			'numberposts'         => -1, // Watch out bigger sites, this might slow ya down.
		];

		// @TODO this needs to be paginated at some point, it won't scale.
		$attachments = get_posts( $args );
		foreach ( $attachments as $post ) {
			$type = get_post_mime_type( $post->ID );
			if ( ! strstr( $type, 'image' ) ) {
				continue;
			}
			$library[] = DL_Helpers::fixslash( wp_get_attachment_url( $post->ID ) );
		}
		wp_cache_set( 'dlthumbs_library', $library, '', self::LIBRARY_CACHE_IN_SECONDS );

		return $library;
    }
}