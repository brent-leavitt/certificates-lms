<?php 

/*
 *  New Beginnings Assignment Map PHP Class
 *	Created on 22 Dec 2015
 *  Updated on 22 Dec 2015
 *
 *	The purpose of this class is to handle the assignment map, which is the master
 *  document for all assignments managed through New Beginning's, beyond what is done 
 *	with the Assignment CPT created in WordPress. Particularly, it is to handle
 *	the evolving nature of course assignments and to give each assignment its
 *	proper placement relative to its own course and other courses. 
 *
 */

//check if WP_user is set.  
 
 
class NB_Assignment_Map { 

	//Properties first
	public $asmt_map;
	/* public $var1 = '';
	 */
	
	//Then Methods

	/**
	 * __construct
	 *
	 * @since 0.1
	 **/
	public function __construct(){
		$this->asmt_map = $this->get_asmt_map();
		
		if( empty( $this->asmt_map ) )
			$this->asmt_map = $this->create_asmt_map();
	}
	
	/**
	 * GET_ASMT_MAP
	 *
	 * Descrip: Get the assignment map from the database's options table. 
	 *
	 * @since 0.1
	 **/
	 
	public function get_asmt_map(){
		$asmt_map =  maybe_unserialize( get_option( 'assignment_map' ) );
	
		return ( empty( $asmt_map ) )? FALSE : $asmt_map ;
	}
	
	
	/**
	 * UPDATE_ASMT_MAP
	 *
	 * Descrip: Update the assignment map from the database's options table. 
	 *
	 * @since 0.1
	 **/
	 //COME BACK AND CLEAN UP 
	 
	public function update_asmt_map( $post ){
			
		$asmt_map_data = $this->get_asmt_map_data();
		
		foreach( $post as $p_key => $p_val ){
			if( array_key_exists( $p_key, $asmt_map_data ) )
					$asmt_map_data[$p_key]->post_id = $p_val;		
		}
		
		$asmt_map = $this->set_asmt_map_asmts( $asmt_map_data );
		$asmt_updated = update_option( 'assignment_map', $asmt_map );	
		
		echo ( $asmt_updated )? 
			'The Assignment Map was  successfully updated in the options table in the database!' : 
			'Failed to update the Assignment Map in the database. Check your code!' ;
			
		return $asmt_updated;
	}
	
	/**
	 * CREATE_ASMT_MAP
	 *
	 * Descrip: Create the Assignment Map if doesn't exist.  
	 *
	 * @since 0.1
	 **/

	public function create_asmt_map(){
		

	/*
	- certs
		- courses
			-units
				-assignments	
					-ID (CPT-ID)
					-title (assignment name)
					-status (current or discontinued)
					-start_date
					-end_date
					-replaced_by (int or array - ids for assignments that replaced this asmt)
	*/
		$option_name = 'assignment_map';
		if ( get_option( $option_name ) == false ) {
		
			$asmt_map = new stdClass();
			
			$asmt_map->certs = array(
				'bd' => new stdClass()
			);
			
			$asmt_map->certs['bd']->title = 'birth doula';
			$asmt_map->certs['bd']->courses = array();
			
			
			$course_arr = array(
				'mc' => 'main course',
				'cb' => 'chidbirth course',
				'da' => 'doula actions',
				'bp' => 'birth packet'
			);

			$gradesArr = array(
				'mc_1'=> array(
					1213, 1224, 1221, 1218, 1226
				),
				'mc_2'=> array(
					1230, 1232, 1236, 1239, 1241
				),
				'mc_3'=> array(
					1249, 1251, 1254, 1257
				),
				'mc_4'=> array(
					1262, 1263, 1267, 1269
				),
				'mc_5'=> array(
					1276, 1277
				),
				'cb_1'=> array(
					1590, 1648, 1609, 1682, 1287
				),
				'cb_2'=> array(
					2920, 2922, 2927, 1328
				),
				'cb_3'=> array(
					2935, 2940, 2944, 732
				),
				'da'=> array(
					2954, 2969, 2970, 2980, 2985, 2990, 2995, 2999, 1356 
				),
				'bp'=> array(
					3015, 3017, 3019
				)
			);	

			
			foreach($course_arr as $cKey => $cVal){
				$asmt_map->certs['bd']->courses[$cKey] = new stdClass();
				$asmt_map->certs['bd']->courses[$cKey]->title = $cVal;	
				$asmt_map->certs['bd']->courses[$cKey]->units = array();
			}
			
			foreach( $gradesArr  as $gKey => $gVal  ){
				$course_id = substr($gKey, 0, 2); 
				if( is_object( $asmt_map->certs['bd']->courses[$course_id] ) ){
					$asmt_map->certs['bd']->courses[$course_id]->units[$gKey] = new stdClass();
					//Add title for Unit
					if( !empty( $unit_num = substr($gKey, 3 ) ) ){
						$unit_prefix = $asmt_map->certs['bd']->courses[$course_id]->title;
						$asmt_map->certs['bd']->courses[$course_id]->units[$gKey]->title = $unit_prefix. ', unit '.$unit_num;
					}
					$asmt_map->certs['bd']->courses[$course_id]->units[$gKey]->assignments = array();
					
					foreach( $gVal as $asmtKey ){
						$post = get_post( $asmtKey );
					
						$asmt_obj = new stdClass();
						$asmt_obj->title = $post->post_title;
						$asmt_obj->status = $post->post_status;
						$asmt_obj->start_date = $post->post_date;
						$asmt_obj->end_date = '0000-00-00 00:00:00';
						$asmt_obj->replaced_by = 0;
						
						
						$asmt_map->certs['bd']->courses[$course_id]->units[$gKey]->assignments[$asmtKey] = $asmt_obj;
					}
				} 
			}
			
			
			//print_pre( $asmt_map );
			//Insert array here to generate from what already exists. 
			
		
		

			// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
			$deprecated = null;
			$autoload = 'no';
			$option_added = add_option( $option_name, $asmt_map, $deprecated, $autoload );
			
			//print_pre( $asmt_map ); //For dev purposes only. 
			
			
		} 
		
		echo ( $option_added )? 
			'The Assignment Map was  successfully added to the options table in the database!' : 
			'Failed to add Assignment Map to database. May already be there.' ;
		
		return ($option_added)? $asmt_map : FALSE ;
		
	}	

