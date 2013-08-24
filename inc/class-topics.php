<?php
/**
 * Feature Name:	WP Simple Feedback Topics
*/

if ( ! class_exists( 'WP_TopPoll_Topics' ) ) {

	class WP_TopPoll_Topics extends WP_TopPoll {
		
		/**
		 * Instance holder
		 *
		 * @var		NULL | WP_TopPoll_Topics
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @return	WP_TopPoll_Topics
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
			global $pagenow;
			
			// Add Custom Post Type
			add_filter( 'init', array( $this, 'init_post_type' ) );
			
			// Save Meta Data
			add_filter( 'save_post', array( $this, 'save_meta_data' ) );
			
			// Custom Columns
			if ( 'edit.php' == $pagenow && 'topics' == $_GET[ 'post_type' ] ) {
				
				// Normal Columns
				add_filter( 'manage_edit-topics_columns', array( $this, 'custom_column_head' ) );
				add_filter( 'manage_posts_custom_column', array( $this, 'custom_column_content' ) );
			}
		}
		
		/**
		 * Initialize Post Type
		 *
		 * @return	void
		 */
		public function init_post_type() {
				
			$labels = array(
				'name'                => _x( 'Voting-Instances', 'Post Type General Name', 'wp-toppoll-tool' ),
				'singular_name'       => _x( 'Voting-Instance', 'Post Type Singular Name', 'wp-toppoll-tool' ),
				'menu_name'           => __( 'Umfrage', 'wp-toppoll-tool' ),
				'parent_item_colon'   => __( 'Parent Product:', 'wp-toppoll-tool' ),
				'all_items'           => __( 'Alle Umfragen', 'wp-toppoll-tool' ),
				'view_item'           => __( 'Zeige Umfragen', 'wp-toppoll-tool' ),
				'add_new_item'        => __( 'Neue Umfrage anlegen', 'wp-toppoll-tool' ),
				'add_new'             => __( 'Neue Umfrage anlegen', 'wp-toppoll-tool' ),
				'edit_item'           => __( 'Umfrage ändern', 'wp-toppoll-tool' ),
				'update_item'         => __( 'Umfrage aktualisieren', 'wp-toppoll-tool' ),
				'search_items'        => __( 'Suche Umfrage', 'wp-toppoll-tool' ),
				'not_found'           => __( 'Keine Umfrage vorhanden', 'wp-toppoll-tool' ),
				'not_found_in_trash'  => __( 'Keine Umfrage vorhanden', 'wp-toppoll-tool' ),
			);
			$args = array(
				'label'               => __( 'topicpoll', 'wp-toppoll-tool' ),
				'description'         => __( 'Topic-Poll-Submit-Voting-Instance', 'wp-toppoll-tool' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'author', 'comments', 'custom-fields', ),
				'taxonomies'          => array( 'category', 'post_tag' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'menu_icon'           => '',
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
			);
			register_post_type( 'topicpoll', $args );
				
		}
		
		/**
		 * Add Costum Collumn Head
		 * 
		 * @param	array $defaults the default headers headers
		 * @return	array $defaults the modified headers headers
		 */
		public function custom_column_head( $defaults ) {
			
			$new_fields = array(
				'topic-author'		=> __( 'Author' ),
			);
			
			$defaults = array_insert( $defaults, 'title', $new_fields );
			
			return $defaults;
		}
		
		/**
		 * Add Costum Collumn Content
		 * 
		 * @param	string the column name
		 * @return	void
		 */
		public function custom_column_content( $column_name ) {
			global $post;

			$post_id = $post->ID;
			
			if ( 'topic-author' == $column_name )
				echo get_the_author();
		}
		
		/**
		 * Initialize Meta Boxes
		 *
		 * @uses	add_meta_box, __
		 * @return	void
		 */
		public function register_topics_metaboxes() {
			
			add_meta_box( 'topic-ratings', __( 'Rating', 'wp-toppoll-tool' ), array( $this, 'topic_metabox_rating' ), 'topics', 'side', 'high' );
		}
		
		/**
		 * The Rating Meta Box Contents
		 *
		 * @param	object $post
		 * @return	void
		 */
		public function topic_metabox_rating( $post ) {
				
			$rating_positive = get_post_meta( $post->ID, 'topic-rating-positive', TRUE );
			$rating_negative = get_post_meta( $post->ID, 'topic-rating-negative', TRUE );
			$rating_abstinence = get_post_meta( $post->ID, 'topic-rating-abstinence', TRUE );
			?>
			<table class="form-table">
				<tr>
					<th class="row-title"><label for="topic-rating-positive" class="left"><?php _e( 'Positive Rating', 'wp-toppoll-tool' ); ?></label></th>
					<td>
						<input type="text" name="topic-rating-positive" id="topic-rating-positive" size="12" value="<?php echo ! empty( $rating_positive ) ? esc_attr( $rating_positive ) : '0'; ?>" /><br />
					</td>
				</tr>
				<tr>
					<th class="row-title"><label for="topic-rating-negative" class="left"><?php _e( 'Negative Rating', 'wp-toppoll-tool' ); ?></label></th>
					<td>
						<input type="text" name="topic-rating-negative" id="topic-rating-positiv" size="12" value="<?php echo ! empty( $rating_negative ) ? esc_attr( $rating_negative ) : '0'; ?>" /><br />
					</td>
				</tr>
				<tr>
					<th class="row-title"><label for="topic-rating-abstinence" class="left"><?php _e( 'Abstinence Rating', 'wp-toppoll-tool' ); ?></label></th>
					<td>
						<input type="text" name="topic-rating-abstinence" id="topic-rating-abstinence" size="12" value="<?php echo ! empty( $rating_abstinence ) ? esc_attr( $rating_abstinence ) : '0'; ?>" /><br />
					</td>
				</tr>
				<tr>
					<th class="row-title"><label for="topic-status" class="left"><?php _e( 'Status', 'wp-toppoll-tool' ); ?></label></th>
					<td>
						<select name="topic-status" id="topic-status">
							<option <?php echo selected( 'open', get_post_meta( $post->ID, 'topic-status', TRUE ) ); ?> value="open"><?php _e( 'Open', 'wp-toppoll-tool' ); ?></option>
							<option <?php echo selected( 'accepted', get_post_meta( $post->ID, 'topic-status', TRUE ) ); ?> value="accepted"><?php _e( 'Accepted', 'wp-toppoll-tool' ); ?></option>
							<option <?php echo selected( 'declined', get_post_meta( $post->ID, 'topic-status', TRUE ) ); ?> value="declined"><?php _e( 'Declined', 'wp-toppoll-tool' ); ?></option>
							<option <?php echo selected( 'undecided', get_post_meta( $post->ID, 'topic-status', TRUE ) ); ?> value="undecided"><?php _e( 'Undecided', 'wp-toppoll-tool' ); ?></option>
						</select>
					</td>
				</tr>
			</table>
			<?php
		}
		
		/**
		 * Saves the post meta
		 *
		 * @uses	DOING_AUTOSAVE, current_user_can
		 * @return	void
		 */
		public function save_meta_data() {
				
			// Preventing Autosave, we don't want that
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return;
		
			// We don't need to save because there is Post Array
			if ( 0 >= count( $_POST ) )
				return;
		
			// Do we have a ticker post
			if ( ! isset( $_POST[ 'post_type' ] ) || 'topics' != $_POST[ 'post_type' ] )
				return;
		
			// Check permissions
			if ( ! current_user_can( 'edit_post', $_POST[ 'ID' ] ) )
				return;
				
			// Save the ratings
			if ( isset( $_POST[ 'topic-rating-positive' ] ) && '' != trim( $_POST[ 'topic-rating-positive' ] ) )
				update_post_meta( $_POST[ 'ID' ], 'topic-rating-positive', $_POST[ 'topic-rating-positive' ] );
			
			if ( isset( $_POST[ 'topic-rating-negative' ] ) && '' != trim( $_POST[ 'topic-rating-negative' ] ) )
				update_post_meta( $_POST[ 'ID' ], 'topic-rating-negative', $_POST[ 'topic-rating-negative' ] );
			
			if ( isset( $_POST[ 'topic-rating-abstinence' ] ) && '' != trim( $_POST[ 'topic-rating-abstinence' ] ) )
				update_post_meta( $_POST[ 'ID' ], 'topic-rating-abstinence', $_POST[ 'topic-rating-abstinence' ] );
			
			if ( isset( $_POST[ 'topic-status' ] ) && '' != trim( $_POST[ 'topic-status' ] ) )
				update_post_meta( $_POST[ 'ID' ], 'topic-status', $_POST[ 'topic-status' ] );
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_TopPoll_Topics::get_instance();
}