<?php
/**
 * Feature Name:	Options
 */

if ( ! class_exists( 'WP_Simple_Feedback_Options' ) ) {

	class WP_Simple_Feedback_Options extends WP_Simple_Feedback {
		
		/**
		 * Instance holder
		 *
		 * @var		NULL | __CLASS__
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @return	__CLASS__
		 */
		public static function get_instance() {
			
			if ( ! self::$instance )
				self::$instance = new self;
			return self::$instance;
		}
		
		/**
		 * Setting up some data, initialize translations and start the hooks
		 *
		 * @return	void
		 */
		public function __construct () {
			
			if ( ! is_admin )
				return;
			
			add_filter( 'admin_menu', array( $this, 'admin_menu' ) );
			add_filter( 'admin_post_wpsf_save_settings', array( $this, 'save_settings' ) );
		}
		
		public function admin_menu() {
			
			add_submenu_page( 'edit.php?post_type=topics', __( 'Settings' ), __( 'Settings' ), 'manage_options', 'wp-simple-feedback-options', array( $this, 'options_page' ) );
		}
		
		public function options_page() {
			
			$redirect_page_id = get_option( 'wpsf_redirect_url' );
			$expiration = get_option( 'wpsf_expiration' );
			if ( $expiration == '' )
				$expiration = 7;
			$pages = get_posts( array(
				'post_type'			=> 'page',
				'post_status'		=> 'publish',
				'posts_per_page'	=> -1
			) );
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"><br></div>
				<h2><?php _e( 'Settings' ); ?></h2>
				
				<?php
				if ( isset( $_GET[ 'message' ] ) ) {
					switch( $_GET[ 'message' ] ) {
						case 'updated':
							echo '<div class="updated"><p>' . __( 'Settings saved.' ) . '</p></div>';
							break;
					}
				}
				?>

				<form method="post" action="<?php echo admin_url( 'admin-post.php?action=wpsf_save_settings' ); ?>">
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<label for="wpsf_redirect_url"><?php _e( 'Redirection', 'wp-simple-feedback' ); ?></label>
								</th>
								<td>
									<select name="wpsf_redirect_url" id="wpsf_redirect_url">
										<?php foreach ( $pages as $page ) : ?>
											<option <?php echo selected( $page->ID, $redirect_page_id ); ?> value="<?php echo $page->ID ?>"><?php echo $page->post_title; ?></option>
										<?php endforeach; ?>
									</select><br />
									<span class="description"><?php _e( 'Choose the page to where the user will be redirected if he head to the custom post type archive page. You should redirect him to the page, where the open topics are displayed.', 'wp-simple-feedback' ); ?></span>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label for="wpsf_expiration"><?php _e( 'Expiration Base', 'wp-simple-feedback' ); ?></label>
								</th>
								<td>
									<input type="number" step="1" min="1" name="wpsf_expiration" id="wpsf_expiration" value="<?php echo $expiration; ?>" class="small-text" /><br />
									<span class="description"><?php _e( 'Set the main expiration time of the topics in days.', 'wp-simple-feedback' ); ?></span>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Changes' ); ?>">
					</p>
				</form>
			</div>
			<?php
		}
		
		public function save_settings() {
			
			update_option( 'wpsf_expiration', $_POST[ 'wpsf_expiration' ] );
			update_option( 'wpsf_redirect_url', $_POST[ 'wpsf_redirect_url' ] );
			
			wp_redirect( 'edit.php?post_type=topics&page=wp-simple-feedback-options&message=updated' );
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_Simple_Feedback_Options::get_instance();
}