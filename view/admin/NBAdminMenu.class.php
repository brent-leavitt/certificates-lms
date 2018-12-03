<?php

/*
	Admin Menus Control Page	
		
*/

namespace view\admin;

use \ctrl\admin\NBAdminMenu as AdminMenu;
 
Class NBAdminMenu{
	
	public $remove_menus = array(
		'posts',
		'media',
		'pages',
		'comments',
		'appearance',
		'plugins',
		'tools',
		'users',
		'settings',
	), 
	$add_menus = array(
		'education' => array(
			'current_submissions',
			'my_students',
			'assignments',
			'coaching_schedule',
			'certificate_generator',
		), 
		'accounts' => array(
			'staff',
			'students',
			'instructor_student_tool',
			'new_account'
		),
		'messages' => array(
			'messaging_tool',
			'automated_messages',
			'message_templates',
			'messaging_logs',
		),
		'publishing' => array( 
			'tracks',
			'courses',
			'materials',
			'certificates',
			'pages',
			'media'
		),
		'finance' => array(
			'invoices',
			'transactions',
			'new_financal',
			'payment_logic',
		),
		'reports' => array(
			'financial_reports',
			'educational_reports',
			'misc_reports',
			'report_settings',
		),
		'settings' => array(
			'LMS_settings',			
			'payment_gateways',
			'certificate_settings',
			'WP_core_menus',
		)
	);
	
	
	
	public function __construct(){
		
		add_action('admin_menu', array( $this, 'process_menus' ), 99);
		
	}
	
	public function process_menus( ){
				
		$this->add_menus();
		$this->remove_menus();
		
	}
	
	
	public function add_menus(){
		
		$menu = new AdminMenu( $this->add_menus , 'add' );
		
		/* $menu = new AdminMenu();
		$menus_added = $menu->add_menu( $this->add_menus ); */
		
	}
	
	
	public function remove_menus(){
		
		$menu = new AdminMenu( $this->remove_menus , 'remove' );
		
		/* $menu = new AdminMenu();
		$menus_removed = $menu->remove_menu( $this->remove_menus ); */
		
	}
	
	
	
	
}



?>