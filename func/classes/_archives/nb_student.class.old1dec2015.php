<?php 

/*
 *  New Beginnings Student PHP Class
 *	Created on 18 July 2013
 *  Updated on 24 June 2014
 *
 *	The purpose of this class is to handle recurring processes related to 
 *	student records for the New Beginnings Doula Training website. 
 *
 */

//check if WP_user is set.  
 
 
class NB_Student { //Not sure we need the WP_User class, but it could be helpful.

	
	public $sid = 0;
	
	//The array of student
	public $student_post = array(
		'ID'=>0,							//123,
		'user_pass'=>null,					//'1234abcd',
		'user_email'=>null,					//'janeflores@example.com',
		'user_url'=>null,					//'http://www.example.com',
		'user_login'=>null,					//'JaneFlores',
		'user_nicename'=>null,				//'jane-flores',
		'display_name'=>null,				//'Jane Flores',
		'nickname'=>null,					//'Jane Flores',
		'first_name'=>null,					//'Jane',
		'last_name'=>null,					//'Flores',
		'user_registered'=>null,			//'2013-06-26 00:00:00',
		'description'=>null,				//'Tell us a little bit about yourself',
		'role'=>null, 						//'student_partial_active',
		'student_address'=>null,			//'123 W 456 N',
		'student_address2'=>null,			//'Apt. 4',
		'student_city'=>null,				//'Anytown',
		'student_state'=>null,				//'AZ',
		'student_country'=>null,			//'United States',
		'student_postalcode'=>null,			//'12345',
		'student_phone'=>null,				//'555-555-5555',
		'student_paypal'=>null,				//'janeflores@example.com',
		'admin_notes'=>null,				//'Administrative Notes',
		'program_rate'=>null,				//'100f',
		'billing_type'=>null,				//'one-time',
		'payments_received'=>null,			//'1/1',
		'last_payment_received'=>null,		//'2013-07-01 00:00:00',
		'course_access'=> 0
	);
	
	public $student_meta_keys = array( 
		'first_name', 
		'last_name', 
		'nickname', 
		'description', 
		'payments_received', 
		'student_address', 
		'student_address2', 
		'student_city', 
		'student_state', 
		'student_country', 
		'admin_notes', 
		'student_postalcode',  
		'student_phone',  
		'student_paypal', 
		'program_rate', 
		'billing_type', 
		'last_payment_received', 
		'course_access' 
		//there should be more here...
	);
	
	
	
	
	public function __construct(){
	
	
	
	
	}
	

	/**
	 * Initialization
	 *
	 * @since 0.1
	 **/
	public function init() {
		//add_action( 'admin_menu', array( __CLASS__, 'add_admin_pages' ) );
		//add_action( 'init', array( __CLASS__, 'process_student' ) );
	}



	/**
	 * Process student 
	 *
	 * @since 0.1
	 **/
	
	public function process_student(){
		
	
	}
	
	

	/**
	 * ADD STUDENT 
	 *
	 * @since 0.1
	 *
	 *	NOTES: This needs to be abstract enough to apply to all circumstances. 
	 *  IPN Auto Registrations, import new students, and manual form submissions. 
	 *
	 **/
	
	
	public function add_student( array $sPost ){
		// We need to prepare information from a post array to be inserted into the student object. 
		//Create a sample array of what the incoming new user post array  looks like:
		
		
		
		$sDataKeys = array( //These are the keys that will be accepted into the WP_INSERT_USER field. 
			'ID',
			'user_pass',
			'user_login',
			'user_nicename',
			'user_url', //I guess we could use this?
			'user_email',
			'user_registered',
			'display_name',
			'nickname',
			'first_name',
			'last_name',
			'description',
			'role'
		);
		
		$sPrepArr = array();
		
		//Seperate out only those post values that are not empty, so we can insert into the new user. 
		foreach($sDataKeys as $sdkey){
			if(	!empty( $sPost[$sdkey] ) )
				$sPrepArr[$sdkey] = $sPost[$sdkey];
		}
		
		$insertUserResponse = wp_insert_user($sPrepArr);
		
		
		//If a username or email address already exists, this will return an error object. 
		if( !is_int( $insertUserResponse ) && is_a( $insertUserResponse, 'WP_Error') ){
			return $insertUserResponse;		
		} else {
			$sPost['ID'] = $sid = $insertUserResponse;
		}

	 			
		if( is_int( $sid ) ){
			 foreach($this->student_meta_keys as $smkey){
				if( !in_array( $smkey, $sDataKeys )){ //If it was set in the sDataKeys, then we don't need it here. 
					//Check that the select metaKey has a value set on the incoming $sPost array. 
					$smval = $sPost[$smkey];
					if( !empty( $smval ) ){
						add_user_meta( $sid, $smkey, $smval, true );
					}
				} 	
			}
		}
		
		
		$student = new WP_User($sid);
		
		return $student; 
		
	}
		
	
	

