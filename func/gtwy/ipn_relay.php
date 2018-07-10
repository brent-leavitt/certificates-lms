<?php
/********
* IPN relay for New Beginnings Doula Training
* Created by Brent Leavitt 
* Created on: 6 June 2013
* Updated on: 2 Jan 2016
*********/
//echo "working?";

ini_set('log_errors', true);
ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');

//Setup file to read information to: 
global $wpdb;
$ipn_data = array();
$msgr = new NB_Message();

// STEP 1: Read POST data

/// CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
// Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
// Set this to 0 once you go live or don't require logging.


//Sandbox?
$ipn_sandbox = ( strcmp( 'crsdev', substr( $_SERVER[ 'HTTP_HOST' ], 0, 6 ) ) == 0 )? true : false ;

// Read POST data
// reading posted data directly from $_POST causes serialization
// issues with array data in POST. Reading raw POST data from input stream instead.
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);

foreach ($raw_post_array as $keyval) {
	$keyval = explode ('=', $keyval);
	if (count($keyval) == 2)
		$ipn_data[$keyval[0]] = urldecode($keyval[1]);
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
	$get_magic_quotes_exists = true;
}

foreach ($ipn_data as $key => $value) {
	if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
		$value = urlencode(stripslashes($value));
	} else {
		$value = urlencode($value);
	}
	$req .= "&$key=$value";
}

// STEP 2: Post IPN data back to PayPal to validate the IPN data is genuine
// Without this step anyone can fake IPN data

$paypal_url_prefix = ( $ipn_sandbox )? 'www.sandbox' : 'ipnpb';

$paypal_url = "https://{$paypal_url_prefix}.paypal.com/cgi-bin/webscr";

$ch = curl_init($paypal_url);

if ($ch == FALSE)
	return FALSE;

curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: new-beginnings-doula-training'));

