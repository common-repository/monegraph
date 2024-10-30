<div class="wrap">
    <h2><?php echo $this->plugin->displayName; ?> &raquo; <?php _e('Settings', $this->plugin->name); ?></h2>
           
    <?php    
    if (isset($this->message)) {
        ?>
        <div class="updated fade"><p><?php echo $this->message; ?></p></div>  
        <?php
    }
    if (isset($this->errorMessage)) {
        ?>
        <div class="error fade"><p><?php echo $this->errorMessage; ?></p></div>  
        <?php
    }
    ?> 
    
    <div id="poststuff">
    	<div id="post-body" class="metabox-holder columns-2">
    		<!-- Content -->
    		<div id="post-body-content">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">                        
	                <div class="postbox">
	                    <h3 class="hndle"><?php _e('Affiliate Settings', $this->plugin->name); ?></h3>
	                    
	                    <div class="inside">
		                    <form action="options-general.php?page=<?php echo $this->plugin->name; ?>" method="post">
		                    	<p>
		                    		<label for="monegraph_affiliate"><strong><?php _e('If you have an Affiliate ID, enter it below:', $this->plugin->name); ?></strong></label>
		                    		<input name="monegraph_affiliate" id="monegraph_affiliate" class="widefat" rows="8" style="font-family:Courier New;" value="<?php echo $this->settings['monegraph_affiliate']; ?>" />
		                    	</p>
		                    	<?php wp_nonce_field($this->plugin->name, $this->plugin->name.'_nonce'); ?>
		                    	<p>
									<input name="submit" type="submit" name="Submit" class="button button-primary" value="<?php _e('Save', $this->plugin->name); ?>" /> 
								</p>
						    </form>
	                    </div>
	                </div>
				</div>
				<!-- /normal-sortables -->
    		</div>
    		<!-- /post-body-content -->
    	</div>
	</div>      
</div>