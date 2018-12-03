<?php
//This is the scripts that generate the progress report page. 


add_shortcode( 'nb_progress_report', 'nb_get_progress_report' );
add_shortcode( 'nb_profile_editor', 'nb_get_profile_editor' );
add_shortcode( 'nb_billing_overview', 'nb_get_billing_overview' );
add_shortcode( 'nb_billing_details', 'nb_get_billing_details' );


function nb_get_billing_overview(){
	global $wpdb;
	
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
		

		echo "<br>Registration Date: <em>$nb_stud_reg_date</em><br><br>";
		if( $today < $nb_stud_end_date ) {
			echo "You have until <strong>$nb_stud_end_date_string</strong> to complete your training.";
		} else {
			echo "Your student account expired on <strong>$nb_stud_end_date_string</strong>. To resume your training for BIRTH DOULA certification, please select from the options available below.";
		}
		
		//echo"<br> <a href='$nb_base_url/request-extension/'  >(Request Extension)</a> <br><br>";

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
			'paypal_manual' => 'by manual invoice using Paypal ',
			'check' => 'via check by mail.'
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
		
		echo "<br><hr><br>";

		
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
		echo "<br><hr><br>";
		
		if( ( strcmp( 'alumnus_inactive', $nb_student_cap ) == 0 ) || ( strcmp('alumnus_active', $nb_student_cap ) == 0 ) )
			$nb_bll_actn[] = 'renew_certification';
		
	}
	
	$actn_details = array(
		'course_extension' => array(
			'title' => 'Course Extension',
			'url' => '#',
			'icon' => 'book',
			'detail' => 'Add six(6) months to your course.'
		),
		'account_payoff' => array(
			'title' => 'Account Payoff',
			'url' => '#',
			'icon' => '',
			'detail' => 'Payoff the balance of your training and save.'
		),
		'cancel_recurring' => array(
			'title' => 'Cancel Account',
			'url' => '#',
			'icon' => '',
			'detail' => 'Deactivate your account and suspend your current payment agreement.'
		),
		'cancel_manual' => array(
			'title' => 'Cancel Account',
			'url' => '#',
			'icon' => '',
			'detail' => 'Deactivate your account and end future invoicing.'
		),
		'cancel_account' => array(
			'title' => 'Cancel Account',
			'url' => '#',
			'icon' => '',
			'detail' => 'Deactivate your account.'
		),
		'reactivate_account' => array(
			'title' => 'Reactivate Account',
			'url' => '#',
			'icon' => '',
			'detail' => 'Resume payments and continue working on your certification.'
		),
		'renew_certification' => array(
			'title' => 'Renew Certification',
			'url' => '#',
			'icon' => '',
			'detail' => ''
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
			<a class='home-icon-item' title='{$details['title']}' target='_self' href='{$details['url']}' >
				<i class='icon-{$details['icon']}'></i><h4>{$details['title']}</h4><p>{$details['detail']} </p> 
			</a>
		</div>";
	
	}	
	
		
		
	//Transactions Table
	
	echo '<br><hr style="clear: both;"><br>
		<h3>Student Transaction Records</h3>';
					
		
		$student_id = $student->ID;
		
		$txn_results = $wpdb->get_results('SELECT * FROM `nb_transactions` WHERE student_id='.$student_id);
	
	//Now Add Tables. 
		
	//	print_pre( $nb_bll_actn );
	//	print_pre( $txn_results );
		
		echo "<table id='student_transactions' class='invoice-list'>";
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
			echo "<tr>
				<td><a href=''>{$txn->transaction_id}</a></td>
				<td>{$txn->trans_time}</td>
				<td>{$txn->trans_amount}</td>
				<td>{$txn->trans_label}</td>
				<td>{$txn->trans_type}</td>
			</tr>";
		}
		
		echo "</tbody></table>";
	
		
	
	return ob_get_clean();
	
}

function nb_get_billing_details(){
	$student = nb_get_student_meta(); 
	
	
	// Get the individual transaction detail for student consideration. 
	//
	
}

//A function to get all the meta data for the student to display and manipulate. 
function nb_get_student_meta(){
	global $current_user;

	if( $current_user->ID != null){
		$sid = $current_user->ID;
		$student = get_userdata($sid); 

	}
	
	return $student;

}

