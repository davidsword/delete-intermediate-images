<?php
defined( 'ABSPATH' ) || exit;

/**
 * Main interface for the plugin.
 *
 * Creates the UX and fires off the classes needed.
 */
class DL_UI {

	/**
	 * Hold the DL_Library class.
	 *
	 * @var array
	 */
	public $library = [];

	/**
	 * Hold the DL_Service class.
	 *
	 * @var array
	 */
	public $service = [];

	/**
	 * Init.
	 */
	public function __construct() {
		$this->hook_into_wp();
	}

	/**
	 * Hook into WordPress.
	 */
	public function hook_into_wp() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_footer', [ $this, 'admin_scripts' ] );
	}

    /**
	 * Add Plugins main interface and post processing into an Admin page.
	 *
	 * @since 2.0
	 */
	public function add_admin_menu() {

		$page_hook_suffix = add_management_page(
			esc_html__( 'Delete Thumbnails', 'dlthumbs' ),
			esc_html__( 'Delete Thumbnails', 'dlthumbs' ),
			'manage_options', // Caps level, has to be an Admin.
			'dlthumbs',
			[ $this, 'interface' ]
		);
		add_action( "admin_footer-{$page_hook_suffix}", [ $this, 'admin_scripts' ] );

	}

    /**
	 * The plugins main interface.
	 *
	 * @since 2.0
	 */
	public function interface() {

		$this->library = new DL_Library();
		$this->service = new DL_Service();

		?>
		<div class='wrap' id='dlthumbs'>
			<h2><?php esc_html_e( 'Delete Resized Images', 'dlthumbs' ); ?></h2>

			<?php
			// Check the dir for permissions.
			$writable = $this->service->check_permission( $this->service->dir );
			if ( ! $writable ) :
				?>
				<div class='notice notice-error'><p>
					<?php
					sprintf(
						esc_html_e( 'This plugin requires your upload directory %s to be at least %s so PHP can edit it. The deletion of files will most likely not work. Please contact your host or website provider for assistance. Mention your %s is currently set to', 'dlthumbs' ),
						'CHMOD',
						'<code>755</code>',
						'CHMOD'
					); ?>: <code><?php echo $writable; ?></code>
				</p></div>
				<?php
			endif;

			// show thumbnails.
			$this->dltthumbs_list_form();
			?>
		</div>
		<?php
	}

    /**
	 * List all files from uploads directory.
	 *
	 * @since 2.0
	 */
	public function dltthumbs_list_form() {
		?>
		<div class="notice notice-<?php echo ( 0 === $this->service->files_count ) ? 'error' : 'info'; ?>">
			<p>
				<?php esc_html_e( 'Browsing', 'dlthumbs' ); ?>:
				<code>/<?php echo str_replace( get_home_path(), '', $this->service->dir ); ?>/</code>
				<?php echo $this->service->files_count; ?>
				<?php esc_html_e( 'files were found', 'dlthumbs' ); ?>,

				<?php echo $this->library->files_count; ?>
				<?php esc_html_e( 'are original images', 'dlthumbs' ); ?>.
				<?php
				if ( $this->service->files_count > 0 ) {
					?>
					<span class='total_thumbnail_count'></span>
					<?php esc_html_e( 'images were detected as resized images and are listed below', 'dlthumbs' ); ?>:
					<?php
				}
				?>
			</p>
		</div>

		<table class='widefat striped'>
			<thead>
				<tr>
					<th>
						<input type='checkbox' name='selectall' title='Select All' />
					</th>
					<th>
						<?php esc_html_e( 'Preview', 'dlthumbs' ); ?>
					</th>
					<th>
						<?php esc_html_e( 'File', 'dlthumbs' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$id = 0;
				$library_of_images = $this->library->files;
				foreach ( $this->service->files as $file_path ) {
					$is_thumb = DL_Helpers::is_thumbnail( $file_path, $library_of_images );
					if ( ! $is_thumb ) {
						continue;
					}

					$file = new DL_File( $file_path );

					$id++;
					?>
					<tr>
						<td>
							&nbsp;<input id='input-<?php echo $id; ?>' type='checkbox' value='<?php echo esc_attr( $file->get_form_value() ) ?>' />
						</td>
						<td>
							<a target='_Blank' href='<?php echo esc_url( $file->get_image_link() ); ?>'>View »</a>
						</td>
						<td>
							<label for='input-<?php echo $id; ?>'>
								<?php echo esc_html( str_replace( $this->service->dir, '', $file->get_file_path() ) ); ?>
							</label>
						</td>
					</tr>
					<?php
				}
				if ( 0 === $id || 0 === $this->service->files_count ) {
					?>
					<tr>
						<td colspan=3>
							<p id='wtfnofiles'><?php esc_html_e( 'No resized images found in', 'dlthumbs' ); ?>:<br />
							<code><?php echo esc_html( $this->service->dir ); ?></code>
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
			// prevent csrf attacks.
			wp_nonce_field( 'dlthumbs_delete_form', '_wpnonce_dlthumbs' );

			if ( 0 !== $id && 0 !== $this->service->files_count ) {
				?>

			<!-- the magic -->
			<textarea name='dlthumbs_list'></textarea>
			<p>
				<label>
					<input class='nag' value='' type='checkbox' name='nag1' />
					<?php esc_html_e( 'I understand that pressing the button below will delete the above selected files', 'dlthumbs' ); ?>.
				</label>
				<br />
				<label>
					<input class='nag' value='' type='checkbox' name='nag2' />
					<?php esc_html_e( 'I have backed up the uploads directory before doing this', 'dlthumbs' ); ?> (<code>/<?php echo esc_html( str_replace( get_home_path(), '', $this->service->dir ) ); ?>/</code>).
				</label>
				<br />
				<label>
					<input class='nag' value='' type='checkbox' name='nag3' />
					<?php esc_html_e( 'I understand this action can not be undone', 'dlthumbs' ); ?>.</label><br />
			</p>
			<input type='submit' class='button-primary button-large button' value='<?php esc_html_e( 'DELETE RESIZED IMAGES', 'dlthumbs' ); ?> &raquo;' disabled>
			<?php } ?>
		</form>

		<p id='streetcred'><?php esc_html_e( 'Plugin By', 'dlthumbs' ); ?> <a href='https://davidsword.ca/' target='_Blank'>David Sword</a></p>
		<?php
	}

	/**
	 * Add Resources
	 */
	public function admin_scripts() {
		if ( 'tools_page_dlthumbs' === get_current_screen()->base ) {
			$version = SCRIPT_DEBUG ? time() : get_plugin_data( __FILE__ )['Version'];

			wp_enqueue_style(
				'css',
				plugins_url( '../assets/style.css', __FILE__ ),
				false,
				$version
			);

			wp_enqueue_script(
				'js',
				plugins_url( '../assets/dltumbs.js', __FILE__ ),
				[ 'jquery' ],
				$version,
				true
			);
		}
	}

}