	//Assignment Manager
	public function asmt_map_manager(){
		
		echo "<p>Welcome to the NB Assignment Map Manager. This tool is used to ensure that all assignments are properly indexed and connected to the proper course assignment so that students get proper credit for assignments completed in the older bookkeeping system.</p>
		(More functionality to be added here later.)<br><br>";
		
		if ( !empty($_POST) && check_admin_referer('assignment_map','asmt-map-check') ){
			
			//update the assignment map with posted changes. 
			$asmt_map_updated = $this->update_asmt_map( $_POST );
			
		} 

		//REFERENCE: http://stackoverflow.com/questions/11282592/php-re-order-associative-array
		
		
		//needs to move up in down in array of assignments. 
		//Need to be able to remove? No, but we can set an end date. 
		//Need to be able to add new assignments and specify where they belong. 
		
		//Take code from database, and create file to manually cut and paste? NOT POSSIBLE. 
		
		//print_pre( get_asmt_map_data() );
		echo "The values of ASMT_MAP::ASMT_MAP: <br>";
		print_pre( $this->asmt_map );
		$this->asmt_map_form();
		
		/* $asmt_count = 0; 
		$arr_count = count($asmt_posts); 
		echo "The total size of the array is: $arr_count <br><hr>"; */
		$this->unmapped_asmts();
	}


	//Consolidate the next three methods. 
	public function get_asmt_map_ids(){
		//$asmt_map =  maybe_unserialize( get_option( 'assignment_map' ) );
		$map_id_list = array();
		$asmt_id = 0;
		
		foreach( $this->asmt_map as  $certs){
			foreach( $certs as $cert_obj ){
				foreach( $cert_obj as $courses ){
					foreach( $courses as $course ){
						foreach( $course->units as $unit ){						
							foreach($unit->assignments as $asmt_id => $asmt_obj){
								
								if( $asmt_id !== 0 || $asmt_id !== 99999 ){
									if( !in_array( $asmt_id, $map_id_list ) )
										$map_id_list[] = $asmt_id;
								}
							}
						}
					}
				}
			}
		}
		return $map_id_list;
	}



	public function get_asmt_map_data(){
		
		//$asmt_map =  maybe_unserialize( get_option( 'assignment_map' ) );
		$map_data_list = array();
		$asmt_id = 0;
		
		foreach( $this->asmt_map as  $certs){
			foreach( $certs as $cert_obj ){
				foreach( $cert_obj as $courses ){
					foreach( $courses as $course ){
						foreach( $course->units as $unit ){						
							foreach($unit->assignments as $asmt_key => $asmt_obj){		
							
								$map_data_list[$asmt_key] = $asmt_obj;

							}
						}
					}
				}
			}
		}
		return $map_data_list;
	}

	
	
