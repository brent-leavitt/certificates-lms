<?php 

/*
 *  New Beginnings Transaction PHP Class
 *	Created on 18 July 2013
 *  Updated on 30 July 2013
 *
 *	The purpose of this class is to handle recurring processes related to 
 *	monetary transactions that take place on behalf of New Beginnings Doula Training
 *
 */

class NB_Transaction{
		
	public $tPost = array(
		'transaction_id' =>	0,
		'student_id' => 	0,
		'pp_txn_id' => 		null,
		'trans_amount' => 	0,
		'trans_time' => 	null,
		'trans_label' => 	null,
		'trans_detail' => 	null,
		'trans_method' => 	null,
		'trans_type' =>		null
	);	
		
		
	
	public function __construct($id) {
		if( !empty( $id ) ){
			$this->tPost['student_id'] = $id;
		} else {
			wp_die('Oops, looks like we\'re missing something!');
		}
	}

	/**
	 * PROCESS TRANSACTION
	 *
	 * @since 0.1
	 *
	 * The purpose of this function is to update the database with transaction info. 
	 **/
	public function process_transaction($nu = 0, $override = 0, $display = 0) {
		global $wpdb;
		$tPost = $this->tPost;	
		$sid = $tPost['student_id'];
		$ppID = $tPost['pp_txn_id'];
		$processed = false;
		
		//Check if transaction already exists. 
		$trans_id = $wpdb->get_var("SELECT transaction_id FROM nb_transactions WHERE pp_txn_id = '$ppID' LIMIT 1");
		
		if( !empty( $trans_id ) ){ //transaction already exists. 
			if(!$override){
				
				if($display) echo "<p>This transaction already exists for user #$sid. TransID is: $trans_id.</p> \r\n";
				
			}else{
				
				array_shift($tPost); //We need to drop the transaction ID from the array. 
				
				$transFormat = array( '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s' );
				$tIDarr = array('transaction_id' => $trans_id);
				$db_updated = $wpdb->update( 'nb_transactions', $tPost, $tIDarr, $transFormat );
						
					
				if($db_updated != false){
				
					$tPost = array('transaction_id' => $trans_id) + $tPost;
					//echo "Transaction #$trans_id has been successfully updated.";
					$processed = true;
				} 
			}
			
		
		} else { //transaction doesn't yet exist, let's insert it. 
			
			
			//echo "Transaction doesn't exist. We need to add it!";
			array_shift($tPost); //We need to drop the transaction ID from the array. 
			
			
			$transFormat = array( '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s' );
			
			$wpdb->insert( 'nb_transactions', $tPost, $transFormat );
			
			$transaction_id = $trans_id = $wpdb->insert_id;
			
			if($transaction_id != null) {
			
				$tPost['transaction_id'] => intval( $transaction_id );
				//$this->tPost = $tPost;
				//echo "Transaction #$transaction_id has been added.";
				$processed = true;
				
			}
		}
		
		if($processed){
		
			if($display) echo "<p>Transaction #$trans_id for user #$sid has been processed.</p> \r\n";
			//We've got a list of housekeeping functions to perform:
			$this->last_updated_invoice(); //Returns nothing. Just keeps records clean. 
		
			$payArr = $this->update_payments_received();
			
			$this->set_course_access( $payArr, $nu, $display );
			
			$this->tPost = $tPost;
			
			return true;
		} else {
			return false;
		}
		
	}


	/**
	 * PAYPAL IMPORT PREP
	 *
	 * @since 0.1
	 **/
	public function paypal_import_prep( array $line ) {
		
		$tPost = $this->tPost;
		
		$tDate  = date('Y-m-d', strtotime($line['Date']));
		$tPost['trans_time'] = $tDate.' '.$line['Time'];
		
		$tPost['trans_amount'] = $trans_amount = $line['Gross'];
		$tPost['trans_label'] = ( $line['Item_Title'] != '')?$line['Item_Title']:$line['Type'];
		$tPost['pp_txn_id'] = $line['Transaction_ID'];
		
		//assemble misc info about transaction.
		$trans_detail = "Paypal Transaction ID: ". $line['Transaction_ID'] ."\r\n";
		$trans_detail .= "Transaction type: ". $line['Type'] .", Transaction status: ". $line['Status']."\r\n";
		$trans_detail .= "Name: ". $line['Name']." (".$line['Counterparty_Status'].")\r\n";
		$trans_detail .= "Email: ". $line['From_Email_Address']."\r\n";
		$trans_detail .= "Payment Sent to: ". $line['To_Email_Address']."\r\n\r\n";
		$trans_detail .= "Total amount: ". $line['Gross']."\r\n";
		$trans_detail .= "Fee amount: ". $line['Fee']."\r\n";
		$trans_detail .= "Net amount: ". $line['Net']."\r\n";
		
		$tPost['trans_detail'] = $trans_detail;
		
		//depending on gross payment amount. 
		switch($trans_amount){
			case 9:
			case 15:
			case 18:
			case 20:
				$tPost['trans_method'] = ( $line['Type'] == 'Recurring Payment Received' )? 'paypal_recurring':'paypal_manual';
				break;

			case 100:
			case 200:
				$tPost['trans_method'] = 'paypal_onetime';
				break;
		}
		
		//douable check payment type
		//for eChecks do double check. 
		if( $line['Status'] == 'Cleared' ){
			$tPost['trans_type'] = ($line['Type'] == 'Update to eCheck Received')? 'payment' : 'other';
		}elseif( $line['Status'] == 'Completed' ){
			$tPost['trans_type'] = ($line['Type'] == 'Refund')? 'refund' : 'payment';
		}
		
		$this->tPost = $tPost;
		
		return $tPost;
	}

	
	

