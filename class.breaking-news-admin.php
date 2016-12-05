<?php
/**
 * Functions for handling the breaking news admin screens.
 *
 * @class    Breaking_News_Admin
 * @version  1.0.0
 * @category Class
 */
class Breaking_News_Admin {
	
	/** @var boolean Check plugin initiated. */
	protected static $initiated = false;
	
	/**
	 * Ensures only one instance of Breaking_News_Admin is loaded or can be loaded.
	 */
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}
	
	/**
	 * Initiate the hooks used for saving breaking news post meta data and settings for the plugin.
	 */
	public static function init_hooks() {
		global $pagenow;
		
		self::$initiated = true;
		
		add_action('admin_menu', array('Breaking_News_Admin', 'bn_settings_menu'));
		add_action('admin_init', array('Breaking_News_Admin', 'admin_init'));
		add_action('admin_enqueue_scripts', array('Breaking_News_Admin', 'bn_admin_enqueue_scripts'));
		add_action('save_post', array('Breaking_News_Admin', 'bn_save_meta_box'));
	}
	
	/**
	 * Add a hyperlink for the Breaking News settings page.
	 * @param string $links Pass external links to the function
	 * @return array
	 */
	public static function bn_add_action_links ( $links ) {
		$mylinks = array('<a href="' . admin_url( 'options-general.php?page=bn-settings' ).'">Settings</a>');
		return array_merge( $links, $mylinks );
	}
	
	/**
	 * Initiate the hooks used for saving breaking news post meta data and settings for the plugin.
	 */
	public static function admin_init() {
		
		// Create a metabox for flagging a post as breaking news
		add_meta_box('bn-meta-box', __('Breaking News Meta', 'breaking-news'), array('Breaking_News_Admin', 'bn_meta_box'), 'post');
		
		// Store the plugin's configuration in the WordPress options table
		register_setting('bn-settings-group', 'bn_settings', array('Breaking_News_Admin', 'bn_settings_validate'));
		
		// Breaking News options declaration
		add_settings_section('first_section', __('Instructions', 'breaking-news'), array('Breaking_News_Admin', 'bn_options_section_text'), 'bn_settings');
		add_settings_field('bn_title', __('Title', 'breaking-news'), array('Breaking_News_Admin', 'bn_title_display'), 'bn_settings', 'first_section');
		add_settings_field('bn_background_color', __('Background Color', 'breaking-news'), array('Breaking_News_Admin', 'bn_background_color_display'), 'bn_settings', 'first_section');
		add_settings_field('bn_text_color', __('Text Color', 'breaking-news'), array('Breaking_News_Admin', 'bn_text_color_display'), 'bn_settings', 'first_section');
		add_settings_field('bn_post_id', '', array('Breaking_News_Admin', 'bn_post_id_display'), 'bn_settings', 'first_section');
	}
	
	/**
	 * Create a new settings menu for Breaking News nested under the Settings Menu.
	 */
	public static function bn_settings_menu() {
		add_options_page( __('Breaking News Options', 'breaking-news'), __('Breaking News', 'breaking-news'), 'manage_options', 'bn-settings', array('Breaking_News_Admin', 'bn_settings_page'));
	}
	
	/**
	 * Display the settings associated with the Breaking News plugin
	 */
	public static function bn_settings_page() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __('You do not have sufficient permissions to access this page.', 'breaking-news'));
		}
		
		?>
		<div class="wrap">
		<h2><?php _e('Breaking News', 'breaking-news');?></h2>

		<form method="post" action="options.php">
			<?php settings_fields( 'bn-settings-group' ); ?>
			<?php do_settings_sections( 'bn_settings' ); ?>
			<?php submit_button(); ?>

		</form>
		</div>
		<?php
	}
	
	/**
	 * Sanitize and validate the user input from the settings page.
	 * @param array $input User input requiring validation
	 * @return array
	 */
	public static function bn_settings_validate($input) {
		$output = (array) get_option( 'bn_settings' );
		
		if(isset($input['bn_title']))
			$output['bn_title'] = sanitize_text_field($input['bn_title']);
		if(isset($input['bn_background_color']))
			$output['bn_background_color'] = self::bn_validate_color_field($input['bn_background_color']);
		if(isset($input['bn_text_color']))
			$output['bn_text_color'] = self::bn_validate_color_field($input['bn_text_color']);
		if(isset($input['bn_post_id']))
			$output['bn_post_id'] = absint($input['bn_post_id']);
		
		return $output;
	}
	
	/**
	 * Display some instructions and an edit link to the post flagged as Breaking News.
	 */
	public static function bn_options_section_text() {
		echo '<p>';
		_e('Configure how you want your breaking news to display below.', 'breaking-news');
		echo '</p>';
		
		$bn_settings = (array) get_option( 'bn_settings' );
		$bn_post_title = '';
		$bn_post_id = 0;
		
		if(isset($bn_settings['bn_post_id'])) {
			$bn_post_id = $bn_settings['bn_post_id'];
			$bn_permalink = get_edit_post_link($bn_post_id);
			$bn_post_title = get_post_meta($bn_post_id, '_bn_post_title', true);
		}
		
		// If the custom title is blank then use the post title
		if(empty($bn_post_title)) {
			$bn_post_title = get_the_title($bn_post_id);
		}
		
		echo '<p><strong>'.__('The current breaking news post: ', 'breaking-news').'</strong>';
		
		if($bn_post_id > 0)
			echo '<a href="' . esc_url($bn_permalink).'">' . esc_html($bn_post_title) . '</a>';
		else 
			_e('No post selected', 'breaking-news');
		
		echo '</p>';
	}
	
	/**
	 * Display a field to capture the title for your Breaking News header.
	 */
	public static function bn_title_display() {
		$bn_settings = (array) get_option( 'bn_settings' );
		if(isset($bn_settings['bn_title']))
			$bn_title = $bn_settings['bn_title'];
		else
			$bn_title = '';
		echo '<input type="text" id="bn_settings[bn_title]" name="bn_settings[bn_title]" class="regular-text ltr" value="' 
				. esc_attr($bn_title) . '"/>';
	}
	
	/**
	 * Display a field for setting the Breaking News background header color.
	 */
	public static function bn_background_color_display() {
		$bn_settings = (array) get_option( 'bn_settings' );
		if(isset($bn_settings['bn_background_color']))
			$bn_background_color = $bn_settings['bn_background_color'];
		else
			$bn_background_color = '';
		echo '<input type="text" id="bn_settings[bn_background_color]" name="bn_settings[bn_background_color]" class="color-field" value="' 
				. esc_attr($bn_background_color) . '"/>';
	}
	
	/**
	 * Display a field for setting the Breaking News text header color.
	 */
	public static function bn_text_color_display() {
		$bn_settings = (array) get_option( 'bn_settings' );
		if(isset($bn_settings['bn_text_color']))
			$bn_text_color = $bn_settings['bn_text_color'];
		else 
			$bn_text_color = '';
		echo '<input type="text" id="bn_settings[bn_text_color]" name="bn_settings[bn_text_color]" class="color-field" value="' 
				. esc_attr($bn_text_color) . '"/>';
	}
	
	/**
	 * Add a hidden field for storing the post's id that is flagged as Breaking News.
	 */
	public static function bn_post_id_display() {
		$bn_settings = (array) get_option( 'bn_settings' );
		if(isset($bn_settings['bn_post_id']))
			$bn_post_id = $bn_settings['bn_post_id'];
		else
			$bn_post_id = 0;
		echo '<input type="hidden" id="bn_settings[bn_post_id]" name="bn_settings[bn_post_id]" class="regular-text ltr" value="' 
				. esc_attr($bn_post_id) . '"/>';
	}
	
	/**
	 * Ensure that the hex color code has a leading hash.
	 * @param string $input Hex color code
	 * @return string
	 */
	public static function bn_validate_color_field($input) {
		$input = sanitize_text_field($input);
		
		if(preg_match('/^[a-f0-9]{6}$/i', $input))
		{
			$input = '#'.$input;
		}
		
		return $input;
	}
	
	/**
	 * Display a meta box for capturing data to flag the selected post as Breaking News.
	 * @param WP_Post $post WordPress post object
	 * @return string
	 */
	public static function bn_meta_box($post) {

		wp_nonce_field( basename( __FILE__ ), 'bn_post_nonce' );
		
		$bn_settings = (array) get_option( 'bn_settings' );
		$bn_post_id = 0;
		$bn_expired = false;
		$bn_enable = false;
		$bn_expire = false;
		$bn_post_title = '';
		$bn_expire_dtm = '';
		
		// Check if a breaking news post has been flagged
		if(isset($bn_settings['bn_post_id'])){
			$bn_post_id = $bn_settings['bn_post_id'];
			$bn_expire_dtm = get_post_meta($post->ID, '_bn_expire_dtm', true);
			
			// Check if breaking news has expired
			$bn_expired = Breaking_News::bn_check_expired_breaking_news($bn_expire_dtm);
			
			if($bn_expired AND !empty($bn_expire_dtm)) {
				Breaking_News::delete_breaking_news($bn_post_id);
				$bn_expire_dtm = '';
				$bn_expire = false;
				$bn_post_title = '';
				$bn_enable = false;
			}
			else {
				$bn_enable = get_post_meta($post->ID, '_bn_enable', true);
				$bn_post_title = get_post_meta($post->ID, '_bn_post_title', true);
				$bn_expire = get_post_meta($post->ID, '_bn_expire', true);
			}
		}

		if(($bn_post_id == 0) OR ($post->ID == $bn_post_id)) {
		?>
			<div id="bn-meta-box-post" class="inside">
				<p>
					<label for="bn_enable"><?php _e( 'Make this post breaking news?', 'breaking-news' )?></label>
					<input type="checkbox" name="bn_enable" id="bn_enable" <?php checked( $bn_enable, '1' ); ?> />
				</p>
				<div class="form-field">
					<label for="bn_post_title"><?php _e( 'Post Title', 'breaking-news' )?></label>
					<input type="text" name="bn_post_title" id="bn_post_title" size="40" value="<?php echo esc_attr($bn_post_title); ?>" />
				</div>
				<p>
					<label for="bn_expire"><?php _e( 'Expire breaking news?', 'breaking-news' )?></label>
					<input type="checkbox" name="bn_expire" id="bn_expire" <?php checked( $bn_expire, '1' ); ?>/>
				</p>
				<div class="form-field">
					<label for="bn_expire_dtm"><?php _e( 'Expiry Date', 'breaking-news' )?></label>
					<input type="text" name="bn_expire_dtm" id="bn_expire_dtm" class="date-picker" value="<?php echo esc_attr($bn_expire_dtm); ?>" />
				</div>
			</div>
		<?php
		}
		else {
			echo __('Breaking news has been selected on another post: ', 'breaking-news') . '<a href="' . esc_url(get_edit_post_link($bn_post_id)) 
					. '">' . esc_html(get_the_title($bn_post_id)) . '</a>';
		}
	}
	
	/**
	 * Save Breaking News meta box content.
	 * @param int $post_id Post ID
	 */
	public static function bn_save_meta_box($post_id) {
		// Verify the nonce before proceeding
		if (!isset( $_POST['bn_post_nonce']) || !wp_verify_nonce( $_POST['bn_post_nonce'], basename(__FILE__)))
			return $post_id;
		
		$bn_settings = (array) get_option('bn_settings');
		
		if(isset($bn_settings['bn_post_id']))
			$bn_post_id = $bn_settings['bn_post_id'];
		else
			$bn_post_id = 0;
		
		// If the enable checkbox is not checked then delete all the Breaking News meta data only if the current post is set to breaking news
		if(isset($_POST['bn_enable'])) {
			
			// Store the post id as an option
			$bn_settings['bn_post_id'] = $post_id;
			update_option('bn_settings', $bn_settings, true);
			
			if(!empty($_POST['bn_post_title'])) {
				update_post_meta($post_id, '_bn_post_title', sanitize_text_field($_POST['bn_post_title']));
			} 
			else {
				delete_post_meta($post_id, '_bn_post_title');
			}
			
			if(isset($_POST['bn_enable'])) {
				update_post_meta($post_id, '_bn_enable', '1');
			} 
			else {
				delete_post_meta($post_id, '_bn_enable');
			}
			
			if(isset($_POST['bn_expire'])) {
				update_post_meta($post_id, '_bn_expire', '1');
			} 
			else {
				delete_post_meta($post_id, '_bn_expire');
			}
			
			if(!empty($_POST['bn_expire_dtm'])) {
				update_post_meta($post_id, '_bn_expire_dtm', sanitize_text_field($_POST['bn_expire_dtm']));
			} 
			else {
				delete_post_meta($post_id, '_bn_expire_dtm');
			}
		}
		elseif ($bn_post_id == $post_id) {
			Breaking_News::delete_breaking_news($post_id);
		}
	}
	
	/**
	 * Enqueue stylesheets and scripts on specific admin screens.
	 * @param string $hook Admin page name
	 */
	public static function bn_admin_enqueue_scripts($hook) {
		global $current_screen;
		global $post;

		if ($hook == 'settings_page_bn-settings') {
			// Prerequisites for the WordPress color picker
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script('bn-custom-script-handle', plugins_url('includes/custom-options-script.js', __FILE__ ), 
				array( 'wp-color-picker' ), Breaking_News::script_version_id(), true); 
		}
		
		// Load scripts when Breaking News is not set or when the post it is set on is selected
		if($current_screen->post_type == 'post') {
			$bn_settings = (array) get_option('bn_settings');
			$bn_post_id = 0;
			
			if(isset($bn_settings['bn_post_id']))
				$bn_post_id = $bn_settings['bn_post_id'];
			
			if($post->ID == $bn_post_id OR $bn_post_id == 0) {
				// Style datepicker with CSS from CDN
				wp_enqueue_style('jquery-style', 'https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css');
				wp_enqueue_style('custom-admin-style', plugins_url('includes/custom-style-admin.css', __FILE__), 
					array(), Breaking_News::script_version_id());
				
				wp_enqueue_script('time-picker', plugins_url('includes/time-picker.min.js', __FILE__), 
					array('jquery', 'jquery-ui-datepicker'), Breaking_News::script_version_id(), true);
				wp_enqueue_script('custom-post-meta-box', plugins_url('includes/custom-post-meta-box-script.js', __FILE__), 
					array('jquery'), Breaking_News::script_version_id(), true);
				
				// Custom message to prevent saving of post without an expiry date if that is checked
				$bn_data = array('stop_save_message' => __( 'Please pick an expiry date for the breaking news.', 'breaking-news' ));
				wp_localize_script( 'custom-post-meta-box', 'bn_data_obj', $bn_data );
			}
		}
	}
}

?>