<?php
// Check Student Meta to see if this assignment is not already completed. 

//post assignment: CPT loads the WYSIWIG Editor for the front end, 
//and commenting on Assignment CPT between student and professor for the assignment. 


//FIRST, process form requests. 
						
			$empty_asmt = true;	
			$atch_msg = array();
			$current_url = get_permalink();
			$asmt_editable = true; 
			
			$allowed_html = wp_kses_allowed_html( 'post' );
		
			
			//REQUEST TO DELETE ATTACHMENT?
			if( isset( $_GET['delete_atch'] )  ){
				$delete_atch_id = $_GET[ 'delete_atch' ];
				//Move this message to Attachment area. 
				
				$atch_msg[] = ( false === wp_delete_attachment( $delete_atch_id ) )?"The file attachement failed to be deleted.":"The file attachment was successfully deleted.";
			}			
			
			if( !empty( $_POST ) && ( wp_nonce_field( 'edit_assignments', 'grape_vines' ) || wp_nonce_field( 'edit_assignments', 'tomato_vines' ) ) ){
				//if we get to this point something is being posted. 
				
				 if( empty( $_POST['edit_assignment'] ) ){
					//The assignment form is empty, no assignment can be submitted. 
					
					$notices['empty'] = 'The assignment form is empty. Please complete your assignment and then save it as a dreft or submit it for grading.'; 
			
					
				} else {
					
					$empty_asmt = false; 
					if( !empty( $_POST['student_id'] ) ){
					
						$sid = $_POST['student_id'];
						
						if( isset( $_POST['save_draft'] ) || isset( $_POST['submit_assignment'] ) ){
							//Do the same things if either of these buttons have been clicked.
							 
							
							$assignment = array(
								'post_type' => 'assignment',
								'post_author' => $sid
							); 
							
							//main insert post info.
							$asmt_meta = array(); //metadatea to be added. 
							
							//if assignment ID exists
							if( !empty( $_POST['assignment_id'] ) ){
								$assignment['ID'] = $asmt_id = $_POST['assignment_id'];
							} else {
								//assignment has no ID yet. 
								$assignment['ID'] = NULL;
							}
						 
					
							//Post content
							$assignment['post_content'] = wp_kses( $_POST['edit_assignment'] , $allowed_html );
							
							$student_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($current_user->ID) );
							
							$student_name = strtolower($student_meta['first_name']).ucfirst(strtolower($student_meta['last_name']));
							
							//Post name
							$assignment['post_name'] = $student_name.'_'.$post->post_name;
							
							//Post title
							$assignment['post_title'] = ucfirst($student_name).': '.$post->post_title; 
							
							//if course ID exists
							$assignment['post_parent'] = ( isset( $_POST['post_id'] ) )? $_POST['post_id'] : NULL ;
							
						}
						
						//Set Post Status
						$asmt_status = get_post_status( $_POST['assignment_id'] );	
					
						if( isset( $_POST['save_draft'] ) ){ //Smaller to-do list for save draft. 
							
							//Post status  - if marked as incomplete and the "Draft" button is pressed then mark as still incomplete, else mark as draft. 
							
							$assignment['post_status'] = (  $asmt_status == 'incomplete' || $asmt_status == 'resubmitted' )?'incomplete':'draft';
							
						} elseif( isset( $_POST['submit_assignment'] ) ){ //This is to submit the assignment for grading
												
							//Post status - we need to determine if this is being submitted or resubmitted. 
											
							$assignment['post_status'] = ( $asmt_status == 'incomplete' ||  $asmt_status == 'resubmitted' )? 'resubmitted' : 'submitted';
							
							$instr_status = 0; //not seen.
							
						} 		
						
						
						//Insert or Update entry in the database. 
						if( isset( $assignment['ID'] ) ){
						
							$asmt_id = wp_update_post( $assignment ); 
							
						} else {
						
							$asmt_id = wp_insert_post( $assignment );
							
						}
						
						//Update the Student's Grade Sheet
						
						$nb_msg = new NB_Message();
						
						if( isset( $asmt_id ) ){
							$asmts = new NB_Assignment( $sid );	
							
							$status_array = array(
								'asmt_key' => $asmt_id,
								'asmt_status' => $assignment['post_status']
							);
							
							$updated_asmts = $asmts->update_grade( $status_array );
							//should send admin notice if not successfully updated. 
							if( !updated_asmts ){
								$admin_sub = 'Failed to update the student_grade metadata from course-assignment template';
								$admin_msg = "This message originated from course-assignment.php template file in the doula-training plugin, line 149. \r\n The asmt key is: 'asmt_key' => $asmt_key, 'asmt_status' => $new_p_status \r\n The Student Grades Metadata failed to update. Please investigate the issue.";
								
								$nb_msg->admin_notice( $admin_sub, $admin_msg );
							}
						}
						
						//Reset instructor status on newly submitted assignments. 
						if( isset( $instr_status ) ){
							$post_meta_result = add_post_meta($asmt_id, 'instructor_status', $instr_status, true);
							
							if( !$post_meta_result )
								update_post_meta($asmt_id, 'instructor_status', $instr_status);
							
							if( ( $instr_status === 0 ) && ( isset( $_POST['submit_assignment'] ) ) ) //Instructor hasn't seen this. Send a message. 
								$mess_sent = $nb_msg->assignment_submitted( $asmt_id ); //returns true or false. 
								
						}
					}
				}
				

				
			} 
			
			if ( isset( $_POST['asmt_atchmnts_nonce'], $_POST['post_id'] ) && wp_verify_nonce( $_POST['asmt_atchmnts_nonce'], 'asmt_atchmnts' ) ) {
				//echo "<p>NONCES are being summoned to upload the file attachments!</p>";
				// The nonce was valid and the user has the capabilities, it is safe to continue.

				// These files need to be included as dependencies when on the front end.
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				
				$files = $_FILES[ "asmt_atchmnts" ];  
				$attachment_ids = array();	
				
				// Let WordPress handle the upload.
				// Set up to handle multiple uploads at once. 
				// Remember, 'asmt_atchmnts' is the name of our file input in our form above.
				
				foreach( $files[ 'name' ] as $key => $value ){            
					if( $files[ 'name' ][ $key ] ){ 
						$file = array( 
							'name' => $files[ 'name' ][ $key ],
							'type' => $files[ 'type' ][ $key ], 
							'tmp_name' => $files[ 'tmp_name' ][ $key ], 
							'error' => $files[ 'error' ][ $key ],
							'size' => $files[ 'size' ][ $key ]
						); 
						
						$_FILES = array( "asmt_atchmnts" => $file ); 
						foreach( $_FILES as $file => $array ) {              
							$attachment_ids[] = media_handle_upload( $file, $_POST['post_id'] ); 
						}
					} 
				} 
				
				foreach( $attachment_ids as $aid ){
					if( isset( $atch_msg ) )
						$atch_msg[] = ( is_wp_error( $aid ) )? "Failed to upload attachments. Try again!" : "Attachments were successfully uploaded! Hoorah!";
				}
				
			} 
			
