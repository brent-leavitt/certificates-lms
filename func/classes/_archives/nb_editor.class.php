<?php 

/*
 *  New Beginnings Editor PHP Class
 *	Created on 18 July 2013
 *  Updated on 18 July 2013
 *
 *	The purpose of this class is to handle recurring processes related to 
 *	editor pages for the New Beginnings Doula Training website. 
 *
 */

 
 
class NB_Editor{ 

	private static $log_dir_path = '';
	private static $log_dir_url  = '';
	
	private function __construct(){
		
		//Let's define our private static variables...
		$upload_dir = wp_upload_dir();
		self::$log_dir_path = trailingslashit( $upload_dir['basedir'] );
		self::$log_dir_url  = trailingslashit( $upload_dir['baseurl'] );
		
		print_pre($upload_dir);
		
		self::init();
	
	}
	

	/**
	 * Initialization
	 *
	 * @since 1.0
	 **/
	public function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_admin_pages' ) );
		//add_action( 'init', array( __CLASS__, 'process_student' ) );
	}

	/**
	 * Add administration menus
	 *
	 * @since 1.0
	 **/
	public function add_admin_pages() {
	
		//STUDENT Editor Pages
		add_menu_page('Students Overview', 'Students', 'edit_users', 'students',  array( __CLASS__, 'load_students_overview' ) , null/*icon_url*/, 50 );
		add_submenu_page( 'students', 'Add New Student', 'Add New', 'edit_users', 'add_student', array( __CLASS__, 'load_new_student_editor' ) );
		add_submenu_page( 'students', 'Import Students', 'Import', 'edit_users', 'import_student', array( __CLASS__, 'load_import_student_editor' ) );
		add_submenu_page( NULL, 'Edit Student', '', 'edit_users', 'edit_student', array( __CLASS__, 'load_student_editor' ) );
		
		//TRANSACTION Editor Pages
		add_submenu_page( 'students', 'Transactions Overview', 'Transactions', 'edit_users', 'nb_transactions', array( __CLASS__,'load_transactions_overview' ) );
		add_submenu_page( NULL, 'Add New Transaction', '', 'edit_users', 'add_transaction',  array( __CLASS__, 'load_new_transaction' ) );
		add_submenu_page( NULL, 'Edit Transaction', '', 'edit_users', 'edit_transaction',  array( __CLASS__, 'load_transaction_editor' ) );
		
		//MISC
		self::add_admin_menu_separator(30);
	}
	
	/*
	 * LOAD STUDENTS OVERVIEW
	 *
	 * @since 1.0
	 **/		

	public function load_students_overview(){
		
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
				self::students_overview_current();
				break;
				
			case 'inactive':
				self::students_overview_inactive();
				break;
				
			case 'alumni':
				self::students_overview_alumni();
				break;
				
			case 'all':
			default:
				self::students_overview_all();
				break;
		}		
	}

				
		
	/*
	 * STUDENTS OVERVIEW ALL
	 *
	 * @since 1.0
	 **/

	public function students_overview_all(){
	 
		self::nb_student_overview_header();
		
	
		
		if( !class_exists('NB_Students_Tables')){ //This should be available because the Tables class is loaded before the editor class...
			echo "Problems, please fix them.";
		} else {
			$nb_students_list = new NB_Students_Tables();
			
			$nb_students_list->prepare_items();
		
			$nb_students_list->display();
		} 
		

		$self::nb_admin_footer(); 
	}
		
		
		
		
	/*
	 * STUDENTS OVERVIEW CURRENT
	 *
	 * @since 1.0
	 **/

	public function students_overview_current(){
		
		echo 'This is the current students overview filtered page.';

	}

	
			
		
	/*
	 * STUDENTS OVERVIEW INACTIVE
	 *
	 * @since 1.0
	 **/

	public function students_overview_inactive(){}


	
			
		
	/*
	 * STUDENTS OVERVIEW ALUMNI
	 *
	 * @since 1.0
	 **/

	public function students_overview_alumni(){}


	
			
		
	/*
	 * LOAD NEW STUDENT EDITOR
	 *
	 * @since 1.0
	 **/

	public function load_new_student_editor(){

		self::load_student_form( 'Add New Student' ); //Title is the minimum variable we need to use this function. 
		
	}



	/*
	 * LOAD STUDENT EDITOR
	 *
	 * @since 1.0
	 **/

	public function load_student_editor(){
		
		//Current User has permission to Edit Students... 
		if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
		
		$errors = null;
		$message = null;
		$sid = $_REQUEST['student_id'];
		$student = get_userdata($sid); 
		
		if( !empty($_POST) || wp_verify_nonce($_POST['trees_and_flowers'],'edit_student') ) {
			
			$nbStud = new NB_Student();

			//We need to run a check to see if new user data is being entered. Probably need to also prepare data to be processed via the NB_Student Class. 
			
			if( !isset( $_REQUEST['student_id'] ) ){//We are inserting a new student's information.
				if( isset($_POST['trees_and_flowers']) && ( $_POST['_wp_http_referer'] == '/wp-admin/admin.php?page=add_student' ) )
				//This is a new student being submitted via the add_student page. Most values will be prepared for submission on that page. 
				
				$sPost = $nbStud->student_post;//an array to prep values for add_student
				
				foreach($_POST as $sPostKey => $sPostVal){
					if( array_key_exists($sPostKey, $sPost) ){
						//echo "sPostKey is $sPostKey and sPostVal is $sPostVal. <br>";
						$sPost[$sPostKey] = $sPostVal;
					}
				}
				
				//Set user_login, user_nicename, nickname
				$sPost['user_login'] = $_POST['first_name'].$_POST['last_name'];
				$sPost['user_nicename'] = strtolower($_POST['first_name'].'-'.$_POST['last_name']);
				$sPost['nickname'] = $_POST['first_name'].$_POST['last_name'];
				$sPost['user_pass'] = wp_generate_password( 12, false );
				
				$student = $nbStud->add_student($sPost);//This is where all the processing happens. 
				
				if( is_a( $student, 'WP_User') ){
				
					$added = true;
				
				} elseif( is_wp_error( $student ) ) {
				
					$errors = $student;
					$student = null; //We need to empty this out, because the form will want to use it. 
					
				}
				
				$message = ( isset($added) && ($add == true) )? "We've added a new student account for $student->display_name." : null;
			
			} else {  //We are UPDATING student information that has been passed. 
				
				//We need the current student data to compare to updated data. 
				
				//Here's a couple of more security checks. 
				if( isset($_POST['trees_and_flowers']) && ( $_POST['_wp_http_referer'] == '/wp-admin/admin.php?page=edit_student&student_id='.$sid ) ){
					$_POST['ID'] = $_GET['student_id'];
					$student = $nbStud->update_student($_POST, 1, 0); //override set, don't display update detail messages. 
					
					if( is_a($student, 'WP_User') ) {// New Method from NB_Student class, needs to be created. 
						$updated = true;
						
					}
					//Do we want to send an update message? we could. 
					$message = ( isset($updated) && ($updated == true) )? "We've updated the student account for $student->display_name." : null;

				}				
			}// end nonce else. 
		}
		
		$studTitle = "Student Editor: <em>". $student->display_name ."</em>"; //Title for student editor.  
		self::load_student_form( $studTitle, 'add_student', $student, $message, $errors );//Load the Student Form. 
	}


	/*
	 * LOAD STUDENT FORM
	 *
	 * @since 1.0
	 **/	
	 
	
	public function load_student_form( $studTitle, $newAction = null, WP_User $student = null, $message = null, WP_Error $errors = null ){
		
 		if( isset($_REQUEST['student_id']) ){
			$sid = $_REQUEST['student_id'];
		} elseif( is_object($student) ) {
			$sid = $student->ID;
		} else {
			$sid = null;
		} 
		
		
		self::nb_admin_header($studTitle, $newAction); 
		
		if( !isset($errors) &&( is_a( $errors, 'WP_Error') ) ){
			echo '<div class="errors" id="erros"><p>There are errors. I still need to improve upon this.</p></div>';
		}
		
		if($message != null)
			echo '<div class="updated" id="message"><p>'.$message.'</p></div>';
		
		
		if( !empty( $sid ) ){
			echo '<h3>Student Transaction Records</h3>';
					
			if( class_exists('NB_Transaction_Tables') ){ //This should already be loaded at this point.
			
				$nb_transaction_list = new NB_Transaction_Tables();
				
				$nb_transaction_list->prepare_items();
			
				$nb_transaction_list->display();
			}		
		}
		
		echo'<form method="post" action="admin.php?page=edit_student';
		if($sid != null)
			echo '&student_id='.intval($sid);
		echo'">';	
		
		wp_nonce_field('edit_student','trees_and_flowers');
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
				
				self::nb_select_forms( $studCurArr, 'student_status', $student_current);
						
						
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
				
				self::nb_select_forms( $pymt_rcvdArr, 'payments_received', $pymt_rcvd);
						
						
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
				
				self::nb_select_forms( $billTypeArr, 'billing_type', $billing_type);
						
						
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
					'200f' 	=> '$200/full'
				);	
				
				self::nb_select_forms( $prgrmRtArr, 'program_rate', $program_rate);
						
						
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
				
				self::nb_select_forms( $courseAccArr, 'course_access', $course_access);
				
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
			</table>';
			
			submit_button('Update Student');
		echo '</form>'; 
		
		self::nb_admin_footer();
	}
	

	
	
	/*
	 * LOAD TRANSACTIONS OVERVIEW
	 *
	 * @since 1.0
	 **/		


	public function load_transactions_overview(){
		self::nb_admin_header('Transactions Overview', 'add_transaction'); 

		//Use the WP_List_Table Class? Maybe. 
		
		
		self::nb_admin_footer();
	}


	/*
	 * LOAD NEW TRANSACTION
	 *
	 * @since 1.0
	 **/


	
	public function load_new_transaction(){
		
		self::load_transaction_form('New Transaction');

	}


	/*
	 * LOAD TRANSACTION EDITOR
	 *
	 * @since 1.0
	 **/

	public function load_transaction_editor(){
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

				
				
		self::load_transaction_form('Edit Transaction', 'add_transaction', $transArr, $transaction_id, $message); 
		
	}



	/*
	 * LOAD TRANSACTION FORM
	 *
	 * @since 1.0
	 **/	
	
	
	
	public function load_transaction_form( $transTitle, $newAction = null, $transArr = array(), $trans_id = null, $message = null ){
		$sid = (isset($_REQUEST['student_id']))?$_REQUEST['student_id']:null;
		if($sid != null)
			$transArr['student_id'] = $sid;
		
		self::nb_admin_header($transTitle, $newAction); 
		
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
									<input type="text" id="trans_time" name="trans_time"  class="regular-text" value="'. $transArr['trans_time'] .'" >
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
		
		self::nb_select_forms( $methodsArr,'trans_method', $trans_method_post);
						
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
		self::nb_select_forms( $typesArr, 'trans_type', $trans_type_post);
						

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
		
		echo '</form>'; 	
		
		
		
		self::nb_admin_footer();
	}

	
		
	/*
	 * LOAD IMPORT STUDENT EDITOR
	 *
	 * @since 1.0
	 **/

	public function load_import_student_editor( ){
	//	echo "This is the student import editor!";
	
		if ( ! current_user_can( 'create_users' ) )
			wp_die('You do not have sufficient permissions to access this page.' );			
			
		//print_pre($_REQUEST);	
		
 		if ( isset( $_POST['humming-birds-and-bees'] ) ) {
			check_admin_referer( 'import_student', 'humming-birds-and-bees' );
			$results = self::process_csv();	
		}
		
			
		self::nb_admin_header( "Batch Import Student Transactions" );
	
		
	
		//If we're not printing to a log, we don't need these. 
		$error_log_file = self::$log_dir_path . 'doula_csv_import_errors.log';
		$error_log_url  = self::$log_dir_url . 'doula_csv_import_errors.log';

		if ( ! file_exists( $error_log_file ) ) {//I don't want to print to a log file. 
			/* if ( ! @fopen( $error_log_file, 'x' ) ){ //This has been finicky and may misbehave again. 
				echo '<div class="updated"><p><strong>' . sprintf('Notice: please make the directory %s writable so that you can see the error log.' ), self::$log_dir_path ) . '</strong></p></div>';
			} */
		} 

	 	if ( isset( $results) ) { //We can flesh this out in much more details later.... 
			if( !empty( $results['user_ids'] )){
				echo '<div class="updated"><p><strong>' .'Student transactions import was successful.' . '</strong></p></div>';
			} 
			
			if( !empty( $results['errors'] )){
				echo '<div class="error"><p><strong>' .  'We had some problems with the import. Please investigate.' . '</strong></p></div>';
			} 
		
/* 			$error_log_msg = '';
			if ( file_exists( $error_log_file ) )
				$error_log_msg = sprintf( ', please <a href="%s">check the error log</a>' , $error_log_url );

			switch ( $_GET['import'] ) {
				case 'file':
					echo '<div class="error"><p><strong>' .  'Error during file upload.' . '</strong></p></div>';
					break;
				case 'data':
					echo '<div class="error"><p><strong>' . 'Cannot extract data from uploaded file or no file was uploaded.' . '</strong></p></div>';
					break;
				case 'fail':
					echo '<div class="error"><p><strong>' . sprintf('No student transactions were successfully imported%s.' , $error_log_msg ) . '</strong></p></div>';
					break;
				case 'errors':
					echo '<div class="error"><p><strong>' . sprintf('Some student transactionss were successfully imported but some were not%s.', $error_log_msg ) . '</strong></p></div>';
					break;
				case 'success':
					
					break;
				default:
					break;
			} */
		}
		
		?>
		<form method="post" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'import_student', 'humming-birds-and-bees' ); ?>
			
			<p>This importer is designed to take a batch of transactions from Paypal and process them. New users will be generated and existing users will have their accounts updated with latest transaction information.</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="users_csv">CSV file</label></th>
					<td>
						<input type="file" id="users_csv" name="users_csv" value="" class="all-options" /><br />
						<span class="description"><?php echo sprintf( 'You may want to see <a href="%s" target="_blank">the example of the CSV file</a>.', '/migration/import-sample.csv'); ?></span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Notification</th>
					<td><fieldset>
						<legend class="screen-reader-text"><span>Notification</span></legend>
						<label for="new_user_notification">
							<input id="new_user_notification" name="new_user_notification" type="checkbox" value="1" />
							Send to new users
						</label>
					</fieldset></td>
				</tr>
				<tr valign="top">
					<th scope="row">Password nag</th>
					<td><fieldset>
						<legend class="screen-reader-text"><span>Password nag</span></legend>
						<label for="password_nag">
							<input id="password_nag" name="password_nag" type="checkbox" value="1" />
							Show password nag on new users signon
						</label>
					</fieldset></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="Import" />
			</p>
		</form>
	<?php
		self::nb_admin_footer();
	} 

	/**
	 * PROCESS CSV
	 *
	 * @since 1.0
	 **/ 
	public function process_csv() {
		
		if ( isset( $_FILES['users_csv']['tmp_name'] ) ) {
			// Setup settings variables
			$filename              = $_FILES['users_csv']['tmp_name'];
			$password_nag          = isset( $_POST['password_nag'] ) ? $_POST['password_nag'] : false;
			$new_user_notification = isset( $_POST['new_user_notification'] ) ? $_POST['new_user_notification'] : false;

			$results = self::import_csv( $filename, array(
				'password_nag' => $password_nag,
				'new_user_notification' => $new_user_notification
			) );
		}
		
		//We need to generate some Response to the import...		
		return $results;
	}



	/**
	 * IMPORT CSV
	 *
	 * @since 1.0
	 */
   	public static function import_csv( $filename, $args ) {
	//This function needs to be seriously modified to working Paypal import files. 
	
		$errors = $user_ids = array();

		$defaults = array(
			'password_nag' => false,
			'new_user_notification' => false,
		);
		extract( wp_parse_args( $args, $defaults ) );

		// User data fields list used to differentiate with user meta
		$userdata_fields       = array(
			'ID', 'user_login', 'user_pass',
			'user_email', 'user_url', 'user_nicename',
			'display_name', 'user_registered', 'first_name',
			'last_name', 'nickname', 'description',
			'rich_editing', 'comment_shortcuts', 'admin_color',
			'use_ssl', 'show_admin_bar_front', 'show_admin_bar_admin',
			'role'
		);

		include_once( 'readcsv.class.php' );
		
	
		// Loop through the file lines
		$file_handle = fopen( $filename, 'r' );	
		$csv_reader = new ReadCSV( $file_handle, ',' , "\xEF\xBB\xBF" ); // Skip any UTF-8 byte order mark.

		$first = true;
		$rkey = 0;
		while ( ( $line = $csv_reader->get_row() ) !== NULL ) {
			
			$trash = array_pop($line); //Drop the last line off the bottom of the array. This is because Paypal adds and extra column.
			
			// If the first line is empty, abort
			// If another line is empty, just skip it
			if ( empty( $line ) ) {
				if ( $first )
					break;
				else
					continue;
			}

			// If we are on the first line, the columns are the headers
			if ( $first ) {
				for($i = 0; $i < sizeof($line); $i++)
					$line[$i] = str_replace( ' ', '_', trim( $line[$i] ) );
				$headers = $line;
				$first = false;
				continue;
			}

			
			$line = array_combine($headers, $line);
			
			
			//POINT OF DEPARTURE: 
	
			$nb_stud = new NB_Student();
		
			$sPost = $nb_stud->paypal_student_import($line); //This will prepare the student info for insert or update. 
			
			//print_pre($sPost); 
			
			$update = false; 
			
			if( !empty( $sPost['ID'] ) ){
			
				$student = $nb_stud->update_student($sPost);
				$update = true;
				
			} else {
			
				$student = $nb_stud->add_student($sPost);
				
			} 
			//Either one of these could return a WP_USER object on success or a WP_ERROR objecdt on fail!
			
			
			if( is_a( $student, 'WP_User' ) ){
				
				$sid = $student->ID;
				
				if( !empty( $sid ) ){ //We are only going to insert transactions when student IDs are set. 
					
					$nu = ( $update )? 0 : 1; //Not update, therfore a new user. 
					
					$nb_trans = new NB_Transaction($sid);
					$nb_trans->paypal_import_prep($line);	
					$nb_trans->process_transaction($nu, 0, 1);
					//print_pre($nb_trans->tPost);
				}
				
				$user_ids[] = $sid;//Add this updated user to the users Array. 
				
				// If we created a new user, perform a few extra steps. 
				if ( ! $update ) {
					$nb_trans->set_billing_type(); //Returns nothing. Will only run on new users, I need the trans method info to successfully set up this.
				
					if ( $password_nag )
						update_user_option( $sid, 'default_password_nag', true, true );

					if ( $new_user_notification )
						wp_new_user_notification( $sid, $sPost['user_pass'] );
				}
			}elseif ( is_wp_error( $student ) ) {
			
				$errors[$rkey] = $student;
				
			}

			$rkey++;
		}
		fclose( $file_handle );

		// Let's log the errors
		self::log_errors( $errors );

		return array(
			'user_ids' => $user_ids,
			'errors'   => $errors
		);
	}

	/**
	 * Log errors to a file
	 *
	 * @since 0.2
	 *
	 * This function needs to be tested. 
	 **/
 	private static function log_errors( $errors ) {
		if ( empty( $errors ) )
			return;

		$log = @fopen( self::$log_dir_path . 'doula_csv_import_errors.log', 'a' );
		@fwrite( $log, sprintf(  'BEGIN %s', date( 'Y-m-d H:i:s', time() ) ) . "\n" );

		foreach ( $errors as $key => $error ) {
			$line = $key + 1;
			$message = $error->get_error_message();
			@fwrite( $log, sprintf( '[Line %1$s] %2$s', $line, $message ) . "\n" );
		}

		@fclose( $log );
	} 

	
	/*
	 * NB ADMIN HEADER
	 *
	 * @since 1.0
	 **/

	private function nb_admin_header( $pTitle , $addNewLink = NULL ){
		
		echo '<div class="wrap">';
		screen_icon(); 
		echo '<h2>'.$pTitle;
		
		if( $addNewLink != null )
				echo'<a class="add-new-h2" href="admin.php?page='. $addNewLink .'">Add New</a>';
		echo '</h2>';	
		
	}


	
			
		
	/*
	 * NB ADMIN FOOTER
	 *
	 * @since 1.0
	 **/

	private function nb_admin_footer(){

		echo '</div><!-- .wrap -->';

	}


	
			
		
	/*
	 * NB STUDENT OVERVIEW HEADER
	 *
	 * @since 1.0
	 **/

	private function nb_student_overview_header(){ //$user_query

		self::nb_admin_header('Students Overview', 'add_student'); 
		
		echo '
		<ul class="subsubsub">
			<li class="all"><a '.self::nb_cur_page('all').' href="admin.php?page=students">All<!-- <span class="count">(83)</span> --></a> |</li>
			<li class="current"><a '.self::nb_cur_page('current').'  href="admin.php?page=students&amp;student_type=current">Current<!-- <span class="count">(83)</span> --></a> |</li>
			<li class="inactive"><a '.self::nb_cur_page('inactive').'  href="admin.php?page=students&amp;student_type=inactive">Inactive<!-- <span class="count">(83)</span> --></a> |</li>
			<li class="alumni"><a '.self::nb_cur_page('alumni').'  href="admin.php?page=students&amp;student_type=alumni">Alumni<!-- <span class="count">(83)</span> --></a></li>
		</ul>';
		
	}

	
			
		
	/*
	 * NB CUR PAGE
	 *
	 * @since 1.0
	 **/

	private function nb_cur_page($nb_cur_page){
		global $student_type;
		if($nb_cur_page == $student_type){
			return ' class="current" ';
		}
	}


	
			
		
	/*
	 * NB SELECT FORMS
	 *
	 * @since 1.0
	 **/

	private function nb_select_forms( array $selectArr, $selectID, $sPostValue = null){

		echo '<select  id="'.$selectID.'" name="'.$selectID.'" >';
		
		foreach($selectArr as $selKey => $selVal){
			echo '<option value="'.$selKey.'" ';
			
			if( ($sPostValue != null) && ($sPostValue == $selKey) ) echo 'selected ';
			
			echo '>'.$selVal.'</option>';
		}	

		echo'</select>';
	}
	
			
		
	/*
	 * ADD ADMIN MENU SEPERATOR
	 *
	 * @since 1.0
	 **/

	private static function add_admin_menu_separator($position) {

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
	
	
}
?>