<?php
/*	NB REG INVITE class 
 *
 * 	
 *  Figure out how to process this request. 
 *
 *
 *
 *
 */

class NB_Reg_Invite{

	public $var;
	
	private $action;
	
	public __construct( $action ){
		
		$this->take_action( $action );
		
	}

	public take_action( $action ){
		
		
	
	}
	
	public processed(){
	//figures out if the previous action was successful or not. 
		echo "NB_Reg_Invite::processed called, line 32."
	}
	
}
?>