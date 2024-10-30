<?php
/**
* Plugin Name: Monegraph Everywhere
* Plugin URI: https://www.monegraph.com/
* Version: 1.0.0
* Author: Monegraph, Inc.
* Author URI: https://www.monegraph.com/
* Description: Allows you to add Monegraph work tooltips to your WordPress blog.
* License: GPL2
*/

// Default build environment during dev
// This will be overridden during build process per below comment
global $monegraph_buildEnvironment;
$monegraph_buildEnvironment = 'staging'; 

// Auto-insert environment variable during build.
// These tokens serves as targets for the file2js HBS Broccoli build steps.
// Do not remove or alter them. Refer to Brocfile.js for usage details.
// Leave this! ↴ 
$monegraph_buildEnvironment = 'production';

function isValidAffiliateId($affiliate) {
  $affiliate = trim($affiliate); // Remove whitespace

  // Test to see if affiliate ID is valid
  return strlen($affiliate) == 0 ||
    (strlen($affiliate) <= 50
      && ctype_alnum(str_replace('_', '', $affiliate))); // Remove underscores for alphanum check
}

class MonegraphEverywhere {
	public function __construct() {
	  // Plugin Details
    $this->plugin               = new stdClass;
    $this->plugin->name         = 'monegraph-everywhere'; // Plugin Folder
    $this->plugin->displayName  = 'Monegraph Everywhere'; // Plugin Name
    $this->plugin->version      = '1.0.0';
    $this->plugin->folder       = plugin_dir_path( __FILE__ );
    $this->plugin->url          = plugin_dir_url( __FILE__ );

    // If not pointing to production, show it in the plugin name
    global $monegraph_buildEnvironment;
    if ($monegraph_buildEnvironment != 'production') {
      $this->plugin->displayName = $this->plugin->displayName . ' (' . $monegraph_buildEnvironment . ')';
    }
  	
  	// Hooks
  	add_action('admin_init', array(&$this, 'registerSettings'));
    add_action('admin_menu', array(&$this, 'adminPanelsAndMetaBoxes'));
        
    // Frontend Hooks
  	add_action('wp_footer', array(&$this, 'injectActivateScript'));
  }

  function updateMonegraphScript() {
    $stagingPrefix = '';
    global $monegraph_buildEnvironment;
    $buildEnvironment = $monegraph_buildEnvironment;
    if ($buildEnvironment == 'staging') {
      $stagingPrefix = 'staging-';
    }

    if ($buildEnvironment != 'development') {
      $assetBase = "https://{$stagingPrefix}cdn.monegraph.com/";
    } else {
      $assetBase = 'https://activate.monegraph.dev/activate/';
    }

    $environmentCode = '';
    if ($buildEnvironment != 'production') {
      $environmentCode = " data-environment='" . $buildEnvironment . "'";
    }

    $affiliate = get_option('monegraph_affiliate');
    $affiliateCode = '';
    if ($affiliate !== false && $affiliate != '') {
      $affiliateCode = " data-affiliate=\'" . urlencode($affiliate) . "'";
    }

    $mgScript = <<<EOT
  <script src='{$assetBase}monegraph-activate.js'
        {$environmentCode}
        {$affiliateCode}></script>
EOT;
    update_option('monegraph_script', $mgScript);
  }
  	
  function registerSettings() {
    register_setting($this->plugin->name, 'monegraph_affiliate', 'trim');
    register_setting($this->plugin->name, 'monegraph_script', 'trim');
    $this->updateMonegraphScript();
  }

  function adminPanelsAndMetaBoxes() {
  	add_submenu_page('options-general.php', $this->plugin->displayName, $this->plugin->displayName, 'manage_options', $this->plugin->name, array(&$this, 'adminPanel'));
  }
    
  function adminPanel() {
    // Save Settings
    if (isset($_POST['submit'])) {
      // Check nonce
    	if (!isset($_POST[$this->plugin->name.'_nonce'])) {
        // Missing nonce	
      	$this->errorMessage = __('nonce field is missing. Settings NOT saved.', $this->plugin->name);
    	} elseif (!wp_verify_nonce($_POST[$this->plugin->name.'_nonce'], $this->plugin->name)) {
        // Invalid nonce
      	$this->errorMessage = __('Invalid nonce specified. Settings NOT saved.', $this->plugin->name);
    	} else {        	
        // Save
        $newAffiliate = $_POST['monegraph_affiliate'];
        if (isValidAffiliateId($newAffiliate)) {
          update_option('monegraph_affiliate', $newAffiliate);
          $this->updateMonegraphScript();
      		$this->message = __('Settings Saved.', $this->plugin->name);
        } else {
          $this->errorMessage = __('Invalid Affiliate ID.', $this->plugin->name);
        }
    	}
    }
    
    // Get latest settings
    $this->settings = array(
    	'monegraph_affiliate' => stripslashes(get_option('monegraph_affiliate')),
    );
    
    // Load Settings Form
    include_once(WP_PLUGIN_DIR.'/'.$this->plugin->name.'/settings.php');  
  }
	
	function injectActivateScript() {
		$this->output('monegraph_script');
	}
	
	function output($setting) {
		// Ignore admin, feed, robots or trackbacks
		if (is_admin() OR is_feed() OR is_robots() OR is_trackback()) {
			return;
		}
		
		// Get meta
		$meta = get_option($setting);
		if (empty($meta)) {
			return;
		}	
		if (trim($meta) == '') {
			return;
		}
		
		// Output
		echo stripslashes($meta);
	}
}
		
$monegraphEverywhere = new MonegraphEverywhere();
?>