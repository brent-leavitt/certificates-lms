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
	$caps = array(
		//TBD
	),
	$defaults = array(
		'author',
		'contributor', 
		'subscriber', 
	);
	
	public function __construct( ){
		
		
	}
	
	
	public function set_roles(){
		
		//Setup New Roles for CERTS LMS
		$roles = $this->roles;

		foreach($roles as $role ){
			$$role = new Role ( $role );
			$$role->add();
		}
		
		//Remove Default Roles on Installation
		$defaults = $this->defaults;
		
		foreach( $defaults as $def ){
			$$def = new Role( $def );
			$$def->remove();
		}
		
		//Need to update this: 
		update_option('default_role', 'student');
	}
	
	
	
/*
* 	Reset Default Roles
*	Description: On deactivation, this reset roles back to original state. 
*
*
*/	
	
	public function reset_default_roles(){
		
		//Reset Default User Roles
		$defaults = $this->defaults;
		
		foreach($defaults as $role ){
			$$role = new Role ( $role );
			$$role->add();
		}
		
		//Remove Custom Added Roles
		$roles = $this->roles;
		
		foreach( $roles as $role ){
			$$role = new Role( $role );
			$$role->remove();
		}

		update_option('default_role', 'subscriber');
	}

}


?>