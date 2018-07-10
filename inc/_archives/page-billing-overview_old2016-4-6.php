<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 * 	Template Name:  Billing Overview Page
 *
 * @file           page-billing-overview.php
 */
 
	global $wpdb;
	
	$bill_url = home_url( 'billing/' );
	$bill_detail_url = $bill_url.'billing-details/';
	
	
	ob_start();
	
	$student = nb_get_student_meta(); 
	//Present an overview of billing information
	$nb_student_cap = $student->roles[0];
	$nb_alumnus = ( ( strcmp( 'alumnus_active', $nb_student_cap ) == 0 ) || ( strcmp( 'alumnus_inactive', $nb_student_cap ) == 0 ) )? true : false ;
	$nb_base_url = home_url();
	
	//Summary Statement at the Top of the Page
	$nb_stud_date_obj = new DateTime( $student->data->user_registered );
	$nb_stud_date_init = clone $nb_stud_date_obj;
	
	$today = new DateTime('now');
	//
	$nb_bll_actn = array(); //A holding bay for relevenat billing actions to display for user. 
	
	//Variables
	$nb_opt_pymnts = false; //Does student have the option to cancel or payoff course, only on payment plans.
	$nb_opt_reactv = false; //Can the student reactivate their account? If inactive and monthly payment plan. 
	$nb_opt_payment_type = 0; //0 = full course, 1 = autopay, 2 = manual. for Cancelling payments. 
	$nb_opt_extend = false; //Does student have the option to extend their training?
	
	$nb_renew_arr = array('student_partial_active', 'student_full_active', 'student_full_inactive' ); //Current and Active Student roles array
			
	//Setting up variables based on user info. 
	if( strcmp( $student->payments_received, '12/12' ) !== 0 ){
		$nb_opt_pymnts = ( strcmp( 'student_partial_active', $nb_student_cap  ) == 0 )? true : false; 
		$nb_opt_reactv = ( strcmp( 'student_partial_inactive', $nb_student_cap  ) == 0 )? true : false;
		
		switch( $nb_stud_bill_type ){
			case "paypal_recurring":
				$nb_opt_payment_type = 1;
				break;
			case "paypal_manual":
				$nb_opt_payment_type = 2;
				break;
			default: 
				$nb_opt_payment_type = 0;
				break;
		}
	}
	
	
	
	echo "<h2>Account Summary</h2>";
	
	
	if( !$nb_alumnus ) { //If not alumni, is student
	
		$nb_stud_reg_date = $nb_stud_date_obj->format( "l, F j, Y" );
		$nb_stud_date_obj->modify('+2 years');
		
		$nb_stud_end_date = clone $nb_stud_date_obj;
		$nb_stud_end_date_string = $nb_stud_date_obj->format( "l, F j, Y" );
		
		$nb_stud_date_obj->modify('+3 month'); //This should already be set to the expiration date for registration, allow 3 more months. 
		$nb_stud_exp_date = clone $nb_stud_date_obj;
		

		echo "<p>Registration Date: <em>$nb_stud_reg_date</em></p><p>";
		if( $today < $nb_stud_end_date ) {
			echo "You have until <strong>$nb_stud_end_date_string</strong> to complete your training.";
		} else {
			echo "Your student account expired on <strong>$nb_stud_end_date_string</strong>. To resume your training for BIRTH DOULA certification, please select from the options available below.";
		}
		
		
		$pymt_rcvdArr = array(
			'1/1' => 'Paid in Full (1/1)',
			'1/12' => '1 of 12',
			'2/12' => '2 of 12',
			'3/12' => '3 of 12',
			'4/12' => '4 of 12',
			'5/12' => '5 of 12',
			'6/12' => '6 of 12',
			'7/12' => '7 of 12',
			'8/12' => '8 of 12',
			'9/12' => '9 of 12',
			'10/12' => '10 of 12',
			'11/12' => '11 of 12',
			'12/12' => 'Complete (12/12)'
		);	
		
		$nb_stud_pay_rcvd = $student->payments_received;
		
		$billTypeArr = array(
			'paypal_recurring' => 'by recurring subscription using Paypal',
			'paypal_manual' => 'by manual invoice using Paypal',
			'check' => 'via check by mail'
		);	
		
		$nb_stud_bill_type = $student->billing_type;
		
		$progRateArr = array(
			'18p' => 18,
			'20p' => 20,
		);
		
		$nb_stud_prog_rate = $student->program_rate;
		
		//Add more Checks here. 
		if( ( $nb_stud_pay_rcvd == '1/1' ) || ( $nb_stud_pay_rcvd == '12/12' ) ){
			echo " Your account is paid for in full. <br>";
		} else {
			echo " You have made {$pymt_rcvdArr[$nb_stud_pay_rcvd]} payments.<br>";
			
			if( $nb_opt_reactv ){
				echo "Your account is currently marked as <em>inactive</em>. To continue with your training, please reactivate your account.";
			} else {
				echo "Your billing plan is \${$progRateArr[$nb_stud_prog_rate]} per month, making payments {$billTypeArr[$nb_stud_bill_type]}.";
			}
		}	
		
		echo "</p><hr>";

		
		//Additional Billing Actions - make visible only if relevant to account
		
		//REQUEST COURSE EXTENSION

		 //This is a partial student whose account is currently inactive, but has still made full payments... they can renew their accout. 
		if( ( strcmp('student_partial_inactive', $nb_student_cap) == 0 )  && ( strcmp( $nb_stud_pay_rcvd, '12/12' )== 0 ) )
			$nb_renew_arr[] = 'student_partial_inactive';
		  
		 
		 // print_pre($today);
		 // print_pre($nb_stud_date_obj);
		 // print_pre($nb_student_cap);
		 // print_pre($nb_renew_arr);
		 
		if( ( in_array( $nb_student_cap, $nb_renew_arr) ) && ( $today < $nb_stud_exp_date ) )
			$nb_bll_actn[] = 'course_extension'; 
			
		
		//REQUEST ACCOUNT PAYOFF
		
		if( ( ( $nb_opt_pymnts !== false ) || ( $nb_opt_reactv !== false ) ) && ( $today < $nb_stud_date_end  ) ){
			$nb_bll_actn[] = 'account_payoff'; 
		}
		
		//CANCEL PAYMENTS AND ACCOUNTS
		
		if( $nb_opt_pymnts !== false ){
			if( strcmp($nb_stud_bill_type, 'paypal_recurring' ) == 0 )
				$nb_bll_actn[] = 'cancel_recurring';
			elseif( strcmp($nb_stud_bill_type, 'paypal_manual' ) == 0 )
				$nb_bll_actn[] = 'cancel_manual';
			elseif( strcmp($nb_stud_bill_type, 'check' ) == 0 )
				$nb_bll_actn[] = 'cancel_account';
		}
		
		
		// REACTIVATE ACCOUNT
		if( ( strcmp( 'student_partial_inactive', $nb_student_cap ) == 0 ) && (  strcmp( $student->payments_received, '12/12' ) !== 0  ) )
			$nb_bll_actn[] = 'reactivate_account';
		
	} else {
	
		echo "Your training certification for BIRTH DOULA was issued on DATE and is good through NEW DATE.";
		
		// RENEW CERTIFICATION
		echo "<hr>";
		
		if( ( strcmp( 'alumnus_inactive', $nb_student_cap ) == 0 ) || ( strcmp('alumnus_active', $nb_student_cap ) == 0 ) )
			$nb_bll_actn[] = 'renew_certification';
		
	}
	
	$actn_details = array(
		'course_extension' => array(
			'title' => 'Course Extension',
			'url' => 'course-extension',
			'icon' => 'share',
			'detail' => 'Add six(6) months to your course.'
		),
		'account_payoff' => array(
			'title' => 'Account Payoff',
			'url' => 'account-payoff',
			'icon' => 'flag-checkered',
			'detail' => 'Payoff the balance of your training and save.'
		),
		'cancel_recurring' => array(
			'title' => 'Cancel Account',
			'url' => 'cancel-recurring',
			'icon' => 'trash',
			'detail' => 'Deactivate your account and suspend your current payment agreement.'
		),
		'cancel_manual' => array(
			'title' => 'Cancel Account',
			'url' => 'cancel-manual',
			'icon' => 'trash',
			'detail' => 'Deactivate your account and end future invoicing.'
		),
		'cancel_account' => array(
			'title' => 'Cancel Account',
			'url' => 'cancel-account',
			'icon' => 'trash',
			'detail' => 'Deactivate your account.'
		),
		'reactivate_account' => array(
			'title' => 'Reactivate Account',
			'url' => 'reactivate-account',
			'icon' => 'share-alt',
			'detail' => 'Resume payments and continue working on your certification.'
		),
		'renew_certification' => array(
			'title' => 'Renew Certification',
			'url' => 'renew-certification',
			'icon' => 'refresh',
			'detail' => 'Renew your certification for the next two years.'
		)
		
		/*,
		'' => array(
			'title' => '',
			'url' => '',
			'icon' => '',
			'detail' => ''
		),*/
	
	);
	
	foreach($nb_bll_actn as $actn){
		
		$details = $actn_details[$actn];
	
		echo "<div class='tcol-lg-4 tcol-md-4 tcol-sm-4 tcol-xs-6 tcol-ss-12 home-iconmenu homeitemcount1'>                                      
			<a class='home-icon-item' title='{$details['title']}' target='_self' href='{$bill_url}{$details['url']}/' >
				<i class='icon-{$details['icon']}'></i><h4>{$details['title']}</h4><p>{$details['detail']} </p> 
			</a>
		</div>";
	
	}	
	
		
		
	//Transactions Table
	
	echo '<hr style="clear: both;">
		<h3>Student Transaction Records</h3>';
					
		
		$student_id = $student->ID;
		
		$txn_results = $wpdb->get_results('SELECT * FROM `nb_transactions` WHERE student_id='.$student_id);
	
	//Now Add Tables. 
		
	//	print_pre( $nb_bll_actn );
	//	print_pre( $txn_results );
		
		echo "<table id='student_transactions' class='invoice-list nb-table'>";
		echo "<thead>
			<tr>
				<th>Txn ID</th>
				<th>Date</th>
				<th>Amount(USD)</th>
				<th>Description</th>
				<th>Type</th>
			</tr>	
		</thead>
		<tbody>
		";
		
		
		
		foreach( $txn_results as $txn ){
			$tx_url = $bill_detail_url."?tx_id=".$txn->transaction_id;
			echo "<tr>
				<td><a href='{$tx_url}'>{$txn->transaction_id}</a></td>
				<td>{$txn->trans_time}</td>
				<td>{$txn->trans_amount}</td>
				<td>{$txn->trans_label}</td>
				<td>{$txn->trans_type}</td>
			</tr>";
		}
		
		echo "</tbody></table>";
	
		
	
	return ob_get_clean();
?>