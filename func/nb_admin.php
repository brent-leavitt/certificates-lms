<?php 

function nb_admin_scripts( $hook ){
		
	echo 'This is the value for the hook in the NB_ADMIN_SCRIPTS function: '. $hook;
	//include( site_url().'/maintenance/nb-jquery.js');
	if( strcmp( $hook, 'students_page_maintenance' ) == 0 )
		wp_enqueue_script('nb-jquery-js', site_url('/maintenance/nb-jquery.js') );
	
} 

add_action( 'admin_enqueue_scripts', 'nb_admin_scripts' );



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

function nb_admin_bar($admin_bar) {
	return ( current_user_can( 'administrator' ) ) ? $admin_bar : false;
}

add_filter( 'show_admin_bar' , 'nb_admin_bar'); 


// ----------------- //
// DASHBOARD WIDGETS
// ----------------- //

// Dashbaord Assignment Manager Widget
function nb_assignment_widget_function() {

	//Count Assignments Needing Action. 
	$asmts = wp_count_posts('assignment');
	
	$submitted = intval( $asmts->submitted );
	$resubmitted = intval( $asmts->resubmitted );
	$asmt_url = admin_url('edit.php?post_type=assignment');
	$total_asmts = $submitted + $resubmitted;
	
	if( $total_asmts == 0 ){
	
		$string = "There are <em>NO ASSIGNMENTS</em> to be graded at this time!";
		
	} else {
	
	 	$string = 'There ';
		$string .= ( $total_asmts > 1 )? 'are ':'is ';
		$s_end = ( $submitted > 1 )? 's':'';
		$string .= ( $submitted > 0 )? "<em>$submitted new assignment$s_end</em> ":"";
		$string .= ( ( $submitted > 0 ) && ( $resubmitted > 0  ) )?'and ': '';
		$s_end = ( $resubmitted > 1 )?'s':'';
		$string .= ( $resubmitted > 0 )?"<em>$resubmitted resubmitted assignment$s_end</em> ":"";
		$string .= 'that ';
		$string .= ( $total_asmts > 1 )? 'are ':'is ';
		$string .= "ready to be graded!"; /**/
		
	}
	
	//print_pre( $asmts );
	// Display whatever it is you want to show.
	echo "<h2>$string</h2>
		<p><a href='$asmt_url'>Go To Assignments &rarr; </a></p>";
}

// Dashbaord Billing Manager Widget
function nb_message_widget_function() {
	global $wpdb; 
	$admin_url = admin_url('/admin-post.php?action=message_dismiss&message_id=');
	$msgs_url = admin_url( 'admin.php?page=admin_messages' );
	$rcnt_msgs = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM nb_messages WHERE message_recipient=1 AND message_active = 'y' LIMIT 4" );
	$actv_msg_count = $wpdb->get_var( "SELECT FOUND_ROWS()" );
	
	// Display whatever it is you want to show.
	echo "<ul>";
	foreach( $rcnt_msgs as $actv_msg  ){
		echo "<li><small><em>{$actv_msg->message_date}</em></small><br>
		{$actv_msg->message_content}<span> | <a href='{$admin_url}{$actv_msg->message_id}'>dismiss x</a></span></li>";
		 //<span><a href='#'>View &rsaquo;</a></span> |
	}
	echo "</ul>
	<h3>Total Active Messages: {$actv_msg_count}</h3>
	<hr>
	<p><a href='$msgs_url'>View All Messages &rarr; </a></p>";
}


// Dashbaord Billing Manager Widget
function nb_billing_widget_function() {

	// Display whatever it is you want to show.
	echo "<h3>Accounts Requiring Action</h3>";
	echo "<p>Holding queue.</p>";
	echo "<h3>Recent Billing Activities</h3>";
	echo "<p>Holding bay</p>";
}


//Add Dashboard Widgets
function nb_add_dashboard_widgets() {

	wp_add_dashboard_widget(
                 'nb_assignment_widget',         // Widget slug.
                 'Assignment Manager',         // Title.
                 'nb_assignment_widget_function' // Display function.
        );	
	wp_add_dashboard_widget(
                 'nb_message_widget',         // Widget slug.
                 'Recent Messages',         // Title.
                 'nb_message_widget_function' // Display function.
        );	
	wp_add_dashboard_widget(
                 'nb_billing_widget',         // Widget slug.
                 'Billing Manager',         // Title.
                 'nb_billing_widget_function' // Display function.
        );	
}

add_action( 'wp_dashboard_setup', 'nb_add_dashboard_widgets' );


//Dismisses active messages and marks them as inactive in the database. 
function nb_dismiss_message() {
	 global $wpdb;

	 if( isset( $_REQUEST[ 'message_id' ] ) ){
		$return_url = $_SERVER[ 'HTTP_REFERER' ];
		$msg_id = $_REQUEST[ 'message_id' ];
			
		$updated = $wpdb->update( 'nb_messages', array( 'message_active' => 'n' ),  array( 'message_id' => $msg_id ), array( '%s' ), array( '%d' ) );	
		
		if( empty( $updated ) ){
			
			$subject = 'NB Dismiss Message Function Error';
			$message = 'This is an WEB ADMIN NOTICE. \r\n The NB dismiss message function failed to update to the database. This message is sent from the nb_admin.php file in the doula-training plugin, on line 137.';
			$msgr = new NB_Message();
			$msgr->admin_notice( $subject, $message );
		}
		
		wp_redirect( $return_url );
		exit;
	} 
}

add_action( 'admin_post_message_dismiss', 'nb_dismiss_message' );

/*-----------------*/
//Styles
add_action( 'admin_enqueue_scripts', 'nb_doula_course_admin_styles' );
function nb_doula_course_admin_styles() { wp_enqueue_style( 'doula-course-admin-styles', plugins_url( 'doula-course/templates/css/admin-styles.css' ), false ); }
?>
