<?php
/*
	This is the file where we control the setup specific custom post types. 
	See also mold/NBPostType for where the actual modelling is happening. 
*/

namespace ctrl;

use \modl\NBRole as Role;

class NBRole{
	
	public $roles = array(
		'instructor',
		'alumnus',
		'student'
	), 
	$defaults = array(
		'subscriber', 
		'contributor', 
		'author'
	);
	
	public function __construct( ){
		
		
	}
	
	
	public function setup(){
		
		$roles = $this->roles;

		foreach($roles as $role ){
			$$role = new Role ( $role );
			$$role->add_new();
		}
				
			
		
	}
	
	
	private function remove_default_roles(){
		$defaults = $this->defaults;
		
	}
	
	
	public function remove(){
		
		$roles = $this->roles;
		foreach( $roles as $role )
			remove_role( $role );
		
	}

	private function restore_default_roles(){
		$defaults = $this->defaults;
		
	}
}


?>