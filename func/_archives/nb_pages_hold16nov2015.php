<?php
//This is the scripts that generate the progress report page. 


add_shortcode( 'nb_progress_report', 'nb_get_progress_report' );

function nb_get_progress_report(){
	
	//Prep work, maybe move to it's own function?
	
	$gradesArr = array(
		'mc_1'=> array(
			'mc_1-1' => 'Assignment 1.1: The Role of a Doula',
			'mc_1-2' => 'Assignment 1.2: Labor Support',
			'mc_1-3' => 'Assignment 1.3: The Cochrane Review',
			'mc_1-4' => 'Assignment 1.4: Listening to Mothers',
			'mc_u1p' => 'Unit 1 Project'
		),
		'mc_2'=> array(
			'mc_2-1' => 'Assignment 2.1: Understanding Research',
			'mc_2-2' => 'Assignment 2.2: Decision Making',
			'mc_2-3' => 'Assignment 2.3: Medical History',
			'mc_u2p1' => 'Unit 2 Project - Mother friendly Initiative',
			'mc_u2p2' => 'Unit 2 Project - Interview'
		),
		'mc_3'=> array(
			'mc_3-1' => 'Assignment 3.1: Needs Identification',
			'mc_3-2' => 'Assignment 3.2: Safety Needs',
			'mc_3-3' => 'Assignment 3.3: Prioritizing Needs',
			'mc_u3p' => 'Unit 3 Project - Theories'
		),
		'mc_4'=> array(
			'mc_4-1' => 'Assignment 4.1: Causes of Pain',
			'mc_4-2' => 'Assignment 4.2: Research on Pain',
			'mc_4-3' => 'Assignment 4.3: Locus of Control',
			'mc_u4p' => 'Unit 4 Project - Pain Assessment'
		),
		'mc_5'=> array(
			'mc_5-1' => 'Assignment 5.1: Assessing Anxiety',
			'mc_u5p' => 'Unit 5 Project'
		),
		'cb_1'=> array(
			'cb_1-1' => 'Assignment 1.1: Models of Care',
			'cb_1-2' => 'Assignment 1.2: Common Complaints',
			'cb_1-3' => 'Assignment 1.3: Exerciese: Natural Movement',
			'cb_1-4' => 'Assignment 1.4: Nutrition',
			'cb_u1p' => 'Unit 1 Project'
		),
		'cb_2'=> array(
			'cb_2-1' => 'Assignment 2.1: Labor Progress',
			'cb_2-2' => 'Assignment 2.2: Six Ways to Progress',
			'cb_2-3' => 'Assignment 2.3: Physiologic Pushing',
			'cb_u2p' => 'Unit 2 Project'
		),
		'cb_3'=> array(
			'cb_3-1' => 'Assignment 3.1: Cord Clamping',
			'cb_3-2' => 'Assignment 3.2: Third Stage Labor',
			'cb_3-3' => 'Assignment 3.3: Newborn Procedures',
			'cb_3-4' => 'Assignment 3.4: Breastfeeding'
		),
		'da'=> array(
			'da_1' => 'Assignment 1: Communitcation',
			'da_2-1' => 'Assignment 2.1: Environment, online',
			'da_2-2' => 'Assignment 2.2: Environment, from text',
			'da_3' => 'Assignment 3: Informed Consent',
			'da_4' => 'Assignment 4: Mindfulness Training',
			'da_5' => 'Assignment 5: Movement',
			'da_6' => 'Assignment 6: Music Therapy',
			'da_7' => 'Assignment 7: Positioning',
			'da_8' => 'Assignment 8: Spirituality'
		),
		'bp'=> array(
			'bp_1' => 'Debriefing',
			'bp_2' => 'Skills Checkoff',
			'bp_3' => 'Birth Plan'
		)
	);

	$optArr = array(
					0 => "No Status",
					1 => "Submitted",
					2 => "Incomplete",
					3 => "Resubmitted",
					4 => "Completed",
				);
	 
	 
	 
	if( $current_user->ID != null){
					$student_id = $current_user->ID;
					$studData =  get_user_meta($student_id, 'student_grades'); //Returns an array of grades
					$studentData = $studData[0];
						
	} 
	
	//END PREP WORK. 
	
	
	$report_op = '';
	
	$report_op = "	
		<p>Below you will find a list of assignments required to complete your doula training and your current status for each assignment according to our records. If you find that our records appear to be incomplete or inaccurate, please contact us so that we may update your account accordingly. </p>";
	
	  $studFName =  get_user_meta($student_id , 'first_name');
	  $studFName = $studFName[0];
	  $studLName =  get_user_meta($student_id , 'last_name');
	  $studLName = $studLName[0];
	
	   $report_op .= "<p>Student Name: <em>$studFName $studLName</em></p>";
    
		

		$report_op .='<table class="form-table">';
		
		 foreach( $gradesArr as $gradesKey => $gradesNameArr ) {
		 
			$report_op .="<tr>
					<th colspan='2'>
						<h4>".gradeKeyVal($gradesKey)."</h4>
						
					</th>
				</tr>
				<tr class='meta-info'>
					<td><em>assignment name</em></td>
					<td><em>status</em></td>
				</tr>
				
				";
		
			foreach( $gradesNameArr  as $gnKey => $gnVal ){
				$studOpt = $studentData[$gradesKey][$gnKey];
				
				$report_op .="<tr>
					<td>$gnKey <a href='#'>$gnVal</a>	
					</td><td>";
						
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