<?php
/*
	Widgets for NB Doula Courses
	Last Updated: 25 July 2017
	
	Moved to "classes" folder. 
	
	
*/

class nb_student_progress extends WP_Widget {

	// constructor
	function nb_student_progress() {
		parent::WP_Widget(false, $name = __('Student Progress', 'nb_student_progress') );
	}

	// widget display
	function widget($args, $instance) {
		extract( $args );
			
		//PREP WORK: Not sure if this is the best place to do this, but it's a start. Should probably be somewhere more universal where other functions have access to it. 

		global $current_user, $post;
		$user_id = $current_user->ID;
		
		/*Determin Course Access*/
		$umeta_key = 'course_access';
		$usingle = true;
		$ca_num = intval( get_user_meta( $user_id, $umeta_key, $usingle ) );
		

		$acc_arr = array(
			1 => "main course only",
			2 => "main and childbirth courses",
			3 => "all course materials"
		);
		if( ( $ca_num >= 1 ) && ( $ca_num <= 3 ) ){
			$status_message .= "You currently have access to <strong>".$acc_arr[$ca_num]."</strong>.";
		}
		
		$stud_mat_arr = array();
		
		
		switch( $ca_num ){
			case 3:
				$stud_mat_arr[] = 'doula-actions';
				
			case 2:
				array_unshift( $stud_mat_arr, 'childbirth' );
				
			case 1:
				array_push( $stud_mat_arr, 'case-studies', 'birth-packet');
				//$stud_mat_arr = array_reverse( $stud_mat_arr );
				array_unshift( $stud_mat_arr,  'main' );
				break;
				
			case 0:
			default:
				$status_message .= "You do not have access to these materials.";
				break;
		}					
		
		/* Determine Account Active Status */
		$s_active = current_user_can('student_current');
			
		if(!$s_active){

			echo "<p>Hmm, Looks like your account is marked as <em>inactive</em>. <a href='$site_url/inactive-student-notice'>Get details.</a></p><span class='inactive'>"; 
		
			//Add to status Message which displays in the side bar on the front page only. 
			$status_message = substr( $status_message, 0, -1 );
			$status_message .= ( !empty( $ca_num ) )? ', but ' : ', and ';
			$status_message .= "your account is currently marked as <strong>inactive</strong>. <a href='$site_url/inactive-student-notice'>Get details.</a><span class='inactive'>";
		}		
			
		//END PREP WORK	
			
		echo $before_widget;
		// Display the widget
		echo '<div class="widget-text nb_student_progress_box">';

		echo $before_title . "Student Progress" . $after_title;

		//Bulk of Widget Goes Here
		
		?>
		<div class="textwidget" id="status_string"><?php echo $status_message; ?>
		<?php
		
			
		//Display only on non-course pages. 
		//if( $post->post_type !== 'course' ){
			$bookmarks = get_user_meta( $user_id, 'course_bookmarks' );
			echo "BOOKMARKS Array: ";
			print_r( $bookmarks );
			$bkmrk_resume_id = $bookmarks[ 0 ][ 'bd' ][ 0 ]; //0 for first entry which is resume location. 
			echo "BkmrkResumeID = $bkmrk_resume_id";
			$bkmrk_resume = get_post( $bkmrk_resume_id );
			
			echo "<hr><h4>Cousre Bookmark</h4><p><small>You last visited <strong>{$bkmrk_resume->post_title}</strong></small></p><a class='button' href='$site_url?p={$bkmrk_resume->ID}/'>RESUME &raquo;</a>"; 
		//}
		?>
		</div>
		<div class="textwidget" id="progress_bar">
			<h4>Progress Summary</h4>
			
<?php 

			$asmt = new NB_Assignment( $user_id );
			$prg_arr = $asmt->get_progress_report();
		
			$percentComplete = ( !empty( $prg_arr['percentComplete'] ) )? $prg_arr['percentComplete'] : 0 ;
			$completedAsmts = ( !empty( $prg_arr['completedAsmt']) )? $prg_arr['completedAsmt'] : 0 ;
			$totalAsmt = ( !empty( $prg_arr['totalAsmt']) )? $prg_arr['totalAsmt'] : 0 ;			
			?>
			<span>Birth Doula Certification</span>
			<div class='progress-mtr-wrap'><div class="progress-mtr"><span class="progress-mtr-bar" style="width:<?php echo $percentComplete; ?>%;">&nbsp;</span><span class="progress-mtr-text"><?php echo $percentComplete."% Complete"; ?></span></div></div>
			
			<?php 
			echo "<span class='progress-asmt-ratio'>(".$completedAsmts."/".$totalAsmt." Assignments Completed)</span>";
			?>	
			<br><a class="button" href="<?php echo $site_url; ?>/progress-report/">Progress Report &raquo;</a>
		</div>
		<?php /* <div class="textwidget" id="assignment_notices">
			<h4>Assignment Notices</h4>
			<ul>
				<li><a href="#">Assignment MC 1-2 has an instructor comment.</a></li>
				<li><a href="#">Assignment MC 2-1 has been marked incomplete.</a></li>
				<li><a href="#">Assignment MC U1 has an instructor comment.</a></li>
			</ul>
			<a href="<?php echo $site_url; ?>/grades/">Go To Progress Report</a> 
		</div> */?>
		<div class="textwidget last" id="billing_status">
			<h4>Billing Information</h4>
			<?php 
			
				$student = get_userdata($user_id);
				//print_pre( $student );
				
				$nb_stud_reg_obj = new DateTime( $student->data->user_registered );
				$nb_stud_reg_date = $nb_stud_reg_obj->format( "D., M. j, Y" );
				$nb_stud_reg_obj->modify('+2 years');
				$nb_stud_complete_date = $nb_stud_reg_obj->format( "l, F j, Y " );
				$nb_stud_reg_obj->modify('-6 months');
				$six_month_mark = $nb_stud_reg_obj->getTimeStamp();
				$now = time();
				
				$offer_extension = ( $now > $six_month_mark )? true : false;
				
				echo "<div class='prg-start-date'><span class='prg-start-date-label'>Start Date:</span><em>{$nb_stud_reg_date}</em></div>";
				echo "<div class='prg-until-date'>You have until <strong>{$nb_stud_complete_date}</strong> to complete your training.</div>";
				echo ( !$offer_extension )? " " : "<a class='button' href='/billing/course-extension/' >Request Extension &raquo;</a>";
				
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
				echo "<div class='prg-bill-detail'>";
				if( ( $nb_stud_pay_rcvd == '1/1' ) || ( $nb_stud_pay_rcvd == '12/12' ) ){
					echo "Your account is paid in full. <br>";
				} else {
				    echo "You have made {$pymt_rcvdArr[$nb_stud_pay_rcvd]} payments.<br>";
					echo "Your billing plan is \${$progRateArr[$nb_stud_prog_rate]} per month, making payments {$billTypeArr[$nb_stud_bill_type]}.";
					
				}	
				echo'</div>';
			// nb_get_student_billing($user_id);
			 //You have one pending invoice due on your account. Total payments made are 3 / 12.
			echo "<div class='bill-overview-btn'><a class='button' href='/billing/'>Billing Overview &raquo;</a></div>";
			
		echo '</div>';
		echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("nb_student_progress");'));

//Create another widget for Grades, Student Profile Editor and Log out. Maybe even, go to where I left off button. 

//Bookmarking Widget

function nb_course_bookmark( $page_id, $bkmrk_type = 0 ){
	
	global $current_user;
	
	$sid = $current_user->ID;
	
	$current_bookmarks = get_user_meta( $sid, 'course_bookmarks', true );
	
	if( !empty( $current_bookmarks ) ){
		$bkmrks_array = $current_bookmarks; 
	} else {
		$bkmrks_array = array(
			'bd' => array(
				0 => 0, //current page
				1 => 0, //user-defined 1
				2 => 0, //user-defined 2
				3 => 0 //user-defined 3
			)
		);
	}
	
	$bkmrks_array[ 'bd' ][ $bkmrk_type ] = $page_id; 
	
	update_user_meta( $sid, 'course_bookmarks', $bkmrks_array );
	
	echo "Cukoo, Cukoo!";
}


?>