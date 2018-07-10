<?php
	/**
	*
	* Registering meta boxes
	*
	**/


	
	//Reference Code - 
	//https://codex.wordpress.org/Function_Reference/add_meta_box#Examples
	
	//You will need to create custom HTML code to update and save Post Status from the admin side of things. 
	function nb_asmt_status_callback( $post ){
		//global $post; //Called in assignment operator
		
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'nb_asmt_meta_box', 'nb_asmt_meta_box_nonce' );
		
		$p_status = $post->post_status;//Post Status for assignemnt
		
		$instr_status = get_post_meta( $post->ID, 'instructor_status', true );
		
		$p_status_array = array( 'draft', 'submitted', 'incomplete', 'resubmitted', 'completed' );
		
		$instr_stat_array = array(
			0 => 'Not seen',
			1 => 'Seen, not graded',
			2 => 'Graded'
		);
		
		_e( 'The assignment is', 'doula' );
		//echo '<br> ';
		echo '<select id="nb_asmt_post_status" name="nb_asmt_post_status" >';
			foreach($p_status_array as $psa_key){
				echo '<option value="'.$psa_key.'"';
				if( $psa_key == $p_status ) echo ' selected ';
				echo '>'.ucfirst($psa_key).'</option>';
				
			}	
		echo '</select>';
		//echo '<br> ';
		_e( 'and the instructor has', 'doula' );
		//echo '<br> ';
		
		
		echo '<select id="nb_asmt_instr_status" name="nb_asmt_instr_status" >';
		foreach($instr_stat_array as $isa_key => $isa_val){
			echo '<option value="'.$isa_key.'"';
			if( intval( $isa_key ) == intval( $instr_status ) ) echo ' selected ';
			echo '>'.$isa_val.'</option>';
			
		}	
		echo '</select>';
		echo '<br> ';
	
		//echo 'POST STATUS: '.$post->post_status.'<br>Instructor Status: '.$instr_status;
		
		
		echo '<input type="submit" value="Save" accesskey="p" id="publish" class="button button-primary button-large" name="save">';		
		
	}
	
	function nb_asmt_rubric_callback( $post ){
		//Get ID of course assignment associated with submitted assigment.
		$course_post_id = $post->post_parent;
		
		$rubric = get_post_meta($course_post_id, 'course_rubric', true);
		
		echo $rubric;
		echo "<br>";
		echo "<p><a href='/wp-admin/post.php?post={$course_post_id}&action=edit#course-rubric-div' target='_blank'>Edit rubric</a>";
	
	
	}
	
	function nb_asmt_atch_callback( $post ){
		//Get ID of course assignment associated with submitted assigment.
		//Add additional functionality in the future: https://codex.wordpress.org/Javascript_Reference/ThickBox
		
		$attach_args = array(
			'post_parent' => $post->ID,
			'post_type'   => 'attachment'
		);
		$attachments = get_children( $attach_args, OBJECT );

		if( !empty( $attachments ) ){
			echo "<ul>";
			foreach( $attachments as $atch ) echo "<li><a href='".wp_get_attachment_url( $atch->ID )."' target='_blank'>{$atch->post_title}</a></li>";
			echo "</ul>";
		} else{
			echo "<p>No attachments are linked to this assignment.</p>";
		}
	
	}
	
	function nb_asmt_content_callback( $post ){
		//Get ID of course assignment associated with submitted assigment.
		$course_post_id = $post->post_parent;
		
		$asmt_content = get_post( $course_post_id );
		
		echo '<h2>'. $asmt_content->post_title. '</h2>';
		echo $asmt_content->post_content;
		echo "<br><br>";
		echo "<p><a href='/wp-admin/post.php?post={$course_post_id}&action=edit' target='_blank'>Edit Course Content</a>";
	
	}
	
	
	function nb_asmt_student_callback( $post ){
		//Get ID of course assignment associated with submitted assigment.
		$nb_student_id = $post->post_author;
	
		$nb_student_info = get_user_by('id',$nb_student_id);
		$roles_arr = array_reverse( explode( '_', $nb_student_info->roles[0] ) ); 
		if( $roles_arr[0] == 'inactive'){
			$asmt_stud_notice = "<div class='error'><p><strong>This student's account is marked as inactive! Go to <a href='/wp-admin/admin.php?page=edit_student&student_id={$nb_student_id}' target='_blank'>student account</a> for details.</strong></p></div>";
		} 
		$acct_status = ucwords( implode( ' ', $roles_arr ) );
		
		//print_pre($nb_student_info);
		if( isset($asmt_stud_notice) ){
			echo "{$asmt_stud_notice}";		
		}
		$nbAsmt = new NB_Assignment( $nb_student_id );
		$prg_arr = $nbAsmt->get_progress_report();
		
		$percentComplete = ( !empty( $prg_arr['percentComplete'] ) )? $prg_arr['percentComplete'] : 0 ;
		$completedAsmts = ( !empty( $prg_arr['completedAsmt']) )? $prg_arr['completedAsmt'] : 0 ;
		$totalAsmt = ( !empty( $prg_arr['totalAsmt']) )? $prg_arr['totalAsmt'] : 0 ;
		$nb_stud_progress = $percentComplete."% (".$completedAsmts."/".$totalAsmt.")";
		
		
		echo "<strong>Student:</strong> <a href='/wp-admin/admin.php?page=edit_student&student_id={$nb_student_id}' target='_blank'>{$nb_student_info->display_name}</a><br>
		      <strong>Account Status:</strong> {$acct_status}<br>
			  <strong>Start Date:</strong> {$nb_student_info->user_registered}<br>
			  <strong>Course Progress:</strong> {$nb_stud_progress}<br>
			  <br>
			  Go to <a href='/wp-admin/admin.php?page=edit_student&student_id={$nb_student_id}' target='_blank'>account details.</a><br>
			  
		";	
	}
	
	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	 
	function nb_asmt_save_meta_box_data( $post_id ) {			
			global $post;
			
			/*
			 * We need to verify this came from our screen and with proper authorization,
			 * because the save_post action can be triggered at other times.
			 */

			// Check if our nonce is set.
			if ( ! isset( $_POST['nb_asmt_meta_box_nonce'] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['nb_asmt_meta_box_nonce'], 'nb_asmt_meta_box' ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'assignment' == $_POST['post_type'] ) {

				if ( ! current_user_can( 'edit_assignment', $post_id ) ) {
					return;
				}

			} else {

				if ( ! current_user_can( 'edit_assignment', $post_id ) ) {
					return;
				}
			}
			
			/* OK, it's safe for us to save the data now. */
			
			// Make sure that it is set.
			if ( ! isset( $_POST['nb_asmt_instr_status'] ) || ! isset( $_POST['nb_asmt_post_status'] ) ) {
				return;
			}
		
			$instr_status = get_post_meta( $post->ID, 'instructor_status', true );
			$new_instr_status =  $_POST['nb_asmt_instr_status'];
			
			if($instr_status != $new_instr_status){//Only perform update if change in values.
				// Update the meta field in the database.
				$up_id = update_post_meta( $post_id, 'instructor_status', $new_instr_status, $instr_status );
				//if($up_id != false) echo "Instructor status was updated!";
			}
			
			
			//Check and update post status
			
			$p_status = $post->post_status;//Post Status for assignemnt
			$new_p_status = $_POST['nb_asmt_post_status'];
			
			
			if($p_status !== $new_p_status){ //Only perform update if change in values.
				$p_data = array(
					'ID' => $post_id,
					'post_status' => $new_p_status 
				);
				
					
				if ( ! wp_is_post_revision( $post_id ) ){
					// unhook this function so it doesn't loop infinitely
					remove_action('save_post', 'nb_asmt_save_meta_box_data');
					
					// update the post, which calls save_post again
					$update_id = wp_update_post( $p_data );
					
					// re-hook this function
					add_action('save_post', 'nb_asmt_save_meta_box_data');
				}//end revision check
				
				
				$asmts = new NB_Assignment( $post->post_author );
				
				$status_array = array(
					'asmt_key' => $post_id,
					'asmt_status' => $new_p_status 
				);
				$updated_asmts = $asmts->update_grade( $status_array );
				
				if( !updated_asmts ){
					$subject = 'Failed to update the student_grade metadata.';
					$message = "This message originated from nb_asmt_save_meta_box_data function, admin_metaboxes.php, line 222. \r\n The asmt key is: 'asmt_key' => {$post->post_parent}, 'asmt_status' => $new_p_status \r\n The Student Grades Metadata failed to update. Please investigate the issue.";
					
				} /* else {
					$subject = 'Successfull update the student_grade metadata.';
					$message = "This message originated from nb_asmt_save_meta_box_data function, admin_metaboxes.php, line 222. \r\n The asmt key is: 'asmt_key' => {$post->post_parent}, 'asmt_status' => $new_p_status \r\n The Student Grades Metadata should have been successfully updated. Please investigate the issue.";
				} */
				$msg = new NB_Message();
				$msg->admin_notice( $subject, $message );
			}
	}
	add_action( 'save_post', 'nb_asmt_save_meta_box_data' );
	
	
	//Course CPT, Course Type Meta Data field added to publish box on admin screen
	add_action( 'post_submitbox_misc_actions', 'nb_course_type_select' );
	add_action( 'save_post', 'save_nb_course_type_select' );
	
	function nb_course_type_select() {
		global $post;
		if (get_post_type($post) == 'course') {
			echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
			wp_nonce_field( 'nb_course_type_select', 'nb_course_type_nonce' );
			$c_type = get_post_meta( $post->ID, 'course_type', true );
			$c_type_arr = array(
				0=>'content',
				1=>'assignment',
				2=>'section',
				3=>'other',
				4=>'manual',
				5=>'certification',
			);
			
			echo 'Course Type: <select id="nb_asmt_course_type" name="nb_asmt_course_type" >';
			foreach($c_type_arr as $cta_key => $cta_val){
				echo '<option value="'.$cta_key.'"';
				if( $cta_key == $c_type ) echo ' selected ';
				echo '>'.ucfirst($cta_val).'</option>';
				
			}	
			echo '</select>';
			
			$ca_type = get_post_meta( $post->ID, 'course_access', true );
			$ca_type_arr = array(
				1=>'Main Course',
				2=>'Main &amp; Childbirth',
				3=>'All Courses'
			);
			
			echo '<br>Course Access: <select id="nb_course_access" name="nb_course_access" >';
			foreach($ca_type_arr as $cata_key => $cata_val){
				echo '<option value="'.$cata_key.'"';
				if( $cata_key == $ca_type ) echo ' selected ';
				echo '>'.ucfirst($cata_val).'</option>';
				
			}	
			echo '</select>';
			echo '</div>';
		}
	}
	function save_nb_course_type_select($post_id) {

		if (!isset($_POST['post_type']) )
			return $post_id;

		if ( !wp_verify_nonce( $_POST['nb_course_type_nonce'], 'nb_course_type_select' ) )
			return $post_id;

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id;

		if ( 'course' == $_POST['post_type'] && !current_user_can( 'edit_course', $post_id ) )
			return $post_id;
		
		if ( !isset( $_POST['nb_asmt_course_type'] ) || !isset( $_POST['nb_course_access'] ) )
			return $post_id;
		else {
			if( isset( $_POST['nb_asmt_course_type'] ) ){
				$ct_data = $_POST['nb_asmt_course_type'];
				update_post_meta( $post_id, 'course_type', $ct_data, get_post_meta( $post_id, 'course_type', true ) );
			}
			
			if( isset( $_POST['nb_course_access'] ) ){
				$ct_data = $_POST['nb_course_access'];
				update_post_meta( $post_id, 'course_access', $ct_data, get_post_meta( $post_id, 'course_access', true ) );
			}
			
		}
	}
	
	//COURSE RUBRIC 
	
	function nb_crs_rubric_callback( $post ){
		
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'nb_crs_rubric', 'nb_crs_rubric_nonce' );
				
		$crs_rubric = get_post_meta( $post->ID, 'course_rubric', true );

		$rubric_settings = array( 'teeny' => true );
		wp_editor($crs_rubric, 'course-rubric-editor', $rubric_settings);
		
	}
	
	function save_nb_crs_rubric_callback( $post_id ) {			
			global $post;
			
			// Check if our nonce is set.
			if ( ! isset( $_POST['nb_crs_rubric_nonce'] ) ) return;
			
			if ( ! wp_verify_nonce( $_POST['nb_crs_rubric_nonce'], 'nb_crs_rubric' ) )	return;

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

			if ( isset( $_POST['post_type'] ) && 'course' == $_POST['post_type'] ) {

				if ( ! current_user_can( 'edit_course', $post_id ) ) return;

			} else {

				if ( ! current_user_can( 'edit_course', $post_id ) ) return;
			}
			
			/* OK, it's safe for us to save the data now. */
			
			
			// Make sure that it is set.
			if ( ! isset( $_POST['course-rubric-editor'] ) )  return;
			
			$cr_data = $_POST['course-rubric-editor'];
			update_post_meta( $post_id, 'course_rubric', $cr_data, get_post_meta( $post_id, 'course_rubric', true ) );

	}
	add_action( 'save_post', 'save_nb_crs_rubric_callback' );
	
	
	if (is_admin()):
		function nb_admin_meta_boxes() {
		
		  //Metaboxes for Assignment Editor Screen
		  remove_meta_box('submitdiv', 'assignment', 'side');
		  add_meta_box( 'save-asmt-div', __( 'Assignment Status' ), 'nb_asmt_status_callback' , 'assignment', 'side', 'high' );
		  add_meta_box( 'asmt-rubric-div', __( 'Rubric' ), 'nb_asmt_rubric_callback' , 'assignment', 'side', 'default' );
		  add_meta_box( 'asmt-student-div', __( 'Student Details' ), 'nb_asmt_student_callback' , 'assignment', 'side', 'default' );
		  add_meta_box( 'asmt-atch-div', __( 'Assignment Attachments' ), 'nb_asmt_atch_callback' , 'assignment', 'side', 'low' );
		  add_meta_box( 'asmt-content-div', __( 'Assignment' ), 'nb_asmt_content_callback' , 'assignment', 'normal', 'low' );
		
		  //Metaboxes for Course Editor Screen
		  add_meta_box( 'course-rubric-div', __( 'Assignment Rubric' ), 'nb_crs_rubric_callback' , 'course', 'normal', 'default' );
		  
		  
		 	  
		}
		add_action( 'admin_menu', 'nb_admin_meta_boxes' );
		
	endif;
	
	
?>