	/**
	 * PREP IPN RECORD
	 *
	 * @since 0.1
	 **/
	public function prep_ipn_record( array $line ) {
		
		$tPost = $this->tPost;
		
		$tPost['trans_time'] = date('Y-m-d H:i:s', strtotime($line['payment_date']));
		
		$tPost['trans_amount'] = $trans_amount = intval($line['mc_gross']);
		$tPost['trans_label'] = ( $line['item_name'] != '')?$line['item_name']:$line['txn_type'];
		$tPost['pp_txn_id'] = $line['txn_id'];
		
		//assemble misc info about transaction.
		$trans_detail = "Paypal Transaction ID: ". $line['txn_id'] ."\r\n";
		$trans_detail .= "Transaction type: ". $line['txn_type'] .", Transaction status: ". $line['payment_status']."\r\n";
		$trans_detail .= "Name: ". $line['address_name']." (".$line['payer_status'].")\r\n";
		$trans_detail .= "Email: ". $line['payer_email']."\r\n";
		$trans_detail .= "Payment Sent to: ". $line['receiver_email']."\r\n\r\n";
		$trans_detail .= "Total amount: ". $line['payment_gross']."\r\n";
		$trans_detail .= "Fee amount: ". $line['payment_fee']."\r\n";
		$line['payment_net'] = (floatval($line['payment_gross'])) - (floatval($line['payment_fee']));
		$trans_detail .= "Net amount: ". $line['payment_net']."\r\n";
		
		$tPost['trans_detail'] = $trans_detail;
		
		//depending on gross payment amount. 
		switch($trans_amount){
			case 9:
			case 15:
			case 18:
			case 20:
				$tPost['trans_method'] = ( isset( $line['recurring_payment_id'] ) )? 'paypal_recurring':'paypal_manual';
				break;

			case 100:
			case 200:
				$tPost['trans_method'] = 'paypal_onetime';
				break;
		}
		
		//douable check payment type
		//for eChecks do double check. 
		if( $line['payment_status'] == 'Completed' ){
			$tPost['trans_type'] =  'payment';
		}elseif( $line['payment_status'] == 'Completed' ){
			$tPost['trans_type'] = ($line['payment_status'] == 'Refunded')? 'refund' : 'payment';
		}
		
		$this->tPost = $tPost;
		
		return $tPost;
	}
	
	

	/**
	 * SET BILLING TYPE
	 *
	 * @since 0.1
	 **/
	public function set_billing_type() {
		//This could do more, but I'm keeping it basic. This is only called on new user insertions. 
		
		$sid = $this->tPost['student_id'];
		$bType = $this->tPost['trans_method'];
		
		if( !empty( $bType ) )
			update_user_meta( $sid, 'billing_type', $bType );
		
	}


	/**
	 * LAST UPDATED INVOICE
	 *
	 * @since 0.1
	 **/
	public function last_updated_invoice() {
		global $wpdb;
		$tPost = $this->tPost;
		$sid = $tPost['student_id'];
		$tStamp = $tPost['trans_time'];
			
		$tCheck = $wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id=$sid AND meta_key='last_payment_received' LIMIT 1");
		
		if( !empty( $tCheck ) && ( strtotime( $tCheck ) < strtotime( $tStamp ) ) ){
			
			if( !empty( $tStamp ) )	
				update_user_meta( $sid, 'last_payment_received', $tStamp, $tCheck );
			
		} else {
		
			add_user_meta( $sid, 'last_payment_received', $tStamp, true);
		}
	}


