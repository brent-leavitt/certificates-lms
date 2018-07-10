<?PHP
/*
	Filename: 		NB CPT CERTIFICATE
	Created: 		6 Feb 2018
	Last Updated: 	6 Feb 2018
	
	Description: 	Register the Certificate Custom Post Type for NB Doula Course

*/

function nb_register_cpt_certificate(){
	
/*
	Certificate CPT
*/  
	//Declare labels for certificate CPT. 
	$nb_certificate_labels = array(
		'name' => _x('Certificates', 'post type general name', 'doula-course'),
		'singular_name' => _x('Certificate', 'post type singular name', 'doula-course'),
		'add_new' => _x('Add New', 'certificate', 'doula-course'),
		'add_new_item' => __('Add New Certificate', 'doula-course'),
		'edit_item' => __('Edit Certificate', 'doula-course'),
		'new_item' => __('New Certificate', 'doula-course'),
		'all_items' => __('All Certificates', 'doula-course'),
		'view_item' => __('View Certificate', 'doula-course'),
		'search_items' => __('Search Certificates', 'doula-course'),
		'not_found' =>  __('No certificates found', 'doula-course'),
		'not_found_in_trash' => __('No certificates found in Trash', 'doula-course'), 
		'parent_item_colon' => '',
		'menu_name' => __('Certificates', 'doula-course')
	);

	
	$nb_certificate_args = array(
		'labels' => $nb_certificate_labels,
		'public' => true,
		'publicly_queryable' => false,
		'query_var' => true,
		'exclude_from_search' => true,
		'rewrite' => array( 'slug' => _x( 'certificates', 'URL slug', 'doula-course' ) ),
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => 51,
		'menu_icon' => 'dashicons-awards',
		'supports' => array( 'title', 'editor', 'comments', 'author', 'revisions' ), 
		'map_meta_cap'=> true 
	); 
	  
	register_post_type('nb_certificate', $nb_certificate_args);
	  
/*
*
*	Registering Custom Post Status for Certificate post types.
*
*/

	register_post_status( 'completed', array(
		'label'                     => _x( 'Completed', 'nb_certificate' ),
		'public'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>' ),
	) );
	
	register_post_status( 'expired', array(
		'label'                     => _x( 'Expired', 'nb_certificate' ),
		'public'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>' ),
	) );
	
	register_post_status( 'renewed', array(
		'label'                     => _x( 'Renewed', 'nb_certificate' ),
		'public'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Renewed <span class="count">(%s)</span>', 'Renewed <span class="count">(%s)</span>' ),
	) );
	
	
}


add_action( 'init', 'nb_register_cpt_certificate' ); 	
	
?>