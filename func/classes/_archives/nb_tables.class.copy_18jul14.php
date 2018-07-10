<?php 

/*
 *  New Beginnings Tables PHP Classes
 *	Created on 10 May 2013
 *  Updated on 18 July 2013
 */

//Our class extends the WP_List_Table class, so we need to make sure that it's there
if(!class_exists('WP_List_Table')){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}

class NB_Students_Tables extends WP_List_Table{

	//Override default class constructor
	public function __construct(){
		
		//$screen = ( isset( $GLOBALS['hook_suffix'] ) )? get_current_screen(): null;
		
		
		
		parent::__construct( array(
		'singular'=>'student',
		'plural'=>'students',
		'ajax'=> true,
		'screen'=> array( 'id' => 'toplevel_page_students' )
		
		));		
	}

	/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	 
	public function extra_tablenav( $which ) {
		if ( $which == "top" ){
			//The code that goes before the table is here	
			echo ' <form method="post">
				<input type="hidden" name="page" value="my_list_test" />';
				$this->search_box('search', 'search_id'); 
			echo '</form>';
		}
		if ( $which == "bottom" ){
			//The code that goes after the table is there
			//echo"Hi, I'm after the table";
		}
	}
	
	
	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	public function get_columns() {
		$columns = array(
			'cb'      				=>	'<input type="checkbox" />', 
			'nb_student_fullname'	=>	__( 'Name' ),
			'nb_student_email'		=>	__( 'Email' ),
			'nb_student_date'		=>	__( 'Start Date' ),
			'nb_student_status'		=>	__( 'Status' ),
			'nb_student_payment'	=>	__( 'Last Payment Received' ),
			'nb_student_grades'		=>	__( 'Grades' )
		);
		
		return $columns;
	}
	
	/**
	* Decide which columns to activate the sorting functionality on
	* @return array $sortable, the array of columns that can be sorted by the user
	*/
	
	//This needs some work.
 	public function get_sortable_columns() {
		return $sortable = array(
            'nb_student_fullname'   => 'display_name',
			'nb_student_email'    	=> 'user_email',
			'nb_student_date'    	=> 'user_registered',
		);
	} 
	
	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	public function prepare_items() {
		global $usersearch;
		
		$usersearch = isset( $_REQUEST['s'] ) ? trim( $_REQUEST['s'] ) : '';
		$role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';
		$users_per_page = $this->get_items_per_page( 'users_per_page' );

		$paged = $this->get_pagenum();

		/*
		 * Pagination 
		*/
		$per_page = 5;
		$current_page = $this->get_pagenum();
		$total_items = count($this->example_data);
		 
		// only ncessary because we have sample data
		$this->found_data = array_slice($this->example_data,(($current_page-1)*$per_page),$per_page);
		 
		$this->set_pagination_args( array(
		'total_items' => $total_items, //WE have to calculate the total number of items
		'per_page' => $per_page //WE have to determine how many items to show on a page
		) );
		$this->items = $this->found_data;
		
		
		$args = array(
			'number' => $users_per_page,
			'offset' => ( $paged-1 ) * $users_per_page,
			'cap' => 'student',
			'exclude' => 1, //exclude super admin who's userId is 1.
			'search' => $usersearch,
			'fields' => 'all_with_meta'
		);
		
		if ( '' !== $args['search'] )
				$args['search'] = '*' . $args['search'] . '*';

		if ( isset( $_REQUEST['orderby'] ) )
				$args['orderby'] = $_REQUEST['orderby'];

		if ( isset( $_REQUEST['order'] ) )
				$args['order'] = $_REQUEST['order'];

				
		// Query the user IDs for this page
		$wp_user_search = new WP_User_Query( $args );

		$this->items = $wp_user_search->get_results();


		
		$this->set_pagination_args( array(
			'total_items' => $wp_user_search->get_total(),
			'per_page' => $users_per_page,
		) );	

	}
	
