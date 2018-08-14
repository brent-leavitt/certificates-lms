<?PHP
/*
	Filename: 		nb_role_alumnus.php
	Created: 		8 Feb 2018
	Last Updated: 	8 Feb 2018
	
	Description: 	Defines the role and capabilities of a alumnus in the course environment. 

*/
//Remove first so we can reset it. 

echo "The NB ROLE ALUMNUS  page is being Called: NB_ROLE_ALUMNUS! <br/>";

function nb_remove_alumnus_role(){
	
	remove_role( 'nb_alumnus' );
	
}

add_action( 'init', 'nb_remove_alumnus_role' );



//Add role of NB_ALUMNUS. 
function nb_add_alumnus_role(){
	
	add_role( 'nb_alumnus', __('Alumnus', 'nb-doula-training'), 
		array(
			'read_course'=>					true,//
			'read_courses'=>				true,//
			'read'=>						true,//
			'read_assignment'=> 			true,//
			'read_assignments'=> 			true//
		)
	);
	
}

add_action( 'init', 'nb_add_alumnus_role', 10 );


?>