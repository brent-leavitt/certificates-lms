<?php
/***
*
*  HELPER FUNCTIONS and CLASSES 
*
*  Created on 24 July 2013
*  Updated on 6 Jan 2016
*
****/

function print_pre($arr = array()){
	print('<pre>');
	print_r($arr);
	print('</pre>');
} 


/*
Built for NB_Pages, NB_STUDENT_UPDATES_STRING, but is not able to dig deep into USER class. 
*/
function nb_compare_groups( $old, $new ){
	$diff = array(); 
	
	if( is_object( $old ) && is_object( $new ) ){
		foreach( $old as $k => $v ) {
			if( property_exists( $new , $k ) ){ 
				if( is_array( $v ) ){
					$rad = nb_compare_groups( $v, $new->$k ); 
					if( count( $rad ) ){ $diff[ $k ] = $rad; } 
				}elseif( is_object( $v ) ) { 
					$rad = nb_compare_groups( $v, $new->$k ); 
					if( count( $rad ) ){ $diff[ $k ] = $rad; } 
				} else { 
					if( $v != $new->$k ){ 
						$diff[ $k ] = $v; 
					}
				}
			} else { 
				$diff[ $k ] = $v; 
			} 
		} 
		return $diff; 
	} elseif( is_array( $old ) && is_array( $new ) ){
		foreach( $old as $k => $v ) {
			if( array_key_exists( $k, $new ) ){ 
				if( is_array( $v ) ){
					$rad = nb_compare_groups($v, $new[$k]); 
					if( count( $rad ) ){ $diff[ $k ] = $rad; } 
				}elseif( is_object( $v ) ) { 
					$rad = nb_compare_groups($v, $new->$k); 
					if( count( $rad ) ){ $diff[ $k ] = $rad; } 
				} else { 
					if( $v != $new[ $k ] ){ 
						$diff[ $k ] = $v; 
					}
				}
			} else { 
				$diff[ $k ] = $v; 
			} 
		} 
		return $diff; 
	} else{
		return false; //Cannot compare the two groups as they do not match type. 
	}

}


/* Unneeded SEO functionality causing second page load. 
Diasable so that bookmarking tool works correctly. */

add_filter( 'index_rel_link', 'disable_stuff' );
add_filter( 'parent_post_rel_link', 'disable_stuff' );
add_filter( 'start_post_rel_link', 'disable_stuff' );
add_filter( 'previous_post_rel_link', 'disable_stuff' );
add_filter( 'next_post_rel_link', 'disable_stuff' );

function disable_stuff( $data ) {
	return false;
}

?>