	/**
	 * Display the rows of students in the table
	 * @return string, echo the markup of the rows
	 */
	public function display_rows() {

		//Get the students registered in the prepare_items method
		$students = $this->items;

		//Get the columns registered in the get_columns and get_sortable_columns methodsc
		list( $columns, $hidden ) = $this->get_column_info();


		$nb_table_output = '';
		
		//Loop for each student
		if(!empty($students)){foreach($students as $student){
/*  		print('<pre>');
			print_r($student);
			print('</pre>'); */
			//Open the line
			$nb_table_output .= '<tr id="student_'.$student->ID.'">';
			foreach ( $columns as $column_name => $column_display_name ) {

				//Style attributes for each col
				$class = "class='$column_name column-$column_name'";
				$style = "";
				if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
				$attributes = $class . $style;

				//edit link
				$editlink  = '/wp-admin/admin.php?page=edit_student&amp;student_id='.(int)$student->ID; //Not sure where this is being called...

				//Display the cell
				switch ( $column_name ) {
					case "cb":	$nb_table_output .=  '<th scope="row" class="check-column"><input type="checkbox" /></th>';	break;
					case "nb_student_id":	$nb_table_output .=  '<td '.$attributes.'>'.stripslashes($student->ID).'</td>';	break;
					case "nb_student_fullname": $nb_table_output .=  '<td '.$attributes.'><a href="'.$editlink.'">'.stripslashes($student->display_name).'</a></td>'; break;
					case "nb_student_email": $nb_table_output .=  '<td '.$attributes.'>'.stripslashes($student->user_email).'</td>'; break;
					case "nb_student_date": $nb_table_output .=  '<td '.$attributes.'>'.stripslashes($student->user_registered).'</td>'; break;
					case "nb_student_status": $nb_table_output .=  '<td '.$attributes.'>';
						$nb_table_output .=  ($student->allcaps['student_current'] == 1)? 'Current' : 'Inactive';
						$nb_table_output .=  '</td>'; break; //fix these.
					case "nb_student_payment": 
					
						$rcvd = get_user_meta($student->ID, 'payments_received', true);
						$rcvd = ($rcvd === '1/1' )? 'full' : $rcvd;
						$rcvd = ($rcvd === '12/12' )? 'full' : $rcvd;
						
						$lastPymt = get_user_meta($student->ID, 'last_payment_received', true);
						$lastPymtTime = strtotime($lastPymt);
						//30 days = (30 * 24 * 60 * 60)
						$currentTime = time();
						$_30DaysBack = $currentTime - (30 * 24 * 60 * 60); 
						$_60DaysBack = $currentTime - (60 * 24 * 60 * 60); 
						
						$lateNote = '';
						
						if(($lastPymtTime < $_30DaysBack ) && ($rcvd != 'full')){
							$lateNote = ' style="color: orange;"';
						}
						
						if(($lastPymtTime < $_60DaysBack ) && ($rcvd != 'full')){
							$lateNote = ' style="color: red;"';
						}
						
						
						
						//if last payment is more than 30 days ago. 
						
						//if last payment is more than 60 days ago. 
						
						
						$nb_table_output .=  '<td '.$attributes.'><span'.$lateNote.'>'.$lastPymt.'('. $rcvd . ')</span></td>'; 
						break; //fix these.
					case "nb_student_grades": $nb_table_output .=  '<td '.$attributes.'><a href="/wp-admin/admin.php?page=edit_grades&amp;student_id='.(int)$student->ID.'">grades</a></td>'; break;
					
				}
			}

			//Close the line
			$nb_table_output .= '</tr>';
		}}
		
		print( $nb_table_output );
	}
	
}


class NB_Transaction_Tables extends WP_List_Table{

	//Override default class constructor
	public function __construct(){
		
		//$screen = ( isset( $GLOBALS['hook_suffix'] ) )? get_current_screen(): null;
		
		
		
		parent::__construct( array(
		'singular'=>'transaction',
		'plural'=>'transactions',
		'ajax'=> true,
		'screen'=> array( 'id' => 'toplevel_page_students' )
		
		));		
	}

