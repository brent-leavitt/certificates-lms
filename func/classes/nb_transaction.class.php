<?php 

/*
 *  New Beginnings Transaction PHP Class
 *	Created on 18 July 2013
 *  Updated on 10 Sept 2015
 *
 *	The purpose of this class is to handle transactions, 
 *  both monetary and otherwise, that take place on behalf 
 *  of New Beginnings Doula Training. 
 *
 */

class NB_Transaction{
		
	public $tPost = array(
		'transaction_id' =>	0,
		'student_id' => 	0,
		'pp_txn_id' => 		null, //change to source_txn_id (to allow for vendors other than PayPal)
		'trans_amount' => 	0,
		'trans_time' => 	null,
		'trans_label' => 	null,
		'trans_detail' => 	null,
		'trans_method' => 	null, //Revisit these last two to make non-vendor specific
		'trans_type' =>		null
		//add trans_source -> 'manual','paypal_auto', etc. 
	);	
		
		
	
	public function __construct( $id ) {
		if( !empty( $id ) ){
			
			$this->tPost['student_id'] = $id;
			
			//echo "<br>NB_Transaction::__construct(), this->tPost is {$this->tPost['student_id']}.";
		} else {
			wp_die('Oops, looks like we\'re missing something!');
		}
	}
	/**
	 * SET TPOST
	 *
	 * @since 2.0
	 *
	 * 
	 *
	 **/

	
	public function set_tPost( array $tArr ){
		$updated = false;
		
		foreach($this->tPost as $tKey => $tVal  ){
			if( !empty( $tArr[$tKey] ) ){
				$this->tPost[$tKey] = $tArr[$tKey];	
				$updated = true;
			}
		}
		return $updated;
	}
	
	
	
	/**
	 * PREP IMPORT
	 *
	 * @since 2.0
	 *
	 * Preparing the IMPORT  of CSV files from Pay Pal for consideration. 
	 *
	 * @ $ppArr  - an associative array of info from Pay Pal, one entry at a time.  
	 *
	 * Called:
	 *  -nb_editor.class.php, NB_Editor::prepare_import_csv, approx line 1189.
	 *  
	 * 
	 **/
	 
	public function prep_import( array $ppArr ){
		global $wpdb;
		$result = array();
		
		$tPost = $this->tPost;	
		$sid = $tPost['student_id'];
		$ppID = $tPost['pp_txn_id'] = $ppArr['Transaction_ID'];
	//	echo "the SID is $sid, The ppID is: $ppID <br>";
		
		$tDate  = date('Y-m-d', strtotime($ppArr['Date']));
		$tPost['trans_time'] = $tDate.' '.$ppArr['Time'];
		
		$tPost['trans_amount'] = $trans_amount = $ppArr['Gross'];
		$tPost['trans_label'] = ( $ppArr['Item_Title'] != '')?$ppArr['Item_Title']:$ppArr['Type'];
		
		
		
		//check if transaction already exists in the database
		//check tx_id and student_id on the database. If they match, then return a message that says the transaction has already been submitted. 
		$trans_id = $wpdb->get_var("SELECT transaction_id FROM nb_transactions WHERE pp_txn_id = '$ppID' AND student_id = '$sid' LIMIT 1");
		
		//If the transaction ID is returned, we will not process it again. 
		if( !empty( $trans_id ) ){
			$result['name'] = $ppArr['Name'];
			$result['sid'] = $sid;
			$result['notice'] = "This transaction already exists in the database.";
			$result['trans_id'] = $trans_id;
			
		} else {
		
			//The transaction array from paypal to use with the actual import. We might want to add those 
			$result['sid'] = $sid;
			$result['transArr'] = $ppArr;			
			
			//Message to display
			$result['import_message'] = "{$ppArr['Name']} has a <em>{$ppArr['Type']}</em> for \${$ppArr['Gross']} on {$ppArr['Date']} {$ppArr['Time']}";		
			$add_mess = array(); //additional messages
			
			//student status?
			$s_status =  key( get_user_meta( $sid, 'wp_capabilities', true ) );
			
			if( $s_status == 'student_full_inactive' || $s_status == 'student_partial_inactive' ){
				$add_mess[] = 'Accout is marked as <em>inactive</em>, this payment should reactive it.';	
			} 
			
			//Number of Payments
			$num_payments = array_shift( explode( '/', get_user_meta( $sid, 'payments_received', true ) ) );			
			
			$num_payments = intval( $num_payments ) + 1 ;
			
			if( is_int( $num_payments ) ){
				$add_mess[] = "# of payments on this account will be $num_payments";
			}
		
			//Course Access Level
			$c_access = intval( get_user_meta( $sid, 'course_access', true ) );//course_access
			
			if( !empty( $c_access ) ){
				if( ( $num_payments >= 6 ) && ( $c_access == 1 ) ){
					$add_mess[] = 'Access granted to level 1, but this will grant them access to level 2.';
				} elseif ( ( $num_payments >= 12 ) && ( $c_access == 1 ) ){
					$add_mess[] = 'Access granted to level 1, but they should have access to level 3.';
				} elseif ( ( $num_payments >= 12 ) && ( $c_access == 2 ) ){
					$add_mess[] = 'Access granted to level 2, but they will now have access to level 3.';
				}
			} else {
				if( $num_payments <= 6 ){
					$add_mess[] = 'Access has not been set. It should be level 1.';
				} elseif ( ( $num_payments <= 11 ) && ( $num_payments > 6) ){
					$add_mess[] = 'Access has not been set. It should be level 2.';
				} elseif ( $num_payments >= 12  ){
					$add_mess[] = 'Access has not been set. It should be level 3.';
				}
				
			}

			
			//print_pre( $add_mess );
			
			if( sizeof($add_mess)>0 ){
				$result['add_mess'] = $add_mess;
			}
			
		}
		
		
		//If they don't match 
			//Student Y has (type of transaction: payment, refund, etc.) for $X amount on When.  
			//Have a link to the student's account in question for review purposes. 
			//How will adding this transaction affect the rest of the student's account. 
				//If student is currently marked as inactive, then this will make the student active again. 
				//If student has 3 payments, this make it 4 payments. 
				//if student has access to level 1, does this give them access to level 2?
				// 
	 
		return ( !empty( $result ) )? $result : false;
	
	}
	 
	 
	 
	 
	 