//END form request processing scripts	

			
//Load the student assignment:

		
			$asmt_args = array(
				'post_type' => 'assignment',
				'post_status' => array( 'draft', 'submitted', 'incomplete', 'resubmitted', 'completed' ),
				'author' => $current_user->ID,
				'post_parent' => $post->ID
			);
				
			$asmt_query = new WP_Query( $asmt_args );
				

//Display Assignment Status Notices: 

				if( isset( $asmt_query->post->ID ) ){
					$asmt_id = $asmt_query->post->ID;
					$empty_asmt = false;
					
					$asmt_status = ( empty( $assignment['post_status'] ) )? get_post_status($asmt_id): $assignment['post_status'] ;
					$instr_status = ( !isset( $instr_status ) )? get_post_meta($asmt_id, 'instructor_status', true): NULL;
					
					//$status_array = array( $asmt_status, $instr_status );
					
					//print_pre( $status_array ); 
					
					switch( array( $asmt_status, $instr_status ) ){
						case array('draft', NULL ): //draft, not yet submitted
						case array('draft', 0 ): 
							$status_message = 'A draft version of this assignment has been saved, but has not yet been submitted to the instructor.';
							$status_class = '';
							break;
						case array('submitted', 0 ): //submitted and not seen
							$status_message = 'Your assignment has been saved and submitted for grading by the instructor, but the instructor has not yet seen or graded it. <em>If you wish to continue working on your assignment before submitting it for review, please click on the "save draft" button instead.</em>'; 
							$asmt_editable = false; 
							if( isset( $mess_sent ) && ( $mess_sent == true ) )
									$status_message .= "<br><br>A receipt of your assignment has been sent to your email account on file. Please retain for your personal records.";
							$status_class = 'pending';
							break;
						case array('submitted', 1): //submitted and seen by the instructor, but not graded
							$status_message = 'This assignment has been submitted and seen by the instructor, but is still pending review and grading.';
							$status_class = 'pending'; 
							$asmt_editable = false; 
							break;
						case array('incomplete',  0):  //unseen but already marked as incomplete
							$status_message = 'A copy of your revised assignment has been saved, but not resubmitted to the instructor. Please continue working on your assignment and resubmit your revised assignment to be graded when ready.';
							$status_class = 'alert';
							break;
						case array('incomplete',  2):  //graded and marked as incomplete
							$status_message = 'This assignment has been graded and marked as incomplete by the instructor. Your attention is needed. Please see comments below for feedback from the instructor.';
							$status_class = 'alert';
							break;
						case array('resubmitted', 0): //resubmitted and not seen
							$status_message = 'This assignment has been re-submitted to the instructor, but the instuctor has not yet seen the latest revisions made to the assignment. <em>If you would like to continue working on the assignment before submitting it for review, please click on the "save draft" button instead.</em>';
							if( isset( $mess_sent ) && ( $mess_sent == true ) )
									$status_message .= "<br><br>A receipt of your assignment has been sent to your email account on file. Please retain for your personal records.";
							$status_class = 'pending';
							$asmt_editable = false; 
							break;
						case array('resubmitted', 1): //resubmitted and seen by the instructor, but not graded
							$status_message = 'This assignment has been resubmitted and seen by the instructor, but it has not received additional review and grading.';
							$status_class = 'pending';
							$asmt_editable = false; 
							break;
						case array('completed', 2 ): //graded and marked as complete
							$status_message = 'This assignment has been submitted and marked as complete by the instructor!';
							$status_class = 'cleared';
							$asmt_editable = false; 
							break;
						default:
							$status_message = 'You have not begun work on this assignment.';
							$status_class = '';
							break;
					}
				}
								
			

 
				if( $empty_asmt )
					$notices['instr'] = '<strong>Instructions:</strong> Use the assignment editor below to compose and submit your assignment. To save your assignment before you are ready to submit it, click "Save Draft." '; 
					
				if( isset( $notices ) ){
					echo "<div class='asmt_notices'>";
					foreach($notices as $nkey => $notice)
						echo "<p class='$nkey'>$notice</p>";
					
					echo "</div>";			
				}
				if( isset( $status_class ) && isset( $asmt_status ) && isset( $status_message ) ){
					echo "
					<div class='asmt_status_box {$status_class}'>
						<h4>Assignment Status: <em>{$asmt_status}</em></h4>
						<p class='asmt_detail_status'>{$status_message}</p>
					</div><!-- end .ASMT_STATUS_BOX -->
					";
				}
				
				if( $asmt_query->have_posts()){ 
				
					$content = $asmt_query->post->post_content;
					
				} else {
				
					$content = NULL;
				}?>
							
				
				
				<form action="<?php echo $current_url; ?>#asmt-editor" method="POST">
				<?php 	
				wp_nonce_field('edit_assignment','grape_vines');
				?>
					<input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
					<input type="hidden" name="student_id" value="<?php echo $current_user->ID; ?>" />
				
				<?php
				//Set the Assignment ID only if it is already set, or that is if it already exists. 
				
				if( isset( $asmt_id ) ){
					echo '<input type="hidden" name="assignment_id" value="'.$asmt_id.'" />';
				}
				$editor_id = 'edit_assignment';
				
				if( $asmt_editable ){
				
					$settings = array( 'media_buttons' => true , );
					wp_editor( $content, $editor_id, $settings );
				
					echo'
						<input type="submit" id="save_draft" name="save_draft" value="Save Draft" />
						<input type="submit" id="submit_assignment" class="button-primary" name="submit_assignment" value="Submit" />
					';
					
				} else {
					add_filter( 'tiny_mce_before_init', function( $args ) {

						if ( 1 == 1 )
							 $args['readonly'] = 1;
						return $args;
						
					} );
					
					$settings = array( 'media_buttons' => false , );
					//$editor_id = 'view_assignment';
					
					wp_editor( $content, $editor_id, $settings );
				
					if( isset( $asmt_status ) && ( $asmt_status == 'completed' ) ){
						echo "<em>This assignment is marked as completed. No further changes can be made.</em>";
					} else {
						echo "<em>This assignment is pending review and cannot be edited. To continue editing, revert to \"draft\" status.</em>";
						echo '<input type="submit" id="save_draft" name="save_draft" value="Revert to Draft" />';
					}
					
					
				}
				?>
				
					
				</form>
