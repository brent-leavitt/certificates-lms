<?php
// Dynamically build course menus here 
// based off of user setting and access priveleges. 


// Filter wp_nav_menu() to add additional links and other output - THIS WORKS. 
//http://wordpress.stackexchange.com/questions/121309/how-do-i-programatically-insert-a-new-menu-item

function nb_nav_menu_items( $items ) {
	global $current_user;
	
	$first_name = get_user_meta( $current_user->ID, 'first_name', true );
	$home_url = home_url();
	$logout_url = str_replace( 'wp-login.php?action', 'check-in.php?go', wp_logout_url( $home_url ) );
   
	$links_array = array(
		'link_url'=>$home_url,
		'link_name'=>'Welcome, '.$first_name,
		'link_slug'=>'welcome',
		'progress_link' => array(
				'link_url'=>$home_url.'/progress-report/',
				'link_name'=>'Progress Report',
				'link_slug'=>'progress-report'
		),
		'billing_link' => array(
				'link_url'=>$home_url.'/billing/',
				'link_name'=>'Billing Overview',
				'link_slug'=>'billing'
		),
		'profile_link' => array(
				'link_url'=>$home_url.'/account-profile/',
				'link_name'=>'Account Profile',
				'link_slug'=>'profile'
		),
		'logout_link' => array(
				'link_url'=> $logout_url,
				'link_name'=>'Log Out',
				'link_slug'=>'log-out'
		)
	);
	
	$links = nb_build_menu_link( $links_array );

   // Build menu.
    $items =  $links . $items;
    
	//print_pre($items);
	
	return $items;
}

add_filter( 'wp_nav_menu_main-menu_items', 'nb_nav_menu_items' );


function nb_build_menu_link( $links = array() ){
	
	$link = '<li class="menu-item-'.$links['link_slug'].'"><a href="'.$links['link_url'].'">'.$links['link_name'].'</a>';
	
	$sublinks = '';		
	foreach( $links as $lkey => $lval){ //time to recurse through sub areas. 
		if( is_array( $lval ) )
			$sublinks .= nb_build_menu_link( $lval );	
	}
	
	if( !empty( $sublinks ) ){
		$link .='<ul class="sf-dropdown-menu">'; //this class is theme specific to the PINNACLE THEME.
		$link .= $sublinks;	
		$link .='</ul>';
	} 
	
	$link .= '</li>';
	
	return $link;
}


//Addons to Nav_Menu_Roles plugin


/*
 * Add custom roles to Nav Menu Roles menu list
 * param: $roles an array of all available roles, by default is global $wp_roles 
 * return: array
 */
function kia_new_roles( $roles ){
  $roles['ca-one-key'] = 'Level 1 Access';
  $roles['ca-two-key'] = 'Level 2 Access';
  $roles['ca-three-key'] = 'Level 3 Access';
  
  return $roles;
}
add_filter( 'nav_menu_roles', 'kia_new_roles' );

/*
 * Change visibilty of each menu item
 * param: $visible boolean
 * param: $item object, the complete menu object. Nav Menu Roles adds its info to $item->roles
 * $item->roles can be "in" (all logged in), "out" (all logged out) or an array of specific roles
 * return boolean
 */
 
function kia_item_visibility( $visible, $item ){
	global $current_user;
	
	if( $current_user->ID != null ){
		$sid = $current_user->ID;
		$student = get_userdata($sid); 
		$course_access = intval($student->course_access);
	}
			
	if( isset( $item->roles ) && is_array( $item->roles ) && in_array( 'ca-one-key', $item->roles ) )
		return( $course_access >= 1 )? true : false ; 	
		
	if( isset( $item->roles ) && is_array( $item->roles ) && in_array( 'ca-two-key', $item->roles ) )
		return( $course_access >= 2 )? true : false ; 
	
	if( isset( $item->roles ) && is_array( $item->roles ) && in_array( 'ca-three-key', $item->roles ) )
		return( $course_access >= 3 )? true : false ;
	 
	return $visible;
}
add_filter( 'nav_menu_roles_item_visibility', 'kia_item_visibility', 10, 2 );


?>