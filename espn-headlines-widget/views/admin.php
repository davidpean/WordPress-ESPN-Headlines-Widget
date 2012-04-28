
<div>
	<fieldset>
                <?php
                if( $instance['widget_espn_apikey'] == "" ){
                    echo _e('This widget requires an ESPN API Key. ', "espn_headlines");
                    echo "<a href='http://developer.espn.com/' target='_new'>"; 
                    echo _e("Please visit the ESPN Developer Center to request or retrieve your key and enter it below", "espn_headlines");
                    echo "</a><p>";
                }
                ?>
                <p>
                <label for="<?php echo $this->get_field_id('widget_espn_apikey'); ?>" class="block">
                        <?php _e('ESPN API Key:', "espn_headlines"); ?>
                </label>
                <input type="text" name="<?php echo $this->get_field_name('widget_espn_apikey'); ?>" id="<?php echo $this->get_field_id('widget_espn_apikey'); ?>" value="<?php echo $instance['widget_espn_apikey']; ?>" class="widefat" />		
                </p>
                <?php
                
                if( $instance['widget_espn_apikey'] != "" ){
                    
                    //Let's make sure they have a valid key
                    $response = wp_remote_get( $this->apiBaseURL . 'sports?apikey=' . $instance['widget_espn_apikey'] );

                    if( !is_wp_error( $response ) ) {
                         $json = json_decode(wp_remote_retrieve_body($response));
                         
                         $invalid = $json->status == "error" && $json->code == 403;
                         
                         if( !$invalid ){
                         ?>    
                                <p>
                                <label for="<?php echo $this->get_field_id('widget_title'); ?>" class="block">
                                        <?php _e('Title:', "espn_headlines"); ?>
                                </label>
                                <input type="text" name="<?php echo $this->get_field_name('widget_title'); ?>" id="<?php echo $this->get_field_id('widget_title'); ?>" value="<?php echo $instance['widget_title']; ?>" class="widefat" />		
                                </p>

                                <p>
                                <label for="<?php echo $this->get_field_id('widget_headlines_number'); ?>" class="block">
                                        <?php _e('Number of headlines to show:', "espn_headlines"); ?>
                                </label>
                                <input type="text" name="<?php echo $this->get_field_name('widget_headlines_number'); ?>" id="<?php echo $this->get_field_id('widget_headlines_number'); ?>" value="<?php echo $instance['widget_headlines_number']; ?>" size="3" />
                                </p>

								<?php
                                /*
                                <p>
                                <label for="<?php echo $this->get_field_id('widget_headlines_type'); ?>" class="block">
                                        <?php _e('Type:', "espn_headlines"); ?>
                                </label>
 
                                <br/>
                                
                                <select style="width:150px;" name="<?php echo $this->get_field_name('widget_headlines_type'); ?>" id="<?php echo $this->get_field_id('widget_headlines_type'); ?>">
                                    <option value="1"><?php _e('By sport / league', "espn_headlines"); ?></option>
                                    <option value="2"><?php _e('By team', "espn_headlines"); ?></option>
                                </select>
                                </p>
                                */
                                ?>
                                
                                <p><input id="<?php echo $this->get_field_id('widget_show_description'); ?>" name="<?php echo $this->get_field_name('widget_show_description'); ?>" type="checkbox" <?php checked(isset($instance['widget_show_description']) ? $instance['widget_show_description'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('widget_show_description'); ?>"><?php _e('Show description'); ?></label></p>
                                <p>
                                <label for="<?php echo $this->get_field_id('widget_headlines_type'); ?>" class="block">
                                        <?php _e('Select:', "espn_headlines"); ?>
                                </label>
 
                                <br/>
                                <select style="width:150px;" name="<?php echo $this->get_field_name('widget_headlines_filter'); ?>" id="<?php echo $this->get_field_id('widget_headlines_filter'); ?>">
                                    <option value="">All news</option>
                                    <?php
                                        if( $json->status == "success" ){
                                            
                                            foreach( $json->sports as $sport ){
                                            	
                                                echo '<option value="' . $sport->name . '" ' . ( $sport->name == $instance['widget_headlines_filter'] ? "selected":"" ). '>' . ucfirst($sport->name) . '</option>';
                                                
                                                if( is_array( $sport->leagues ) ){
                                                	foreach( $sport->leagues as $league ){
                                                		$leagueName = $league->shortName;
                                                		if( $leagueName == "" ){
                                                			$leagueName = strtoupper($league->abbreviation);
                                                		}
                                                		echo '<option value="' . $sport->name . '/' . $league->abbreviation . '" ' . ( $sport->name . '/' . $league->abbreviation == $instance['widget_headlines_filter'] ? "selected":"" ). '>-- ' . $leagueName . '</option>';
                                                	}
                                                }
                                            }
                                            
                                        }
                                    ?>
                                    <option value="espnw" <?php echo ( $instance['widget_headlines_filter'] == "espnw" ? "selected":"" );?>>espnW</option>
                                    
                                </select>
                                </p> 
                          <?
                          }else{
                              echo _e('Invalid API Key', "espn_headlines");
                          }
                    }
                }
                ?>
        </fieldset>
        <div>
        	<?php
        	echo _e("In order to use the ESPN API you must agree to their ", "espn_headlines");
        	echo '<a href="http://developer.espn.com/terms" target="_new">';
        	echo _e("terms and conditions.", "espn_headlines");
        	echo '</a>';
        	?>
        </div>
</div>