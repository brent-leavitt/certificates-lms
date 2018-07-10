<?php 

/*
	This is the file where we register our custom post types that we've created for the doula course. 
*/

namespace modl;

class NBPostType{
	
	//Register the following post types: 
	/*
	* Certificates
	* Courses
	* Assignments
	* 
	* 
	* 
	*/
	public CONST TD = 'certificates_lms'; //Text Domain
	
	public 	$cpt_args = array(
		'post_type' => '', 			//
		'post_name' => '', 			//
		'post_name_single' => '', 	//
		'post_item' => '', 			//
		'post_items' => '', 		//
		'description' => '', 		//
		'menu_pos' => 51,			//
		'menu_icon' => 'awards',	//
		'supports' => array( 'title', 'editor', 'page-attributes', 'revisions', 'comments' ),		//
		'cap_posts' => '',			//
		'cap_post' => '',			//
		'rewrite' => ''				//
		
	);

			
	
	
	public function __construct( $args ){
		
		//declare the custom post types here. 
		$this->set_post_type_args( $args );
		$this->register_cpt();
	}
	
	
	public function set_post_type_args( $args ){
		
		$a = $this->cpt_args; 
		
		//Get avaiable parameters sent at time of initialization. 
		foreach( $args as $key => $arg ){
			if( key_exists( $key, $a ) ){
				$a[ $key ] = $arg;
			}
		}
		
		//Then for any empty fields, fill in based on post_type value. 
		if( !empty( $a[ 'post_type' ] ) ){
			$p_name = $a[ 'post_type' ];
			
			//Set Post Name if Empty, Uppercase first letter and add s. 
			if( empty( $a[ 'post_name' ] ) )
				$a[ 'post_name' ] = ucfirst( $p_name ).'s';
				
			//Set Post Name Single, Uppercase first letter
			if( empty( $a[ 'post_name_single' ] ) )
				$a[ 'post_name_single' ] = ucfirst( $p_name );
			
			//Set Post Item, Uppercase first letter
			if( empty( $a[ 'post_item' ] ) )
				$a[ 'post_item' ] = ucfirst( $p_name );
			
			//Set Post Items, Uppercase first letter, make plural
			if( empty( $a[ 'post_items' ] ) )
				$a[ 'post_items' ] = ucfirst( $p_name ).'s';
			
			//Set Post Description
			if( empty( $a[ 'description' ] ) )
				$a[ 'description' ] =  ' This is the '.$p_name.' post type.';
			
			//Set rewrite
			if( empty( $a[ 'rewrite' ] ) )
				$a[ 'rewrite' ] =  $p_name.'s';
			
			//Set Capabilities Posts
			if( empty( $a[ 'cap_posts' ] ) )
				$a[ 'cap_posts' ] = $p_name.'s';
			
			//Set Capabilities Posts
			if( empty( $a[ 'cap_post' ] ) )
				$a[ 'cap_post' ] = $p_name;
			
		}
				
		$this->cpt_args = $a; 
		
		
	}
	
	
	public function get_post_type_args(){
		
		
	}
	
	
	public function register_cpt( ){
		
		$args = $this->cpt_params();
		$post_type = $this->cpt_args[ 'post_type' ];
		
		register_post_type( $post_type, $args );
		
	}
	
	
	/*
	*
	*
	* 	Params: $args = array( 
	*		'post_type' => (str)'', 		//Cardinal Name for the Post Type
	*		'post_name' => (str)'', 		//Display Name of the Post Type
	*		'post_name_single' => (str)'', 	//Singular Version of the Display Name
	*		'post_item' => (str)'', 		//Display Name of an individual Item of the Post Type
	*		'post_items' => (str)'', 		//Display Name of Items (plural) of the Post Type
	*	)
	*	Returns: $labels (array) 	
	*
	*/
	public function cpt_labels(){
		
		$a = $this->cpt_args;
		
		//build labels arguments array. 
		
		$labels = array(
			'name' => _x( $a[ 'post_name' ], 'post type general name', $this->TD),
			'singular_name' => _x( $a[ 'post_name_single' ], 'post type singular name', $this->TD),
			'add_new' => _x('Add New', $a[ 'post_type' ], $this->TD),
			'add_new_item' => __('Add New '.$a[ 'post_item' ], $this->TD),
			'edit_item' => __('Edit '.$a[ 'post_item' ], $this->TD),
			'new_item' => __('New '.$a[ 'post_item' ], $this->TD),
			'all_items' => __('All '.$a[ 'post_items' ], $this->TD),
			'view_item' => __('View '.$a[ 'post_item' ], $this->TD),
			'search_items' => __('Search '.$a[ 'post_items' ], $this->TD),
			'not_found' =>  __('No '.$a[ 'post_items' ].' found', $this->TD),
			'not_found_in_trash' => __('No '.$a[ 'post_items' ].' found in Trash', $this->TD), 
			'parent_item_colon' => '',
			'menu_name' => __( $a[ 'post_name' ], $this->TD)
		);
		
		return $labels;
	}
	
	
	/*
	*
	*
	* 	Params: $args = array( 
	*		'post_type' => (str)'', 		//Cardinal Name for the Post Type
	*		'description' => (str)'', 		//
	*		'menu_pos' => (int)'', 			//Menu Position
	*		'menu_icon' => (str)'', 		//Sub-string from dashicons set. 'dashicons-' is already included
	*		'supports' => (arr)'', 			//Editor items included with this post type
	*		'rewrite' => (str)'', 			//Rewrite Slug
	*		'labels' => (arr)'', 			//an array of label variables
	*		
	*	)
	*	Returns: $post_type_args (array) 	
	*
	*/
	