function nb_get_profile_editor(){
	ob_start();
	$student = nb_get_student_meta();

	if( !empty($_POST) || wp_verify_nonce($_POST['trees_and_flowers'],'account-profile') ) {

		//	echo "The value of Post: <br>";
		//	var_dump( $_POST );

		//	echo "<br><br>The value of STUDENT: <br>";
		//	var_dump( $student );
		if( isset($_POST['trees_and_flowers']) && ( $_POST['_wp_http_referer'] == '/account-profile/' ) ){
			
			
			$nbStud = new NB_Student();
			$_POST['ID'] = $student->ID;
			
			
			$new_student_data = $nbStud->update_student($_POST, 1, 0); //override set, don't display update detail messages. 
			
			if( is_a( $new_student_data, 'WP_User' ) ) {// New Method from NB_Student class, needs to be created. 
				$updated = true;
				
				//Need a way to show what's been updated, to compare before and after changes. 
				$eTo = "brent@trainingdoulas.com";
				$eSubject = "Student Profile Updated for: {$student->first_name} {$student->last_name}";
				$eAdminURL = get_bloginfo( 'url' );
				$eAdminURL .= "/check-in.php?redirect_to=%2Fwp-admin%2Fadmin.php%3Fpage%3Dedit%5Fstudent%26student%5Fid%3D{$sid}";
				$eMessage = "Just a heads up. The student profile for {$student->first_name} {$student->last_name} has been updated. To review changes, go to:\n\r{$eAdminURL}";
				
				 wp_mail( $eTo, $eSubject, $eMessage );
			}
			//Do we want to send an update message? we could. 
			$message = ( isset($updated) && ($updated == true) )? "We've updated the student account for $student->display_name." : "There was nothing to update. Please try again.";
			
				//echo "The value of Student is:";
				//var_dump($student);
		}

	}


	if($message != null)
		echo '<div class="updated" id="message"><p>'.$message.'</p></div>';
		 
	echo'<form method="post" action="/account-profile/">';	
		
		wp_nonce_field('account-profile','trees_and_flowers');
		echo'	
			<h3>Personal Information</h3>
			<table class="form-table">
				<tr>
					<td>
						<label for="first_name">First Name</label>
						<input type="text" id="first_name" name="first_name"  class="regular-text" value="'.$student->first_name.'" >
						<span class="reason">Your first given name that you are formally recognized as; this will be printed on your certificate.</span>
					</td>
					<td>
						<label for="last_name">Last Name</label>
						<input type="text" id="last_name" name="last_name"  class="regular-text" value="'.$student->last_name.'" >
						<span class="reason">Your current legal last name; this will also be printed on your certificate.</span>
					</td>	
				</tr>
				<tr>
					<td>
						<label for="user_login">Username</label>
						<span><em>(cannot be changed)</em></span>
						<input disabled type="text" id="user_login" name="user_login"  class="regular-text" value="'.$student->data->user_login.'" >
						<span class="reason">This is your log-in name for your account access; no one sees this except for you when you log in.</span>
					</td>
					<td>
						<label for="display_name">Screen Name</label>
						<input type="text" id="display_name" name="display_name"  class="regular-text" value="'.$student->data->display_name.'" >
						<span class="reason">Your preferred name for contact and for public and private communications; nicknames are ok here.</span>
					</td>
				</tr>
				<tr>
					<td>
						<label for="user_email">Primary Email</label>
						<input type="email" id="user_email" name="user_email"  class="regular-text" value="'.$student->data->user_email.'" >
						<span class="reason">Valid email address for course correspondence; PLEASE double check your spelling!</span>
					</td>
					<td>
						<label for="student_phone">Phone</label>
						<input type="text" id="student_phone" name="student_phone"  class="regular-text" value="'.$student->student_phone.'" >
						<span class="reason">Should we need to contact you by phone, please provide a current, working number</span>
					</td>
				</tr>
				<tr>
					<td>
						<label for="student_address">Address</label>
						<input type="text" id="student_address" name="student_address"  class="regular-text" value="'.$student->student_address.'" >
						<span class="reason">Current mailing address; used for mailing certificate on completion.</span>
					</td>
					<td>
						<label for="student_address2">Address, Second Line</label>
						<input type="text" id="student_address2" name="student_address2"  class="regular-text" value="'.$student->student_address2.'" >
					</td>
				</tr>
				<tr>
					<td>
						<label for="student_city">City</label>
						<input type="text" id="student_city" name="student_city"  value="'.$student->student_city.'" >
					</td>
					<td>
						<label for="student_state">State/Providence/Region</label>
						<input type="text" id="student_state" name="student_state"  value="'.$student->student_state.'" >
					</td>
				</tr>
				<tr>
					<td>
						<label for="student_country">Country</label>
						<input type="text" id="student_country" name="student_country"  class="regular-text" value="'.$student->student_country.'" >
					</td>
					<td>
						<label for="student_postalcode">Postal Code</label>
						<input type="text" id="student_postalcode" name="student_postalcode"  value="'.$student->student_postalcode.'" >
					</td>
					
					
				</tr>
			</table>
			
			
			<h3>Payment Information</h3>
			<table class="form-table">
				<tr>
					<td colspan="2" >
						<label for="student_paypal">Paypal Email</label>
						<input type="email" id="student_paypal" name="student_paypal"  class="regular-text" value="'.$student->student_paypal.'" >
						<span class="reason">The email address of the PayPal account that you are using to pay for your course work. This ensures that your account receives credit for funds paid.</span>
					</td>
				</tr>
			</table>
			
			<p class="submit"><input type="submit" value="Update Student" class="button button-primary" id="submit" name="Update Account"></p>
			
		</form>'; 
		
		
		//Insert code to send to another page for transaction history. 
		

	return ob_get_clean();
}



