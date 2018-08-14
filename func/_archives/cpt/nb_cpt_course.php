<?PHP
/*
	Filename: 		NB CPT COURSE
	Created: 		6 Feb 2018
	Last Updated: 	6 Feb 2018
	
	Description: 	Register the Course Custom Post Type for NB Doula Course

*/

function nb_register_cpt_course(){
	
	//Setup our labels first. 
	$nb_course_labels = array(
		'name' => _x('Courses', 'post type general name', 'doula-course'),
		'singular_name' => _x('Course', 'post type singular name', 'doula-course'),
		'add_new' => _x('Add New', 'course', 'doula-course'),
		'add_new_item' => __('Add New Section', 'doula-course'),
		'edit_item' => __('Edit Section', 'doula-course'),
		'new_item' => __('New Section', 'doula-course'),
		'all_items' => __('All Sections', 'doula-course'),
		'view_item' => __('View Section', 'doula-course'),
		'search_items' => __('Search Sections', 'doula-course'),
		'not_found' =>  __('No sections found', 'doula-course'),
		'not_found_in_trash' => __('No sections found in Trash', 'doula-course'), 
		'parent_item_colon' => '',
		'menu_name' => __('Courses', 'doula-course')
	);

	//Now declare full set of arguements for the nb_course cpt. 
	$nb_course_args = array(
		'labels' => $nb_course_labels, //stuff labels from above. 
		'description' => 'doula training manuals',
		'public' => true ,
		'publicly_queryable' => true,
		'query_var' => true,
		'show_ui' => true,
		'has_archive' => true, 
		'hierarchical' => true,
		'menu_position' => 52,
		'menu_icon' => 'dashicons-book-alt',
		'supports' => array( 'title', 'editor', 'page-attributes', 'revisions', 'comments' ),  
		'capabilities' => array(
			'publish_posts' => 'publish_courses',
			'edit_posts' => 'edit_courses',
			'edit_others_posts' => 'edit_others_courses',
			'delete_posts' => 'delete_courses',
			'delete_others_posts' => 'delete_others_courses',
			'read_private_posts' => 'read_private_courses',
			'edit_post' => 'edit_course',
			'delete_post' => 'delete_course',
			'read_post' => 'read_course',
			'read' => 'read_courses',
			'edit_private_posts' => 'edit_private_courses',
			'edit_published_posts' => 'edit_published_courses',
			'delete_published_posts' => 'delete_published_courses',
			'delete_private_posts' => 'delete_private_courses'
		), 
		'map_meta_cap'=> true, 
		'rewrite' => array( 'slug' => 'manuals' )
	); 

	register_post_type('nb_course', $nb_course_args);
	
	
}


add_action( 'init', 'nb_register_cpt_course' ); 

?>

