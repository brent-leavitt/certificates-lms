<?php 

add_action( 'admin_menu', 'register_students_admin' );

function register_students_admin(){
	add_menu_page('Students Overview', 'Students', 'edit_users', 'students', 'load_students_overview', null, 50 );
	add_submenu_page( 'students', 'Add New Student', 'Add New', 'edit_users', 'add_student', 'load_new_student_editor' );
	add_submenu_page( NULL, 'Edit Student', '', 'edit_users', 'edit_student', 'load_student_editor' );
	add_submenu_page( 'students', 'Transactions Overview', 'Transactions', 'edit_users', 'nb_transactions', 'load_transactions_overview' );
	add_submenu_page( NULL, 'Add New Transaction', '', 'edit_users', 'add_transaction', 'load_new_transaction');
	add_submenu_page( NULL, 'Edit Transaction', '', 'edit_users', 'edit_transaction', 'load_transaction_editor');
	add_admin_menu_separator(30);
	
	//add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );

}



function load_students_overview(){
	
	if (!current_user_can('edit_users'))
        wp_die(__('You do not have sufficient permissions to access this page.'));
		
	global $student_type;
	
	if( !isset ( $_GET['student_type'] ) ){
		$student_type = 'all';
	} else {
		$student_type = $_GET['student_type'];	
	}

	switch ($student_type){
		case 'current':
			students_overview_current();
			break;
			
		case 'inactive':
			students_overview_inactive();
			break;
			
		case 'alumni':
			students_overview_alumni();
			break;
			
		case 'all':
		default:
			students_overview_all();
			break;
	}	
	
	
}


/*
 *	Admin Editor Screens
 */