		/**
	 * Add extra markup in the toolbars before or after the list
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	 
	public function extra_tablenav( $which ) {
		$sid = (isset($_REQUEST['student_id']))?$_REQUEST['student_id']:null;
	
		if ( $which == "top" ){
			//The code that goes before the table is here	
			
			if($sid != null)
				echo '<div class="new_trans"><a class="secondary" href="admin.php?page=add_transaction&amp;student_id='.$sid.'" >Add New Transaction</a></div>';
			echo ' <form method="post">
				<input type="hidden" name="transaction" value="student_transactions_list" />';
/* 				$this->search_box('search', 'search_id');  */
			echo '</form>';
		}
		if ( $which == "bottom" ){
			//The code that goes after the table is there
			//echo"Hi, I'm after the table";
		}
	}
	
	
	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	public function get_columns() {
		$columns = array(
			'cb'      				=>	'<input type="checkbox" />', 
			'trans_id'				=>	__( 'ID' ),
			'trans_date'			=>	__( 'Date' ),
			'trans_amount'			=>	__( 'Amount' ),
			'trans_label'			=>	__( 'Description' ),
			'trans_type'			=>	__( 'Type' )
		);
		
		return $columns;
	}
	
	
	public function prepare_items(){
		global $wpdb;
		
		$student_id = $_REQUEST['student_id'];
		
		$test_query = $wpdb->get_results('SELECT * FROM `nb_transactions` WHERE student_id='.$student_id);
		

		
		$this->items = $wpdb->get_results('SELECT * FROM nb_transactions WHERE student_id='.$student_id.' LIMIT 40');
	
	}
	
	public function display_rows() {

		//Get the students registered in the prepare_items method
		$transactions = $this->items;

		//Get the columns registered in the get_columns and get_sortable_columns methods
		list( $columns, $hidden ) = $this->get_column_info();


		$nb_table_output = '';
		
		//Loop for each student
		if(!empty($transactions)){foreach($transactions as $transaction){
/* 			print('<pre>');
			print_r($transaction);
			print('</pre>'); */
			//Open the line
			$nb_table_output .= '<tr id="transaction_'.intval($transaction->transaction_id).'">';
			foreach ( $columns as $column_name => $column_display_name ) {

				//Style attributes for each col
				$class = "class='$column_name column-$column_name'";
				$style = "";
				if ( in_array( $column_name, $hidden ) ) $style = ' style="display:none;"';
				$attributes = $class . $style;

				//edit link
				$editlink  = '/wp-admin/admin.php?page=edit_transaction&amp;trans_id='.intval($transaction->transaction_id); //Not sure where this is being called...

				//Display the cell
				switch ( $column_name ) {
					case "cb":	$nb_table_output .=  '<th scope="row" class="check-column"><input type="checkbox" /></th>';	break;
					case "trans_id":	$nb_table_output .=  '<td '.$attributes.'><a href="'.$editlink.'">'.stripslashes($transaction->transaction_id).'</a></td>';	break;
					case "trans_date": $nb_table_output .=  '<td '.$attributes.'>'.stripslashes($transaction->trans_time).'</td>'; break;
					case "trans_amount": $nb_table_output .=  '<td '.$attributes.'>'.stripslashes($transaction->trans_amount).'</td>'; break;
					case "trans_label": $nb_table_output .=  '<td '.$attributes.'>'.stripslashes($transaction->trans_label).'</td>'; break;
					case "trans_type": $nb_table_output .=  '<td '.$attributes.'>'.stripslashes($transaction->trans_type).'</td>'; break;
					
					
				}
			}

			//Close the line
			$nb_table_output .= '</tr>';
		}}
		
		print( $nb_table_output );
	}
	
	
}



/*
			'nb_student_id'=>__('ID'),
			'nb_student_username'=>__('Username'),
			'nb_student_fullname'=>__('Name'),
			'nb_student_email'=>__('Email'),
			'nb_student_status'=>__('Status'),
			'nb_student_payment'=>__('Payments')

*/
?>