	public function cpt_params( ){
		
		$a = $this->cpt_args;
		
		$labels = $this->cpt_labels();
		
		$params = array(
			'labels' => $labels,
			'description' => $a[ 'description' ],
			'public' => true ,
			'publicly_queryable' => true,
			'query_var' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'has_archive' => true, 
			'hierarchical' => true,
			'menu_position' => $a[ 'menu_pos' ],
			'menu_icon' => 'dashicons-'. $a[ 'menu_icon' ],
			'supports' => $a[ 'supports' ],  
			'capability_type'=>'post',
			'capabilities' => array(
				'publish_posts' => 'publish_'.$a[ 'cap_posts' ],
				'edit_posts' => 'edit_'.$a[ 'cap_posts' ],
				'edit_others_posts' => 'edit_others_'.$a[ 'cap_posts' ],
				'delete_posts' => 'delete_'.$a[ 'cap_posts' ],
				'delete_others_posts' => 'delete_others_'.$a[ 'cap_posts' ],
				'read_private_posts' => 'read_private_'.$a[ 'cap_posts' ],
				'edit_post' => 'edit_'.$a[ 'cap_post' ],
				'delete_post' => 'delete_'.$a[ 'cap_post' ],
				'read_post' => 'read_'.$a[ 'cap_post' ],
				'read' => 'read_'.$a[ 'cap_posts' ],
				'edit_private_posts' => 'edit_private_'.$a[ 'cap_posts' ],
				'edit_published_posts' => 'edit_published_'.$a[ 'cap_posts' ],
				'delete_published_posts' => 'delete_published_'.$a[ 'cap_posts' ],
				'delete_private_posts' => 'delete_private_'.$a[ 'cap_posts' ]
			), 
			'map_meta_cap'=> true, 
			'rewrite' => array( 'slug' => $a[ 'rewrite' ] )
		);
		
		return $params;
		
	}
	
}



/*
add_action( 'init', 'nb_register_CPT', 10, 0 ); 

if( !function_exists( 'nb_register_CPT' ) ){
	
	function nb_register_CPT() {
		
		
		
		//	Course CPT
		 

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

		$nb_course_args = array(
			'labels' => $nb_course_labels,
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

		
		//	Assignment CPT
		
		  
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
		  

		
		//	Certificate CPT
		  

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
}

}


*/


 
 /*
function nb_post_status(){
	
	
	//Registering Custom Post Status for Assignment post types.
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
	
	
	//Registering Custom Post Status for Certificate post types.
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
add_action( 'init', 'nb_post_status' );

*/


//Limit the number of revisions for the assignment post type

/* add_filter( 'wp_revisions_to_keep', 'nb_assignment_revision_limit', 10, 2 );

function nb_assignment_revision_limit( $num, $post ) {
    
    if( 'asignment' == $post->post_type ) {
	$num = 5;
    }
    return $num;	
} */

//Enable commenting on assignments in status other than Published. 
//Is this working? I think so, but need to double check. 
/* 
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
                'assignment', 
                'normal', 
                'core'
            );
    }
}

add_action( 'admin_init', 'enable_custom_status_comments' ); */
/* 

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

 */


// Stopped here in migration to individual files for each CPT. 6Feb18
/* 
add_filter( 'manage_edit-course_columns', 'my_edit_course_columns' ) ;

function my_edit_course_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Section' ),
		'course_type' => __( 'Type' ),
		'course_access' => __( 'Access Level' ),
	);

	return $columns;
} */


