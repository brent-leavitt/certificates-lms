<?php
/*
	This is the file where we control the setup specific custom roles. 
	See also mold/NBRole for where the actual modelling is happening. 
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
	
	
	public function set_roles(){
		
		$roles = $this->roles;

		foreach($roles as $role ){
			$$role = new Role ( $role );
			$$role->add();
		}
	}
	
	
	private function remove_default_roles(){
		$defaults = $this->defaults;
		foreach( $defaults as $def )
			remove_role( $def );
	}
	
	
	public function remove(){
		
		$roles = $this->roles;
		foreach( $roles as $role ){
			$$role = new Role( $role );
			$$role->remove();
		}

	}

	private function restore_default_roles(){
		$defaults = $this->defaults;
		
	}
}


?>