	/**
	 * PROCESS TRANSACTION
	 *
	 * @since 0.1
	 *
	 * The purpose of this function is to update the database with transaction info. 
	 *
	 *
	 * @$nu, new user
	 * @$override, allows us to override the existing transaction in the database, say from an editor screen. 
	 * @$display, displays to the screen the transaction results.
	 *
	 *
	 * Called in:
	 *  -nb_editor.class.php, NB_Editor::process_import_csv() approx line 1373
	 *	-ipn_relay.php, process_transaction() approx line 231
	 *  
	 * SHOULD ALSO BE INCLUDED IN: 
	 *	-nb_editor.class.php, NB_Editor:: load_transaction_editor(), approx line 578 Call twice...
	 *
	 *
	 **/
	public function process_transaction($nu = 0, $override = 0, $display = 0) {
		global $wpdb;
		$tPost = $this->tPost;	
		$sid = $tPost['student_id'];
		$ppID = $tPost['pp_txn_id'];
		$tTime = $tPost['trans_time'];
		$processed = false;
		$stud_home_url = home_url();
		$stud_link = "<a href='$stud_home_url/wp-admin/admin.php?page=edit_student&student_id=$sid' target='_blank'>user #$sid</a>";
		
		$batch_report = array();
		//Check if transaction already exists. 
		$trans_id = $wpdb->get_var("SELECT transaction_id FROM nb_transactions WHERE pp_txn_id = '$ppID' LIMIT 1");
		
		if( !empty( $trans_id ) ){ //transaction already exists. 
			if(!$override){
				
				$batch_report[] = "This transaction already exists for $stud_link. TransID is: $trans_id.";
				
				if($display) echo "<p>This transaction already exists for $stud_link. TransID is: $trans_id.</p> \r\n";
				
			}else{
				
				array_shift($tPost); //We need to drop the transaction ID from the array. 
				
				$transFormat = array( '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s' );
				$tIDarr = array('transaction_id' => $trans_id);
				$db_updated = $wpdb->update( 'nb_transactions', $tPost, $tIDarr, $transFormat );
						
					
				if($db_updated != false){
				
					$tPost = array('transaction_id' => $trans_id) + $tPost;
					//echo "Transaction #$trans_id has been successfully updated.";
					$processed = true;
					$batch_report[] = "This transaction has been updated for $stud_link. TransID is: $trans_id.";
				}  else {
				
					$batch_report[] = "This transaction failed to be updated for $stud_link. TransID is: $trans_id.";
				}
			}
			
		
		} else { //transaction doesn't yet exist, let's insert it. 
			
			
			//echo "<br>NB_Transaction::process_transaction(), line 219. Transaction doesn't exist. We need to add it! Value of $tPost is:";
			//print_pre($tPost);
			
			array_shift($tPost); //We need to drop the transaction ID from the array. 
			
			
			$transFormat = array( '%f', '%s', '%d', '%s', '%s', '%s', '%s', '%s' );
			
			if( ( $wpdb->insert( 'nb_transactions', $tPost, $transFormat ) ) === FALSE ){
				
				$batch_report['errors'][] = 'NB_Transaction::process_tranasction(), line229. Failed to insert new transaction.';
			
			} else {
				
				$transaction_id = $trans_id = $wpdb->insert_id;
				
				if($transaction_id != null) {
				
					$tPost['transaction_id'] = intval( $transaction_id );
					//$this->tPost = $tPost;
					$batch_report['trans_add'] = "Transaction #$transaction_id on $tTime has been added.";
					$processed = true;
					
				} else {
					$batch_report['errors'][] = 'NB_Transaction::process_tranasction(), line 273.No transaction ID has been returned.';
				}
			}
			
		}
		
		if( $processed ){
			//This will return tPost back to the class properties and update the transaction ID. 
			$this->tPost = $tPost;
			
			
			$batch_report['trans_proc'] = "Transaction #$trans_id for $stud_link has been processed.";
			if($display) echo "<p>Transaction #$trans_id for $stud_link has been processed.</p> \r\n";
		
			//We've got a list of housekeeping functions to perform:
		
		//These need to be grouped into a new function called: update_trxn_course_info... come back to this. As it should apply to all transaction type updates: batch processed, manual additions, auto-IPM updates. 
			
			$additional_messages = $this->update_trxn_course_info( $nu, $display );  
			
			if( !empty( $additional_messages ) ){
				$batch_report['add_mess'] = $additional_messages;
			}
			
			
		} else {
			$batch_report['errors'][] = 'NB_Transaction::process_tranasction(), line 299.The transaction failed to be processed';
		}
		
		return $batch_report;
	}


