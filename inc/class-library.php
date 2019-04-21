<?php
defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_Library {

	/**
	 * An array of all files from the Media Library.
	 *
	 * @var array
	 */
	public $files = [];

	/**
	 * A count of the total number of files in the Media Library.
	 *
	 * @var string
	 */
	public $files_count = '';

	/**
	 * Init Class.
	 */
	public function __construct() {
		$this->files = $this->get();
		$this->files_count = count( $this->files );
	}

	/**
	 * How long to store the Media Library in cache.
	 */
	const LIBRARY_CACHE_IN_SECONDS = 60*5;

	/**
	 * Name of the cache.
	 */
	const LIBRARY_CACHE_NAME = 'dlthumbs_library';

	/**
	 * Get the Media Library
	 *
	 * @return array of URLs of current media items.
	 */
	static function get() {

		$result = wp_cache_get( self::LIBRARY_CACHE_NAME );
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

		/**
		 * @TODO this needs to be paginated at some point, it won't scale.
		 */
		$attachments = get_posts( $args );
		foreach ( $attachments as $post ) {
			$type = get_post_mime_type( $post->ID );
			if ( ! strstr( $type, 'image' ) ) {
				continue;
			}
			$library[] = DL_Helpers::fixslash( wp_get_attachment_url( $post->ID ) );
		}
		wp_cache_set( self::LIBRARY_CACHE_NAME, $library, '', self::LIBRARY_CACHE_IN_SECONDS );

		return $library;
    }
}