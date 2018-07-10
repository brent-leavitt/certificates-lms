<?php 

function nb_not_admin(){
	if( is_user_logged_in() && !current_user_can( 'administrator' ) ){
		
	 if( is_admin() ){
		
			$site_url = site_url();
			wp_redirect($site_url); 
			exit; 
			
		}	
	}
}

add_action('init', 'nb_not_admin');

//Disable the default admin bar for all users except administrators. 

function doula_admin_bar($admin_bar) {

	return ( current_user_can( 'administrator' ) ) ? $admin_bar : false;

}


add_filter( 'show_admin_bar' , 'doula_admin_bar'); 

?>
