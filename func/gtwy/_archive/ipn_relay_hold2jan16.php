<?php
/**
 *  PHP-PayPal-IPN
 *
 *  For a more in depth tutorial, see my blog post:
 *  http://www.micahcarrick.com/paypal-ipn-with-php.html
 *
 *  This code is available at github:
 *  https://github.com/Quixotix/PHP-PayPal-IPN
 *
 *  @package    PHP-PayPal-IPN
 *  @author     Micah Carrick
 *  @copyright  (c) 2011 - Micah Carrick
 *  @license    http://opensource.org/licenses/gpl-3.0.html
 */
 
 
/*



Since this script is executed on the back end between the PayPal server and this
script, you will want to log errors to a file or email. Do not try to use echo
or print--it will not work! 

Here I am turning on PHP error logging to a file called "ipn_errors.log". Make
sure your web server has permissions to write to that file. In a production 
environment it is better to have that log file outside of the web root.
*/
ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');


//Using Wordpress. 
global $wpdb;
$msgr = new NB_Message();
$msgr->admin_notice( 'BEFORE ANYTHING',  "Sent from line 37: \r\n " );
//Sandbox?
$ipn_sandbox = ( strcmp( 'crsdev', substr( $_SERVER[ 'HTTP_HOST' ], 0, 6 ) ) == 0 )? true : false ;

// instantiate the IpnListener class
require(dirname(__FILE__).'/../classes/paypal_ipn.class.php');
$listener = new IpnListener();

/*
When you are testing your IPN script you should be using a PayPal "Sandbox"
account: https://developer.paypal.com
When you are ready to go live change use_sandbox to false.
*/
$listener->use_sandbox = ( $ipn_sandbox )? true : false; 

/*
By default the IpnListener object is going  going to post the data back to PayPal
using cURL over a secure SSL connection. This is the recommended way to post
the data back, however, some people may have connections problems using this
method. 

To post over standard HTTP connection, use:
$listener->use_ssl = false;

To post using the fsockopen() function rather than cURL, use:
$listener->use_curl = false;
*/

/*
The processIpn() method will encode the POST variables sent by PayPal and then
POST them back to the PayPal server. An exception will be thrown if there is 
a fatal error (cannot connect, your server is not configured properly, etc.).
Use a try/catch block to catch these fatal errors and log to the ipn_errors.log
file we setup at the top of this file.

The processIpn() method will send the raw data on 'php://input' to PayPal. You
can optionally pass the data to processIpn() yourself:
$verified = $listener->processIpn($my_post_data);
*/
$msgr->admin_notice( 'BEFORE THE TRY STATEMENT',  "Sent from line 76: \r\n ".$listener->getTextReport() );

try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
	
    error_log( $e->getMessage(). var_dump( $_SERVER ) );
    exit(0);
}


/*
The processIpn() method returned true if the IPN was "VERIFIED" and false if it
was "INVALID".
*/
if( $verified ){
    /*
    Once you have a verified IPN you need to do a few more checks on the POST
    fields--typically against data you stored in your database during when the
    end user made a purchase (such as in the "success" page on a web payments
    standard button). The fields PayPal recommends checking are:
    
        1. Check the $_POST['payment_status'] is "Completed"
	    2. Check that $_POST['txn_id'] has not been previously processed 
	    3. Check that $_POST['receiver_email'] is your Primary PayPal email 
	    4. Check that $_POST['payment_amount'] and $_POST['payment_currency'] 
	       are correct
    
    Since implementations on this varies, I will leave these checks out of this
    example and just send an email using the getTextReport() method to get all
    of the details about the IPN.  
    */
	email_notice( "Testing Verified.", $listener->getTextReport() );
	$ipn_data = $listener->getPostData();
	$ipn_data['ipn_sandbox'] = $ipn_sandbox;
	
 	if( !sort_ipn( $ipn_data ) ) {
		email_notice( "Valid IPN, but not didn't pass IPN Check.", "Sent from Line 110.");
	} 

} else {
    /*
    An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
    a good idea to have a developer or sys admin manually investigate any 
    invalid IPN.
    */
	$msgr->admin_notice( 'IPN INVALID',  "Sent from line 120: \r\n ".$listener->getTextReport() );
    //email_notice( '', );
}

/******************************
*
* SORT IPN
*
* Sort IPN data for further action.
*
******************************/