function load_student_editor(){
	global $wpdb;
	
	//This is what loads the individual student pages. 
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	
	$sid = $_REQUEST['student_id'];
	$student = get_userdata($sid);

	$student_meta_keys = array( 'first_name', 'last_name', 'nickname', 'description', 'payments_received', 'student_address', 'student_address2', 'student_city', 'student_state', 'student_country', 'admin_notes', 'student_postalcode',  'student_phone',  'student_paypal', 'program_rate', 'billing_type', 'last_payment_received', 'course_access' );
	
	//A quick little fix to pull values out of arrays. 
 	foreach($student_meta as $sm_val=>$sm_array)
		$student_meta[$sm_val] = $sm_array[0];	
		
/* 	print('<pre>');
	print_r($_POST);
	print_r($student);
	print('</pre>');	 */
	
	if( isset($_POST['student-editor-check']) && $_POST['_wp_http_referer'] == '/wp-admin/admin.php?page=edit_student&student_id='.$sid ){
		
		//We need to update the database
		$update_student = array( 'ID' => $sid );
		$update_student_meta = array( 'ID' => $sid );
 		
		
		foreach($_POST as $post_key => $post_value){
			if(  isset( $student->$post_key )  && ( $student->$post_key != $post_value ) && ( $post_key != 'student_status' ) ){
				if ( in_array( $post_key, $student_meta_keys )  ){
					//checking to see if it is a meta value. 
					update_user_meta( $sid, $post_key, $post_value, $student->$post_key );
				} else {
					$update_student[$post_key] = $post_value;	
				}
			} elseif( !isset( $student->$post_key ) && in_array( $post_key, $student_meta_keys ) && ( $post_key != 'student_status' )  ){
				
				update_user_meta( $sid, $post_key, $post_value );
				
			} elseif( $post_key == 'student_status' ) {
			
				if( isset($student->allcaps['student_current'])  &&  ($post_value == 0)){//switch to inactive student
					if($student->roles[0] == 'student_full_active'){	
					
						 $sUser = new WP_User($sid);
						 $sUser->remove_role('student_full_active');
						 $sUser->add_role('student_full_inactive');
						 
					}elseif($student->roles[0]  == 'student_partial_active'){
					
						 $sUser = new WP_User($sid);
						 $sUser->remove_role('student_partial_active');
						 $sUser->add_role('student_partial_inactive');
					}	
					
					$update_id = $sid; //Triggers the get_userdata function. 
					
				}elseif( !isset($student->allcaps['student_current'])  && ($post_value == 1) ){//switch to current student
					if($student->roles[0]  == 'student_full_inactive'){		
					
						 $sUser = new WP_User($sid);
						 $sUser->remove_role('student_full_inactive');
						 $sUser->add_role('student_full_active');
						 
					}elseif($student->roles[0]  == 'student_partial_inactive'){
					
						 $sUser = new WP_User($sid);
						 $sUser->remove_role('student_partial_inactive');
						 $sUser->add_role('student_partial_active');
						 
					}
					
					$update_id = $sid; //Triggers the get_userdata function. 
				}
			}
		}
		if( count( $update_student ) > 1 )
			$update_id = wp_update_user( $update_student );
		
		if( isset($update_id) )
			$student = get_userdata($update_id);
		
	}
	
	//$student_current = ($student->allcaps['student_current'] == 1)? 'current': 'inactive';
	
	echo '<div class="wrap">';
	screen_icon('edit-user'); 
	echo '<h2>Student Editor: <em>'.$student->display_name.'</em></h2>
	
	<h3>Student Transaction Records</h3>';
	
	if(class_exists('NB_Transaction_Tables')){
	
		$nb_transaction_list = new NB_Transaction_Tables();
		
 		$nb_transaction_list->prepare_items();
	
		$nb_transaction_list->display();
	}
	
	
	echo'<form method="post">';
		 wp_nonce_field('edit_student','student-editor-check');
	echo'	
		<h3>Personal Information</h3>
		<table class="form-table">
			<tr>
				<td>
					<label for="first_name">First Name</label>
					<input type="text" id="first_name" name="first_name"  class="regular-text" value="'.$student->first_name.'" >
				</td>
				<td>
					<label for="last_name">Last Name</label>
					<input type="text" id="last_name" name="last_name"  class="regular-text" value="'.$student->last_name.'" >
				</td>
				<td>
					<label for="user_login">User Name</label>
					<input disabled type="text" id="user_login" name="user_login"  class="regular-text" value="'.$student->data->user_login.'" >
				</td>
			</tr>
			<tr>
				
				<td>
					<label for="display_name">Display Name</label>
					<input type="text" id="display_name" name="display_name"  class="regular-text" value="'.$student->data->display_name.'" >
				</td>
				<td>
					<label for="user_email">Email</label>
					<input type="email" id="user_email" name="user_email"  class="regular-text" value="'.$student->data->user_email.'" >
				</td>
				<td>
					<label for="student_phone">Phone</label>
					<input type="phone" id="student_phone" name="student_phone"  class="regular-text" value="'.$student->student_phone.'" >
				</td>
			</tr>
			<tr>
				<td>
					<label for="student_address">Address</label>
					<input type="text" id="student_address" name="student_address"  class="regular-text" value="'.$student->student_address.'" >
				</td>
				<td>
					<label for="student_address2">Address, Second Line</label>
					<input type="text" id="student_address2" name="student_address2"  class="regular-text" value="'.$student->student_address2.'" >
				</td>
				<td>
					<label for="student_city">City</label>
					<input type="text" id="student_city" name="student_city"  value="'.$student->student_city.'" >
				</td>
			</tr>
			<tr>
				<td>
					<label for="student_state">State</label>
					<input type="text" id="student_state" name="student_state"  value="'.$student->student_state.'" >
				</td>
				<td>
					<label for="student_postalcode">Postal Code</label>
					<input type="text" id="student_postalcode" name="student_postalcode"  value="'.$student->student_postalcode.'" >
				</td>
				<td>
					<label for="student_country">Country</label>
					<input type="text" id="student_country" name="student_country"  class="regular-text" value="'.$student->student_country.'" >
				</td>
				
			</tr>
		</table>
		
		
		<h3>Payment Information</h3>
		<table class="form-table">
			<tr>
				<td colspan="2" >
					<label for="student_paypal">Paypal Email</label>
					<input type="email" id="student_paypal" name="student_paypal"  class="regular-text" value="'.$student->student_paypal.'" >
				</td>
				<td>
					<label for="user_registered">Registration Date</label>
					<input type="datetime" id="user_registered" name="user_registered"  class="regular-text" value="'.$student->data->user_registered.'" >
				</td>
				
			</tr>
			<tr>
				<td>
					<label for="student_status">Status</label>';
		
			$student_current = (isset($student->allcaps['student_current']))? $student->allcaps['student_current']: null;
			
			$studCurArr = array(
				0 => 'Inactive',
				1 => 'Current'
			);	
			
			nb_select_forms( $studCurArr, 'student_status', $student_current);
					
					
				echo '</td>
				<td>
					<label for="payments_received">Payments Received</label>';
		
			$pymt_rcvd = isset($student->payments_received)? $student->payments_received: null;

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
			
			nb_select_forms( $pymt_rcvdArr, 'payments_received', $pymt_rcvd);
					
					
				echo '</td>
			
				
				<td>
					<label for="last_payment_received">Last Payment Received</label>
					<input type="datetime" id="last_payment_received" name="last_payment_received"  class="regular-text" value="'.$student->last_payment_received.'" >
				</td>
			</tr>
			<tr>
				<td>
					<label for="billing_type">Billing Type</label>';
		
			$billing_type = isset($student->billing_type)? $student->billing_type: null;

			$billTypeArr = array(
				'' => '---',
				'paypal_recurring' => 'Paypal (recurring)',
				'paypal_manual' => 'Paypal (manual)',
				'paypal_onetime' => 'Paypal (one-time)',
				'check' => 'Check',
				'other' => 'Other'
			);	
			
			nb_select_forms( $billTypeArr, 'billing_type', $billing_type);
					
					
				echo '</td>
							<td>
								<label for="program_rate">Program Rate</label>';
		
			$program_rate = isset($student->program_rate)? $student->program_rate: null;

			$prgrmRtArr = array(
				''	 	=> '---',
				'9p' 	=> '$9/partial',
				'15p'	=> '$15/partial',
				'18p' 	=> '$18/partial',
				'20p' 	=> '$20/partial',
				'100f' 	=> '$100/full',
				'150f' 	=> '$150/full',
				'180f' 	=> '$180/full',
				'200f' 	=> '$200/full'
			);	
			
			nb_select_forms( $prgrmRtArr, 'program_rate', $program_rate);
					
					
				echo '</td>
						
				<td> 
					<!-- empty -->
				</td>
			</tr>
		</table>
		<h3>Course Information</h3>
		
		<table class="form-table">
			<tr>
				<td>
				<label for="course_access">Course Access</label>';
				
			$course_access = isset($student->course_access)? $student->course_access: null;

			$courseAccArr = array(
				0	=> '(not set)',
				1 	=> 'main course only',
				2	=> 'main & childbirth',
				3 	=> 'all course materials'
			);	
			
			nb_select_forms( $courseAccArr, 'course_access', $course_access);
			
			echo '</td>
			</tr>
		</table>
		
		<h3>Administrator Notes</h3>
		<table class="form-table">
			<tr>
				<td>
					<textarea name="admin_notes" id="admin_notes" rows="5" cols="100">'.$student->admin_notes.'</textarea>
				</td>
			</tr>
		</table>
		
		
		
		';
		
		submit_button('Update Student');
	echo '</form>
	</div>'; 

}




