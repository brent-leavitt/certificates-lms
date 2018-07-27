<?php
/**
* Plugin Name: 	Certificates LMS
* Plugin URI: 	https://www.trainingdoulas.com/
* Author:		New Beginnings Childbirth Services
* Author URI: 	https://www.trainingdoulas.com/
* Version: 		2.0
* Description: 	A learning management system built for administering our training program online. 
* License:      GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  certificates-lms
* Domain Path:  /languages
*
*/


if ( ! defined( 'ABSPATH' ) ) { exit; }


Class Certificates_LMS{

	/*properties*/
	
	
	
	/*Methods*/

	public function __construct(){
			
		$this->autoload_classes();
			
		add_action('admin_notices', array( $this, 'sample_admin_notice__success' ) );
	}
	
	
	
	public function init(){
		
		//setup Custom Post Types
		
		//require( __DIR__ .'/modl/NBPostType.class.php');
		
		
		add_action( 'init', array( $this, 'set_cpts' ) );
		
		
		//setup activation and deactivation hooks
		
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		
		//version control?

	}
	
	//Add Custom Post Types
	public function set_cpts(){
		
		$cpts = new ctrl\NBPostType();
		$cpts->setup();
	}
	

	//Add Custom User Roles
	public function set_roles(){
		$roles = new ctrl\NBRole();
		$roles->set_roles();
		
	}	
	
	//Add Custom User Capabilities
	public function set_caps(){
		
		
	}
	
	//Remove Custom Post Types
	public function remove_cpts(){
	
		$cpts = new ctrl\NBPostType();
		$cpts->remove();
	
	}
	
		
	//Remove Cusotm User Roles
	public function remove_roles(){
	
		
	}	
		
	//Remove Custome User Capabilities
	public function remove_caps(){
	
		
	}	
		
	
	
	public function sample_admin_notice__success() {
		
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Test from within the Certs LMS Initiating Class!', 'certificates-lms' ); ?></p>
		</div>
		<?php
		
	}
		
	
	
	
	private function autoload_classes( ){
		
		//This loads all classes, as needed, automatically! Slick!
		
		spl_autoload_register( function( $class ){
			
			$path = substr( str_replace( '\\', '/', $class), 0 );
			$path = __DIR__ .'/'. $path . '.class.php';
			
			if (file_exists($path))
				require $path;
			
		} );
	}
	
	
	
	public function activation(){
	
		//Add CPTs and Flush Rewrite Rules
		$this->set_cpts();  	//Register the custom post type
		flush_rewrite_rules();	//Clear the permalinks after CPTs have been registered
	
	
		// Add Custom Roles. 	
		$this->set_roles();
	
	
		//https://wordpress.stackexchange.com/questions/108338/capabilities-and-custom-post-types
		//Custom Caps need to be given to each user role for each CPT that has been added. 
		$this->set_caps();
	
		//Setup a configuration process that explains how to use the plugin. 
	}
	
	
	public function deactivation(){
		
		//Clean up Post Types being removed. 
		$this->remove_cpts(); 	// unregister the post type, so the rules are no longer in memory
		flush_rewrite_rules();	// clear the permalinks to remove our post type's rules from the database
		
		//See Activiation: 
		//Remove caps given to all roles for plugin specific CPTs. 
		$this->remove_caps();
		$this->remove_roles();
		
		//Setup a configuration process that explains how to use the plugin. 
	}
	
	

	
	
	
	/* public function first_run(){
	
		//Setup a configuration process that explains how to use the plugin. 
	} */
	
}


$loader = new Certificates_LMS();

$loader->init();



//NBDT Course Functions

/* 

// Add admin functions. 
require_once('func/nb_admin.php');

// Add helper functions.
require_once('func/nb_helper.php');

// Add NB Doula Training classes.
require_once('func/nb_classes.php');

// Admin Extras To Help with the creation of content
include_once('func/nb_admin_metaboxes.php');

// Add Custom Post Types. 
include_once('func/nb_register_cpt.php'); 

// Add Custom Roles. 
include_once('func/nb_roles.php'); 

// Add Additional Automated Triggers (IPN and Crons)
include_once('func/nb_query_vars.php');  

// Login Screen Customization
include_once('func/nb_login.php');

// Add Widgets
// include_once('func/nb_widgets.php');
// This is a class now, should be called from the nb_classes.php file. 


// Add Custom Page Functionality 
include_once('func/nb_pages.php');

// Add Menus based on user permissions
include_once('func/nb_menus.php');

// Add Admin Editors
include_once('func/nb_admin_editors.php');
 */


?>