	/** !!! OBSOLETE !!!
	 * PAYPAL IMPORT PREP
	 *
	 * @since 0.1
	 *
	 * 
	 * called in nb_editor.class.php NB_Editor::process_import_csv, approx line 1395.
 	 *
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
			/* case 9:
			case 15: We don't offere these prices any more */
			case 18:
			case 20:
				$tPost['trans_method'] = ( $line['Type'] == 'Recurring Payment Received' )? 'paypal_recurring':'paypal_manual';
				break;

			/* case 100: Obsolete */
			case 180:
			case 200:
				$tPost['trans_method'] = 'paypal_onetime';
				break;
		}
		
		//double check payment type
		//for eChecks do double check. 
		if( $line['Status'] == 'Cleared' ){
			$tPost['trans_type'] = ($line['Type'] == 'Update to eCheck Received')? 'payment' : 'other';
		}elseif( $line['Status'] == 'Completed' ){
			$tPost['trans_type'] = ($line['Type'] == 'Refund')? 'refund' : 'payment';
		}
		
		$this->tPost = $tPost;
		//Does any function currently use the return value? I don't think so. 
		//return $tPost;
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
/* 			case 9:
			case 15: */
			case 18:
			case 20:
			case 34:
			case 36:
				$tPost['trans_method'] = ( isset( $line['subscr_id'] ) )? 'paypal_recurring':'paypal_manual';
				break;