	/**
	 * UPDATE PAYMENTS RECEIVED
	 *
	 * @since 0.1
	 **/
	public function update_payments_received() {
	
		global $wpdb;
		$tPost = $this->tPost;
		$sid = $tPost['student_id'];
		$tStamp = $tPost['trans_time'];
		$tMethod = $tPost['trans_method'];

		$payCountStr = null;
		
		$tPayArr = $this->checkPayRcvd();//This function should run after the new invoice has been inserted into the database. It's going to tally all recorded payment transactions for user, and return an integer that represents payments made. 
		
		$pRate = $tPayArr['program_rate'];
		//This will only run if an invoice has been received. In otherwords, students with full payments will only have this processed once. 
		//Does this script get run if it is a new student registration on a full course? I think yes, but not sure. 
		
		$tCheck = $wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE meta_key = 'payments_received' AND user_id=$sid LIMIT 1");


		if( !empty( $tCheck ) ){
			
			if($tCheck != '1/1'){ //This is a full payment, shouldn't be this anyways. 
				$payCount = intval(strstr($tCheck, '/', true));
				$payCount++;
				
				
				
				$curPayCount = $tPayArr['total_payments'];
				
				//At this point payCount and curPayCount should be equal. If not, don't update. Send a notice to attend to individually. 
				if( gmp_cmp( $payCount, $curPayCount ) == 0 ){ 
					$payCountStr = $payCount."/12";
					
					if( !empty( $payCountStr ) ){
						update_user_meta( $sid, 'payments_received', $payCountStr, $tCheck );
					}
					
					
					
				} else {
					//There was a conflict in Payments Received for this user, between what is recorded and what is actually there. We need to manual review this record. 
					echo "<br>
						<h3>Conflict in Payments Received for user $sid, please manually record and review.</h3>
						<ul style='color:red;'>
							<li>Transaction recorded suggest that $curPayCount payments have been made. </li>
							<li>PaymentsReceived metaData says that $payCount payments have been made. </li>
						</ul>
					";
				}
			}
			$payRcvd = $payCountStr; 
			
		} else { //New student, set new metadata for this. What if it is full payment? hmm. 
			//check amount paid. 
			
			$payRcvdVal = ($tMethod == 'paypal_onetime')? '1/1' : '1/12';

			add_user_meta( $sid, 'payments_received', $payRcvdVal, true );

			$payRcvd = $payRcvdVal;
			
		}
		
 		return array(
			'payments_received' => $payRcvd, 
			'program_rate' => $pRate
		); 
	
	} 


	/**
	 * SET COURSE ACCESS
	 *
	 * @since 0.1
	 **/
	 
  	public function set_course_access( array $payArr, $nu = 0, $display = 0){ 
		
		global $wpdb;
		$tPost = $this->tPost;
		$sid = $tPost['student_id'];
		//print_pre($tPost);
		//print_pre($payArr);
		$payRcvd = $payArr['payments_received'];
		$prgRate = $payArr['program_rate'];
		
		//paying in part or full?
		$cAccess = 0;
		$caChange = 0;
		$p_rate = substr( $prgRate, -1, 1 );
		
		$pay_val = intval( strstr( $payRcvd, '/', true ) );
		
		//Check if student is new
		if( ($nu == 1) && ($p_rate == 'f') ){
			//If new and paying in full, give full access
			$cAccess = 3; //Full Access
			$caChange = 1;
		} elseif( ($nu == 1) && ($p_rate == 'p') ) {
			//If new and paying in part, give limited access
			$cAccess = 1; //Limited Access, Level 1
			$caChange = 1;
		} elseif( ($nu == 0) && ($p_rate == 'p') )  {
			$cAccVal = $wpdb->get_var( "SELECT meta_value FROM wp_usermeta WHERE user_id = $sid AND meta_key = 'course_access' LIMIT 1");
			
			$cAccVal = ( isset( $cAccVal ) )? intval($cAccVal) : 0 ;
			
			if( $cAccVal == 0){
				$cAccess = 1;
				$caChange = 1;
				
			//If level isn't set to 3, and more than 6 payments have been received, step up to level 2. 
			} elseif( ( $cAccVal < 3 ) && ( $pay_val > 6 ) ){
				$cAccess = 2;
				$caChange = 2;

			//If level 3 isn't set and 12 payments have been received, step up to level 3. 
			} elseif( ( $cAccVal < 3 ) && ( $pay_val == 12 ) ){
				$cAccess = 3;
				$caChange = 2;
			} 
			
		}
		
		
		
		//update or insert into database
		if(	!empty( $cAccess ) ){
			if($display) echo "The course access for user #$sid has been set to $cAccess.";
			
			if( $caChange ==  1 ){
			
				add_user_meta($sid, 'course_access', $cAccess, true );
				
			} elseif( $caChange == 2 ){
			
				update_user_meta($sid, 'course_access', $cAccess, $cAccVal);
				
			}
		}
	} 