function load_transactions_overview(){
	nb_admin_header('Transactions Overview', 'add_transaction'); 

	//Use the WP_List_Table Class? Maybe. 
	
	
	nb_admin_footer();
}
function load_new_transaction(){

	load_transaction_form('New Transaction');

}


function load_transaction_editor(){
	global $wpdb;
	
	$transaction_id = null;
	$message = null;
	$transArr = array();

	
	if( isset($_GET['trans_id']) ){
		//load the transaction details from the database
		$transaction_id = $_GET['trans_id'];
		if ( !empty($_POST) && check_admin_referer('edit_transaction','transaction-check') ){
			//This is an update to an existing transaction. Proceed accordingly. 
			
			$transTime = ( isset($_POST['trans_time']) )? $_POST['trans_time']: date("Y-m-d H:i:s");
			
			$transData = array(
				'student_id'=>$_POST['student_id'],
				'trans_amount'=>$_POST['trans_amount'],
				'trans_time' => $transTime,
				'trans_label'=>$_POST['trans_label'],
				'trans_detail'=>$_POST['trans_detail'],
				'trans_method'=>$_POST['trans_method'],
				'trans_type'=>$_POST['trans_type']
			);
			
			$transFormat = array( '%f', '%d', '%s', '%s', '%s', '%s', '%s' );	
			
/* 			if( isset($_POST['trans_time'])){
				$transData['trans_time'] = $_POST['trans_time'];
				$transFormat[] = '$s';
					print('<pre>');
					print_r($_POST);
					print_r($transData);
					print_r($transFormat);
					print('</pre>');
			} */
			
			$transWhere = array( 'transaction_id'=>$transaction_id );
			
			$db_updated = $wpdb->update( 'nb_transactions', $transData, $transWhere, $transFormat );
				
			
			if($db_updated != false) $message = 'This transaction has been successfully updated.';
		}
		
	} elseif ( !empty($_POST) && check_admin_referer('edit_transaction','transaction-check') ){ //Not quite the right check???
		//
		$timestamp =  ( isset($_POST['trans_time']) )? $_POST['trans_time']: date("Y-m-d H:i:s");
		
		$transData = array(
			'student_id'=>$_POST['student_id'],
			'trans_amount'=>$_POST['trans_amount'],
			'trans_time'=> $timestamp,
			'trans_label'=>$_POST['trans_label'],
			'trans_detail'=>$_POST['trans_detail'],
			'trans_method'=>$_POST['trans_method'],
			'trans_type'=>$_POST['trans_type']
		);
		
		$transFormat = array( '%f', '%d', '%s', '%s', '%s', '%s', '%s' );
		
		$wpdb->insert( 'nb_transactions', $transData, $transFormat );
		
		$transaction_id = $wpdb->insert_id;
		
		if($transaction_id != null) $message = 'This transaction has been added.';
		
	}
			
	if( $transaction_id != null){
			$transArr = $wpdb->get_results( 'SELECT * FROM nb_transactions WHERE transaction_id='.intval($transaction_id).' LIMIT 1', ARRAY_A );
			$transArr = $transArr[0];
			foreach($transArr as $tKey => $tVal){
				$transArr[$tKey] = stripslashes($tVal);
			}
	}

			
			
	load_transaction_form('Edit Transaction', 'add_transaction', $transArr, $transaction_id, $message); 
	
}

