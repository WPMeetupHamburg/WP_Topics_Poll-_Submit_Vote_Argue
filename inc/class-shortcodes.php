<?php
/**
 * Feature Name:	WP Simple Feedback Shortcode
 */

if ( ! class_exists( 'WP_TopPoll_Shortcode' ) ) {

	class WP_TopPoll_Shortcode extends WP_TopPoll {
		
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
			
			add_filter( 'the_content', array( $this, 'the_content' ) );
			add_shortcode( 'topics-open', array( $this, 'table_open_topics' ) );
			add_shortcode( 'topics-closed', array( $this, 'table_closed_topics' ) );
		}
		
		public function the_content( $content ) {
			
			// Return if post type is topic
			if ( get_post_type() != 'topics' )
				return $content;
			if ( ! is_single() && get_post_type() == 'topics' )
				return $content;
			
			// Content at first
			$rtn = $content;
			
			// Expiration Date
			$expiration = get_option( 'wpsf_expiration' );
			if ( $expiration == '' )
				$expiration = 7;
			$time_of_topic = mktime( get_the_time( 'h' ), get_the_time( 'm' ), get_the_time( 'i' ), get_the_time( 'm' ), get_the_time( 'd' ), get_the_time( 'y' ) );
			$time_of_exp = 60*60*24*$expiration;
			$due_time = $time_of_topic + $time_of_exp;
			$date_of_exp = date( 'd.m.Y', $due_time );
			
			// Check status
			$status = get_post_meta( get_the_ID(), 'topic-status', TRUE );
			if ( $status == 'open' )
				$class = '';
			else
				$class = 'inactive';
			
			// Check if user already rated this topic
			// Get list of voted users
			$voted_users = get_post_meta( get_the_ID(), 'topic-voted-users', TRUE );
			if ( ! is_array( $voted_users ) )
				$voted_users = array();
			
			if ( array_key_exists( get_current_user_id(), $voted_users ) )
				$class = 'inactive';
			else
				$class = '';
			
			if ( ! is_user_logged_in() )
				$class = 'inactive';
			
			// Build list of users
			$users_for_it = array();
			$users_against = array();
			$users_undecided = array();
			
			foreach ( $voted_users as $voted_user => $vote ) {
				
				if ( $vote == 'for-it' )
					$users_for_it[] = $voted_user;
				if ( $vote == 'against' )
					$users_against[] = $voted_user;
				if ( $vote == 'undecided' )
					$users_undecided[] = $voted_user;
			}
			
			// Lists
			ob_start();
			?>
			<div class="vote <?php echo $class; ?>" topicid="<?php the_ID(); ?>">
				<?php if ( $status == 'open' ) : ?>
					<p class="note"><?php _e( '<strong>Important:</strong> This topic ends at ', 'wp-toppoll-tool' ); ?> <?php echo $date_of_exp; ?></p>
				<?php else : ?>
					<p class="note <?php echo $status; ?>"><?php _e( '<strong>Important:</strong> This vote ended and is ', 'wp-toppoll-tool' ); ?> <strong><?php $status = ucfirst( $status ); _e( $status, 'wp-toppoll-tool' ); ?></strong></p>
				<?php endif; ?>
				<ul class="for-it">
					<li class="btn">
						<?php _e( 'For it', 'wp-toppoll-tool' ); ?>
						(<span class="cnt"><?php echo count( $users_for_it ); ?></span>)
					</li>
					<?php
					foreach ( $users_for_it as $user_for_it ) {
						$user_data = get_userdata( $user_for_it );
						?>
						<li class="usr">
							<div class="avatar"><?php
								if ( is_user_logged_in() )
									echo get_avatar( $user_against, 40 );
							?></div>
							<div class="name">
								<?php
									if ( is_user_logged_in() )
										echo $user_data->display_name;
									else
										_e( 'Anonym', 'wp-toppoll-tool' );
								?>
							</div>
							<br class="clear">
						</li>
						<?php
					}
					?>
				</ul>
				<ul class="undecided">
					<li class="btn">
						<?php _e( 'Undecided', 'wp-toppoll-tool' ); ?>
						(<span class="cnt"><?php echo count( $users_undecided ); ?></span>)
					</li>
					<?php
					foreach ( $users_undecided as $user_undecided ) {
						$user_data = get_userdata( $user_undecided );
						?>
						<li class="usr">
							<div class="avatar"><?php
								if ( is_user_logged_in() )
									echo get_avatar( $user_against, 40 );
							?></div>
							<div class="name">
								<?php
									if ( is_user_logged_in() )
										echo $user_data->display_name;
									else
										_e( 'Anonym', 'wp-toppoll-tool' );
								?>
							</div>
							<br class="clear">
						</li>
						<?php
					}
					?>
				</ul>
				<ul class="against">
					<li class="btn">
						<?php _e( 'Against', 'wp-toppoll-tool' ); ?>
						(<span class="cnt"><?php echo count( $users_against ); ?></span>)
					</li>
					<?php
					foreach ( $users_against as $user_against ) {
						$user_data = get_userdata( $user_against );
						?>
						<li class="usr">
							<div class="avatar"><?php
								if ( is_user_logged_in() )
									echo get_avatar( $user_against, 40 );
							?></div>
							<div class="name">
								<?php
									if ( is_user_logged_in() )
										echo $user_data->display_name;
									else
										_e( 'Anonym', 'wp-toppoll-tool' );
								?>
							</div>
							<br class="clear">
						</li>
						<?php
					}
					?>
				</ul>
				<br class="clear">
			</div>
			<?php
			$list = ob_get_contents();
			ob_end_clean();
			
			return $rtn . $list;
		}
		
		public function table_open_topics() {
			
			$topics = new WP_Query(
				array(
					'post_type'			=> 'topics',
					'post_status'		=> 'published',
					'meta_key'			=> 'topic-status',
					'meta_value'		=> 'open',
					'posts_per_page'	=> -1
				)
			);
			
			ob_start();
			
			if ( $topics->have_posts() ) {
				?>
				<table>
					<thead>
						<tr>
							<th style="width: 70%;"><?php _e( 'Topic', 'wp-toppoll-tool' ); ?></th>
							<th style="width: 15%;"><?php _e( 'Author', 'wp-toppoll-tool' ); ?></th>
							<th style="width: 15%;"><?php _e( 'Expiration', 'wp-toppoll-tool' ); ?></th>
						</tr>
					</thead>
					<tbody>
				<?php
				while ( $topics->have_posts() ) {
					$topics->the_post();
					
					// Expiration Date
					$time_of_topic = mktime( get_the_time( 'h' ), get_the_time( 'm' ), get_the_time( 'i' ), get_the_time( 'm' ), get_the_time( 'd' ), get_the_time( 'y' ) );
					$expiration = get_option( 'wpsf_expiration' );
					if ( $expiration == '' )
						$expiration = 7;
					$time_of_exp = 60*60*24*$expiration;
					$date_of_exp = date( 'd.m.Y', $time_of_topic + $time_of_exp );
					?>
					<tr>
						<td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
						<td><?php
						if ( is_user_logged_in() )
							the_author();
						else
							_e( 'Anonym', 'wp-toppoll-tool' );
						?></td>
						<td><?php echo $date_of_exp; ?></td>
					</tr>
					<?php
				}
				?>
					</tbody>
				</table>
				<?php
			} else {
				?><p><?php _e( 'Currently there are no open topics.', 'wp-toppoll-tool' ) ?></p><?php
			}
			wp_reset_postdata();
			wp_reset_query();
			
			// Output
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		
		public function table_closed_topics() {
			
			$topics = new WP_Query(
				array(
					'post_type'			=> 'topics',
					'post_status'		=> 'published',
					'meta_query'		=> array(
						array(
							'key'		=> 'topic-status',
							'value'		=> 'open',
							'compare'	=> '!=',
						)
					),
					'posts_per_page'	=> -1
				)
			);
				
			ob_start();
				
			if ( $topics->have_posts() ) {
				?>
				<table>
					<thead>
						<tr>
							<th style="width: 70%;"><?php _e( 'Topic', 'wp-toppoll-tool' ); ?></th>
							<th style="width: 15%;"><?php _e( 'Author', 'wp-toppoll-tool' ); ?></th>
							<th style="width: 15%;"><?php _e( 'Conclusion', 'wp-toppoll-tool' ); ?></th>
						</tr>
					</thead>
					<tbody>
				<?php
				while ( $topics->have_posts() ) {
					$topics->the_post();
					
					// Check status
					$status = get_post_meta( get_the_ID(), 'topic-status', TRUE );
					?>
					<tr class="<?php echo $status; ?>">
						<td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></td>
						<td><?php
						if ( is_user_logged_in() )
							the_author();
						else
							_e( 'Anonym', 'wp-toppoll-tool' );
						?></td>
						<td><strong><?php $status = ucfirst( $status ); _e( $status, 'wp-toppoll-tool' ); ?></strong></td>
					</tr>
					<?php
				}
				?>
					</tbody>
				</table>
				<?php
			} else {
				?><p><?php _e( 'Currently there are no closed topics.', 'wp-toppoll-tool' ) ?></p><?php
			}
			wp_reset_postdata();
			wp_reset_query();
			
			// Output
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
	}
	
	// Kickoff
	if ( function_exists( 'add_filter' ) )
		WP_TopPoll_Shortcode::get_instance();
}