<?php if(strlen(trim($widget_title)) > 0) { ?>
<h3 class="widget-title">
<?php echo $widget_title; ?>
</h3>

<?php
} // end if

$cacheKey = $this->cacheKey;
$cacheTTL = 60 * 10;

$json = get_transient( $cacheKey );

if( $json === false ){
    
    $limit = $instance['widget_headlines_number'];
    
    $response = wp_remote_get( $this->apiBaseURL . 'sports/' . $instance['widget_headlines_filter'] . '/news?apikey=' . $instance['widget_espn_apikey'] . '&limit=' . $limit);
 
    if( !is_wp_error( $response ) ) {
        $json = json_decode(wp_remote_retrieve_body($response));

        $cache = set_transient($cacheKey, $json, $cacheTTL);
        
        echo "<!--Getting from source-->";
    }
    
}else{
    
    echo '<!--Getting from cache-->';
}

if( $json->status == "success" ){

	echo '<ul>';
    
    if( empty( $json->headlines ) ){
    	echo _e("No headlines found","espn_headlines");
    }
    
	foreach( $json->headlines as $headline ){
        $url = "";
        $links = $headline->links;
                
        if( $links ){
        	$web = $links->web;
            $url = $web->href;
        }
    	if ( $instance['widget_show_description'] ) {
			$description = "<div class='rssSummary'>$headline->description</div>";
		} else {
			$description = '';
		}
		
        $publishedDate = date( "F j", strtotime($headline->published) );
        if( isset( $headline->linkText ) && isset( $url ) ){
        	echo "<li><a href='" . $url . "' target='_new'>" . $headline->linkText . '</a><div>' . $headline->type . ' | ' . $publishedDate . '</div>' . $description . '</li>';
        }
                
    }
        
    echo '</ul>';
}
?>
<div style="margin-top:5px;">
    <a href="http://espn.go.com/" target="_new"><img src="http://a.espncdn.com/i/apis/attribution/espn-red_50.png"></a>
</div>