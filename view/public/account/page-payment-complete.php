<?php
// Template Name: Payment Complete Page
// Created on: 21 June 2013
// Created by: Brent Leavitt for NBDT
//

//Collect $_GET data from string, 
//REFERENCE: ?tx=8U832199DH075882N&st=Completed&amt=20.00&cc=USD&cm=20p&item_number=

global $wpdb;

$cleared = false;

$rqArr = array( 'tx','st','amt','cc','cm' );

foreach( $rqArr as $rqKey ){
	if( !isset( $_REQUEST[$rqKey] ) )
		go_out( 0, $cleared );
}

$trsArr = $_REQUEST;

$tx_id = $trsArr['tx'];
$tx_status = $trsArr['st'];
$tx_amount = $trsArr['amt'];
$tx_currency = $trsArr['cc'];
$tx_course = $trsArr['cm'];

//The problem is that the transaction doesn't always get recorded into the database before this call is executed to look for it. If the transaction is not found, we abort early. 


$txQ1 = $wpdb->prepare("SELECT student_id FROM nb_transactions WHERE pp_txn_id = %s LIMIT 1", $tx_id);
$txR1 = $wpdb->get_var( $txQ1 );

if( $txR1 != NULL ){
	//continue the checking process: 
	if( ( strcmp($tx_status,'Completed') == 0 ) && (  strcmp( $tx_currency, 'USD' ) == 0) ){
		$prc1 = intval( $tx_amount );
		$prc2 = intval( substr( $tx_course, 0, -1 ) );
		$cleared = ( gmp_cmp( $prc1, $prc2 ) !== 0 )? : true ;		
	}
}

go_out( $tx_id, $cleared );

function go_out( $tx_id, $cleared ){
	$go_url = ( $cleared ) ? 'complete-registration?tx_id='.$tx_id :'payment-completed/' ;//If you can validate registration, let them through. Else, send them to a generic page. 
	
	$go_url = home_url( $go_url ); 
	wp_redirect( $go_url ); exit;
}
?>