function sort_ipn( array $ipn_data ){

		//PRELIMINARY CHECKS. CHECK USD currency and then receiver email address.
		 
		$biz_email = ( $ipn_data[ 'ipn_sandbox' ] )? "testbiz1@trainingdoulas.com" : "rachel.leavitt@gmail.com" ; //True= sandbox, False = live. 
		
 		if( ( !array_key_exists ( 'mc_currency', $ipn_data ) ) && ( strcmp( $ipn_data['mc_currency'], 'USD' ) !== 0 ) ){
			
			email_notice( 'Notice from IPN Relay - Currency Mismatch', "MC_CURRENCY key did NOT exist in this transaction OR the currency was not set to USD. May be fraudulent. \r\n Sent from ipn_relay, Line 139. Here are the details: \r\n". print_r(  $ipn_data, true ) );
			
			return false; 
		} 
		
		if( array_key_exists ( 'receiver_email', $ipn_data )  ){
						
			if( strcmp( $biz_email, $ipn_data['receiver_email'] ) !== 0 ){
				
				email_notice( 'Notice from IPN Relay - Business Email Mismatch', "RECEIVER EMAIL key is set, however the email address doesn't fit! IPN_SANDBOX: {$ipn_data['ipn_sandbox']}, BIZ_EMAIL: $biz_email, IPN_DATA-RECEIVER EMAIL: {$ipn_data['receiver_email']}. May be fraudulent. \r\n Sent from ipn_relay, Line 148. Here are the details: \r\n". print_r(  $ipn_data, true ) );
			
				return false; 
			}
		
		} else {
			
			email_notice( 'Notice from IPN Relay - Business Email Missing', "RECEIVER EMAIL key did NOT exist in this transaction. May be fraudulent. \r\n Sent from ipn_relay, Line 155. Here are the details: \r\n". print_r(  $ipn_data, true ) );
			
			return false; 
		} 
		
		//END PRELIMINARY CHECKS
		//CHECK for TXN TYPE
		
		
		 
		if( array_key_exists ( 'txn_type', $ipn_data ) ){
				
			switch($ipn_data['txn_type']){
				case 'send_money':		// Payment received; source is the Send Money tab on the PayPal website
				case 'subscr_payment':	//Subscription payment received
				case 'web_accept': 		//Payment received
					if( check_payment_ipn( $ipn_data ) ){
						//Perform some action to process transactions. 
						email_notice( 'PASSED CHECK_PAYMENT_IPN', 'Line 178: We are here.' );
					}
					break;
					
				case 'subscr_signup':	//Subscription started
					break;
				
				case 'subscr_cancel':	//Subscription canceled
					break;
				
				case 'subscr_eot':		//Subscription expired
					break;
				
				case 'subscr_failed': 	//Subscription payment failed
					break;
				
				case 'subscr_modify': 	//Subscription modified
					break;
				
				default:
					break;
					
			}
			
		} else {
			email_notice( 'Notice from IPN Relay - NBDT', "TXN_TYPE key did NOT exist in this transaction. May be fraudulent. \r\n Here are the details: \r\n". print_r(  $ipn_data, true ) );
			
			return false; //If txn_type isn't set, we shouldn't be going any further. 
		}	 
		
		//error_log( "The value of Data is: ". print_r( $data, true ) );

		return true; 
}
 

/******************************
*
* CHECK PAYMENT IPN
*
* Check Payment IPN data for further action.
*
******************************/

function check_payment_ipn( array $ipn_data ){

	global $wpdb;

	/*	1. Check the $_POST['payment_status'] is in list of likey statuses
	    2. Check that $_POST['txn_id'] has not been previously processed    
	*/
		

	//Check PAYMENT STATUS	
	
	$payment_status_array = array(
		'Completed',
		'Reversed',
		'Canceled_Reversal',
		'Refunded'			
	);  

	if( array_key_exists( 'payment_status', $ipn_data ) ){
		
		if( !in_array( $ipn_data['payment_status'], $payment_status_array ) ){
		
			//A less common payment action has been posted. Probably won't process. 
			email_notice( 'Notice from IPN Relay - Payment Status Unknown', "Payment Status key existed in this transaction but it was considered to be of lesser importance and wasn't prcoessed. \r\n Sent from ipn_relay, Line 240. Here are the details: \r\n". print_r(  $ipn_data, true ) ); 			
			return false; 
			
		}	
		
	} else {
		
		//Some other type of IPN message has been sent, such as new subscription or expired subscription, etc. 
		email_notice( 'Notice from IPN Relay - Payment Status Not Found', "Payment Status key DOES NOT existed in this transaction but it should have. Possibly fraudulant. \r\n Sent from ipn_relay, Line 248. Here are the details: \r\n". print_r(  $ipn_data, true ) );
		
		return false; 
	}

	//Check for Dupliates.
	
	$ipn_txn_id = $ipn_data['txn_id']; 
	$ipn_query = "SELECT id FROM nb_ipn_records WHERE ipn_id = '$ipn_txn_id ' LIMIT 1";
	$ipn_qRslt = $wpdb->query( $ipn_query );
	
	if($ipn_qRslt == 0){//If there is no return result, then this transaction has not been recorded. 
		
		$ipn_txn_date = date( 'Y-m-d H:i:s', strtotime( $ipn_data['payment_date'] ) ); //convert date to store in database
		$ipn_string = implode( '&', $ipn_data ); //convert to string to store in database. 
		
		$ipn_query2 = "INSERT INTO nb_ipn_records (`created`,`ipn_id`,`info`) VALUES ( '$ipn_txn_date', '$ipn_txn_id ', '$ipn_string' )";
		
		$ipn_qRslt2 = $wpdb->get_var($ipn_query2);
		
		if( $ipn_qRslt2 == NULL ){
			email_notice( 'IPN Database Insertion Failed - IPN Relay Report', "The IPN RELAY failed to record the IPN Transaction ID in the database for future reference. \r\n Here's some more details: ".print_r( $wpdb , true ) ); 
		}
		
		//After the check, let's clean up a little:
		$deleteQ = "DELETE FROM nb_ipn_records WHERE created < (NOW() - INTERVAL 2 DAY)";
		$deleteRes = $wpdb->query( $deleteQ ); 
		
	} else {
		
		return false; 
	}
		
	return true; 
}




/******************************
*
* EMAIL NOTICE
*
* This function is purely administative. Should probably make a record and send it to the database also... 
*
******************************/

function email_notice($ipn_subject, $emlMsg){

		$sent = wp_mail('brent@trainingdoulas.com', $ipn_subject, $emlMsg);
		
		//If we can't email a message to admin, let's at least send it to the error log. 
		if(!$sent){
			error_log( $ipn_subject." - Failed to send email notice to admin. \r\n". $emlMsg );
		} 
		

} 

?>
