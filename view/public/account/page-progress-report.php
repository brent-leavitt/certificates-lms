<?php
	
	$student = nb_get_student_meta(); 
	$asmt = new NB_Assignment( $student->ID );
	$asmt->set_status_to_num();
	
	$nb_asmt_url = home_url( '/?p=' );
	$asmt_map = new NB_Assignment_Map();
	
	 $certsArr = array(
		'bd' => 'Birth Doula',
		'pd' => 'Postpartum Doula'
	);  
	
	//END PREP WORK. 	
	$report_op = "<p>Below you will find a list of assignments required to complete your doula training and your current status for each assignment according to our records. If you find that our records appear to be incomplete or inaccurate, please contact us so that we may update your account accordingly. </p>
	<p>Student Name: <em>{$student->first_name} {$student->last_name}</em></p>";
	

		
	foreach( $asmt_map->asmt_map->certs as $cert_key => $cert ){
		$report_op .= ( isset( $certsArr[ $cert_key ] ) )? "<h2>{$certsArr[ $cert_key ]}</h2>" : " ";	
		$report_op .= "<table class='form-table nb-student-reports nb-table' >";
		foreach( $cert->courses as $course_key => $course ){
				foreach( $course->units as $unit_key => $unit ){		

					$report_op .="<tr>
						<th colspan='2'>
							<h4>";
							$report_op .= ( !empty( $unit->title ) )? $unit->title : $course->title ;
							
							$report_op .="</h4>
							
						</th>
					</tr>
					<tr class='meta-info'>
						<td><em>assignment name</em></td>
						<td><em>status</em></td>
					</tr>
					
					";

				
					foreach($unit->assignments as $asmt_key => $asmt_obj){
						
						//Get the meat of the report
						$report_op .="<tr>
							<td><a href='{$nb_asmt_url}{$asmt_key}' target='_blank'>{$asmt_obj->title}</a>	
							</td><td>";
							
						$studOpt = $asmt->grades[$cert_key][$asmt_key]['status'];					
								
						foreach($asmt->status_arr as $oKey => $oVal){
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
						
						//end middle meat, yum!
						
						
					}
				}
			}
		}
		

		$report_op .='	
			</table>
			';
			
	echo $report_op;
?>