	/**
	 * UPDATE STUDENT
	 *
	 * @since 0.1
	 * @updated 24 Jun 2014
	 **/
			

	public function update_student( array $sPost, $update_override = 0, $display = 1 ){
		//$student info is what is already in the database. 
		//$sPost info is what is being proposed to be changed in the databse. 
				
		$student = get_userdata( $sPost['ID'] );//We need the current student info before we can update requested student info. 
		$updated = false;
		//echo "This is the STUDENT var:";
		//var_dump($student);
		
		//echo "<br><br>This is the sPOST var:";
		//var_dump($sPost);
	
		$sMetaKeys = $this->student_meta_keys; 
		$sid = $student->ID;
		
		//An array to hold anything that needs to be update from the main user object.
		$update_student = array( 'ID' => $sid );
				
		foreach($sPost as $sPost_key => $sPost_value){
		
			if( !empty($sPost_value) || ( $update_override == 1 ) ){
				
				// IF the existing $student info has the propoerty of the proposed update AND if the existing $student property is not equal to the update value AND if it's not the STUDENT_STATUS property. 
				if(  ( $student->has_prop($sPost_key) )  && ( strcmp( $student->$sPost_key, $sPost_value) != 0 ) && ( $sPost_key != 'student_status' ) ){
				//This metavalue has already been set. 
					$stud_post_key = $student->$sPost_key;
					if($display) echo "Student #$sid: updated the <em>$sPost_key</em> meta key which has a value of <em>$sPost_value</em>. The previous value was <em>$stud_post_key</em>. <br>";
					if( in_array( $sPost_key, $sMetaKeys ) ){ //checking to see if it is a meta value. 
						
						//Comparing to current value set in the database. 
						$updated = update_user_meta( $sid, $sPost_key, $sPost_value, $stud_post_key );
						
					} else {
						
						//It's part of the main user object (like username or primary email), add to the $UDPATE_STUDENT array. 
						if($display) echo "Student #$sid: updated the <em>$sPost_key</em> meta key which has a value of <em>$sPost_value</em>. The previous value was <em>$stud_post_key</em>. <br>";
						$update_student[$sPost_key] = $sPost_value;	
					}
					
				// IF the particular property that we want to update isn't already set, 
				//AND the particular property that we are updating is a User Meta value
				//AND it's not the STUDENT STATUS meta value. 	
				} elseif( !isset( $student->$sPost_key ) && in_array( $sPost_key, $sMetaKeys ) && ( $sPost_key != 'student_status' )  ){
					//This metavalue has not been set before. 	
					if($display) echo "Student #$sid: updated the $sPost_key meta_key which has a value of $sPost_value <br>";
					$updated = update_user_meta( $sid, $sPost_key, $sPost_value ); //Nothing to compare it shouldn't be set, just add it. 
				
				// FINALLY IF the particular property is STUDENT STATUS meta value. 	
				} elseif( $sPost_key == 'student_status' ) {
					if($display) echo "Student #$sid: updated the $sPost_key meta_key which has a value of $sPost_value <br>";
										
					if( isset($student->allcaps['student_current'])  &&  ($sPost_value == 0)){//switch to inactive student
						if($student->roles[0] == 'student_full_active'){	
						
							 $student->remove_role('student_full_active');
							 $student->add_role('student_full_inactive');
							 
						}elseif($student->roles[0]  == 'student_partial_active'){
						
							 $student->remove_role('student_partial_active');
							 $student->add_role('student_partial_inactive');
						}	
						
						$updated = $sid; //Triggers the get_userdata function. 
						
					}elseif( !isset($student->allcaps['student_current'])  && ($sPost_value == 1) ){//switch to current student
						if($student->roles[0]  == 'student_full_inactive'){		
						
							 $student->remove_role('student_full_inactive');
							 $student->add_role('student_full_active');
							 
						}elseif($student->roles[0]  == 'student_partial_inactive'){
						
							 $student->remove_role('student_partial_inactive');
							 $student->add_role('student_partial_active');
							 
						}
						
						$updated = $sid; //Triggers the get_userdata function. 
					}
				}
			}
		}
				
		//Time to update the MAIN USER info (wp_users table). 
		if( count( $update_student ) > 1 ){
		
			$updated = wp_update_user( $update_student );
			//update_id could return as an error object... then what?
		
		} 			

		//Resets the Student object after the database has been updated. 
		if( !is_int( $updated ) && is_a( $updated, 'WP_Error') ){
			//echo "Case 1 triggered. There's an error. This almost never gets triggered.";
			return $updated;		
		} elseif( $updated === FALSE ){
			//echo "Case two triggered";
			return $updated;
		} else {
			//echo "Case three triggered. the value of Updated is: $updated <br><br>";
			$student = get_userdata( $sid );
			//var_dump( $student );
			return $student; //This is a WP_USER object. It's needed to propagate the form. 
		}
		
		
	}// end UPDATE STUDENT method
	
	

