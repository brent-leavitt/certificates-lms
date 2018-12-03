<?php
/*
	This is the file where we control the setup specific custom post types. 
	See also mold/NBPostType for where the actual modelling is happening. 
*/

namespace ctrl;

use \modl\NBPostType as CPT;

class NBPostType{
	
	public $post_types = array(
		'track',
		'course',
		'material',
		'assignment',
		'certificate'
	);
	
	public function __construct( ){
		
		
	}
	
	public function setup(){
		//Declare 5 Custom Post Types: Track, Course, Material, Assignment(user gen), Certificate(user gen)
		
		

		//Track
		$track		= new CPT( array( 
			'post_type'=>'track',
			'description'=>'Unique Certification Tracks',
			'menu_icon'=>'flag',
			
		) );
		
		
		//Course
		$course		= new CPT( array( 
			'post_type'=>'course',
			'post_item'=>'section',
			'post_items'=>'sections',
			'description'=>'course training manuals',
			'menu_icon'=>'book-alt',
			'rewrite'=>'manuals'
			
		) );
		
		//Material
		$material	= new CPT( array( 
			'post_type'=>'material',
			'post_item'=>'document',
			'post_items'=>'documents',
			'description'=>'Individual Course Documents and Materials that Constititue the body of the course',
			'menu_icon'=>'media-document',
			'rewrite'=>'docs'
			
		) );
		
		//Certificate
		$cert 		= new CPT( array( 
			'post_type'=>'certificate', 
			'description'=>'student generated certificates',
			/* 'menu_pos'=>54, */
			'supports' => array(
				'title', 
				'editor',
				'author',
				'revisions', 
				'comments' 
			),
		) );
		
		//Assignment
		$assignment	= new CPT( array( 
			'post_type'=>'assignment',
			'description'=>'student submitted assignments',
			/* 'menu_pos'=>53,*/
			'menu_icon'=>'portfolio', 
			'hierarchical' => false,
			'exclude_from_search' => true,
			'supports' => array( 
				'title', 
				'editor', 
				'comments', 
				'author', 
				'revisions' 
			)
		) );
	}
	
	
	
	public function remove(){
		
		$types = $this->post_types;
		foreach( $types as $type )
			unregister_post_type( $type );
		
	}
}


?>