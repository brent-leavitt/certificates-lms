<?PHP
/*
	Filename: 		nb_role_student.php
	Created: 		7 Feb 2018
	Last Updated: 	7 Feb 2018
	
	Description: 	Defines the role and capabilities of a student in the course environment. 

*/
//Remove first so we can reset it. 

echo "The NB ROLE STUDENT  page is being Called: NB_ROLE_STUDENTS! <br/>";

function nb_remove_student_role(){
	
	remove_role( 'nb_student' );
	
}

add_action( 'init', 'nb_remove_student_role' );



//Add role of NB_STUDENT. 
function nb_add_student_role(){
	
	add_role( 'nb_student', __('Student', 'nb-doula-training'), 
		array(
			'read_course'=>					true,//
			'read_courses'=>				true,//
			'read'=>						true,//
			'publish_assignments'=> 		true,//
			'edit_assignments'=> 			true,//
			'edit_assignment'=> 			true,//
			'read_assignment'=> 			true,//
			'read_assignments'=> 			true,//
			'edit_published_assignments'=> 	true,//
		)
	);
	
}

add_action( 'init', 'nb_add_student_role', 10 );



?>