	/**
	 * GET STUDENT FROM PAYPAL
	 *
	 * @since 2.0
	 *
	 * Revised and simplified version of paypal_student_import( array )
	 * 
	 * 
	 * called: 
	 *  - nb_editor.class.php, NB_Editor::prepare_import_csv, approx line 1204.
	 * 
	 **/
				
	public function get_student_from_paypal( array $ppArr ){
	
		global $wpdb; 
	 	$sid = 0;
		$sPost = $this->student_post; 
		
		$sName = $ppArr['Name'] = $this->clean_paypal_names( $ppArr['Name'] );
		$sEmail = strtolower( $ppArr['From_Email_Address'] );
	
		//We can't do anything without an email address:
		if( !empty( $sEmail ) ){
			//Check to see if student exists based on paypal email address. 
			$student = get_user_by('email', $sEmail);
			//print_pre( $student );
			 
			if( is_a($student, 'WP_User') ){
				$sPost['ID'] = $sid = $student->ID;
				$sPost['user_email'] = $sEmail; 
				$sPost['user_login'] = $student->user_login;
			} else {//Keep trying, check student paypal. 
				$result1 = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM wp_usermeta WHERE meta_key = 'student_paypal' AND meta_value = %s ", $sEmail));
				if( !empty($result1) ){
					$sPost['ID'] = $sid = intval( $result1 );
					$sPost['student_paypal'] = $sEmail; 
					$student = get_userdata($sid);
					$sPost['user_login'] = $student->user_login;
				} 
			}	
		}
		
		//We should have a user id by this point. If not, we'll assume the user doesn't exist. 
		if( $sPost['ID'] === 0 ){
			$student = $sPost = NULL;
			return $sPost; 
		}else{
			return $student;
		}
		
	}
				
				

	/**
	 * PAYPAL STUDENT IMPORT
	 *
	 * @since 0.1
	 **/
				
