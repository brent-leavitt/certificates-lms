<?php 

/*
	Modeling of the User create functionality
*/

namespace modl;

class NBRole{
	
	public $role = '',
		$display = '';
			
	
	
	public function __construct( $role ){
		
		//assign the role
		$this->init( $role );
		
	}
		
	private function init( $role ){

		$this->role = 'nb_'.$role;
		$this->display = ucfirst( $role );
	}
	
	public function add_new(){
		
		$role  = $this->role;
		
		if( empty( get_role( $role ) ) ){
			add_role( $role, $this->display );
			
		}
		
	}
	
	
	
}

?>