function load_transaction_form( $transTitle, $newAction = null, $transArr = array(), $trans_id = null, $message = null ){
	
	$sid = (isset($_REQUEST['student_id']))?$_REQUEST['student_id']:null;
	if($sid != null)
		$transArr['student_id'] = $sid;
	
	nb_admin_header($transTitle, $newAction); 
	
	if($message != null)
		echo '<div class="updated" id="message"><p>'.$message.'</p></div>';
	
	echo'<form method="post" action="admin.php?page=edit_transaction';
	if($trans_id != null)
		echo '&trans_id='.intval($trans_id);
	echo'">';
	wp_nonce_field('edit_transaction','transaction-check', true);

	echo'	
		<h3>Transaction Details</h3>
		<table class="form-table">
			<tr>
				<td>
					<label for="student_id">Student ID</label>
					<input type="text" id="student_id" name="student_id"  class="regular-text" value="'. $transArr['student_id'] .'" >
				
				</td>
				<td>
					<table class="form-table">
						<tr>
							<td>
								<label for="trans_amount">Amount(0.00)</label>
								<input type="text" id="trans_amount" name="trans_amount"  class="regular-text" value="'. $transArr['trans_amount'] .'" >
							</td>
							<td>
								<label for="trans_time">Date &amp; Time</label>
								<input type="datetime" id="trans_time" name="trans_time"  class="regular-text" value="'. $transArr['trans_time'] .'" >
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td>
					<label for="trans_label">Transaction Label</label>
					<input type="text" id="trans_label" name="trans_label"  class="regular-text" value="'. $transArr['trans_label'] .'" >
				
				</td>
				<td>
					<label for="trans_detail">Transaction Details</label>
					<textarea id="trans_detail" name="trans_detail" cols="80" rows="5" >'. $transArr['trans_detail'] .' </textarea>
				
				</td>
			</tr>
			<tr>
				<td>
					<label for="trans_method">Method of Payment</label>';
				
	
	$trans_method_post = isset($transArr['trans_method'])? $transArr['trans_method']: null;
	

	$methodsArr = array(
		'paypal_manual' => 'Paypal (manual)',
		'paypal_onetime' => 'Paypal (one-time)',
		'paypal_recurring' => 'Paypal (recurring)',
		'credit_card' => 'Credit Card',
		'check' => 'Check',
		'other' => 'Other'
	);	
	
	nb_select_forms( $methodsArr,'trans_method', $trans_method_post);
					
	echo '		</td>
				<td>
					<label for="trans_type">Type of Transaction</label>';
					
	$trans_type_post = isset($transArr['trans_type'])? $transArr['trans_type']: null;
	$typesArr = array(
		'payment' => 'Payment',
		'invoice' => 'Invoice',
		'purchase' => 'Purchase',
		'refund' => 'Refund',
		'credit' => 'Credit',
		'other' => 'Other'
	);
	nb_select_forms( $typesArr, 'trans_type', $trans_type_post);
					

	echo '		</td>
			</tr>
		</table>
		';
	if( $newAction != null ){
		submit_button('Update Transaction');	
	}else{
		submit_button('Add Transaction');	
	}
	
	if(isset($transArr['student_id']) && $transArr['student_id'] != 0){
		echo '<a class="secondary" href="admin.php?page=edit_student&student_id='.$transArr['student_id'].'"><- go back to Student</a>';
	}
	
	echo '</form>
	</div>'; 	
	
	
	
	nb_admin_footer();
}