	public function paypal_student_import( array $ppArr ){ 
	//This processes the data imported from Paypal transactions on the Students Import screen. 
		global $wpdb;
	 	$sid = 0;
		
		$sPost = $this->student_post;
	
		$sName = $ppArr['Name'] = $this->clean_paypal_names( $ppArr['Name'] );
	
		$sEmail = strtolower( $ppArr['From_Email_Address'] );
			
		//Check to see if student exists based on paypal email address. 
		$student = get_user_by('email', $sEmail);
		
		 
		if( is_a($student, 'WP_User') ){
			$sPost['ID'] = $sid = $student->ID;
			$sPost['user_email'] = $sEmail; 
			$sPost['user_login'] = $student->user_login;
		} else {//Keep trying, check student paypal. 
			$result1 = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM wp_usermeta WHERE meta_key = 'student_paypal' AND meta_value = %s ", $sEmail));
			if( !empty($result1) ){
				$sPost['ID'] = $sid = $result1;
				$sPost['student_paypal'] = $sEmail; 
				$student = get_userdata($sid);
				$sPost['user_login'] = $student->user_login;
			} else {
				//we could keep diggin' but I don't think we'll find what we're looking for beyond this.
			}
		}
		
		/* 
		if( empty( $sPost['student_address'] ) && !empty( $ppArr['Address_Line_1'] ) ){
			$sPost['student_address'] = $ppArr['Address_Line_1'];
		}

		
		if( empty( $sPost['student_address2'] ) && !empty( $ppArr['Address_Line_2/District/Neighborhood'] ) ){
			$sPost['student_address2'] = $ppArr['Address_Line_2/District/Neighborhood'];
		}

		
		if( empty( $sPost['student_city'] ) && !empty( $ppArr['Town/City'] ) ){
			$sPost['student_city'] = $ppArr['Town/City'];
		}

		
		if( empty( $sPost['student_state'] ) && !empty( $ppArr['State/Province/Region/County/Territory/Prefecture/Republic'] ) ){
			$sPost['student_state'] = $ppArr['State/Province/Region/County/Territory/Prefecture/Republic'];
		}

		
		if( empty( $sPost['student_country'] ) && !empty( $ppArr['Country'] ) ){
			$sPost['student_country'] = $ppArr['Country'];
		}

		
		if( empty( $sPost['student_postalcode'] ) && !empty( $ppArr['Zip/Postal_Code'] ) ){
			$sPost['student_postalcode'] = $ppArr['Zip/Postal_Code'];
		}

		
		if( empty( $sPost['student_phone'] ) && !empty( $ppArr['Contact_Phone_Number'] ) ){
			$sPost['student_phone'] = $ppArr['Contact_Phone_Number'];
		}
		 */
		
		
		if( empty( $sid ) ){//if Student ID is empty, this is a new student. we don't need to define the student posts if it's an update. 
		
			//We'll assign email to main email address, but there is no USER ID associated with the account.
			if( empty( $sPost['user_email'] ) ){
				$sPost['user_email'] = $sEmail;
			}

			//The main reason we are going to update all these fields even if the user exists, is to keep our records in sync with Paypal. Assuming that paypal's records are the most up-to-date that a student would provide us with... 
			
			if( empty( $sPost['user_nicename'] ) ){
				$sPost['user_nicename'] = strtolower( str_replace(' ', '-', $sName ) );
			}
			
			if( empty( $sPost['display_name'] ) ){
				$sPost['display_name'] = $sName;
			}
			
			if( empty( $sPost['nickname'] ) ){
				$sPost['nickname'] = $sName;
			}

			if( empty( $sPost['first_name'] ) ){
				$sPost['first_name'] = substr( $sName, 0, strrpos( $sName, ' ' ) ); //This should return everything except the last name. 
			}
			
			if( empty( $sPost['last_name'] ) ){
				$sPost['last_name'] = substr( strrchr( $sName, ' ' ), 1 );
			}
			
			if( empty( $sPost['user_registered'] ) ){//New students shouldn't have an ID set. 
				$sPost['user_registered'] = date('Y-m-d', strtotime($ppArr['Date'])).' '.$ppArr['Time'];
			}
			
			if( empty( $sPost['student_paypal'] ) ){
				$sPost['student_paypal'] = $sEmail;
			}

			$gross = intval($ppArr['Gross']); //Based on the amount they have paid. 
			
			$pArr = array(9,15,18,20);
			$fArr = array(100,180,200);
			
			if( in_array( $gross, $pArr ) ){
				$sRole = "student_partial_active";
				$pRate = $gross."p";
			} elseif( in_array( $gross, $fArr ) ) {
				$sRole = "student_full_active";		
				$pRate = $gross."f";
			}
			
			
			//depending on gross payment amount. 
			switch($gross){
				case 9:
				case 15:
				case 18:
				case 20:
					$bType = ( $ppArr['Type'] == 'Recurring Payment Received' )? 'paypal_recurring':'paypal_manual';
					break;

				case 100:
				case 180:
				case 200:
					$bType = 'paypal_onetime';
					break;
			}
			
			
			if( empty( $sPost['role'] ) && !empty( $sRole ) ) $sPost['role'] = $sRole;
		
			if( empty( $sPost['program_rate'] ) && !empty( $pRate ) ) $sPost['program_rate'] = $pRate; 
			
			if( empty( $sPost['billing_type'] ) && !empty( $bType ) ) $sPost['billing_type'] = $bType;
			
			//Two more values that should only be set if its a new user. 
			if( empty( $sPost['user_pass'] ) )	$sPost['user_pass'] = '1234abcd'; //Default Password

			if( empty( $sPost['user_login'] ) )	$sPost['user_login'] = str_replace(' ', '', $sName);
			//NOTE: User login may have already been set above, if the user ID was set. 
			
		}		
		
		return $sPost; 
	}