/* 
add_action( 'manage_course_posts_custom_column', 'my_manage_course_columns', 10, 2 );

function my_manage_course_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		// If displaying the 'course_type' column. 
		case 'course_type' :

			//Get the post meta. 
			$cType = get_post_meta( $post_id, 'course_type', true );

			switch(	$cType ){
			
			case 0:
				echo __( 'Content' );
				break;
			
			case 1:
				echo __( 'Assignment' );
				break;
				
			case 2: 
				echo __('Section Head');
				break;
				
			case 3:
				echo __( 'Other' );
				break;
					
			case 4:
				echo __( 'Manual' );
				break;
					
			case 5:
				echo __( 'Certification' );
				break;
				
			default:
				echo __( 'Unknown' );
				break;
			}
			
				
			break;

		case 'course_access':	
			$cAccess= get_post_meta( $post_id, 'course_access', true );

			switch(	$cAccess ){
				
			case 3:
				echo __( 'All Courses' );
				break;
	
			case 2: 
				echo __('Main and Childbirth');
				break;
			
			case 1:
				echo __( 'Main Course' );
				break;
				
			default:
				echo __( '-not set-' );
				break;
				
			
			}
		// Just break out of the switch statement for everything else. 
		
		default :
			break;
	}
} */

/* 
function set_course_post_type_admin_order($wp_query) {
  if (is_admin()) {

    $post_type = $wp_query->query['post_type'];

    if ( $post_type == 'nb_course' && empty($_GET['orderby'])) {
      	$wp_query->set('orderby', 'menu_order');
      	$wp_query->set('order', 'ASC');
    }

  }
}
add_filter ( 'pre_get_posts', 'set_course_post_type_admin_order' ); */

