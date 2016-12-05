<?php
/**
 * Functions associated with the WordPress front end.
 *
 * @class    Breaking_News
 * @version  1.0.0
 * @category Class
 */
class Breaking_News {

	/** @var boolean Check plugin initiated. */
	private static $initiated = false;

	/**
	 * Ensures only one instance of Breaking_News is loaded or can be loaded.
	 */
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Enable the ability to internationalise the plugin and load scripts used for the admin section.
	 */
	public static function init_hooks() {
		self::$initiated = true;
		load_plugin_textdomain('breaking-news');
		add_action('wp_enqueue_scripts', array('Breaking_News', 'bn_enqueue_front_end_scripts'));
	}
	
	/**
	 * Load stylesheet used for formatting the Breaking News in WordPress pages.
	 */
	public static function bn_enqueue_front_end_scripts() {
		wp_enqueue_style('custom-front-end', plugins_url('includes/custom-style-front-end.css', __FILE__), array(), self::script_version_id());
	}
	
	/**
	 * Display the flagged Breaking News post as a headline on every page in WordPress
	 */
	public static function bn_display_breaking_news() {
		$bn_settings = (array) get_option( 'bn_settings' );
		$bn_post_id = 0;
		$bn_title = '';
		$bn_background_color = '';
		$bn_text_color = '';
		
		if(isset($bn_settings['bn_post_id']))
			$bn_post_id = $bn_settings['bn_post_id'];
		
		if($bn_post_id > 0) {

			if(isset($bn_settings['bn_title']))
				$bn_title = $bn_settings['bn_title'];
			
			if(isset($bn_settings['bn_background_color']))
				$bn_background_color = $bn_settings['bn_background_color'];
			
			if(isset($bn_settings['bn_text_color']))
				$bn_text_color = $bn_settings['bn_text_color'];
			
			$bn_expired = false;
			$bn_expiry_dtm = get_post_meta($bn_post_id, '_bn_expire_dtm', true);
			
			// Check if our selected Breaking News post has expired
			$bn_expired = self::bn_check_expired_breaking_news($bn_expiry_dtm);
			
			if(!$bn_expired) {
				$bn_post_title = get_post_meta($bn_post_id, '_bn_post_title', true);
			
				$bn_permalink = get_permalink($bn_post_id);
			
				// If the custom title is blank then use the post title
				if(empty($bn_post_title)) {
					$bn_post_title = get_the_title($bn_post_id);
				}
				
				if(!empty($bn_settings['bn_title']))
					echo '<div class="breaking_news" style="color:' . esc_attr($bn_text_color) . ';background-color:' . esc_attr($bn_background_color) 
						.'">' . $bn_title . ': <a href="' . esc_url($bn_permalink) . '" style="color:' . esc_attr($bn_text_color) 
						. ';text-decoration: underline;">' . esc_html($bn_post_title) . '</a></div>';
			}
			elseif($bn_expired AND !empty($bn_expiry_dtm)) {
				// Unflag the post as Breaking News if it has expired
				self::delete_breaking_news($bn_post_id);
			}
		}	
	}
	
	/**
	 * Check if the current Breaking News post has expired.
	 * @param  string $bn_expiry_dtm Expiry date of selected breaking news post
	 * @return boolean
	 */
	public static function bn_check_expired_breaking_news($bn_expiry_dtm) {
		if(!empty($bn_expiry_dtm)) {
			// Use WordPress timezone, convert to epoch for comparison
			$current_time = current_time('timestamp');
			
			// Check if breaking news has expired
			if($current_time > strtotime($bn_expiry_dtm)){
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Remove all traces of the plugin when it has been uninstalled.
	 */
	public static function on_uninstall()
    {
        if ( ! current_user_can( 'activate_plugins' ) )
            return;
        check_admin_referer( 'bulk-plugins' );

		//Remove all meta data and options
		$bn_post_id = 0;
		$bn_settings = (array) get_option( 'bn_settings' );
		
		if(isset($bn_settings['bn_post_id']))
			$bn_post_id = $bn_settings['bn_post_id'];
		
		self::delete_breaking_news($bn_post_id);
		
		delete_option('bn_settings');
    }
	
	/**
	 * Remove the breaking news post meta from the current one.  No breaking news will display after this.
	 * @param int $post_id Post id of the selected Breaking News
	 */
	public static function delete_breaking_news ($post_id) {
		if($post_id != 0) {
			$bn_settings = (array) get_option( 'bn_settings' );
			$bn_settings['bn_post_id'] = 0;
			
			update_option('bn_settings', $bn_settings, true);
			delete_post_meta($post_id, '_bn_enable');
			delete_post_meta($post_id, '_bn_expire');
			delete_post_meta($post_id, '_bn_post_title');
			delete_post_meta($post_id, '_bn_expire_dtm');
		}
	}
	
	/**
	 * Used for debugging when we want javascript and stylesheets to reflect our changes
	 */
	public static function script_version_id() {
		if ( WP_DEBUG )
			return time();
		return BN_VERSION;
	}
}

?>