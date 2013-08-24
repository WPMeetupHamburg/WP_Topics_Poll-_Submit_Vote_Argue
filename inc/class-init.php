<?php
/**
 * Feature Name:	Init
 */

if ( ! class_exists( 'WP_TopPoll_Init' ) ) {

	class WP_TopPoll_Init extends WP_TopPoll {
		
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
			
			if ( ! is_admin() ) {
				// Redirect
				add_filter( 'template_include', array( $this, 'redirect' ) );
				
				// Frontend Scripts
				add_filter( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_filter( 'wp_head', array( $this, 'wp_head' ) );
			}
		}
		
		public function redirect( $template ) {
			
			if ( get_post_type() == 'topics' && is_archive() ) {
				
				$redirect_page_id = get_option( 'wpsf_redirect_url' );
				if ( $redirect_page_id == '' )
					return $template;
				
				wp_redirect( get_permalink( $redirect_page_id ) );
				exit;
			}
				
			return $template;
		}
		
		public function wp_head() {
			
			?>
			<script type="text/javascript">
				var ajaxurl = '<?php echo get_admin_url( null, 'admin-ajax.php' ); ?>';
			</script>
			<?php
		}
		
		/**
		 * Enqueue Scripts
		 *
		 * @return	void
		 */
		public function enqueue_scripts () {
			// Style
			wp_enqueue_style( 'WP_TopPoll', plugin_dir_url( __FILE__ ) . '../css/frontend.css' );
		
			// Script
			wp_enqueue_script( 'WP_TopPoll_Frontend', plugin_dir_url( __FILE__ ) . '../js/frontend.js', array( 'jquery' ) );
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_TopPoll_Init::get_instance();
}