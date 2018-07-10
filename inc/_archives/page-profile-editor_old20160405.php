<?php

	$student = nb_get_student_meta();

	if( !empty( $_POST ) || wp_verify_nonce( $_POST['trees_and_flowers'],'account-profile' ) ) {

		//	echo "The value of Post: <br>";
		//	var_dump( $_POST );

		//	echo "<br><br>The value of STUDENT: <br>";
		//	var_dump( $student );
		if( isset($_POST['trees_and_flowers']) && ( $_POST['_wp_http_referer'] == '/account-profile/' ) ){
			
			
			$nbStud = new NB_Student();
			$_POST['ID'] = $student->ID;
			
			ob_start();
			$new_student_data = $nbStud->update_student($_POST, 1, 1); //override set, don't display update detail messages. 
			$update_results = ob_get_contents();
			ob_end_clean();
			
			if( is_a( $new_student_data, 'WP_User' ) ) {// New Method from NB_Student class, needs to be created. 
				$updated = true;
				$msg = new NB_Message();
				
				//Need a way to show what's been updated, to compare before and after changes. 
				//$eTo = "brent@trainingdoulas.com";
				//$eMsgUpdates = nb_student_updates_string( $student, $new_student_data );
				$eSubject = "Student Profile Updated for {$student->first_name} {$student->last_name}";
				$eAdminURL = get_bloginfo( 'url' );
				$eAdminURL .= "/check-in.php?redirect_to=%2Fwp-admin%2Fadmin.php%3Fpage%3Dedit%5Fstudent%26student%5Fid%3D{$sid}";
				$eMessage = "The student profile for {$student->first_name} {$student->last_name} has been updated.\r\n";
				if( !empty( $update_results ) )
					$eMessage .= "Changes to the following fields have been made: {$update_results}\r\n";
				$eMessage .= "To review changes, go to:\n\r{$eAdminURL}";
				
				 //wp_mail( $eTo, $eSubject, $eMessage );
				 
				 $msg->admin_notice( $eSubject, $eMessage );
				 //Add student notice? 
			}
			//Do we want to send an update message? we could. 
			$message = ( isset( $updated ) && ( $updated == true ) )? "We've updated the student account for $student->display_name." : "There was nothing to update. Please try again.";
			
			
			if( !empty( $new_student_data ) )
				$student = $new_student_data; //Replace old data with updated data. 
			
				
		}

	}
	
	ob_start();
	
	echo "<h3>Personal Information</h3>";
	
	
	
	if( !empty( $message ) ){
		echo '<div class="updated" id="message"><p>'.$message.'</p></div>';
	} 
	
	echo '<p>Below is the personal information that New Beginnings Childbirth Services has on file for your account. Please help us keep our recods up-to-date and accurate by updating any personal informatin below, as needed. Thank you!</p>';

	echo'<form method="post" action="/account-profile/">';	
		
		wp_nonce_field('account-profile','trees_and_flowers');
		echo'	
			
			<table class="form-table nb-table profile-table">
				<tr>
					<td>
						<label for="user_login">Username</label> <span class="reason"><em>(cannot be changed)</em> This is your log-in name for your account access; no one sees this except for you when you log in.</span>
						<input disabled type="text" id="user_login" name="user_login"  class="regular-text" value="'.$student->data->user_login.'" >
						
					</td>
				</tr>
				<tr>
					<td>
						<label for="display_name">Screen Name</label> <span class="reason">Your preferred name for contact and for public and private communications; nicknames are ok here.</span>
						<input type="text" id="display_name" name="display_name"  class="regular-text" value="'.$student->data->display_name.'" >
						
					</td>
				</tr>
				<tr>
					<td>
						<label for="first_name">First Name</label> <span class="reason">Your first given name that you are formally recognized as; this will be printed on your certificate.</span>
						<input type="text" id="first_name" name="first_name"  class="regular-text" value="'.$student->first_name.'" >
						
					</td>
				</tr>
				<tr>
					<td>
						<label for="last_name">Last Name</label> <span class="reason">Your current legal last name; this will also be printed on your certificate.</span>
						<input type="text" id="last_name" name="last_name"  class="regular-text" value="'.$student->last_name.'" >
						
					</td>	
				</tr>
				<tr>
					<td>
						<label for="user_email">Primary Email</label> <span class="reason">Valid email address for course correspondence; PLEASE double check your spelling!</span>
						<input type="email" id="user_email" name="user_email"  class="regular-text" value="'.$student->data->user_email.'" >
						
					</td>
				</tr>
				<tr>
					<td>
						<label for="student_phone">Phone</label> <span class="reason">Should we need to contact you by phone, please provide a current, working number</span>
						<input type="text" id="student_phone" name="student_phone"  class="regular-text" value="'.$student->student_phone.'" >
						
					</td>
				</tr>
				<tr>
					<td>
						<label for="student_address">Address</label> <span class="reason">Current mailing address; used for mailing certificate on completion.</span>
						<input type="text" id="student_address" name="student_address"  class="regular-text" value="'.$student->student_address.'" >
						
					</td>
				</tr>
				<tr>
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
				</tr>
				<tr>
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
				</tr>
				<tr>
					<td>
						<label for="student_postalcode">Postal Code</label>
						<input type="text" id="student_postalcode" name="student_postalcode"  value="'.$student->student_postalcode.'" >
					</td>
					
					
				</tr>
			</table>
			
			
			<h3>Payment Information</h3>
			<table class="form-table nb-table profile-table">
				<tr>
					<td colspan="2" >
						<label for="student_paypal">Paypal Email</label> <span class="reason">The email address of the PayPal account that you are using to pay for your course work. This ensures that your account receives credit for funds paid.</span>
						<input type="email" id="student_paypal" name="student_paypal"  class="regular-text" value="'.$student->student_paypal.'" >
						
					</td>
				</tr>
			</table>
			
			<p class="submit"><input type="submit" value="Update Student" class="button button-primary" id="submit" name="Update Account"></p>
			
		</form>'; 
		
		
		//Insert code to send to another page for transaction history. 
		

	return ob_get_clean();
?>