	/**
	 * CHECK PAY RCVD
	 *
	 * @since 0.1
	 **/
  	public function checkPayRcvd(){
		global $wpdb;
		$tPost = $this->tPost;
		$sid = $tPost['student_id'];

		//Pull program rate. 
		$pRate = $wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id = $sid AND meta_key = 'program_rate' LIMIT 1");

		if( !empty($pRate) ){
			$progRateInt = intval(substr($pRate, 0, -1));
		}

		//pull transactions
		$transArr = array();
		$transQ = "SELECT * FROM nb_transactions WHERE student_id = $sid AND trans_type = 'payment'";
		
		$tqResult = $wpdb->get_results( $transQ , ARRAY_A );

		foreach($tqResult as $tqr){
			$transArr[] = intval($tqr['trans_amount']);
		}
		
		$studentTotal = intval( array_sum($transArr) );		
		
		$totalPayments = ( $studentTotal/$progRateInt );
		
		
		//One final check to make sure that things are in scope. 
		if( is_int( $totalPayments ) && ( $totalPayments > 0) && ( $totalPayments < 13)){
			$tPayments = $totalPayments;		
		} else {
			$tPayments = 0; 	
		}
		
		return array(
			'total_payments' => $tPayments,
			'program_rate' => $pRate
		);
	}
	
	
	/**
	 * EMAIL RECEIPT
	 *
	 * @since 0.1
	 *
	 * This function defines who is receiving the emails and what information to send the receiver.  
	 *
	 **/
	 
	public function email_receipt($emlWho, $emlWhat, $emlWhy){
		
		//$emlWhat is an array or json string of info to assemble.
		
		$ewt = $emlWhat; //This is the infoPost array. 
		$emlSubject = '';
		$emlMsg = '';
		
		$nbti = ( isset( $ewt['nbti'] ) )? $ewt['nbti'] : 0 ; //This isn't set. 
		$tDate = $ewt['payment_date'];
		$tFrom = $ewt['first_name'].' '. $ewt['last_name'];
		$tEmail = $ewt['payer_email'];
		$tAmount = $ewt['mc_gross'];
		$tDes = ( isset( $ewt['item_name'] ) )? $ewt['item_name'] : $ewt['txn_type'] ;
		$tPpTxnId = $ewt['txn_id'];
		$tPayStat = $ewt['payment_status'];
		
		//Standard Headers? 
		
		
		$rcptText = "----------------------------------------------------- \r\n
\r\n
New Beginnings Transaction ID: $nbti\r\n
Date: $tDate \r\n
From: ($tFrom, $tEmail) \r\n		
Amount: $tAmount \r\n
Description: $tDes \r\n
Paypal Transaction ID: $tPpTxnId \r\n
Payment Status: $tPayStat \r\n
\r\n
----------------------------------------------------- \r\n ";
		
		//$emlWhy is the type of email to send.
		switch($emlWhy){
			case 'new_student':
				$emlSubject = 'New Beginnings Doula Training Registration - One More Step';	
				$txnID = $ewt['txn_id'];
				$emlMsg = "----------------------------------------------------- \r\n
\r\n
Thank you for registering with New Beginnings Doula Training! Please complete the registration process by clicking on the following link: \r\n
http://www.trainingdoulas.com/complete-registration?tx_id=$txnID \r\n
\r\n
Registering for someone else? Please forward this email to them, or click on the link above and complete the registration process for them. \r\n
\r\n
After completing the registration, a username and password will be sent to the primary email address that is specified in the registration process. The username and password will be used to access course materials online. \r\n
\r\n
\r\n
----------------------------------------------------- \r\n
\r\n
A copy of the Paypal transaction is include below for your records: \r\n
$rcptText ";				
				break;
				
			case 'admin':
				$emlSubject = 'Admin Notification - NB Doula Training - Payment Receipt';
				$emlMsg = "----------------------------------------------------- \r\n
\r\n
A new payment has been processed on the trainingdoulas.com website. \r\n
\r\n
----------------------------------------------------- \r\n
\r\n
$rcptText ";
				break;			
				
			case 'payment':
			default:
				$emlSubject = 'New Beginnings Doula Training - Payment Receipt';
				$emlMsg = "----------------------------------------------------- \r\n
\r\n
Thank you for your payment to New Beginnings Doula Training. A copy of your payment receipt is included below for your records. \r\n
\r\n
----------------------------------------------------- \r\n
\r\n
$rcptText ";
				break;
		}
		
		$emlHdrs = 'From: New Beginnings <rachel@trainingdoulas.com>' . "\r\n";
		
		if( ( !empty($emlSubject) ) && ( !empty ($emlMsg) ) ){
		
		
			 $mailResults = wp_mail( $emlWho, $emlSubject, $emlMsg, $emlHdrs );		
		}
		$mlRslts = ( isset($mailResults) )? $mailResults : false ;
		
		return $mlRslts; 
}

	
	
} 
?>