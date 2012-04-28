<?php
/*
Plugin Name: ESPN Headlines Widget
Description: Pulls in news by sport and team using the ESPN API
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

?>