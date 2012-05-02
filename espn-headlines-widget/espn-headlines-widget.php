<?php
/*
Plugin Name: ESPN Headlines Widget
Description: Display ESPN content by sport and league using the ESPN API
Version: 0.1
Author: David Pean
Author URI: http://www.twitter.com/davidpean
*/
   
class ESPN_Headlines_Widget extends WP_Widget{

	var $cacheKey = "espn_headlines";
	var $apiBaseURL = "http://api.espn.com/v1/";
        
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
            
        load_plugin_textdomain( 'espn_headlines', false, plugin_dir_path( __FILE__ ) . '/lang/' );
                
		parent::__construct(
	 		'ESPN_Headlines_Widget', // Base ID
			'ESPN Headlines', // Name
			array( 'description' => __( 'ESPN headlines by sports and league', 'espn_headlines' ), ) // Args
		);
	}

	/**
 	* Outputs the content of the widget.
 	*
 	* @args The array of form elements
  	* @instance
 	*/
	function widget( $args, $instance ) {
	
		extract( $args, EXTR_SKIP );
		
		echo $before_widget;
    
        $widget_title = empty($instance['widget_title']) ? '' : apply_filters('widget_title', $instance['widget_title']);

		// Display the widget
		include( plugin_dir_path(__FILE__) . '/views/widget.php' );
		
		echo $after_widget;
		
	} // end widget

 	/**
  	* Processes the widget's options to be saved.
  	*
  	* @new_instance The new instance of values to be generated via the update.
  	* @old_instance The previous instance of values before the update.
  	*/
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
        delete_transient( $this->cacheKey );
                
		$numberToShow = (int) $new_instance['widget_headlines_number'];
		if ( numberToShow == 0 || ! $numberToShow = absint( $numberToShow ) ){
 			$number = 10;
 		}
 		
        $instance['widget_title'] = strip_tags($new_instance['widget_title']);
        $instance['widget_headlines_number'] = $numberToShow;
        $instance['widget_espn_apikey'] = strip_tags($new_instance['widget_espn_apikey']);
        $instance['widget_headlines_type'] = $new_instance['widget_headlines_type'];
        $instance['widget_headlines_filter'] = $new_instance['widget_headlines_filter'];
        $instance['widget_show_description'] = isset($new_instance['widget_show_description']);
                
		return $instance;
		
	} // end widget

  	/**
  	* Generates the administration form for the widget.
  	*
  	* @instance The array of keys and values for the widget.
  	*/
	function form( $instance ) {
	
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'widget_title'                  =>      '',
                		'widget_headlines_number'	=>	5
			)
		);
	
		// Display the admin form
        include( plugin_dir_path(__FILE__) . '/views/admin.php' );
		
	} // end form

}

add_action( 'widgets_init', create_function( '', 'register_widget("ESPN_Headlines_Widget");' ) ); 

/**
* Class to manage global plugin settings
*
*/
class ESPNHeadlinesSettingsMenu {

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {
		load_plugin_textdomain( 'espn_headlines', false, plugin_dir_path( __FILE__ ) . '/lang/' );
		add_action( 'admin_menu', array( $this, 'espn_headlines_admin_add_page' ) );
		add_action('admin_init', array( $this, 'espn_admin_init' ));
	}

	/**
	 * Hook to add to Settings menu item
	 *
	 * Only users with "manage_options" access level will see it
	 */
	function espn_headlines_admin_add_page() {
		add_options_page(
			__('ESPN Headlines Settings', 
			"espn_headlines"), 
			'ESPN Headlines', 
			'manage_options', 
			'espn_headlines',
			array( $this, 'plugin_options_page' )
		);
	}

	/**
	 * Hook to add to register settings on admin init
	 *
	 */
	function espn_admin_init(){
		
		register_setting( 
			'espn_headlines_options', 
			'espn_headlines_options', 
			array( $this, 'espn_headlines_admin_delete_cache' ) 
		);
		
		add_settings_section(
			'plugin_main', 
			__('Configure global widget settings', "espn_headlines"), 
			array( $this, 'plugin_section_text' ), 
			'plugin'
		);
		
		add_settings_field(
			'espn_headlines_api_key',
			__('ESPN API Key:', "espn_headlines"), 
			array( $this, 'plugin_setting_string' ), 
			'plugin', 'plugin_main'
		);
	}

	/**
	 * Callback from add_options_page()
	 *
	 * Displays content of settings page
	 */
	function plugin_options_page() {
		echo '<div class="wrap">';
		echo screen_icon();
		echo '<h2>';
		_e('ESPN Headlines Settings', "espn_headlines");
		echo '</h2>';
		echo '<form action="options.php" method="post">';
		echo settings_fields('espn_headlines_options');
		echo do_settings_sections('plugin');
		
		echo '<input name="Submit" type="submit" value="' . __('Save Changes') . '" />';
		echo '</form></div>';
	}

	/**
	 * Callback from add_settings_section()
	 *
	 * Displays content of settings section
	 */
	function plugin_section_text() {
		echo _e('This widget requires an ESPN API Key. ', "espn_headlines");
		echo "<br/><br><a href='http://developer.espn.com/' target='_new'>"; 
		echo _e("Please visit the ESPN Developer Center to request or retrieve your key and enter it below", "espn_headlines");
		echo "</a><p>";
	}

	/**
	 * Callback from add_settings_field
	 *
	 * Fills the field with the desired inputs as part of the larger form
	 */
	function plugin_setting_string() {
		$options = get_option('espn_headlines_options');
		echo "<input id='espn_headlines_api_key' name='espn_headlines_options[api_key]' size='40' type='text' value='{$options['api_key']}' />";
	
		if( isset( $options['api_key'] ) ){
			echo '<br>';
			//Let's make sure they have a valid key
			
			$json = get_transient("espn_headlines_admin" );
			if( $json === false ){
				$response = wp_remote_get( "http://api.espn.com/v1/" . 'sports?apikey=' . $options['api_key'] );
				
				if( !is_wp_error( $response ) ) {
			    	$json = json_decode(wp_remote_retrieve_body($response));
			     	$cache = set_transient("espn_headlines_admin", $json, 60 * 60 * 4);
				}
			}else{
				echo '<!-- ESPN Headlines admin data from cache -->';	
			}
			
		     $invalid = $json->status == "error" && $json->code == 403;
		     
		     if( !$invalid ){
				echo '<span style="color:green;">';
				echo _e('Valid API Key', "espn_headlines");
				echo '</span>';
		     }else{
		     	echo '<span style="color:red;">';
				echo $json->message;
				echo '</span>';
		     }
		 
		}
	
	}
	
	/**
	 * Callback from register_setting
	 *
	 * We aren't doing anything to the value, just making sure
	 * cache is cleared on every time you save the value
	 *
	 * @args String of form value
	 */
	function espn_headlines_admin_delete_cache( $input ){
		delete_transient("espn_headlines_admin");
		return $input;
	}
}

new ESPNHeadlinesSettingsMenu();
?>