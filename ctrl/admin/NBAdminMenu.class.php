<?php
/*
	This is the file where we control the setup specific custom post types. 
	See also mold/NBPostType for where the actual modelling is happening. 
*/

namespace ctrl\admin;

use \modl\admin\NBAdminMenu as AdminMenu;

class NBAdminMenu{
	
	public $pos_offset = 10,	 //position offset
	$set_menu = array(), 
	$unset_menu = array();
 	
	public function __construct( $array, $action ){
		
		$action_menu = $action.'_menu';
		
		$this->$action_menu( $array );
		
	}
	
/*
	Add Menu
	Assess array being passed in and determine what appropriate actions to take. 
*/	
	
	public function add_menu( $array ){
		
		//Every key in this list get's processed as a main page. 
		//then every value in the subarrays gets processed as a sub page for each parent page. 
		
		$pos = intval( $this->pos_offset );
		
		//An alternative to this may be to create another array (set_menus aray) with four values: main or sub, menu_slug, position, parent
		
		foreach( $array as $main_menu => $sub_menu_array ){
			
			$this->set_menu[] = array( 
				'type' 		=> 'main',		// $type
				'slug'		=> $main_menu, 	// $menu_slug
				'pos' 		=> $pos,		// $position
				'parent' 	=> null			// $parent
			);
			
			/* $menu = new AdminMenu();
			$menu->add_menu( $main_menu, $pos ); */
			
			$sub_pos  = 1;
			
			foreach( $sub_menu_array as $menu_val ){
				
				$this->set_menu[] = array( 
					'type' 		=> 'sub',		// $type
					'slug'		=> $menu_val, 	// $menu_slug
					'pos' 		=> $sub_pos,	// $position
					'parent' 	=> $main_menu	// $parent
				);
				
				$sub_pos ++;
				
				/* $sub_menu = new AdminMenu();
				$menu->add_submenu( $menu_val, $main_menu );		 */		
			}
			
			$pos = $pos + 5;
		}
		
		
		$this->set_menus();
	}
	
	
/*
	Remove Menu
*/	
	
	public function remove_menu( $array ){
		
		foreach( $array as $item ){
			
			$menu = new AdminMenu();
			
			$menu->remove_menu( $item );
			
		}
	}
	
	
/*
	Set Menus
*/	
		
	public function set_menus(){
		
		$menu_list = $this->set_menu;
		
		foreach( $menu_list as $m ){
			
			$menu = new AdminMenu();
			
			if( strcmp( $m['type'], 'main' ) == 0 ){
				
				$menu->add_menu( $m['slug'] , $m['pos'] ); //$slug, $pos
				
			}elseif( strcmp( $m['type'], 'sub' ) == 0 ){
				
				$menu->add_submenu( $m['slug'] , $m['parent'] ); //$slug, $parent
				
			}
			
		}
		
	}
	
		
/*
	Unset Menu
*/	
	
	
	public function unset_menus(){
		
	
		
	}
}


?>