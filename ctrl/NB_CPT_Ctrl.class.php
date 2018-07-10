<?php

if( !class_exists() ){
	
	class NB_CPT_Ctrl{
		
		public static function  init(){
			
			$cpt_arr = [ 'certificate', 'course', 'assignment', '' ]
			self::register_cpt();
			
		}
		
		
		
		
	}
}



?>