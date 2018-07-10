<?php// Exit if accessed directlyif ( !defined('ABSPATH')) exit;/** * 	Adapted from Full Content Template *	Template Name:  Complete Registration Page (no sidebars or widgets) * * @file           page-complete-registration.php */global $wpdb; $no_student = true; $review = false;$formArr = array(	'user_login'		=>'User Login <small><em>Subject to availablility, no spaces please. This cannot be changed after registration is completed. No one sees this except for you when you log in. </em></small>',	'display_name'		=>'Screen Name <small><em>Your preferred name for contact and for public and private communications; nicknames are ok here.</em></small>',	'user_email'		=>'Main Email <small><em>Valid email address for course correspondence; PLEASE double check your spelling!</em></small>',	'student_paypal'	=>'Paypal Email <small><em>The email address connected to the PayPal account you are using to pay for the course; this ensures that your account is properly credited.</em></small>',	'first_name'		=>'First Name <small><em>Your first given name that you are formally recognized as; this will be printed on your certificate.</em></small>',	'last_name'			=>'Last Name <small><em>Your current legal last name; this will also be printed on your certificate.</em></small>',	'student_address'	=>'Address <small><em>Current mailing address; used for mailing certificate on completion.</em></small>',	'student_address2'	=>'Address, 2nd Line',	'student_city'		=>'City',	'student_state'		=>'State/Province/Region',	'student_postalcode'=>'Postal Code/Zip Code',	'student_country'	=>'Country',	'student_phone'		=>'Phone Number <small><em>Should we need to contact you by phone, please provide a current, working number.</em></small>');$reqFieldsArr = array(	'user_login'=>'user login', 	'display_name'=>'screen name', 	'first_name'=>'first name', 	'last_name'=>'last name', 	'student_city'=>'city', 	'student_country'=>'country');if( isset( $_REQUEST['tx_id'] ) && !empty( $_REQUEST['tx_id'] ) ){	$tx_id = $_REQUEST['tx_id'];		$tq = $wpdb->prepare("SELECT student_id FROM nb_transactions WHERE pp_txn_id = %s LIMIT 1", $tx_id);	$sid = $wpdb->get_var($tq);	if( !empty( $sid ) ) $student = new WP_User($sid);	if( is_a( $student, 'WP_User' ) ) $no_student = false;		//We need to check to see if MetaKey hasn't already been set. 	if(!$no_student) $reg_meta = get_user_meta($sid, 'student_registered');		if( !empty( $reg_meta ) ) $student_registered = true;	//Set Registration MetaKey to know if a student has registered or not yet. }//Basic student information is now set. Let's process the form.if( isset( $_POST['fruits_and_flowers'] ) ) $troll = wp_verify_nonce( $_POST['fruits_and_flowers'], 'student_reg' );// --------------------- ////  VALIDATION SCRIPTS   //// --------------------- ////If the Troll doesn't come back as false, begin to process form.  Otherwise, load form info. if( ( $troll != 'false' ) && isset( $_POST['register'] ) ){	$formErrArr = array();	//Begin the validation process. 	//Checking UserName Availability	$u_login = $_POST['user_login'];	$s_login = $student->get('user_login');		//Check if this matches the current student user_login. 	if( strcmp($u_login, $s_login) != 0){ //if they are not equal, then let's process the change. 		//First check to see if formatted correctly. (no spaces)				$spc_pos = strpos(  $u_login, ' ' );				if( $spc_pos !== false){						$u_login = str_replace( ' ', '', $u_login);						$student->user_login = $_POST['user_login'] = $u_login;						$formErrArr['user_login'] = "The requested username contained spaces. We've gone ahead and removed these spaces, but you may also change it to something else if you prefer. But please remember, no spaces."; 		}else{					//Next check to see if the proposed username already exists. 						$u_login_exists =  username_exists( $u_login );			if( empty($u_login_exists) ){				//Update the username.				$dbResult = $wpdb->update(					'wp_users',					array('user_login' => $u_login),					array('ID'=> $sid),					'%s',					'%d'				);			} else { //It's not available, so send back an error message. 				//perhaps run a while loop								$r = 1;				while(username_exists( $u_login )){					$u_login = $u_login.$r;					$r++;				}				$student->user_login = $_POST['user_login'] = $u_login;//This should just be temporary for the sake of propagating to the form.								$formErrArr['user_login'] = "The requested username is not available. We've suggested an available alternative, but you are free to pick something else if you choose."; 			}		}							}	//Validate Email Addresses 	$u_email = $_POST['user_email'];	$s_email = $student->get('user_email');	$s_paypal = $_POST['student_paypal'];	if( !is_email( $u_email )){		$formErrArr['user_email'] = "The email address provided as your primary contact is not valid. Please choose another."; 	} elseif( strcmp($u_email, $s_email) != 0 ){		//we need to check that it isn't already registered. 		$u_email_exists =  email_exists( $u_email );				if( !empty($u_email_exists) ){						$formErrArr['user_email'] = "This email address is already connected to another account at New Beginnings Doula Training. Please check the email address and try again. If you have entered the correct email address but cannot complete the registration process, please contact us using <a href='/doula-training/contact-information/' target='_blank'>the contact form</a>. "; 		}	}		if( !is_email( $s_paypal )){		$formErrArr['student_paypal'] = "The email address provided for your paypal account is not valid. Please choose another."; 	}			//Required Fields: UserName, Screen Name, Main Email, First Name, Last Name, Student City, Student Country,  		foreach($reqFieldsArr as $req_key => $req_val){				if( empty( $_POST[$req_key] ) ){			$formErrArr[$req_key] = "The <em>$req_val</em> field cannot be left empty."; 					}	}		//If we didn't pass any errors, then let's review what was submitted. 	$review = ( empty( $formErrArr ) )? true : false ;	}		$metaArr = array( 'student_address', 'student_address2', 'student_city', 'student_state', 'student_postalcode', 'student_country', 'student_phone', 'student_paypal' );		//If there are no errors, then let us process the form. if( isset($_POST['confirm']) ){		foreach($formArr as $form_key => $form_val){		$f_val = $_POST[$form_key];		$d_val = $student->get($form_key);						if( strcmp( $f_val, $d_val ) != 0 ){			//Values are different, update the database. 			if( in_array( $form_key, $metaArr ) ){				//update unique meta data first.				update_user_meta( $sid, $form_key, $f_val, $d_val ); 			} else {				//then update the standard user info (the wp_update_user function doesn't handle custom metadata)				wp_update_user( array(					'ID' => $sid,					$form_key => $f_val 				) );			}		}	}		//update user-nicename?	$nicename = strtolower($_POST['first_name'].'-'.$_POST['last_name']);	$nickname = $_POST['first_name'].' '.$_POST['last_name'];	wp_update_user( array(					'ID' => $sid,					'user_nicename' => $nicename,					'nickname' => $nickname				) );					//Set the student_registered metadata. 		add_user_meta( $sid, 'student_registered', 1, true );	//Send Email Confirmations...		$emlWho = $_POST['user_email'];	$emlSubject = 'Welcome to New Beginnings Doula Training!';	$emlMsg = "Thank you for registering with New Beginnings.  To log-in to your account for the course, go to ".get_bloginfo('url')."Your username is: ".$_POST['user_login']." Your passphrase is: 1234abcd ***We strongly recommend that you change yourpassphrase. To do so, click on the \"Forgot Password\" link on the log-in screen and enter your email address in the available field, then follow the instructions to reset your password.***DON'T FORGET TO ORDER YOUR BOOKS! Please visit https://www.trainingdoulas.com/books/ for more information on ordering the three required books for the course. Alternatively, you can get a discount for \"The Nurturing Touch at Birth\" if you go to this web site: http://cuttingedgepress.net/cep/default.asp .  Call the number on the left and tell them you are with New Beginnings.  You should get a 20% discount.  You can get a 35% discount for \"Homebirth in the Hospital\" from this website http://www.sentientpublications.com/catalog/homebirth.php .  Enter the code \"DOULA\" in the SPECIAL OFFER code field or call 1-866-588-9846 and give them the code.  Let me know if you have any questions.Rachel";			$emlHdrs = array();	$emlHdrs[] = 'From: New Beginnings <office@trainingdoulas.com>' . "\r\n";	$emlHdrs[] = 'Reply-To: Rachel Leavitt <rachel@trainingdoulas.com>' . "\r\n";		if( ( !empty($emlSubject) ) && ( !empty ($emlMsg) ) ){			 $mailResults = wp_mail( $emlWho, $emlSubject, $emlMsg, $emlHdrs  );			 	}		$adminEmail = 'brent@trainingdoulas.com';		if( isset($mailResults) && ( $mailResults == true ) ){		//SUCCESS		//$emlHdrs[] = 'Cc: Rachel Leavitt <rachel@trainingdoulas.com>' . "\r\n";		wp_mail(			$adminEmail, //email			'New Student Registration Successfully Completed',// subject			'New student information was successfully setup, and a confirmation email was sent with course materials to the following student:'. $_POST['display_name'] .', ID:'. $sid .', '. $_POST['user_email'] .'',// message			$emlHdrs // headers		);			}else{		//FAILED		wp_mail(			$adminEmail, //email			'Incomplete New Student Registration',// subject			'There was an error in student information setup for the following student:'. $_POST['display_name'] .', ID:'. $sid .', '. $_POST['user_email'] .'',// message			$emlHdrs // headers		);		}	wp_redirect( home_url('/registration-completed/') );	exit;} $hrArr = array('display_name','student_paypal','student_phone');//Insert an <hr> tag after items in this array.   ?>					<div class="feedback">						<p>If you are having trouble with the registration process, please <a href="https://www.trainingdoulas.com/doula-training/contact-information/" target="_blank">give us your concerns</a>. </p>					</div><!-- End FEEDBACK -->					<?php //print_pre($_POST); ?>					<?php //print_pre($student); ?>																			<?php										//echo 'NO STUDENT VALUE: '.$no_student;										if( $no_student ){						echo "<h2>Hmmm...</h2>						<p>There is no student account associated with this transaction. If you were expecting to register, please <a href='https://www.trainingdoulas.com/doula-training/contact-information/'>contact New Beginnings</a> to report the issue.</p>";					}elseif( $student_registered ){						echo "<p>Thanks for visiting,<br> but registration for this student has already been completed.</p> <p>To log in, visit the <a href='".home_url()."'>student check-in</a> page.</p>";					}elseif( $review ){												echo "<h2>Almost There...</h2>							<p>Please take a moment to review your registration details before submitting them. Pay particularly close attention to your <em><a href='#primary_email'>main email address</a></em>.</p>														<p> If it all looks good, go ahead and confirm below, and we'll send you on your way!</p>							<hr>														<h3>User Names</h3>						 	<p>Login Name: <em>".$_POST['user_login']."</em> <small>(Used only for logging in, otherwise not visible)</small></p>							<p>Screen Name: <em>".$_POST['display_name']."</em> <small>(Preferred name, nicknames ok)</small></p>							<hr id='primary_email'>														<h3>Email Addresses</h3>							<p>Main Email: <em>".$_POST['user_email']."</em> <small>(For course correspondence)</small></p>							<p>Paypal Email: <em>".$_POST['student_paypal']."</em> <small>(For billing purposes)</small></p>							<hr>														<h3>Contact Information</h3>							<p><small>This should be your current legal name and current mailing address. Upon completion of your training, this information will be used to print and mail your certificate. You may update your contact information at any time on the account profile page. </small></p>							<p>Name:<br> <em>".$_POST['first_name']." ".$_POST['last_name']."</em></p>							<p>Address:<br>  <em>".$_POST['student_address']."<br> ".$_POST['student_address2']."</em></p>							<p>City:<br>  <em>".$_POST['student_city']."</em></p>							<p>State:<br>  <em>".$_POST['student_state']."</em></p>							<p>Country:<br>  <em>".$_POST['student_country']."</em></p>							<p>Postal Code:<br>  <em>".$_POST['student_postalcode']."</em></p>							<p>Phone Number:<br> <em>".$_POST['student_phone']."</em> <small>(For correspondence purposes only)</small></p>							<hr>							<p><small>In case you were wondering, we never have and never will resell or provide your personal information to third-party vendors. Thx!</small></p>						"; 											echo "<form action='' method='post' >";						/*Insert Nonce Check*/						wp_nonce_field( 'student_reg', 'fruits_and_flowers' );						foreach($formArr as $formK => $formV){							$inputVal = '';														if( isset( $_POST['register'] ) ){								$inputVal = $_POST[$formK];							}														echo "<input type='hidden' name='$formK' value='$inputVal' />";						}																		echo "<input class='button' type='submit' name='confirm' value='Confirm' /> <input class='button minor' type='submit' name='change' value='Change' /> ";						echo "</form>";					}else{						if( empty( $formErrArr ) ){													echo "<h2>Welcome to New Beginnings Doula Training!</h2>							<p>To complete the registration process and gain access to your course materials, please verify and correct (if needed) the contact information below.</p>";												} else {							echo "							<h2>Almost done, but not quite...</h2>							<p>Alright, we've got just a small problem or two that needs your attention before we can move on: </p>								<ul class='reg_error'>							";							foreach( $formErrArr as $err_k => $err_v ){								echo "<li>".$err_v."</li>";							}							echo "</ul>";						}												echo "<form action='' method='post' >";						/*Insert Nonce Check*/						wp_nonce_field( 'student_reg', 'fruits_and_flowers' );						foreach($formArr as $formK => $formV){							$inputVal = '';							$inputType = 'text';							if( isset( $_POST['register'] ) || isset( $_POST['change'] ) ){								$inputVal = $_POST[$formK];							}elseif($student->has_prop($formK)){								$inputVal = $student->get($formK);							}														if( ( $formK == 'user_email' ) || ( $formK == 'student_paypal' ) ) $inputType = 'email';														$error_class = ( array_key_exists( $formK, $formErrArr ) )? "class='error'" : "" ;							 							echo "<p $error_class><label for='$formK'>$formV</label><input type='$inputType' name='$formK' value='$inputVal' /></p>";														if( in_array( $formK, $hrArr ) ) echo '<hr>';													}						echo "<input class='button' type='submit' name='register' value='Register' />";						echo "</form>";					}				?>