	/**
	 * PREPARE IPN RECORD
	 *
	 * @since 0.1
	 **/
				
	public function prepare_ipn_record( array $ppArr ){ 
	//This processes the data imported from Paypal transactions on the Students Import screen. 
		global $wpdb;
	 	$sid = 0;
		
		$sPost = $this->student_post;
	
		$sName = $ppArr['address_name'] = $this->clean_paypal_names( $ppArr['address_name'] );
	
		$sEmail = strtolower( $ppArr['payer_email'] );
			
		//Check to see if student exists based on paypal email address. 
		$student = get_user_by('email', $sEmail);
		
		 
		if( is_a($student, 'WP_User') ){
			$sPost['ID'] = $sid = $student->ID;
			$sPost['user_email'] = $sEmail; 
			$sPost['user_login'] = $student->user_login;
		} else {//Keep trying, check student paypal. 
			$result1 = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM wp_usermeta WHERE meta_key = 'student_paypal' AND meta_value = %s ", $sEmail));
			if( !empty($result1) ){
				$sPost['ID'] = $sid = $result1;
				$sPost['student_paypal'] = $sEmail; 
				$student = get_userdata($sid);
				$sPost['user_login'] = $student->user_login;
			} else {
				//we could keep diggin' but I don't think we'll find what we're looking for beyond this.
			}
		}
		
		if( empty( $sPost['student_address'] ) && !empty( $ppArr['address_street'] ) ){
			$sPost['student_address'] = $ppArr['address_street'];
		}

		
		/* if( empty( $sPost['student_address2'] ) && !empty( $ppArr['Address_Line_2/District/Neighborhood'] ) ){
			$sPost['student_address2'] = $ppArr['Address_Line_2/District/Neighborhood'];
		} //This value doesn't appear to be passed through IPN
 */
		
		if( empty( $sPost['student_city'] ) && !empty( $ppArr['address_city'] ) ){
			$sPost['student_city'] = $ppArr['address_city'];
		}

		
		if( empty( $sPost['student_state'] ) && !empty( $ppArr['address_state'] ) ){
			$sPost['student_state'] = $ppArr['address_state'];
		}

		
		if( empty( $sPost['student_country'] ) && !empty( $ppArr['address_country_code'] ) ){
			$sPost['student_country'] = $ppArr['address_country_code'];
		}

		
		if( empty( $sPost['student_postalcode'] ) && !empty( $ppArr['address_zip'] ) ){
			$sPost['student_postalcode'] = $ppArr['address_zip'];
		}

		
/* 		if( empty( $sPost['student_phone'] ) && !empty( $ppArr['Contact_Phone_Number'] ) ){
			$sPost['student_phone'] = $ppArr['Contact_Phone_Number'];
		} This value is not passed via IPN
		 */
		
		
	 	if( empty( $sid ) ){//if Student ID is empty, this is a new student. we don't need to define the student posts if it's an update. 
		
			//We'll assign email to main email address, but there is no USER ID associated with the account.
			if( empty( $sPost['user_email'] ) ){
				$sPost['user_email'] = $sEmail;
			}

			//The main reason we are going to update all these fields even if the user exists, is to keep our records in sync with Paypal. Assuming that paypal's records are the most up-to-date that a student would provide us with... 
			
			if( empty( $sPost['user_nicename'] ) ){
				$sPost['user_nicename'] = strtolower( str_replace(' ', '-', $sName ) );
			}
			
			if( empty( $sPost['display_name'] ) ){
				$sPost['display_name'] = $sName;
			}
			
			if( empty( $sPost['nickname'] ) ){
				$sPost['nickname'] = $sName;
			}

			if( empty( $sPost['first_name'] ) ){
				$sPost['first_name'] = substr( $sName, 0, strrpos( $sName, ' ' ) ); //This should return everything except the last name. 
			}
			
			if( empty( $sPost['last_name'] ) ){
				$sPost['last_name'] = substr( strrchr( $sName, ' ' ), 1 );
			}
			
			if( empty( $sPost['user_registered'] ) ){//New students shouldn't have an ID set. 
				$sPost['user_registered'] = date('Y-m-d H:i:s', strtotime($ppArr['payment_date'])); // 15:25:02 Aug 07, 2013 PDT  
			}
			
			if( empty( $sPost['student_paypal'] ) ){
				$sPost['student_paypal'] = $sEmail;
			}

			$gross = intval($ppArr['mc_gross']); //Based on the amount they have paid. 
			

			$pArr = array(9,15,18,20);
			$fArr = array(100,180,200);
			
			if( in_array( $gross, $pArr ) ){
				$sRole = "student_partial_active";
				$pRate = $gross."p";
			} elseif( in_array( $gross, $fArr ) ) {
				$sRole = "student_full_active";		
				$pRate = $gross."f";
			}

			

			
			
			//depending on gross payment amount. 
			switch($gross){
				case 9:
				case 15:
				case 18:
				case 20:
					$bType = ( isset( $ppArr['recurring_payment_id'] ) )? 'paypal_recurring':'paypal_manual';
					break;

				case 100:
				case 180:
				case 200:
					$bType = 'paypal_onetime';
					break;
			}
			
			
			if( empty( $sPost['role'] ) && !empty( $sRole ) ) $sPost['role'] = $sRole;
		
			if( empty( $sPost['program_rate'] ) && !empty( $pRate ) ) $sPost['program_rate'] = $pRate; 
			
			if( empty( $sPost['billing_type'] ) && !empty( $bType ) ) $sPost['billing_type'] = $bType;
			
			//Two more values that should only be set if its a new user. 
			if( empty( $sPost['user_pass'] ) )	$sPost['user_pass'] = '1234abcd'; //Default Password

			if( empty( $sPost['user_login'] ) ){
				$s_user_login =  str_replace(' ', '', $sName);
				//check if username exists in the system already. 
				$r = 1;
				while(username_exists( $s_user_login )){
					$s_user_login = $s_user_login.$r;
					$r++;
				}
				
				$sPost['user_login'] = $s_user_login;
				
			}	
			//NOTE: User login may have already been set above, if the user ID was set. 
			
		}		
		
		return $sPost; 
	}

	/**
	 * CLEAN PAYPAL NAMES
	 *
	 * @since 0.1
	 *
	 * Because paypal doesn't always send the names over clean. 
	 **/
	
	public function clean_paypal_names($name){
		if( !ctype_lower($name) && !ctype_upper($name) ){
			//Let's assume that it's formatted correctly
			return $name;
		} else {
			//not correctly formatted, so let's clean just a little. 
			
			//If there
			if( ( strpos( $name, ' ' ) !== FALSE ) ){
				$nameArr = explode($name, ' ');//Break full name into individual names.
				
				for($i=0; $i< sizeof($nameArr); $i++){
					$nameArr[$i] = ucwords(strtolower($nameArr[$i]));
				}
				$name = implode(' ', $nameArr);
				
			} else {
				//in the rare case that it's solid string... All we can do programmatically is uppercase the first letter. 
				$name = ucwords( $name );
			}
			return $name;
		}		
	}
	
}
?>