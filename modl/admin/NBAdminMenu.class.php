<?php 

/*

	This function needs to extend the AdminMenu Class found in the NN_Network plugin. 
	This is the file where we handle the functions that will create the admin menus for the plugin. 
*/

namespace modl\admin;



class NBAdminMenu{
	

	/*
	* 
	*/
	
	public CONST TD = 'certificates_lms'; //Text Domain
	
	public 	$icons = array(
		//https://developer.wordpress.org/resource/dashicons/
		//'menu_slug'	=> 'icon_slug',
		'education'		=> 'welcome-learn-more',
		'publishing' 	=> 'book',
		'accounts' 		=> 'heart',
		'messages' 		=> 'format-chat',
		'finance' 		=> 'chart-line',
		'settings' 		=> 'admin-settings',
		'reports' 		=> 'chart-pie',
		
	),
	$default_slugs = array(
		'dashboard' 	=> 'index.php',
		'posts' 		=> 'edit.php',
		'media' 		=> 'upload.php',
		'pages' 		=> 'edit.php?post_type=page',
		'comments' 		=> 'edit-comments.php',
		'appearance' 	=> 'themes.php',
		'plugins' 		=> 'plugins.php',
		'users' 		=> 'users.php',
		'tools' 		=> 'tools.php',
		'settings' 		=> 'options-general.php',
		
		//Custom Post Types
		'tracks'		=> 'edit.php?post_type='.NB_PREFIX.'track',
		'courses'		=> 'edit.php?post_type='.NB_PREFIX.'course',
		'materials'		=> 'edit.php?post_type='.NB_PREFIX.'material',
		'certificates'	=> 'edit.php?post_type='.NB_PREFIX.'certificate',
		'assignments'	=> 'edit.php?post_type='.NB_PREFIX.'assignment'
	),

	$access = array(
		//slugs that users have access to, this determines $capability variable 
		'admin' => array(
			'publishing',
			'accounts',
			'finance',
			'settings',
			'reports'
		),
		'trainer' => array( 
			'education',
			'messages'
		)
	);

			
	
	
	public function __construct( ){
		
		
	}
	
/*
	Add Menu
	params: $slug = string, $pos = int
*/	
	
	public function add_menu( $slug, $pos ){
		
		//echo "Modl\Admin\NBAdminMenu.class <b>add_menu</b> function called. Slug: $slug and Pos: $pos <br />";
		
		//Setup all paramaters to runn the add_menu_page
			
		//menu_title
		$menu_title = ucwords( str_replace( '_', ' ', $slug ) );
		
		//page_title
		$page_title =  $menu_title. " Overview";
		
		//capability
		$capability = ( in_array( $slug, $this->access['admin'] ) )? 'edit_users' : 'edit_assignments';
		
		//menu slug
		//Menu Slugs can be replaced with PHP files that represent the new page. 
		
		$menu_slug =  $slug;
		
		//callback 
		//  NULL 'cuz $menu_slug loads file. See Plugin Dev manual. 
		
		$callable = array( $this, 'menu_callable' );
		
		//menu icon
		$icon_url = 'dashicons-'.$this->icons[ $slug ];
		
		//position
		$position = $pos;
		
		
		add_menu_page(
			$page_title, 		//string
			$menu_title, 		//string
			$capability,		//string 
			$menu_slug, 		//string 
			$callable,			//callable
			$icon_url,			//string 
			$position			//int 
		);
		
	}
	
	
	
	public function add_submenu( $slug, $parent ){
		
		//echo "Modl\Admin\NBAdminMenu.class <b>add_submenu</b> function called. Slug: $slug and Parent: $parent <br />";
		
		
		//Setup all parameters to run the add_submeu_page
		
		//Parent Slug
		$parent_slug = $parent;
			
		//menu_title
		$menu_title = ucwords( str_replace( '_', ' ', $slug ) );
		
		//page_title
		$page_title =  $menu_title;
		
		//capability
		$capability = 'edit_users';//( in_array( $slug, $this->access['admin'] ) )? 'edit_users' : 'read_posts';
		
		
		//Menu Slugs can be replaced with PHP files that represent the new page. 
		
		//$menu_slug =  'certificates-lms/view/admin/'.$parent.'/'.$slug.'.php';
		if( !isset( $this->default_slugs[ $slug ] ) ){
			//menu slug
			$menu_slug = $slug;
			//callback 
			$callable = array( $this, 'menu_callable' );
		}else{
			//menu slug
			$menu_slug = $this->default_slugs[ $slug ];
			//callable NULL 'cuz $menu_slug loads file. See Plugin Dev manual. 
			$callable = null;
		
			
		}	
		
		add_submenu_page(
			$parent_slug,	//string 
			$page_title,	//string 
			$menu_title,	//string 
			$capability,	//string 
			$menu_slug,		//string 
			$callable	//callable 
		);
		
		//NOT WORKING AS EXPECTED
		if( isset( $this->default_slugs[ $slug ] ) ){
			add_filter( 'parent_file', function($pf) use( $parent_slug ){
				
				//var_dump( $pf );
				
				return 'admin.php?page='.$parent_slug;
				
			}, 999 );
			
		}
	}
	
	
	
/*
	param: $slug //menu slug
	reference: https://developer.wordpress.org/reference/functions/remove_menu_page/
*/	
	
	public function remove_menu( $slug ){
		
		remove_menu_page( $this->default_slugs[ $slug ] );
		
	}
	
	
	public function menu_callable(){
		
		//'certificates-lms/view/admin/'.$slug.'.php';
		global $plugin_page, $title;
		
		echo "<div class='wrap'>
		<h1 id='wp-heading-inline'>$title</h1>";
		
		$path = NB_ROOT_DIR.'view/admin/pages/'.$plugin_page.'.php';
		
		if (file_exists($path))
				require $path;
		
		echo"</div>";
		
		
	}
	
}


?>