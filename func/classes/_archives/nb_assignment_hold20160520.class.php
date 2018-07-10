<?php 

/*
 *  New Beginnings Assignment PHP Class
 *	Created on 7 Dec 2015
 *  Updated on 22 Dec 2015
 *
 *	The purpose of this class is to handle assignments beyond what is done 
 *	with the Assignment CPT created in WordPress. Particularly, it is to handle
 *	the evolving nature of course assignments and to give each assignment its
 *	proper placement relative to its own course and other courses. 
 *
 */

//check if WP_user is set.  
 
 
class NB_Assignment { //Not sure we need the WP_User class, but it could be helpful.

	//Properties first
	public $grades;
	public $sid = 0; 
	public $status_arr = array(
		0 => "No Status",
		1 => "Submitted",
		2 => "Incomplete",
		3 => "Resubmitted",
		4 => "Completed"
	);
	public $new_grade = array(
		'id' => 0, 						// asmt_id
		'status' => 'submitted', 		//asmt_grade
		'date' => '0000-00-00 00:00:00' //asmt_date
	);
	
	//Then Methods

	/**
	 * _construct
	 *
	 * @since 0.1
	 **/
	public function __construct( $sid ){
		$this->sid = $sid;
		$this->get_grades();
			
	}
	
	/**
	 * GET_GRADES
	 * 
	 * Descrip: Retrieve existing student grades meta data from database
	 *
	 * @since 0.1
	 **/
	public function get_grades(){
		
		$this->grades = maybe_unserialize( get_usermeta( $this->sid, 'student_grades', true ) );	
		
		//print_pre( $this );
		
		return $this->grades;
	}
	
	
	/**
	 * ADD_GRADE
	 * 
	 * Descrip: Adds a grade to the grades property. 
	 *
	 * NEEDED VARIABLES: $asmt_id (ASMT CPT id), $asmt_status, $post_date, $cert_id, $crs_id (COURSE CPT id). 
		$asmt_arr = array( 'asmt_id', 'asmt_status', 'cert_id', 'crs_id', 'post_date' );
	 *
	 * @since 0.1
	 **/
	 
	public function add_grade(  $asmt_arr  ){
		
		extract( $asmt_arr );
		$grade = $this->new_grade;
		
		if( !empty( $asmt_id ) ) $grade[ 'id' ] = $asmt_id;
		
		if( isset( $asmt_status ) ) $grade[ 'status' ] = $asmt_status;
		$grade[ 'date' ] = ( !empty( $post_date ) ) ? $post_date : date( "Y-m-d H:i:s" );
		
		$this->grades[ $cert_id ][ $crs_id ] = $grade; //THIS IS THE CODE THAT WE"VE BEEN WORKING TO BUILD. 
		
	}
	
	/**
	 * UPDATE_ALL_ASMTS
	 * 
	 * Descrip: Update all assignments, because post passes all assignments. 
	 *
	 * @since 0.1
	 **/
	 
	public function update_all_asmts( $post ){
		$old_grades = $this->grades; 
		$asmt_map = new NB_Assignment_Map();

		foreach( $post as $crs_id => $new_status ){
			//Check if assignment is set in $this->grades
			//If the assignment doesn't exists, and the value is not empty, add it. 
			
			//If this is a a revision or some other post type, we shouldn't be in here. 
			if( strcmp( $post->post_type, 'assignment' ) !== 0 ){
				return false;
			}
			
			if( !$this->asmt_exists( $crs_id ) ){
				
				if( !empty( $new_status ) ){
					//add it. 
					$cert_id = $asmt_map->get_cert_id( $crs_id ); 
					$asmt_id = $this->get_asmt_id( $crs_id ) ;
					
					$asmt_arr = array( 
						'asmt_id' => $asmt_id, 
						'asmt_status' => $new_status , 
						'cert_id' => $cert_id, 
						'crs_id' => $crs_id
					);
					
					$this->add_grade( $asmt_arr );
				}
				
			} else { //assignment does exists, updated it. 
				$cert_id = $asmt_map->get_cert_id( $crs_id ); 
				
				$current_status = $this->set_status_num( $this->grades[ $cert_id ][ $crs_id ][ 'status' ] );
				
				//check if status has changed. 
				if( $new_status !== $current_status ){					
					$this->grades[ $cert_id ][ $crs_id ][ 'status' ] = $new_status;
				}
			} 			
		}
		
		$db_updated = ( $this->grades !== $old_grades )? update_user_meta( $this->sid, 'student_grades', $this->grades, $old_grades ): FALSE ;
	
		return ( is_numeric( $db_updated ) )? TRUE : $db_updated ; 	
	}
	
