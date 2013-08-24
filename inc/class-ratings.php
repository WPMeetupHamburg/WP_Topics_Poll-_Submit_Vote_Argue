<?php
/**
 * Feature Name:	WP Simple Feedback Ratings
 */

if ( ! class_exists( 'WP_Simple_Feedback_Ratings' ) ) {

	class WP_Simple_Feedback_Ratings extends WP_Simple_Feedback {
		
		/**
		 * Instance holder
		 *
		 * @var		NULL | WP_Simple_Feedback_Ratings
		 */
		private static $instance = NULL;
		
		/**
		 * Method for ensuring that only one instance of this object is used
		 *
		 * @return	WP_Simple_Feedback_Ratings
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
			
			add_filter( 'wp_ajax_rate_topic', array( $this, 'rate_topic' ) );
			
			// wp-cron
			add_filter( 'wpsf_check_topics', array( $this, 'check_topics' ) );
			if ( ! wp_next_scheduled( 'wpsf_check_topics' ) )
				wp_schedule_event( time(), 'twicedaily', 'wpsf_check_topics' );
		}
		
		public function rate_topic() {
			
			$topic_id = (int) $_REQUEST[ 'topic' ];
			$rate_type = $_REQUEST[ 'rate_type' ];
			$user_id = get_current_user_id();
			
			// Get list of voted users
			$voted_users = get_post_meta( $topic_id, 'topic-voted-users', TRUE );
			if ( ! is_array( $voted_users ) )
				$voted_users = array();
			
			// Check if the user already voted
			if ( array_key_exists( $user_id, $voted_users ) ) {
				echo 0;
				die;
			} else if ( get_post_meta( $topic_id, 'topic-status', TRUE ) != 'open' ) {
				echo 1;
				die;
			} else {
				// get userdata
				$user_data = get_userdata( $user_id );
				
				// Update Vote list
				$voted_users[ $user_id ] = $rate_type;
				update_post_meta( $topic_id, 'topic-voted-users', $voted_users );
				
				// Update ratings
				if ( $rate_type == 'for-it' ) {
					$rating_positive = get_post_meta( $topic_id, 'topic-rating-positive', TRUE );
					$rating_positive = $rating_positive + 1;
					update_post_meta( $topic_id, 'topic-rating-positive', $rating_positive );
				}
				
				if ( $rate_type == 'against' ) {
					$rating_negative = get_post_meta( $topic_id, 'topic-rating-negative', TRUE );
					$rating_negative = $rating_negative + 1;
					update_post_meta( $topic_id, 'topic-rating-negative', $rating_negative );
				}
				
				if ( $rate_type == 'undecided' ) {
					$rating_abstinence = get_post_meta( $topic_id, 'topic-rating-abstinence', TRUE );
					$rating_abstinence = $rating_abstinence + 1;
					update_post_meta( $topic_id, 'topic-rating-abstinence', $rating_abstinence );
				}
				
				?>
				<li class="usr">
					<div class="avatar"><?php echo get_avatar( $user_id, 40 ); ?></div>
					<div class="name"><?php echo $user_data->display_name; ?></div>
					<br class="clear">
				</li>
				<?php
			}
			
			die;
		}
		
		public function check_topics() {
			
			$topics = get_posts(
				array(
					'post_type'			=> 'topics',
					'post_status'		=> 'published',
					'meta_key'			=> 'topic-status',
					'meta_value'		=> 'open',
					'posts_per_page'	=> -1
				)
			);
			
			if ( ! empty( $topics ) ) {
				foreach ( $topics as $topic ) {
					
					$time_of_topic = mktime( get_the_time( 'h', $topic->ID ), get_the_time( 'm', $topic->ID ), get_the_time( 'i', $topic->ID ), get_the_time( 'm', $topic->ID ), get_the_time( 'd', $topic->ID ), get_the_time( 'y', $topic->ID ) );
					$expiration = get_option( 'wpsf_expiration' );
					if ( $expiration == '' )
						$expiration = 7;
					$time_of_exp = 60*60*24*$expiration;
					$due_time = $time_of_topic + $time_of_exp;
					
					// Check if the topic ends
					if ( $due_time <= time() ) {
						
						// Get the ratings
						$rating_positive = get_post_meta( $topic->ID, 'topic-rating-positive', TRUE );
						$rating_negative = get_post_meta( $topic->ID, 'topic-rating-negative', TRUE );
						
						$status = 'open';
						if ( $rating_positive > $rating_negative )
							$status = 'accepted';
						else if ( $rating_positive < $rating_negative )
							$status = 'declined';
						else if ( $rating_positive == $rating_negative )
							$status = 'undecided';
						
						update_post_meta( $topic->ID, 'topic-status', $status );
					}
				}
			}
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_Simple_Feedback_Ratings::get_instance();
}