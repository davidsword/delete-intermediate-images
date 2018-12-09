<?php
/**
 * Plugin Name:    Delete Thumbnails & Resized Images
 * Plugin URI:     https://davidsword.ca/wordpress-plugins/
 * Description:    Find and delete thumbnails & resized images from your Media Library
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

add_action( 'admin_init', function() {
	new Delete_Thumbnails();
});

/**
 * Main and only Class for plugin.
 */
class Delete_Thumbnails {

	/**
	 * Class construct
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->init();
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

		$this->library = $this->get_library();

		$dir       = wp_upload_dir();
		$this->dir = $dir['basedir'];
		$this->url = $dir['baseurl'];
	}

	/**
	 * Hook the plugin into WordPress
	 */
	public function hooks() {

		// Add in our JS resources.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Add in our custom menu page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	/**
	 * Get the Media Library
	 *
	 * @TODO this all needs to be cached.
	 * @TODO this only needs to be run on the options page.
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
	 * Add Menu Page
	 *
	 * @since 2.0
	 */
	public function add_admin_menu() {
		$this->menu_id = add_management_page(
			__( 'Delete Thumbnails', 'dlthumbs' ),
			__( 'Delete Thumbnails', 'dlthumbs' ),
			'administrator', // Caps level.
			'dlthumbs',
			array( $this, 'interface' )
		);
	}

	/**
	 * Add Resources
	 *
	 * @since 2.0
	 */
	public function admin_scripts() {
		if ( 'tools_page_dlthumbs' === get_current_screen()->base ) {
			wp_register_style(
				'css',
				plugins_url( 'style.css', __FILE__ ),
				false,
				'2.0'
			);
			wp_enqueue_style( 'css' );

			wp_register_script(
				'js',
				plugins_url( 'dltumbs.js', __FILE__ ),
				array( 'jquery' ),
				'2.0',
				true
			);
			wp_enqueue_script( 'js' );
		}
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
	 * HTML Page
	 *
	 * @since 2.0
	 */
	public function interface() {
		?>
		<div class='wrap' id='dlthumbs'>
			<h2><?php _e( 'Delete Resized Images', 'dlthumbs' ); ?></h2>

			<?php
			// Load files in media upload dir.
			$this->files       = $this->get_files_from_folder( $this->dir );
			$this->files_count = count( $this->files );

			// Check the dir for permissions.
			$writable = $this->check_permission( $this->dir );
			if ( ! $writable ) :
				?>
				<div class='notice notice-error'><p>
					<?php _e( 'This plugin requires Your upload directory CHMOD to be at least <code>755</code> so PHP can edit it. The deletion of files will most likely not work. Please contact your host or website provider for assistance. Mention your CHMOD is currently set to:', 'dlthumbs' ); ?> <code><?php echo $writable; ?></code>
				</p></div>
				<?php
			endif;

			// Form submit, run deletion.
			$this->dltthumbs_form_submit();

			// show thumbnails.
			$this->dltthumbs_list_form();
			?>
		</div>
		<?php
	}

	/**
	 * Ensure nothing naughty is happening. Clean out any bad behaviour.
	 *
	 * @param string $path the path of what should be an image.
	 * @return clean path
	 */
	public function verify_and_sanatize_path( $path ) {
		$path_no_funny_business = str_replace( [ '../', '/.' ], '', $path );
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

	/**
	 * Form Submit Actions
	 *
	 * @since 2.0
	 */
	public function dltthumbs_form_submit() {
		if (
			isset( $_POST['list'] ) &&
			! empty( $_POST['list'] ) &&
			'[]' !== $_POST['list']
		) {
			if ( check_admin_referer( 'submit' ) ) {
				$not_deleted   = [];
				$deleted       = [];
				$filestodelete = json_decode( str_replace( '\"', '"', $_POST['list'] ) );

				foreach ( $filestodelete as $deleteme ) {
					$delete_file = verify_and_sanatize_path( $this->dir . $deleteme );
					if ( $delete_file ) {
						// CYA LATER!
						if ( unlink( $delete_file ) ) { // yeah unlink.
							$deleted[] = $delete_file;
						} else {
							$not_deleted[] = $deleteme . ' (could not delete)';
						}
					} else {
						$not_deleted[] = $deleteme . ' (could not verify path)';
					}
				}
				if ( count( $deleted ) > 0 ) {
					$error_class = 'notice-success is-dismissible';
					$error_text  = count( $deleted );
					$error_text .= _e( 'files successfully deleted.', 'dlthumbs' ); // @TODO change to print_f format
				}
				if ( count( $not_deleted ) > 0 ) {
					$error_class = 'notice-error';
					$error_text  = _e( 'Yikes, files were marked to-delete but PHP was unable to delete them.', 'dlthumbs' ); // @TODO change to print_f format
					$error_text .= count( $not_deleted ) . implode( '<br /> - ', $not_deleted );
				}
			} else {
				$error_class = 'notice-error';
				$error_text  = _e( 'Something went wrong with the', 'dlthumbs' );
				$error_text .= '<code>wp_nonce_field()</code>.';
			}
			?>
			<div class="notice <?php echo $error_class; ?>">
				<p><?php echo $error_text; ?></p>
			</div>
			<?php
		}
	}

	/**
	 * List all files from uploads directory.
	 *
	 * @since 2.0
	 */
	public function dltthumbs_list_form() {
		?>
		<div class="notice notice-<?php echo ( 0 === $this->files_count ) ? 'error' : 'info'; ?>">
			<p>
				<?php _e( 'Browsing', 'dlthumbs' ); ?>:
				<code>/<?php echo str_replace( get_home_path(), '', $this->dir ); ?>/</code>
				<?php echo $this->files_count; ?>
				<?php _e( 'files were found', 'dlthumbs' ); ?>
				<?php if ( $this->files_count > 0 ) { ?>
					, <span class='total_thumbnail_count'></span>
					<?php _e( 'images detected as resized images', 'dlthumbs' ); ?>.
				<?php } ?>
			</p>
		</div>

		<table class='widefat'>
			<thead>
				<tr>
					<th>
						<input type='checkbox' name='selectall' title='Select All' />
					</th>
					<th>
						<?php _e( 'Preview', 'dlthumbs' ); ?>
					</th>
					<th>
						<?php _e( 'File', 'dlthumbs' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$id = 0;
				foreach ( $this->files as $afile ) {
					$is_thumb = $this->is_thumbnail( $afile );
					$file     = $afile;
					if ( ! $is_thumb ) {
						continue;
					}
					$id++;
					?>
					<tr>
						<td>
							&nbsp;<input id='input-<?php echo $id; ?>' type='checkbox' value='<?php echo str_replace( $this->dir, '', $file ); ?>' />
						</td>
						<td>
							<a target='_Blank' href='<?php echo $this->fixslash( str_replace( $this->dir, $this->url, $file ) ); ?>'>View »</a>
						</td>
						<td>
							<label for='input-<?php echo $id; ?>'>
								<?php echo str_replace( $this->dir, '', $file ); ?>
							</label>
						</td>
					</tr>
					<?php
				}
				if ( 0 === $id || 0 === $this->files_count ) {
					?>
					<tr>
						<td colspan=3>
							<p id='wtfnofiles'><?php _e( 'No resized images found in', 'dlthumbs' ); ?>:<br />
							<code><?php echo $this->dir; ?></code>
							</p>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>

		<br />

		<form action="" method="POST">
			<?php
			// security.
			wp_nonce_field( 'submit' );

			if ( 0 !== $id && 0 !== $this->files_count ) {
				?>

			<!-- the magic -->
			<textarea name='list'></textarea>
			<p>
				<label>
					<input class='nag' value='' type='checkbox' name='nag1' />
					<?php _e( 'I understand that pressing the button below will delete the above selected files', 'dlthumbs' ); ?>.
				</label>
				<br />
				<label>
					<input class='nag' value='' type='checkbox' name='nag2' />
					<?php _e( 'I have backed up the uploads directory before doing this', 'dlthumbs' ); ?> (<code>/<?php echo str_replace( get_home_path(), '', $this->dir ); ?>/</code>).
				</label>
				<br />
				<label>
					<input class='nag' value='' type='checkbox' name='nag3' />
					<?php _e( 'I understand this action can not be undone', 'dlthumbs' ); ?>.</label><br />
			</p>
			<input type='submit' class='button-primary button-large button' value='<?php _e( 'DELETE RESIZED IMAGES', 'dlthumbs' ); ?> &raquo;' disabled>
			<?php } ?>
		</form>

		<p id='streetcred'><?php _e( 'Plugin By', 'dlthumbs' ); ?> <a href='https://davidsword.ca/' target='_Blank'>David Sword</a></p>
		<?php
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
	 * @param boolean $godeep true to look deeper than current dir, false if /.
	 * @return array of files.
	 */
	public function get_files_from_folder( $folder, $godeep = true ) {

		$files = [];

		// start read.
		if ( is_dir( $folder ) ) {
			$dh       = opendir( $folder );
			$filename = readdir( $dh );
			while ( false !== $filename ) {
				if ( ! in_array( $filename, $this->the_naughty_list(), true ) ) {

					// it's a dir, index contents w/ current function.
					if ( $this->check_if_dir( $filename ) ) {
						// repeat same function, find files within folders.
						$subfiles = $this->get_files_from_folder( $this->fixslash( $folder . '/' . $filename . '/' ), false );
						foreach ( $subfiles as $subfile ) {
							$files[] = $subfile;
						}
					} else {
						// it's a file.
						$files[] = $this->fixslash( $folder . '/' . $filename );
					}
				}
			}
		}
		return $files;
	}

	/**
	 * List of files that are kinda files but not really files.
	 *
	 * @return array of files that are kinda files but not really files.
	 */
	public function the_naughty_list() {
		return array( '.DS_Store', '.', '..', '' );
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
	 * Check if item is a dir or file
	 *
	 * Find out if the current item in dir is another dir a hidden file, or an actual file.
	 *
	 * @since 2.0.0
	 * @param string $filename the file name to check if it's a directory or not.
	 * @return boolean true if is, false if now
	 */
	public function check_if_dir( $filename ) {
		$pos = strpos( $filename, '.' );
		if ( false === $pos ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Helper, replace double slash.
	 *
	 * @since 2.0.0
	 *
	 * @param string $str string to replace double slashes on.
	 * @return string hopefully without double slashes.
	 */
	public function fixslash( $str ) {
		return str_replace( '//', '/', $str );
	}

}
?>