	// Preparing the assignment map array to be reinserted into the database.

	public function set_asmt_map_asmts( $asmt_data ){
		
		//$asmt_map =  maybe_unserialize( get_option( 'assignment_map' ) );
		
		foreach( $this->asmt_map as  $certs){
			foreach( $certs as $cert_obj ){
				foreach( $cert_obj as $courses ){
					foreach( $courses as $course ){
						foreach( $course->units as $unit ){						
							foreach($unit->assignments as $asmt_key => $asmt_obj){		
							
								$asmt_obj->post_id = $asmt_data[$asmt_key]->post_id;

							}
						}
					}
				}
			}
		}
		return $asmt_map;
	}







	public function asmt_map_form(){
		//Retrived assignment map from database
		$option_name = 'assignment_map';
		$asmt_map =  maybe_unserialize( get_option( $option_name ) );
		
		
		/* echo "From the databse: <br><hr>";
		print_pre( $asmt_map );
		echo "<hr>";  */
		
		
		//If no assignment map in database, create it. 
		//Probably will never use this, but it's here as a backup to the default. 
		if ( $asmt_map == false ) {
			//Creates an assignment map from a default set of data. Can be used as a data reset option if needed. 
			$asmt_map = $this->asmt_map_create(); 
		}
		
		//Retrieve course cpt's marked as assignments. 		
		$asmt_post_args = array(
			'numberposts' =>  -1,
			'post_type' =>  'course',
			'meta_key' =>  'course_type',
			'meta_value' =>  1 //for assignments 
		);
		
		$asmt_posts = get_posts( $asmt_post_args );

		
		$asmt_dropdown = array(
			999999 => 'unset',
			0 => 'deprecated'		
		);
		
		foreach( $asmt_posts as $asmt_obj ){
			if( $asmt_obj->post_status !== 'trash' ) //only make available those assignments which haven't been trashed. 
				$asmt_dropdown[$asmt_obj->ID] = $asmt_obj->post_title;
		}
		

	?>
	<form method='post'action="admin.php?page=assignment_map" >
	<?php 

		wp_nonce_field('assignment_map','asmt-map-check', true);

	?>
	<table class="wp-list-table widefat fixed striped">

		
	<?php
		$table_head = false;
		$table_body = false;
		
		foreach( $asmt_map as  $certs){
			
			foreach( $certs as $cert_key => $cert_obj ){
				if(!$table_head){
					$table_head = true;
					echo "<thead>";
				}
				
				$cert_name = ucwords( $cert_obj->title );
				echo "<tr>
					<th colspan='5' class='asmt-cert'>$cert_name Certification</th>
				</tr>";
				foreach( $cert_obj as $courses ){
					foreach( $courses as $course_key => $course ){
					if(!$table_head){
						$table_head = true;
						echo "<thead>";
					}
						echo "<tr>
							<th colspan='5' class='asmt-course'>";echo ucwords( $course->title );
						echo"</th>
						</tr>";
						
						$total_units = count($course->units);
						$unit_count = 0; 
						foreach( $course->units as $unit_id => $unit ){
							$unit_count++;
							
							if(!$table_head){
								$table_head = true;
								echo "<thead>";
							}
							if( strcmp($course_key, $unit_id) !== 0 ){
								echo "<tr>
									<th colspan='5' class='asmt-unit'>";
								echo ucwords( $unit->title );
								echo "</th>
								</tr>";						
							}


							if( $table_head ){
								echo "
								<tr class='asmt-key'>
									<th>ID</th>
									<th>Title</th>
									<th>Status</th>
									<th>Start Date</th>
									<th>End Date</th>
									
								</tr>
								</thead>";
								$table_head = false;
							}
							
							//STOPPED HERE> 
							if( !$table_body ){
								
								$table_body = true; 
							}
							echo "<tbody>";
							
							foreach($unit->assignments as $asmt_key => $asmt_obj){
							
							/* 	$post_id_select = "<select style='width:100px' name='{$asmt_key}'>";
								
								foreach($asmt_dropdown as $add_key => $add_val){
									$post_id_select .= "<option value='{$add_key}'";
									if( strcmp( $add_key, $asmt_key) == 0 ){
										$post_id_select .= " selected ";
									}
									$post_id_select .= ">{$add_key} - {$add_val}</option>";
								}
								$post_id_select .= "</select>"; */
								
								$end_date = ( strcmp( $asmt_obj->end_date, '0000-00-00 00:00:00' ) !== 0 )? $asmt_obj->end_date: 'not set' ;
								echo"<tr>
										<td>{$asmt_key}</td>
										<td>{$asmt_obj->title}</td>
										<td>{$asmt_obj->status}</td>
										<td>{$asmt_obj->start_date}</td>
										<td>{$end_date}</td>
									</tr>";
							
							}
							echo "</tbody>";
							
							if( ( !$table_head ) && ( $unit_count < $total_units ) ){
								$table_head = true;
								echo "<thead>";
							}
							//Disbled submit button until there is something to edit. 
							/* echo "<tr>
								<th colspan='5' class='empty'>"; 
								
								submit_button('Update Map');
								
							echo "</th>
							</tr>"; */
							
						}
					}
				}
			}
		
		}

	?>	
		
	</table>
	</form>	

	<?php

	}

