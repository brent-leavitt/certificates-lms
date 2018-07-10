<?PHP 
/*
	Filename: nb_roles.php
	Created:		5 Feb 2018
	Last Updated:	9 Jul 2018
	
	Description: Custom Roles and User Permissions that are created by the Doula Course plugin are defined here. 
	
	This is for Development Purposes ONLY, including the "required" files that are linked into this page!

	User roles should only be added on plugin activation,
	and only removed on deactivation. 
	

*/

/*  User Roles are currrently being managed by a plugin (WP_Capabilities)  */


if( empty( get_role( 'nb_instructor' ) ) )
	require_once( 'roles/nb_role_instructor.php' );

if( empty( get_role( 'nb_alumnus' ) ) )
	require_once( 'roles/nb_role_alumnus.php' );

if( empty( get_role( 'nb_student' ) ) )
	require_once( 'roles/nb_role_student.php' );

?>