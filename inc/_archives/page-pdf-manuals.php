<?php 					/*  Bulk of the Filter goes Here.  */					$shortcode = '';										global $current_user;					$user_id = $current_user->ID;					$umeta_key = 'course_access';					$usingle = true;					$ca_num = intval( get_user_meta( $user_id, $umeta_key, $usingle ) );										$acc_arr = array(						1 => "main course only",						2 => "main and childbirth courses",						3 => "all course materials"					);					if( ( $ca_num >= 1 ) && ( $ca_num <= 3 ) ){						$shortcode .= "<p>Your account currently has access to <strong>".$acc_arr[$ca_num]."</strong>.</p>";					}										$stud_mat_arr = array();															switch( $ca_num ){						case 3:							$stud_mat_arr[] = 'DoulaActions';													case 2:							$stud_mat_arr[] = 'ChildbirthCourse';													case 1:							array_push( $stud_mat_arr, 'CheckList', 'BirthPacket', 'CaseStudies', 'MainCourse' );							break;													case 0:						default:							$shortcode .= "You do not have access to these materials.";							break;					}															$stud_mat_arr = array_reverse( $stud_mat_arr );										$site_url = home_url();										foreach($stud_mat_arr as $mat_val){						$shortcode .= "<a href='".$site_url."/wp-content/uploads/2016/02/NBDT_".$mat_val.".pdf' target='_blank' ><img class='no-shadow' src='".$site_url."/wp-content/uploads/2016/02/NBDT_".$mat_val."_icon.png' alt='$mat_val' /></a>";					}				return $shortcode;?>