/*
 *	Student Overview Variations
 */


function students_overview_current(){
	
	echo 'This is the current students overview filtered page.';

}


function students_overview_inactive(){}


function students_overview_alumni(){}



/*
 *	Student Editor Screens
 */


function students_overview_all(){

	 nb_student_overview_header();
	
	if(class_exists('NB_Students_Tables')){
	
		$nb_students_list = new NB_Students_Tables();
		
		$nb_students_list->prepare_items();
	
		$nb_students_list->display();
	}
	

	nb_admin_footer();
}





function load_new_student_editor(){
	nb_admin_header('Add New Student' );
	
	
	
	nb_admin_footer();
}




/*
 *	New Beginnings Admin Screen Wrappers and Menus
 */


function nb_admin_header( $pTitle , $addNewLink = NULL ){
	
	echo '<div class="wrap">';
	screen_icon(); 
	echo '<h2>'.$pTitle;
	
	if( $addNewLink != null )
			echo'<a class="add-new-h2" href="admin.php?page='. $addNewLink .'">Add New</a>';
	echo '</h2>';	
	
}


function nb_admin_footer(){

	echo '</div><!-- .wrap -->';

}


function nb_student_overview_header(){ //$user_query

	nb_admin_header('Students Overview', 'add_student'); 
	
	echo '
	<ul class="subsubsub">
		<li class="all"><a '.nb_cur_page('all').' href="admin.php?page=students">All<!-- <span class="count">(83)</span> --></a> |</li>
		<li class="current"><a '.nb_cur_page('current').'  href="admin.php?page=students&amp;student_type=current">Current<!-- <span class="count">(83)</span> --></a> |</li>
		<li class="inactive"><a '.nb_cur_page('inactive').'  href="admin.php?page=students&amp;student_type=inactive">Inactive<!-- <span class="count">(83)</span> --></a> |</li>
		<li class="alumni"><a '.nb_cur_page('alumni').'  href="admin.php?page=students&amp;student_type=alumni">Alumni<!-- <span class="count">(83)</span> --></a></li>
	</ul>';
	
}

function nb_cur_page($nb_cur_page){
	global $student_type;
	if($nb_cur_page == $student_type){
		return ' class="current" ';
	}
}


function nb_select_forms( array $selectArr, $selectID, $postValue = null){

	echo '<select  id="'.$selectID.'" name="'.$selectID.'" >';
	
	foreach($selectArr as $selKey => $selVal){
		echo '<option value="'.$selKey.'" ';
		
		if( ($postValue != null) && ($postValue == $selKey) ) echo 'selected ';
		
		echo '>'.$selVal.'</option>';
	}	

	echo'</select>';
}


/*
 *	Misc Admin Scripts
 */

function add_admin_menu_separator($position) {

	global $menu;
	$index = 0;

	foreach($menu as $offset => $section) {
		if (substr($section[2],0,9)=='separator')
		    $index++;
		if ($offset>=$position) {
			$menu[$position] = array('','read',"separator{$index}",'','wp-menu-separator');
			break;
	    }
	}

	ksort( $menu );
}


?>