	/**
	 * UPDATE_GRADE
	 * 
	 * Descrip: Update the grade for just one assignment. 
	 *
	 * Parameters: $asmt_update = array( 'asmt_key' => $asmt_key, 'asmt_status' => $asmt_status )
	 *
	 * @since 0.1
	 *
	 * Called in: templates/course-asignment.php, line 152, func/admin_metaboxes.php, line 219.
	 **/
	 
	public function update_grade( $asmt_update ){
		
		$old_grades = $this->grades; 
		$asmt_key = $asmt_update[ 'asmt_key' ]; 
		$asmt_status = $asmt_update[ 'asmt_status' ];
		
		//Check value of $asmt_update['asmt_status'], it may come in as a string. 
		//$status_num = $this->set_status_num( $asmt_status );
		
		// $msg = new NB_Message();
		// $message = "The update_grade method has been called in the nb_asmt Class. ASMT KEY is $asmt_key and the ASMT STATUS is $asmt_status . ". print_r( $asmt_update, TRUE );	
		// $msg->admin_notice( 'Test from NB_ASMT class, line 206', $message );
		
		if( isset( $asmt_key ) ){
			
			
			//Check to see if the assignment is found in the student's record
			$post = get_post( $asmt_key );
			$crs_id = intval( $post->post_parent );
			$asmt_map = new NB_Assignment_Map();	
			$cert_id = $asmt_map->get_cert_id( $crs_id ); 
			
			//If this is a a revision or some other post type, we shouldn't be in here. 
			if( strcmp( $post->post_type, 'assignment' ) !== 0 ){
				return false;
			}
			/* $message = "The ASMT KEY is set. Here's some extra variable info: A ID: $crs_id, Cert ID $cert_id .\n\r Post info is:". print_r( $post , TRUE)." \n\r and the ASMT MAP is". print_r( $asmt_map, TRUE );	
			$msg->admin_notice( 'Test from NB_ASMT class, line 218', $message ); */
			if( !$this->asmt_exists( $asmt_key ) ){
				//ADD this to the grades array. 
				$add_grade_arr = array(
					'asmt_id' => $asmt_key,
					'asmt_status' => $asmt_status,
					'cert_id' => $cert_id,
					'crs_id' => $crs_id ,
					'post_date' => $post->post_date
				);
				
				$this->add_grade( $add_grade_arr ); 
				
			} else {
			
				$this->grades[ $cert_id ][ $crs_id ]['status'] = $asmt_status;
			
			}
		}
		
		$db_updated = ( $this->grades !== $old_grades )? update_user_meta( $this->sid, 'student_grades', $this->grades, $old_grades ): FALSE ;
			
		//Not sure what purpose this has.
		/* if( $db_updated !== FALSE )
			$this->grades = $gradesData;  */
			
		return ( is_numeric( $db_updated ) )? TRUE : $db_updated ; 	
	}
	
	
	/**
	 * ASMT_EXISTS 
	 * 
	 * Descrip: Checks to see if the assignment exixts in the student's list of grades
	 * NOTE TO BRENT: This is intentionally simple, don't over complicate it. 
	 *
	 * Parameters: $asmt_status
	 *
	 * @since 0.1
	 * 18 Feb 2016
	 **/
	
