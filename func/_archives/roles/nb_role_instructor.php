<?PHP
/*
	Filename: 		nb_role_instructor.php
	Created: 		8 Feb 2018
	Last Updated: 	8 Feb 2018
	
	Description: 	Defines the role and capabilities of an instructor in the course environment. 
	

*/
//Remove first so we can reset it. 

echo "The NB ROLE INSTRUCTOR  page is being Called: NB_ROLE_INSTRUCTOR! <br/>";

function nb_remove_instructor_role(){
	
	remove_role( 'nb_instructor' );
	
}

add_action( 'init', 'nb_remove_instructor_role' );



//Add role of NB_INSTRUCTOR. 
function nb_add_instructor_role(){
	
	add_role( 'nb_instructor', __('Instructor', 'nb-doula-training'), 
		array(
			'read_course'=>					true,//
			'read_courses'=>				true,//
			'read'=>						true,//
			'publish_assignments'=> 		true,//
			'edit_others_assignments'=> 	true,//
			'edit_assignments'=> 			true,//
			'edit_assignment'=> 			true,//
			'read_assignment'=> 			true,//
			'read_assignments'=> 			true,//
			'edit_published_assignments'=> 	true//
		)
	);
	
}

add_action( 'init', 'nb_add_instructor_role', 10 );


?>