$res = curl_exec($ch);
if( curl_errno($ch) != 0 ){ // cURL error
	
	error_log(date('[Y-m-d H:i e] '). "Line 100 - Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL);
}
 	
curl_close($ch);

	
// STEP 3: Inspect IPN validation result and act accordingly

if( strcmp( $res, "VERIFIED" ) == 0) {
	// check whether the payment_status is Completed
	// check that txn_id has not been previously processed
	// check that receiver_email is your PayPal email
	// check that payment_amount/payment_currency are correct
	// process payment and mark item as paid.

	// assign posted variables to local variables
	//$item_name = $_POST['item_name'];
	//$item_number = $_POST['item_number'];
	//$payment_status = $_POST['payment_status'];
	//$payment_amount = $_POST['mc_gross'];
	//$payment_currency = $_POST['mc_currency'];
	//$txn_id = $_POST['txn_id'];
	//$receiver_email = $_POST['receiver_email'];
	//$payer_email = $_POST['payer_email'];
	
	
	//$msgr->admin_notice( "Testing IPN Verified.", "Sent from line 105 \r\n". ipn_text_report( $ipn_data ) );
	
	$ipn_data['ipn_sandbox'] = $ipn_sandbox;
	
	if( !sort_ipn( $ipn_data ) ) {
		$msgr->admin_notice( "Valid IPN, but not didn't pass IPN Check.", "Sent from Line 110." );
	} 

} else if( strcmp( $res, "INVALID" ) == 0 ) {
	
	$msgr->admin_notice( "The IPN Relay Is Invalid", "Possible Fraudulent Activity, Please Investigate!\r\n". ipn_text_report( $ipn_data ) );
	
} else {
	
	error_log( date( '[Y-m-d H:i e]' ). "Line 128 - \$res has no value: $res .". PHP_EOL );

}
	

 

/******************************
*
* SORT IPN
*
* Sort IPN data for further action.
*
******************************/

function sort_ipn( array $ipn_data ){
		
		$msgr = new NB_Message();
		
		//PRELIMINARY CHECKS. CHECK USD currency and then receiver email address.
		 
		$biz_email = ( $ipn_data[ 'ipn_sandbox' ] )? "testemail@example.com" : "liveemail@example.com" ; //True= sandbox, False = live. 
		
 		if( ( !array_key_exists ( 'mc_currency', $ipn_data ) ) && ( strcmp( $ipn_data['mc_currency'], 'USD' ) !== 0 ) ){
			
			if( $ipn_data['txn_type'] !== 'recurring_payment_failed' ){
				$msgr->admin_notice( 'Notice from IPN Relay - Currency Mismatch', "MC_CURRENCY key did NOT exist in this transaction OR the currency was not set to USD. May be fraudulent. \r\n Sent from ipn_relay, Line 139. Here are the details: \r\n". ipn_text_report( $ipn_data ) );
			
				return false; 
			}

		} 
		
		if( array_key_exists ( 'receiver_email', $ipn_data )  ){
						
			if( strcmp( $biz_email, $ipn_data['receiver_email'] ) !== 0 ){
				
				$msgr->admin_notice( 'Notice from IPN Relay - Business Email Mismatch', "RECEIVER EMAIL key is set, however the email address doesn't fit! IPN_SANDBOX: {$ipn_data['ipn_sandbox']}, BIZ_EMAIL: $biz_email, IPN_DATA-RECEIVER EMAIL: {$ipn_data['receiver_email']}. May be fraudulent. \r\n Sent from ipn_relay, Line 148. Here are the details: \r\n".ipn_text_report( $ipn_data ));
			
				return false; 
			}
		
		} else {
			
			$msgr->admin_notice( 'Notice from IPN Relay - Business Email Missing', "RECEIVER EMAIL key did NOT exist in this transaction. May be fraudulent. \r\n Sent from ipn_relay, Line 155. Here are the details: \r\n". ipn_text_report( $ipn_data ) );
			
			return false; 
		} 
		
		//END PRELIMINARY CHECKS
		//CHECK for TXN TYPE
		 
		if( array_key_exists ( 'txn_type', $ipn_data ) ){
				
			switch($ipn_data['txn_type']){
				case 'send_money':		// Payment received; source is the Send Money tab on the PayPal website
					//IF SEND MONEY IS FROM OTHER BUSINESS ACCOUNTS, don't process here! 
					if( is_admin_payment( $ipn_data ) ){
						break;
					}
				case 'subscr_payment':	//Subscription payment received
				case 'web_accept': 		//Payment received
				case 'invoice_payment': 		//Payment received via invoice
					if( check_payment_ipn( $ipn_data ) ){
						//Perform some action to process transactions. 
						if( !process_transaction( $ipn_data ) )
							$msgr->admin_notice( 'Payment Failed to Process', "A payment has been received, but failed to be processed. Here are the details of the payment: \r\n". ipn_text_report( $ipn_data )   );
					}
					break;
					
				case 'subscr_signup':	//Subscription started
					
					$msgr->admin_notice( 'New Subscription Created', "A new payment subscription has been created for {$ipn_data['first_name']} {$ipn_data['last_name']}, {$ipn_data['payer_email']}." );
					break;
				
				case 'subscr_cancel':	//Subscription canceled
					$msgr->admin_notice( 'Subscription Cancelled', "A subscription has been cancelled for {$ipn_data['first_name']} {$ipn_data['last_name']}, {$ipn_data['payer_email']}." );
					break;
				
				case 'subscr_eot':		//Subscription expired
					$msgr->admin_notice( 'Subscription Expired', "A subscription has expired for {$ipn_data['first_name']} {$ipn_data['last_name']}, {$ipn_data['payer_email']}." );
					break;
				
				case 'subscr_failed': 	//Subscription payment failed
					$msgr->admin_notice( 'Failed Subscription Payment', "A monthly automatic payment failed to process for {$ipn_data['first_name']} {$ipn_data['last_name']}, {$ipn_data['payer_email']}." );
					break;
				
				case 'subscr_modify': 	//Subscription modified
					$msgr->admin_notice( 'Subscription Modified', "A subscription has been modified for {$ipn_data['first_name']} {$ipn_data['last_name']}, {$ipn_data['payer_email']}." );
					break;
				
				case 'recurring_payment_failed':  //Recurring Payment Failed
					$msgr->admin_notice( 'Recurring Payment Failed', "A monthly automatic payment has tried to process but failed three times. Consequently, the subscription has been cancelled for {$ipn_data['first_name']} {$ipn_data['last_name']}, {$ipn_data['payer_email']}." );
					break;
				
				default:
					$msgr->admin_notice( 'IPN Notification', "This is the default notification for successful IPN transaction that don't fit into a pre-defined category. The type of transaction was: {$ipn_data['txn_type']}.\r\n Here are the IPN details for you to take action if needed.\r\n". ipn_text_report( $ipn_data ) );
					break;
					
			}
			
		} else {
			$msgr->admin_notice( 'Notice from IPN Relay - NBDT', "TXN_TYPE key did NOT exist in this transaction. May be fraudulent. \r\n Here are the details: \r\n". ipn_text_report( $ipn_data ) );
			
			return false; //If txn_type isn't set, we shouldn't be going any further. 
		}	 
		
		//error_log( "The value of Data is: ". print_r( $data, true ) );

		return true; 
}
 

/******************************
*
* IS ADMIN PAYMENT
*
* Check to see if payment is coming from another business related account.
* If yes, we'll return true to indicate that this is an admin payment/transfer. 
*
******************************/
function is_admin_payment( array $ipn_data ){
	
	$admin_arr = array(
		'admin1@example.com','admin2@example.com' //an email list of all admin users who should receive notice of payments made. 
	);

	if( isset( $ipn_data[ 'payer_email' ] ) ){
		$payer_email = $ipn_data[ 'payer_email' ];
		
		if( in_array( $payer_email , $admin_arr ) ){
			return true;
		}
	}

	return false;	
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
	$msgr = new NB_Message();
	
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
			$msgr->admin_notice( 'Notice from IPN Relay - Payment Status Unknown', "Payment Status key existed in this transaction but it was considered to be of lesser importance and wasn't prcoessed. \r\n Sent from ipn_relay, Line 240. Here are the details: \r\n".ipn_text_report( $ipn_data ) ); 			
			return false; 
		}	
		//Insert code for Pending Payment from eChecks. Send message to customers, especially first time customers. 
		
	} else {
		
		//Some other type of IPN message has been sent, such as new subscription or expired subscription, etc. 
		$msgr->admin_notice( 'Notice from IPN Relay - Payment Status Not Found', "Payment Status key DOES NOT existed in this transaction but it should have. Possibly fraudulant. \r\n Sent from ipn_relay, Line 248. Here are the details: \r\n".ipn_text_report( $ipn_data ) );
		
		return false; 
	}

	//Check for Dupliates.
	$ipn_string = ''; //convert to string to store in database. 
	foreach ($ipn_data as $key => $value) {
		if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
			$value = urlencode(stripslashes($value));
		} else {
			$value = urlencode($value);
		}
		$ipn_string .= "&$key=$value";
	}
	$ipn_string = substr( $ipn_string, 1 );
	
	$ipn_txn_id = $ipn_data['txn_id']; 
	$ipn_query = "SELECT id FROM nb_ipn_records WHERE ipn_id = '$ipn_txn_id' LIMIT 1";
	$ipn_qRslt = $wpdb->query( $ipn_query );
	
	if( empty( $ipn_qRslt ) ){//If there is no return result, then this transaction has not been recorded. 
		
		$ipn_txn_date = date( 'Y-m-d H:i:s', strtotime( $ipn_data['payment_date'] ) ); //convert date to store in database
		
		
		$ipn_query2 = "INSERT INTO nb_ipn_records (`created`,`ipn_id`,`info`) VALUES ( '$ipn_txn_date', '$ipn_txn_id', '$ipn_string' )";
		
		$ipn_qRslt2 = $wpdb->query( $ipn_query2 );
		
		if( empty( $ipn_qRslt2 ) ){
			$msgr->admin_notice( 'IPN Database Insertion Failed - IPN Relay Report', "The IPN RELAY failed to record the IPN Transaction ID in the database for future reference. \r\n Here's some more details: 
			QUERY 1: {$ipn_query} 
			RESULTS 1: {$ipn_qRslt}
			
			QUERY 2: {$ipn_query2} 
			RESULTS 2: {$ipn_qRslt2}" );
		}
		
		//After the check, let's clean up a little:
		$deleteQ = "DELETE FROM nb_ipn_records WHERE created < (NOW() - INTERVAL 2 DAY)";
		$deleteRes = $wpdb->query( $deleteQ ); 
		
	} else {
		error_log( date( '[Y-m-d H:i e]' ). "Double Transaction Insertion Attempted.\r\nFor your information:\r\n". $ipn_string . PHP_EOL );
		return false; 
	}
		
	return true; 
}