/* 			case 100:
			case 180: */
			case 200:
			case 400:
				$tPost['trans_method'] = 'paypal_onetime';
				break;
				
			default:
				$tPost['trans_method'] = 'other';
				break; 
		}
		
		//douable check payment type
		//for eChecks do double check. 
		if( $line['payment_status'] == 'Completed' ){
			$tPost['trans_type'] = 'payment';
		}elseif( $line['payment_status'] == 'Refunded' ){
			$tPost['trans_type'] = 'refund';
		}
		
		$this->tPost = $tPost;
		
		return $tPost;
	}
	
	

	/**
	 * UPDATE TRXN COURSE INFO
	 *
	 * @since 2.0
	 *
	 * Description: This takes information from 
	 * a newly inserted transaction and updates
	 * the student's course records relevant to 
	 * payments. 
	 *
	 * @$nu, new user? 0,1
	 * @$display, whether or not to display results. 
	 *   	0 = no, 1 = yes
	 *
	 *
	 * Called in: NB_Transaction::process_transaction
	 *
	 * Returns: Batch report(array) on updates. 
	 *
	 **/
	
	public function update_trxn_course_info( $nu, $display ){
		
		$batch_report = array();
	
		if( $this->last_updated_invoice() ){ 	//Updates the Student's record to info us when the last invoice was received. 
			$batch_report[] = "User's account has been updated to the last invoice recieved.";
		} 
		
		//payArr is used to set course access. 
		$payArr = $this->update_payments_received();
		if( !empty( $payArr['messages'] ) )
			$batch_report[] = $payArr['messages'];
		
		//set course access 
		$course_access_results = $this->set_course_access( $payArr, $nu, $display );
		
		if( !empty( $course_access_results ) ){
			$batch_report[] = $course_access_results;
		}
			
	
		return ( !empty( $batch_report ) )? $batch_report : FALSE;
	
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
		$update = NULL;
			
		$tCheck = $wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id=$sid AND meta_key='last_payment_received' LIMIT 1");
		
		if( !empty( $tCheck ) && ( strtotime( $tCheck ) < strtotime( $tStamp ) ) ){
			
			if( !empty( $tStamp ) )	
				$update = update_user_meta( $sid, 'last_payment_received', $tStamp, $tCheck );
			
		} else {
		
			$update = add_user_meta( $sid, 'last_payment_received', $tStamp, true);
		}
		
		return ( !empty( $update ) )? TRUE : FALSE;
			
	}


	/**
	 * UPDATE PAYMENTS RECEIVED
	 *
	 * @since 0.1
	 *
	 * Description: This keeps a running tally of
	 * how many payments have been received from the
	 * student. 
	 *
	 *
	 *
	 * Called in NB_Tranaction::update_trxn_course_info(), line 426.
	 *
	 **/
	public function update_payments_received() {
	
		global $wpdb;
		$tPost = $this->tPost;
		$sid = $tPost['student_id'];
		$tStamp = $tPost['trans_time'];
		$tMethod = $tPost['trans_method'];

		$payCountStr = null;
		$conflict = null;
		
		$tPayArr = $this->checkPayRcvd();//This function should run after the new invoice has been inserted into the database. It's going to tally all recorded payment transactions for user, and return an integer that represents payments made. 
		
		$pRate = $tPayArr['program_rate'];
		//This will only run if an invoice has been received. In otherwords, students with full payments will only have this processed once. 
		//Does this script get run if it is a new student registration on a full course? I think yes, but not sure. 
		
		$tCheck = $wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE meta_key = 'payments_received' AND user_id=$sid LIMIT 1");


		if( !empty( $tCheck ) ){
			
			if($tCheck != '1/1'){ //This is a full payment, shouldn't be this anyways. 
				$payCount = intval(strstr($tCheck, '/', true));
								
				$curPayCount = $tPayArr['total_payments'];
				
				//At this point payCount and curPayCount should be equal. If not, don't update. Send a notice to attend to individually. 
				if( gmp_cmp( $payCount, $curPayCount ) !== 0 ){ 
					$payCountStr = $curPayCount."/12";
					
					if( !empty( $payCountStr ) ){
						update_user_meta( $sid, 'payments_received', $payCountStr, $tCheck );
					}
					
					
					
				} else {
					//There was a conflict in Payments Received for this user, between what is recorded and what is actually there. We need to manual review this record. 
					$stud_home_url = home_url();
					$stud_url = $stud_home_url .'/wp-admin/admin.php?page=edit_student&student_id='.$sid;
					$conflict = "Conflict in Payments Received for <a href='$stud_url' target='_blank'>user $sid</a>, please manually review.";
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
			'program_rate' => $pRate,
			'messages' => $conflict
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

		$payRcvd = $payArr['payments_received'];
		$prgRate = $payArr['program_rate'];
		
		$cAccess = 0; 
		$update = false;
		$batch_report = '';
		
		//This is returning letter 'f' or 'p', value not a number. 
		$p_rate = substr( $prgRate, -1, 1 );
		
		//This is returning the first part of the $payRcvd value. This is a number.
		$pay_val = intval( strstr( $payRcvd, '/', true ) );
		
		//Check if student is new
		if( ($nu == 1) && ($p_rate == 'f') ){
			//If new and paying in full, give full access
			$cAccess = 3; //Full Access
			
		} elseif( ($nu == 1) && ($p_rate == 'p') ) {
			//If new and paying in part, give limited access
			$cAccess = 1; //Limited Access, Level 1
			
		} elseif( ($nu == 0) && ($p_rate == 'p') )  {
			$cAccVal = $wpdb->get_var( "SELECT meta_value FROM wp_usermeta WHERE user_id = $sid AND meta_key = 'course_access' LIMIT 1");
			
			
			if($display) echo "NB_Transaction::set_course_access() cAccVal is $cAccVal. ";
			$cAccVal = ( !empty( $cAccVal ) )? intval( $cAccVal ) : 0 ;
			//Not a new user so it has to be an update. 
			$update = true;
			
			if( $cAccVal == 0){
				$cAccess = 1;
				
				
			//If level isn't set to 3, and more than or equal to 6 payments have been received, step up to level 2. 
			} elseif( (  $pay_val < 12 ) && ( $pay_val >= 6 ) ){
				$cAccess = 2;

			//If level 3 isn't set and 12 payments have been received, step up to level 3. 
			} elseif( ( $cAccVal <= 3 ) && ( $pay_val === 12 ) ){
				$cAccess = 3;
			} 
			
		}
		
		
		
		//update or insert into database
		if(	!empty( $cAccess ) ){
			if($display) echo "The course access for user #$sid has been set to $cAccess.";
			
			
			
			if( !$update ){
				
				
				if( ( add_user_meta($sid, 'course_access', $cAccess, true ) ) !== FALSE ){
					$batch_report = "The course access was not set for user #$sid. It has been added and set to $cAccess.";
				}
				
			} else{
			
				if( ( update_user_meta($sid, 'course_access', $cAccess, $cAccVal) ) !== FALSE ){
					$batch_report = "The course access has been updated for user #$sid. It has been set to $cAccess.";
				}
				
			}
		}
		
		return $batch_report;
	} 


	/**
	 * CHECK PAY RCVD
	 *
	 * @since 0.1
	 * 
	 * Description: 
	 * 
	 * 
	 * Called in: self::update_payments_received(), approx line: 511
	 * 
	 *
	 *
	 **/
  	public function checkPayRcvd(){
		global $wpdb;
		$tPost = $this->tPost;
		$sid = $tPost['student_id'];

		//Pull program rate. 
		$pRate = $wpdb->get_var("SELECT meta_value FROM wp_usermeta WHERE user_id = $sid AND meta_key = 'program_rate' LIMIT 1");

		//Trim off the number from the program rate.
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
		
		//Are there any refunds, or anything else that would credited to their account? 
		
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
				$emlMsg = "
Thank you for registering with New Beginnings Doula 
Training! Please complete the registration process 
by clicking on the following link: 
\r\n
".home_url()."/complete-registration?tx_id=$txnID 
(NOTE: You may have already completed this step if 
you were automatically redirected to the registration 
form after your payment was received.)
\r\n
After completing the registration, a username and 
password will be sent to the primary email address 
specified in the registration process. The username 
and password will be used to access course materials 
online. 
\r\n
=========
IS THIS A GIFT REGISTRATION? During the holiday season, we 
will take the registration link from above and \"gift wrap\" 
it on a digital gift certificate that you can print off or 
send directly as an email attachment. We will contact you 
within one business day for details regarding the gift 
certificate. 
\r\n
Not interested in the gift certicate? Just forward this 
email to the recipient, or click on the link above and 
complete the registration process for them. 
========
\r\n
A copy of the Paypal transaction is include below 
for your records: \r\n
-----------------------------------------------------
$rcptText ";				
				break;
				
			case 'admin':
				$emlSubject = 'Admin Notification - NB Doula Training - Payment Receipt';
				$emlMsg = "
A payment has been processed on 
the trainingdoulas.com website. \r\n
----------------------------------------------------- 
$rcptText ";
				break;			
				
			case 'payment':
			default:
				$emlSubject = 'New Beginnings Doula Training - Payment Receipt';
				$emlMsg = "
Thank you for your payment to New Beginnings Doula
Training. A copy of your payment receipt is included
below for your records. \r\n
----------------------------------------------------- 
$rcptText ";
				break;
		}
		$emlHdrs = array();
		$emlHdrs[] = 'From: New Beginnings <office@trainingdoulas.com>' . "\r\n";
		$emlHdrs[] = 'Reply-To: Rachel Leavitt <rachel@trainingdoulas.com>' . "\r\n";
		
		if( ( !empty($emlSubject) ) && ( !empty ($emlMsg) ) ){
		
		
			 $mailResults = wp_mail( $emlWho, $emlSubject, $emlMsg, $emlHdrs );		
		}
		$mlRslts = ( isset($mailResults) )? $mailResults : false ;
		
		return $mlRslts; 
	}

	
	
} 
?>