<?php
/*
* 	IPN handler that processes new student registrations. 
*   Created on 10 June 2013
*/
	
	//This allows us to use wordpress to handle the ipn request. 
	add_action( 'template_redirect', 'nb_queryVarsListener' );
	add_filter( 'query_vars',  'nb_queryVar' );

	function nb_queryVar($public_query_vars) {
		$public_query_vars[] = 'foo'; // For IPN Relay
		$public_query_vars[] = 'opp'; // For Cron Job Access
		return $public_query_vars;
	}

	function nb_queryVarsListener() {
		//Check that the query var is set and is the correct value.
		if (isset($_GET['foo']) && $_GET['foo'] == 'bar'){

			include "ipn/ipn_relay.php";
			//Stop WordPress entirely
			exit;
		}
		
		if(isset($_GET['opp']) && $_GET['opp'] == '123'){
			//Run NB Cron Tasks Such as invoicing and scheduled registration invites. 
			include "nb_crons.php";
			//Stop the rest of Wordpress. 
			exit;
		}
	}

?>