/******************************
*
* Process Transaction
*
* Process IPN Transaction being sent from PayPal
*
******************************/

function process_transaction( array $ipn_data ){	
	global $wpdb;
	$msgr = new NB_Message();
	
	$nb_stud = new NB_Student();
	$sPost = $nb_stud->prepare_ipn_record( $ipn_data );
	
	//$msgr->admin_notice( 'Process Transaction Fuction has been triggered.', "Type of transaction that was being processed is: {$ipn_data['txn_type']}. Sent from line 321. Also here's the value of SPost" .ipn_text_report( $sPost ) );	
	$update = false; 
	
	if( empty( $sPost['ID'] ) ){ //This says that if no ID is set, then this is a new user, and we need to create a new student account first. 
		$student = $nb_stud->add_student( $sPost );
	} else {
		$student = new WP_User( $sPost['ID'] );
		$update = true; //we are updating a current student's account, ei, adding a transaction. 
	}
	//Either one of these could return a WP_USER object on success or a WP_ERROR object on fail!
	
	if( is_a( $student, 'WP_User' ) ){
		
		$sid = $student->ID;
		
		if( !empty( $sid ) ){ //We are only going to insert transactions when student IDs are set. 
			
			$nu = ( $update )? 0 : 1; //Not update, therfore a new user. 
			
			$nb_trans = new NB_Transaction( $sid );
			$nb_trans->prep_ipn_record( $ipn_data );	
			$trans_processed = $nb_trans->process_transaction( $nu ); //If this comes back unsuccessful we need to index to the database. 
			
			$ipn_data['nbti'] = $nbti = $nb_trans->tPost[ 'transaction_id' ];
			//print_pre($nb_trans->tPost);
		
			$user_ids[] = $sid;//Add this updated user to the users Array. 
			$payerEml = $ipn_data[ 'payer_email' ];
			$ipn_dataTxnID = $ipn_data[ 'txn_id' ];
			
			// If we created a new user, perform a few extra steps. 
			if ( ( $update == false ) && ( $trans_processed == true ) && ( !empty( $nbti ) ) ) {
				$nb_trans->set_billing_type(); //Returns nothing. Will only run on new users, I need the trans method info to successfully set up this.
				
				$emailSuccess = $nb_trans->email_receipt( $payerEml, $ipn_data, 'new_student' );		
				
				
				if( !$emailSuccess ) email_notice("A new student has just registered, but we weren't able to send them a registration email. The transaction id is: $ipn_dataTxnID .");//Do something to notify admin. 
			} elseif( ( $update == true ) && ( $trans_processed == true ) && ( !empty( $nbti ) ) ) {
				$emailSuccess = $nb_trans->email_receipt($payerEml, $ipn_data, 'payment');
				
				if( !$emailSuccess ) email_notice("A student payment was just processed, but we weren't able to send them a receipt by email. The transaction id is: $ipn_dataTxnID .");//Do something to notify admin. 
			}
			
			if( !empty( $nbti ) ){
				$adminEmailSuccess = $nb_trans->email_receipt('brent@trainingdoulas.com', $ipn_data, 'admin');
			} else {
				email_notice("A student payment was just processed, but we weren't able to send them a receipt by email.  Sent from line 406. The transaction id is: $ipn_dataTxnID . The NBTI is $nbti .");
			}
		}
		
	} elseif( is_wp_error( $student ) ) {
		
		 $errors[] = $student;
		 foreach ( $errors as $key => $error ) {
			$message = $error->get_error_message();
			@$msgr->admin_notice("An error occurred in the IPN relay: ".$message);
			
		}
	}
	return true;
}


/******************************
*
* IPN TEXT REPORT
*
* This function is purely administative. Generates a text report of IPN data stored in an array for printing to emails. 
*
******************************/

function ipn_text_report( $data ){
		
		$r = '';
		
	// POST vars
        for ($i=0; $i<80; $i++) { $r .= '-'; }
        $r .= "\n";
        
        foreach ($data as $key => $value) {
            $r .= str_pad($key, 25)."$value\n";
        }
        $r .= "\n\n";
        
        return $r;

}


?>
