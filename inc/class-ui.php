<?php
defined( 'ABSPATH' ) || exit;

/**
 * @TODO
 */
class DL_UI {

	/**
	 * Will store the list of original URLs from the Media Library.
	 *
	 * @var array
	 */
	public $library = [];

	/**
	 *
	 *
	 * @var array
	 */
	public $service = [];

	/**
	 *
	 */
	public function __construct( $_library, $_service ) {
		$this->library = $_library;
		$this->service = $_service;
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
	 * Add Menu Page
	 *
	 * @since 2.0
	 */
	public function add_admin_menu() {

		$page_hook_suffix = add_management_page(
			esc_html__( 'Delete Thumbnails', 'dlthumbs' ),
			esc_html__( 'Delete Thumbnails', 'dlthumbs' ),
			'manage_options', // Caps level.
			'dlthumbs',
			[ $this, 'interface' ]
		);
		add_action( "admin_footer-{$page_hook_suffix}", [ $this, 'admin_scripts' ] );

	}

    /**
	 * HTML Page
	 *
	 * @since 2.0
	 */
	public function interface() {
		?>
		<div class='wrap' id='dlthumbs'>
			<h2><?php esc_html_e( 'Delete Resized Images', 'dlthumbs' ); ?></h2>

			<?php
			// Load files in media upload dir.
			$this->files       = $this->service->get_files_from_folder( $this->service->dir );
			$this->files_count = count( $this->files );

			// Check the dir for permissions.
			$writable = $this->service->check_permission( $this->service->dir );
			if ( ! $writable ) :
				?>
				<div class='notice notice-error'><p>
					<?php
					sprintf( esc_html_e( 'This plugin requires Your upload directory %s to be at least %s so PHP can edit it. The deletion of files will most likely not work. Please contact your host or website provider for assistance. Mention your %s is currently set to', 'dlthumbs' ), 'CHMOD', '<code>755</code>', 'CHMOD'); ?>: <code><?php echo $writable; ?></code>
				</p></div>
				<?php
			endif;

			// Form submit, run deletion.
			// @TODO why is this inline, can't we hook this elsewhere?
			$this->service->delete();

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
		<div class="notice notice-<?php echo ( 0 === $this->files_count ) ? 'error' : 'info'; ?>">
			<p>
				<?php esc_html_e( 'Browsing', 'dlthumbs' ); ?>:
				<code>/<?php echo str_replace( get_home_path(), '', $this->service->dir ); ?>/</code>
				<?php echo $this->files_count; ?>
				<?php esc_html_e( 'files were found', 'dlthumbs' ); ?>.
				<?php if ( $this->files_count > 0 ) { ?>
					<span class='total_thumbnail_count'></span>
					<?php esc_html_e( 'images detected as resized images and are listed below', 'dlthumbs' ); ?>.
				<?php } ?>
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
				$library_of_images = $this->library::get();
				foreach ( $this->files as $afile ) {
					$is_thumb = DL_Helpers::is_thumbnail( $afile, $library_of_images );
					$file     = $afile;
					if ( ! $is_thumb ) {
						continue;
					}
					$id++;
					?>
					<tr>
						<td>
							&nbsp;<input id='input-<?php echo $id; ?>' type='checkbox' value='<?php echo str_replace( $this->service->dir, '', $file ); ?>' />
						</td>
						<td>
							<a target='_Blank' href='<?php echo DL_Helpers::fixslash( str_replace( $this->service->dir, $this->service->url, $file ) ); ?>'>View »</a>
						</td>
						<td>
							<label for='input-<?php echo $id; ?>'>
								<?php echo str_replace( $this->service->dir, '', $file ); ?>
							</label>
						</td>
					</tr>
					<?php
				}
				if ( 0 === $id || 0 === $this->files_count ) {
					?>
					<tr>
						<td colspan=3>
							<p id='wtfnofiles'><?php esc_html_e( 'No resized images found in', 'dlthumbs' ); ?>:<br />
							<code><?php echo $this->service->dir; ?></code>
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
					<?php esc_html_e( 'I understand that pressing the button below will delete the above selected files', 'dlthumbs' ); ?>.
				</label>
				<br />
				<label>
					<input class='nag' value='' type='checkbox' name='nag2' />
					<?php esc_html_e( 'I have backed up the uploads directory before doing this', 'dlthumbs' ); ?> (<code>/<?php echo str_replace( get_home_path(), '', $this->service->dir ); ?>/</code>).
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