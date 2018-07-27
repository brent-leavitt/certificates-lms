<?php 

/*
	Modeling of the User create functionality
	This handles only one role at a time. 
*/

namespace modl;

class NBRole{
	
	public $role = '',
		$display = '';
		
	const TD = 'certificate-lms'; //text domain	
	
	public function __construct( $role ){
		
		//assign the role
		$this->init( $role );
		
	}
	
	
	private function init( $role ){

		$this->role = 'nb_'.$role;
		$this->display = ucfirst( $role );
	}
	
	
	public function add(){
		
		$role  = $this->role;
		
		if( empty( get_role( $role ) ) )
			add_role( $role, __( $this->display, TD ) );
		
		return ( !empty( get_role( $role ) ) )? TRUE : FALSE;
	}
	
	
	//This is will only remove roles that have been added by the plugin. 
	public function remove(){
		
		$role  = $this->role;
		
		if( !empty( get_role( $role ) ) )
			remove_role( $role );
		
		return ( empty( get_role( $role ) ) )? TRUE : FALSE;
	}
	
	
}

?>