// Add quick edit functionality for Course Access
/* add_action('quick_edit_custom_box',  'nb_course_access_quick_edit', 10, 2);
 
function nb_course_access_quick_edit($column_name, $post_type) {
    if ($column_name != 'course_access') return;
    ?>
    <fieldset class="inline-edit-col-left">
        <div class="inline-edit-col">
            <span class="title">Course Access</span>
            <input id="cAccess_noncename" type="hidden" name="cAccess_noncename" value="" />
            <select id="cAccess" name="cAccess">
				<option value="1">Main Course</option>
				<option value="2">Main and Childbirth</option>
				<option value="3">All Courses</option>
			</select> 
        </div>
    </fieldset>
     <?php
} */
 /* 
 // Add to our admin_init function 
add_action('save_post', 'nb_save_quick_edit_data');   
 
function nb_save_quick_edit_data($post_id) {     
  // verify if this is an auto save routine.         
  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )          
      return $post_id;         
  // Check permissions     
  if ( 'nb_course' == $_POST['post_type'] ) {         
    if ( !current_user_can( 'edit_page', $post_id ) )             
      return $post_id;     
  } else {         
    if ( !current_user_can( 'edit_post', $post_id ) )         
    return $post_id;     
  }        
  // Authentication passed now we save the data       
  if (isset($_POST['cAccess']) && ($post->post_type != 'revision')) {
        $my_fieldvalue = esc_attr($_POST['cAccess']);
        if ($my_fieldvalue)
            update_post_meta( $post_id, 'course_access', $my_fieldvalue);
        else
            delete_post_meta( $post_id, 'course_access');
    }
    return $my_fieldvalue;
}
  */
 /*  
add_action('admin_footer', 'nb_quick_edit_javascript');

function nb_quick_edit_javascript() {
    global $current_screen;
    if (($current_screen->post_type != 'nb_course')) return;
 
    ?>
<script type="text/javascript">
function set_cAccess_value( fieldValue, nonce ) {
        // refresh the quick menu properly
        inlineEditPost.revert();
        console.log( fieldValue );
        jQuery( '#cAccess' ).val( fieldValue ).change();
}
</script>
 <?php 
}  */
/* 
// Add to our admin_init function 
add_filter('post_row_actions', 'nb_expand_quick_edit_link', 10, 2);   
function nb_expand_quick_edit_link($actions, $post) {     
    global $current_screen;     
    if (($current_screen->post_type != 'nb_course')) 
        return $actions;
    $nonce = wp_create_nonce( 'cAccess_'.$post->ID);
    $myfielvalue = get_post_meta( $post->ID, 'course_access', TRUE);
    $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';     
    $actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '"';
    $actions['inline hide-if-no-js'] .= " onclick=\"set_cAccess_value('{$myfielvalue}')\" >";
    $actions['inline hide-if-no-js'] .= __( 'Quick Edit' );
    $actions['inline hide-if-no-js'] .= '</a>';
    return $actions;
} */
 /* 
add_action( 'loop_start', 'nb_course_loop' );
add_action( 'loop_end', 'nb_course_loop' );
 
 //This function is called twice in each loop. 
 
function nb_course_loop( &$obj ) {
    if( get_query_var('post_type') == 'nb_course' ) {
		global $post, $current_user;
		
		// Start output buffering at the beginning of the loop and abort
		if ( 'loop_start' === current_filter() )			
			return ob_start(); //Stops here on first time (loop start) and returns the ob_start
		
		// At the end of the loop, we end the buffering and save into a var
		$loop_content = ob_get_clean();
		
		$s_active = current_user_can('student_current');
			
		if(!$s_active){
			 wp_redirect( home_url()."/inactive-student-notice" ); 
			 exit;
			
		} else {
		
			if ( is_archive() || is_search() ) {
				if ( is_main_query() ){
					if( is_archive() )
						$obj->max_num_pages = 0; //Kill pagination on archives. 
				
					include( plugin_dir_path( __FILE__ ) . "../templates/course/course-overview.php" );
				}
			 } else { //if not archive or search result, probably single. 
				
				$course_type_num = intval( get_post_meta($post->ID, 'course_type', true) );
				$course_type = 'content';
				
				switch($course_type_num){
					case 5: 
					case 4: 	
					case 2: 
						$course_type = 'section';
						break;
					case 1: 
						$course_type = 'assignment';
						break;
					case 0:
					default: 
						$course_type = 'content';
						break;
				}
				
				$course_access = intval( get_post_meta($post->ID, 'course_access', true) );
				$student_access = intval( get_user_meta( $current_user->ID, 'course_access', true ) );
				 
				// echo "PAGE IS SINGLE! Course type is: $course_type ." ;
				if( is_main_query() ){
					include( plugin_dir_path( __FILE__ ) . "../templates/course/course-navigation.php" );
					
					if( $student_access >= $course_access ){
						include( plugin_dir_path( __FILE__ ) . "../templates/course/course-$course_type.php" );
					} else {
						include( plugin_dir_path( __FILE__ ) . "../templates/course/course-access.php" );
					}
				}
				
			}// endif;
		}
    }	
} */
/* 
//Make CPT's so that they can have shortlinks:
if( !function_exists( 'nb_cpt_shortlinks' ) ) {
  
  // Allow shortlinks to be retrieved for pages and custom post types
  
	function nb_cpt_shortlinks( $shortlink, $id, $context, $allow_slugs=true ) {
		
		 // If query is the context, we probably shouldn't do anything
		 
		if( 'query' == $context )
			return $shortlink;

		$post = get_post( $id );
		$post_id = $post->ID;

		
		 // If this is a standard post, return the shortlink that was already built
		
		if( 'post' == $post->post_type )
			return $shortlink;

		
		 // Retrieve the array of publicly_queryable, non-built-in post types
		 
		$post_types = get_post_types( array( '_builtin' => false, 'publicly_queryable' => true ) );
		if( in_array( $post->post_type, $post_types ) || 'page' == $post->post_type )
			$shortlink = home_url('?p=' . $post->ID);

		return $shortlink;
	}
}
add_filter( 'get_shortlink', 'nb_cpt_shortlinks', 10, 4 );

 */
//Messaging actions on comments submitted for assignments
/* 
function nb_asmt_comments( $comment_id, $comment_obj ){	
	
	if( strcmp( get_post_type( $comment_obj->comment_post_ID ), 'assignment' ) == 0 ){
	
		$msg = new NB_Message();
		
		$msg->comment_notify( $comment_obj );
		
	}
}

add_action( 'wp_insert_comment', 'nb_asmt_comments' ,10 ,2 );

 */
//Theme specific modifications for Pinnacle Theme by Kadence
/* 
add_filter( 'kadence_page_title', 'nb_course_page_titles' );

function nb_course_page_titles( $title ){
	global $post, $nb_page_title;
	
	if( get_query_var('post_type') == 'nb_course' ) {
	
		if ( is_archive() || is_search() ) {
			$title = "Course Manuals";
			
         } else { //if not archive or search result, probably single. 
			
			$title = get_the_title( $post->post_parent );
			
			
        }// endif;
    }	
	$nb_page_title = $title;
	
	return $title;
}

 */

?>