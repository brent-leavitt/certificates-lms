<?php
/*
	Filename: nb_pages.php
	Created: ?
	Last Updated: 5 Feb 2018
	
	Description: This defines the location of all template pages used by the doula course plugin. 

*/

/*  SHORT CODES for Pages that are defined in the Admin Area. 
// This is the scripts that generate the progress report page. */

add_shortcode( 'nb_complete_registration', 'nb_get_complete_registration' );
add_shortcode( 'nb_payment_complete', 'nb_get_payment_complete' );
/*-----------------*/
add_shortcode( 'nb_progress_report', 'nb_get_progress_report' );
add_shortcode( 'nb_profile_editor', 'nb_get_profile_editor' );
add_shortcode( 'nb_billing_overview', 'nb_get_billing_overview' );
add_shortcode( 'nb_billing_details', 'nb_get_billing_details' );
/*-----------------*/
add_shortcode( 'nb_course_extension', 'nb_get_course_extension' );
add_shortcode( 'nb_account_payoff', 'nb_get_account_payoff' );
add_shortcode( 'nb_cancel_recurring', 'nb_get_cancel_recurring' );
add_shortcode( 'nb_cancel_manual', 'nb_get_cancel_manual' );
add_shortcode( 'nb_cancel_account', 'nb_get_cancel_account' );
add_shortcode( 'nb_reactivate_account', 'nb_get_reactivate_account' );
add_shortcode( 'nb_renew_certification', 'nb_get_renew_certification' );
/*-----------------*/
add_shortcode( 'nb_pdf_manuals', 'nb_get_pdf_manuals' );
/*-----------------*/
//Styles
add_action( 'wp_enqueue_scripts', 'nb_doula_course_scripts' );
function nb_doula_course_scripts() { wp_enqueue_style( 'doula-course-styles', plugins_url( 'doula-course/templates/styles.css' ), false ); }
/*-----------------*/
/* Page Shortcode Teplates */

function nb_get_complete_registration(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/account/page-complete-registration.php" ); }
function nb_get_payment_complete(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/account/page-payment-complete.php" ); }
function nb_get_profile_editor(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/account/page-profile-editor.php" ); }
function nb_get_progress_report(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/account/page-progress-report.php" ); }

function nb_get_billing_overview(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-billing-overview.php" ); }
function nb_get_billing_details(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-billing-details.php" ); }
function nb_get_course_extension(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-course-extension.php" ); } 
function nb_get_account_payoff(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-account-payoff.php" ); }
function nb_get_cancel_recurring(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-cancel-recurring.php" ); }
function nb_get_cancel_manual(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-cancel-manual.php" ); }
function nb_get_cancel_account(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-cancel-account.php" ); } 

function nb_get_reactivate_account(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-reactivate-account.php" ); }
function nb_get_renew_certification(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/billing/page-renew-certification.php" ); }

/* function nb_get_pdf_manuals(){ return include_once( plugin_dir_path( __FILE__ ) . "../templates/page-pdf-manuals.php" ); } */  /* OBSOLETE */
/*-----------------*/

//A function to get all the meta data for the student to display and manipulate. 
function nb_get_student_meta(){
	global $current_user;

	if( $current_user->ID != null){
		$sid = $current_user->ID;
		$student = get_userdata($sid); 
	}
	return $student;
}
/*-----------------*/
	
/*
 * ADD Grade Key Value translator
 *
 * @since 1.1
 * Was also declared in FUNC/NB_PAGES.PHP, need to find a permenant location. 
 
 **/

function gradeKeyVal($gradeKey){
	$gk = substr($gradeKey, 0, 2);
	$uNum = ( strlen($gradeKey) == 4 )? substr($gradeKey, 3, 1) : NULL ;
	switch($gk){
		case 'mc':
			return 'Main Course, Unit '.$uNum;
		case 'cb':
			return 'Childbirth Course, Unit '.$uNum;
		case 'da':
			return 'Doula Actions';
		case 'bp':
			return 'Birth Packet';
		default:
			return NULL;
	}
}
?>