	public function asmt_exists( $crs_key ){
			
		foreach( $this->grades as $asmts ){
			if( array_key_exists( $crs_key, $asmts ) ){
				return true;
			}
		}
		return false;
	}


	 /**
	 * SET_STATUS_NUM
	 * 
	 * Descrip: Checks to see if status is set to numberic value
	 *
	 * Reverse of SET STATUS STRING
	 *
	 * Parameters: $asmt_status
	 *
	 * @since 0.1
	 **/
	 
	public function set_status_num( $asmt_status ){
		
		if( !is_numeric( $asmt_status ) ){
			$a_status = strtolower( $asmt_status );
			switch( $a_status ){
				case "draft":
				case "no status":
					$status_num = 0;
					break;
				case "submitted":
					$status_num = 1;
					break;
				case "incomplete":
					$status_num = 2;
					break;
				case "resubmitted":
					$status_num = 3;
					break;
				case "completed":
					$status_num = 4;
					break;
			}
			$asmt_status = intval( $status_num );

		}
		return $asmt_status; 
	}
	
	
	
	
	/**
	 * SET_STATUS_STRING
	 * 
	 * Descrip: Checks to see if status is set to string, and updates if not. Reverse of SET_STATUS_NUM
	 *
	 * Parameters: $asmt_status
	 *
	 * @since 0.1
	 **/
	 
	public function set_status_string( $asmt_status ){
	
		if( !is_string( $asmt_status ) && is_int( $asmt_status ) ){
			$a_status = intval( $asmt_status );
			switch( $a_status ){
				case 0:
					$status_str = "draft";
					break;
				case 1:
					$status_str = "submitted";
					break;
				case 2:
					$status_str = "incomplete";
					break;
				case 3:
					$status_str = "resubmitted";
					break;
				case 4:
					$status_str = "completed";
					break;
			}
			
			$asmt_status =  $status_str ;
		}
		return $asmt_status; 
	}
	
	
	
	
	
	/**
	 * SET_STATUS_TO_NUM
	 * 
	 * Descrip: Checks to see if status is set to string, and updates if not. Reverse of SET_STATUS_NUM
	 *
	 * Parameters: $asmt_status
	 *
	 * @since 0.1
	 **/
	 
	public function set_status_to_num(){
		
		foreach( $this->grades as $cert_id => $cert ){
			foreach( $cert as $asmt_id => $asmt ){
				
				$this->grades[$cert_id][$asmt_id]['status'] = $this->set_status_num( $asmt['status'] );
			}
		}
	}
	
	
	
	
	

	//Summerize Student Progress through Grades Metadata
	// Returns an array of data to work with

	//THIS SHOULD BE IN A DIFFERENT FILE, BUT WHERE?