	public function unmapped_asmts(){

		$asmt_post_args = array(
			'numberposts' =>  -1,
			'post_type' =>  'course',
			'meta_key' =>  'course_type',
			'meta_value' =>  1 //for assignments 
		);
		
		$asmt_posts = get_posts( $asmt_post_args );	
		
		$asmt_id_list = array(); //This is list of post_ids tagged as assignments. 
		foreach( $asmt_posts as $asmt_obj ){
			if( $asmt_obj->post_status !== 'trash' ) //only make available those assignments which haven't been trashed. 
				$asmt_id_list[] = $asmt_obj->ID;
		}
		
		$map_id_list = $this->get_asmt_map_ids(); //This is a list of map assignmt ids. 
		
		$unmapped_ids = array_diff( $asmt_id_list, $map_id_list );
		
		$unmapped_posts = array();
		foreach( $asmt_posts as $asmt_obj ){
			if( in_array( $asmt_obj->ID, $unmapped_ids ) ){
				$unmapped_posts[] = $asmt_obj;
			}	
		}
		
		if( !empty( $unmapped_posts ) ){
			echo "<hr>
			<h3>Unmapped Assignments</h3>
			<p>The following assignments have not been added to the assignment map.</p>
			
			<ul>
			";
			$admin_url = admin_url('post.php?action=edit&post=');
			foreach($unmapped_posts as $upost){
				echo "<li>Post #{$upost->ID}:<a href='{$admin_url}{$upost->ID}' target='_blank'>{$upost->post_title}</a></li>";
			}
			
			echo "</ul>";
		}
		
		//go through the assignment map and find if each post 

	}


	
	
		/**
	 * GET_ASMT_MAP_KEY //DEPRECATED, I beleive. Asmt keys were replaced by parent POST ID. 
	 *
	 * Descrip: Get one specific assignment key from the assignment map. 
	 *
	 * @since 0.1
	 **/
	 
	public function get_asmt_key( $post_id ){
		
		$asmt_key = $this->parse_map( 'asmt_key', $post_id );
		
		return ( empty( $asmt_key ) )? FALSE : $asmt_key ;
	}
	
	
	/**
	 * 
	 *
	 * @since 0.1
	 */
	 
	public function parse_map( $action, $data ){
		
		foreach( $this->asmt_map->certs as $cert_id => $cert){
			foreach( $cert->courses as $course ){
				foreach( $course->units as $unit ){					
					foreach($unit->assignments as $asmt_key => $asmt_obj){		
					
						switch( $action ){
							case 'asmt_key': 
								if( strcmp( $asmt_obj->post_id, $data ) == 0 )
									$found = $asmt_key;
								break;
							case 'cert_id': 
								if( strcmp( $asmt_key, $data ) == 0 )
									$found = $cert_id;
								break;
							default:
								break;
						}
						//$asmt_obj->post_id = $asmt_data[$asmt_key]->post_id;

					}
				}
			}
		}
		
		return ( isset( $found ) )? $found : FALSE ;
	}
	
	
	/**
	 * GET CERT ID
	 * 
	 * Get the Certification ID for a given ASMT
	 *
	 * @since 0.1
	 */
	 
	public function get_cert_id( $asmt_id ){
		
		$cert_id = $this->parse_map( 'cert_id', $asmt_id );
		
		return ( empty( $cert_id ) )? FALSE : $cert_id ;
	}

	
	/**
	 * 
	 *
	 * @since 0.1
	 */
	 
/*	public function _(){
	
			
	}
	*/
	
}
?>