<?php

defined( 'ABSPATH' ) || exit;

/**
 * @TODO wire this all up & complete.
 */
class DL_UI {
	public function __construct() {

	}

    /**
	 * Add Menu Page
	 *
	 * @since 2.0
	 */
	public function add_admin_menu() {
		$this->menu_id = add_management_page(
			esc_html__( 'Delete Thumbnails', 'dlthumbs' ),
			esc_html__( 'Delete Thumbnails', 'dlthumbs' ),
			'manage_options', // Caps level.
			'dlthumbs',
			[ $this, 'interface' ]
		);
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
			$this->files       = $this->get_files_from_folder( $this->dir );
			$this->files_count = count( $this->files );

			// Check the dir for permissions.
			$writable = $this->check_permission( $this->dir );
			if ( ! $writable ) :
				?>
				<div class='notice notice-error'><p>
					<?php
					sprintf( esc_html_e( 'This plugin requires Your upload directory %s to be at least %s so PHP can edit it. The deletion of files will most likely not work. Please contact your host or website provider for assistance. Mention your %s is currently set to', 'dlthumbs' ), 'CHMOD', '<code>755</code>', 'CHMOD'); ?>: <code><?php echo $writable; ?></code>
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
	 * List all files from uploads directory.
	 *
	 * @since 2.0
	 */
	public function dltthumbs_list_form() {
		?>
		<div class="notice notice-<?php echo ( 0 === $this->files_count ) ? 'error' : 'info'; ?>">
			<p>
				<?php esc_html_e( 'Browsing', 'dlthumbs' ); ?>:
				<code>/<?php echo str_replace( get_home_path(), '', $this->dir ); ?>/</code>
				<?php echo $this->files_count; ?>
				<?php esc_html_e( 'files were found', 'dlthumbs' ); ?>
				<?php if ( $this->files_count > 0 ) { ?>
					, <span class='total_thumbnail_count'></span>
					<?php esc_html_e( 'images detected as resized images', 'dlthumbs' ); ?>.
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
							<p id='wtfnofiles'><?php esc_html_e( 'No resized images found in', 'dlthumbs' ); ?>:<br />
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
					<?php esc_html_e( 'I understand that pressing the button below will delete the above selected files', 'dlthumbs' ); ?>.
				</label>
				<br />
				<label>
					<input class='nag' value='' type='checkbox' name='nag2' />
					<?php esc_html_e( 'I have backed up the uploads directory before doing this', 'dlthumbs' ); ?> (<code>/<?php echo str_replace( get_home_path(), '', $this->dir ); ?>/</code>).
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

}