	public function get_progress_report(){
		$progress_arr = array();
		$flat_arr = array(); //Flatten results from mutli-dimensional array.
		
		$asmt_map = new NB_Assignment_Map();
		
		$this->set_status_to_num();
		
		foreach( $asmt_map->asmt_map->certs as $cert_key => $cert){
			foreach( $cert->courses as $course_key => $course ){
				foreach($course->units as $unit_key => $unit ){					
					foreach($unit->assignments as $asmt_key => $asmt ){
						if( strcmp( $asmt->status, 'publish' ) == 0	)
							$flat_arr[] =  $asmt_map->asmt_map->certs[ $cert_key ]->courses[ $course_key ]->units[ $unit_key ]->assignments[ $asmt_key ];
					}
				}
			}	
		}

		
		//- Total Number of Assignments
		$num_assignments = count( $flat_arr );
		
		if( isset($num_assignments) ){
			$progress_arr['totalAsmt'] = intval( $num_assignments );
		}
		
		
		//- How Many Assignments have been submitted
		$asmt_submitted = 0;
		//- How Many have been graded
		$asmt_graded = 0;
		//- How Many have been completed
		$asmt_completed = 0;
		//- How Many Marked as Incomplete
		$asmt_incomplete = 0;
		//- How Many Marked as Submitted
		$submitted_asmt = 0;
		
		$test = 0; 
		
		
		foreach($this->grades as $cert_id => $cert_arr){
			
			foreach( $cert_arr as $asmt_id => $asmt_arr  ){
				$t = intval($this->grades[ $cert_id ][ $asmt_id ][ 'status' ]);
				
				/* $test++;
				echo $test." iteration. Value of T is: {$t} Asmt ID is {$asmt_id}.<br>"; */
				
				switch( true ){
					case ( $t >= 1):
						$asmt_submitted++;
					
					case ( $t >= 2 ):
						$asmt_graded++;
				
					case ( $t >= 4 ):
						$asmt_completed++;
				
					case ( $t == 2 ):
						$asmt_incomplete++;
						
					case ( $t == 1 ):
						$submitted_asmt++;
					
					default: 
						break;
				}
			}
		}
		
		if( !empty( $asmt_submitted ) ){
			$progress_arr['submittedAsmt'] = intval( $asmt_submitted );
		}
		
		if( !empty( $asmt_graded ) ){
			$progress_arr['gradedAsmt'] = intval( $asmt_graded );
		}
		
		if( !empty( $asmt_completed ) ){
			$progress_arr['completedAsmt'] = intval( $asmt_completed );
		}
		
		if( !empty( $asmt_incomplete ) ){
			$progress_arr['asmtIncomplete'] = intval( $asmt_incomplete );
		}
		
		if( !empty( $submitted_asmt ) ){
			$progress_arr['asmtSubmitted'] = intval( $submitted_asmt );
		}
		
		//- Percentage Complete
		$percent_complete = 0;
		
		if( !empty( $num_assignments ) && !empty( $asmt_completed ) ){
			$percent_complete = ceil( ( $asmt_completed / $num_assignments ) * 100);
		}
		
		$progress_arr['percentComplete'] = $percent_complete;
		
		
		//print_pre( $progress_arr );
		return $progress_arr;
		
	}
	
		
	/**
	 * GET_ASMT_POST_INFO 
	 * 
	 * Descrip: Checks to see if status is set to string, and updates if not. Reverse of SET_STATUS_NUM
	 *
	 * Parameters: $asmt_status
	 *
	 * @since 0.1
	 **/
	 
	public function get_asmt_id( $crs_id ){
		
		//Not sure if this would ever get called without a Student ID already assigned, I'm commenitng out this part of the code: 
	/* 	if( empty( $this->sid ) ){
			if( isset( $_GET[ 'student_id' ] ) ){
				$this->sid = $_GET[ 'student_id' ];
			}else{
				return NULL;
			}
		} 
		
		echo "The value of SID in ASMT::GET_ASMT_ID is: $sid, while the value of THIS->SID: is {$this->sid}. <br>"; */
		
		$asmt_args = array( 
			'post_type' => 'assignment',
			'post_status' => 'draft, submitted, resubmitted, incomplete, completed',
			'author' => $this->sid,
			'post_parent' => $crs_id 
		);
		
		$asmt_post = get_posts( $asmt_args );
		
		return $asmt_post[ 0 ]->ID;
	}
	
		
	/**
	 * GET_ASMT_STATUS
	 * 
	 * Descrip: Returns the string value of the assignment status, for a given asmt based on the crs_id
	 *
	 * Parameters: $crs_id
	 *
	 * @since 0.1
	 **/
	 
	public function get_asmt_status( $crs_id ){
		
		$asmt_status = NULL;

		foreach( $this->grades as $asmts ){
			if( isset( $asmts[ $crs_id ] ) ){
				$asmt_status = $asmts[ $crs_id ][ 'status' ];
				
				//switch to string. 
				if( is_numeric( $asmt_status ) ){
					$asmt_status = $this->status_arr[ $asmt_status ]; 
				}
				
				break;
			}
		}
		
		return $asmt_status;
	}
	
	
}
?>