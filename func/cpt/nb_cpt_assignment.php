<?PHP
/*
	Filename: 		NB CPT ASSIGNMENT
	Created: 		6 Feb 2018
	Last Updated: 	6 Feb 2018
	
	Description: 	Register the Assignment Custom Post Type for NB Doula Course

*/

function nb_register_cpt_assignment(){
	

/*
	Assignment CPT
*/
	//Setup Labels   
	$nb_assignment_labels = array(
		'name' => _x('Assignments', 'post type general name', 'doula-course'),
		'singular_name' => _x('Assignment', 'post type singular name', 'doula-course'),
		'add_new' => _x('Add New', 'assignment', 'doula-course'),
		'add_new_item' => __('Add New Assignment', 'doula-course'),
		'edit_item' => __('Edit Assignment', 'doula-course'),
		'new_item' => __('New Assignment', 'doula-course'),
		'all_items' => __('All Assignments', 'doula-course'),
		'view_item' => __('View Assignment', 'doula-course'),
		'search_items' => __('Search Assignments', 'doula-course'),
		'not_found' =>  __('No assignments found', 'doula-course'),
		'not_found_in_trash' => __('No assignments found in Trash', 'doula-course'), 
		'parent_item_colon' => '',
		'menu_name' => __('Assignments', 'doula-course')
	);

	//Setup all arguments for the nb_assignment cpt
	$nb_assignment_args = array(
		'labels' => $nb_assignment_labels,
		'public' => true,
		'publicly_queryable' => false,
		'query_var' => true,
		'exclude_from_search' => true,
		'rewrite' => array( 'slug' => _x( 'assignment', 'URL slug', 'doula-course' ) ),
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => 53,
		'menu_icon' => 'dashicons-portfolio',
		'supports' => array( 'title', 'editor', 'comments', 'author', 'revisions' ), 
		'capabilities' => array(
			'publish_posts' => 'publish_assignments',
			'edit_posts' => 'edit_assignments',
			'edit_others_posts' => 'edit_others_assignments',
			'delete_posts' => 'delete_assignments',
			'delete_others_posts' => 'delete_others_assignments',
			'read_private_posts' => 'read_private_assignments',
			'edit_post' => 'edit_assignment',
			'delete_post' => 'delete_assignment',
			'read_post' => 'read_assignment',
			'read' => 'read_assignments',
			'edit_private_posts' => 'edit_private_assignments',
			'edit_published_posts' => 'edit_published_assignments',
			'delete_published_posts' => 'delete_published_assignments',
			'delete_private_posts' => 'delete_private_assignments'
		),
		'map_meta_cap'=> true 
	); 
	  
	register_post_type('nb_assignment', $nb_assignment_args);

	
/*
*
*	Registering Custom Post Status for Assignment post types.
*
*/

	register_post_status( 'submitted', array(
		'label'                     => _x( 'Submitted', 'nb_assignment' ),
		'public'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Submitted <span class="count">(%s)</span>', 'Submitted <span class="count">(%s)</span>' ),
	) );
	register_post_status( 'incomplete', array(
		'label'                     => _x( 'Incomplete', 'nb_assignment' ),
		'public'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Incomplete <span class="count">(%s)</span>', 'Incomplete <span class="count">(%s)</span>' ),
	) );
	register_post_status( 'resubmitted', array(
		'label'                     => _x( 'Resubmitted', 'nb_assignment' ),
		'public'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Resubmitted <span class="count">(%s)</span>', 'Resubmitted <span class="count">(%s)</span>' ),
	) );
	register_post_status( 'completed', array(
		'label'                     => _x( 'Completed', 'nb_assignment' ),
		'public'                   => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>' ),
	) );
	
	
}

//INITIALIZE THE ASSIGNMENT CPT.

add_action( 'init', 'nb_register_cpt_assignment' ); 	




//Limit the number of revisions for the assignment post type
// *** NEEDS to be checked for proper functioning. ***

function nb_assignment_revision_limit( $num, $post ) {
    
    return ( 'nb_assignment' == $post->post_type )? 5 : $num ;
}

add_filter( 'wp_revisions_to_keep', 'nb_assignment_revision_limit', 10, 2 );




//Enable commenting on assignments in status other than "Published". 
/// *** NEEDS to be checked for proper functioning. ***

function enable_custom_status_comments()
{
    if( isset( $_GET['post'] ) ) 
    {
        $post_id = absint( $_GET['post'] ); 
        $post = get_post( $post_id ); 
		$post_status = array('submitted','incomplete','resubmitted','completed');
		
        if ( in_array( $post->post_status, $post_status  ) )
            add_meta_box(
                'commentsdiv', 
                __('Comments'), 
                'post_comment_meta_box', 
                'nb_assignment', 
                'normal', 
                'core'
            );
    }
}

add_action( 'admin_init', 'enable_custom_status_comments' );





// Not sure what this function does. 
// *** NEEDS to be checked for proper functioning. ***

function nb_assignment_overview_status( $states ) {
     global $post;
     $arg = get_query_var( 'post_status' );
	 
	 $status_arr = array('submitted','incomplete','resubmitted','completed');
	 
     if( !in_array( $arg, $status_arr ) ){
          if($post->post_status == 'submitted'){
			return array('Submitted');
          }elseif($post->post_status == 'incomplete'){
			return array('Incomplete');
          }elseif($post->post_status == 'resubmitted'){
			return array('Resubmitted');
          }elseif($post->post_status == 'completed'){
			return array('Completed');
		  }
     }
    return $states;
}
add_filter( 'display_post_states', 'nb_assignment_overview_status' );









?>