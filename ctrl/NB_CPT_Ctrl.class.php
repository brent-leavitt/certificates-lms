<?php

//NOT IN USE??? I beleive this file can be deleted. 

if( !class_exists() ){
	
	class NB_CPT_Ctrl{
		
		public static function  init(){
			
			$cpt_arr = [ 'certificate', 'course', 'assignment', '' ]
			self::register_cpt();
			
		}
		
		
		
		
	}
}



?>