function nb_get_progress_report(){
	
	$student = nb_get_student_meta();
	$studGrades = $student->student_grades; //Returns an array of grades		
	$studFirstName = $student->first_name;
	$studLastName = $student->last_name;
	
	$nb_asmt_url = home_url( '/?p=' );
	$asmt_map = new NB_Assignment_Map();
	
	//print_pre( $studGrades );
	//print_pre( $asmt_map->asmt_map );
	
	$optArr = array(
		0 => "No Status",
		1 => "Submitted",
		2 => "Incomplete",
		3 => "Resubmitted",
		4 => "Completed",
	);
	 
	 
	 
	
	
	//END PREP WORK. 
	
	
	$report_op = '';
	
	$report_op = "	
		<p>Below you will find a list of assignments required to complete your doula training and your current status for each assignment according to our records. If you find that our records appear to be incomplete or inaccurate, please contact us so that we may update your account accordingly. </p>";
	

	
	   $report_op .= "<p>Student Name: <em>$studFirstName $studLastName</em></p>";
    
		

		$report_op .='<table class="form-table nb-student-reports nb-progress">';
		
		foreach( $asmt_map->asmt_map->certs as $cert_key => $cert ){
			foreach( $cert->courses as $course_key => $course ){
				foreach( $course->units as $unit_key => $unit ){		

					$report_op .="<tr>
						<th colspan='2'>
							<h4>{$unit->name}</h4>
							
						</th>
					</tr>
					<tr class='meta-info'>
						<td><em>assignment name</em></td>
						<td><em>status</em></td>
					</tr>
					
					";

				
					foreach($unit->assignments as $asmt_key => $asmt_obj){
						
						//Get the meat of the report
						$report_op .="<tr>
							<td><a href='{$nb_asmt_url}{$asmt_obj->post_id}' target='_blank'>{$asmt_obj->asmt_title}</a>	
							</td><td>";
							
						$studOpt = $studGrades[$cert_key][$course_key][$unit_key][$asmt_key];					
								
						foreach($optArr as $oKey => $oVal){
							if( $studOpt == $oKey ){
								if( $oKey == 0 ){
									$report_op .="<span style='color: #B4B4B4'>";
								} elseif( $oKey == 2 ){
									$report_op .="<span style='color: red'>";
								} elseif( $oKey == 4 ){
									$report_op .="<span style='color: DarkGreen'>";
								}elseif( ( $oKey == 1 ) || ( $oKey == 3 ) ){
									$report_op .="<span style='color: blue'>";
								} else {
									$report_op .="<span>";
								}
								$report_op .="$oVal </span>";
							}
						}
								
						$report_op .="</td>
						</tr>";
						
						//end middle meat, yum!
						
						
					}
				}
			}
		}
		

		$report_op .='	
			</table>
			';
	
	
	return $report_op;
} //END nb_get_progress_report

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