<?php 
//Add Attachment Functionality:
//Check if assignments are attached? An Assignment ID must be created first. 
			echo "<hr>";

			if( isset( $atch_msg ) && !empty( $atch_msg ) ) {
				echo "<div class='asmt_status_box'><ul>";
				foreach( $atch_msg as $a_msg )
					echo "<li><em>".$a_msg."</em></li>";
				echo "</ul></div>";
			}
				
				

			echo "<p id='attachments'><strong>Attachments:</strong> ";
			if( empty( $asmt_id ) ){
				
				?>An assignment should first be created before you can include attachments. Begin working on your assignment and click "Save Draft", or click below to add attachments without a written assignment.</p> 
				<form action="<?php echo $current_url; ?>#asmt_atchmnts" method="POST">
				<?php 	
					wp_nonce_field('edit_assignment','tomato_vines');
				?>
					<input type="hidden" name="post_id" value="<?php echo $post->ID; ?>" />
					<input type="hidden" name="student_id" value="<?php echo $current_user->ID; ?>" />
					<input type="hidden" name="edit_assignment" value="[No written assignment; see attachments]" />				
					<input type="submit" id="save_draft" name="save_draft" value="Add Attachments Only" />
				</form>
				<?php
			}else{
				
				//Assignment ID is set: Add or edit attachments. 
				//Are there attachments associated with this assignment.
				$attach_args = array(
					'post_parent' => $asmt_id,
					'post_type'   => 'attachment'
				);
				$attachments = get_children( $attach_args, OBJECT );

				if( !empty( $attachments ) ){
					echo "The following files are linked to this assignment: </p>";
					//print_pre( $attachments );
					echo "<ul>";
					foreach( $attachments as $atch ){
						echo "<li><a href='".wp_get_attachment_url( $atch->ID )."' target='_blank'>{$atch->post_title}</a>"; 
						if( $asmt_editable ) echo" <small>[<a href='?delete_atch=".$atch->ID."#attachments' >remove X</a>]</small></li>";
					
					}
					echo "</ul>";
					
					if( $asmt_editable ) echo "<p>To add additional files, click the \"Browse...\" button below.</p>";
				} else{
					echo "No files are linked to this assignment. </p>";
				}
				
				if( $asmt_editable ):
				?>
				
				<form id="asmt_atchmnts" method="post" action="<?php echo $current_url; ?>#attachments" enctype="multipart/form-data">
					<input type="file" name="asmt_atchmnts[]" id="asmt_atchmnts"  multiple="multiple" />
					<input type="hidden" name="post_id" id="post_id" value="<?php echo $asmt_id; ?>" />
					<?php wp_nonce_field( 'asmt_atchmnts', 'asmt_atchmnts_nonce' ); ?>
					<input id="submit_asmt_atchmnts" name="submit_asmt_atchmnts" type="submit" value="Attach Files" />
				</form>
				<?php 
				endif; //if( $asmt_editable ), started on line 411. 
				
			}
			echo "<hr>";

	//Add commenting functionality: 

		//We can't comment if the post has not been submitted. 
				if( $asmt_status !== 'draft' && ( !empty( $asmt_id ) ) ){ 
				
					echo "<div class='commentlist'>";
					$asmt_comments = get_comments('post_id='.$asmt_id.'&order=DESC'); 
					
					
					if( !empty( $asmt_comments ) ){
						
						echo "<p class='info-right'>(Feedback is listed newest to oldest.)</p>";
						echo "<h3>Instructor Feedback</h3>";
						$comment_parms = array( 'reply_text' => '', 'avatar_size' => 0 , 'style' => 'div' );
						wp_list_comments( $comment_parms, $asmt_comments ); 
						
						$defaults = array(
							'title_reply' => 'Reply to Instructor Feedback',
							'label_submit'      => __( 'Post Reply' ),
							'logged_in_as' => '',
							'comment_field' =>  '
							<input type="hidden" name="redirect_to" value="'.$course_permalink.'" />
							<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>'
						);
						
						comment_form($defaults, $asmt_id);		
					
					} else {
						echo"<p><em>No feedback from the course instructor has been posted for your assignment yet. Feedback for specific assignments will appear here when available. Thank you.</em></p>"; 
					}
					
					echo "</div><!-- end .commentlist --> ";				
				
				}//if $asmt_status !== 'draft' 				
			
?>
