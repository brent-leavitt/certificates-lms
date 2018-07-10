<?php
	global $wpdb;
	$student = nb_get_student_meta(); 
	$shortcode = '';
	
	$tx_id =  ( isset( $_GET[ 'tx_id' ] ) )? $_GET[ 'tx_id' ] : null ;
	
	if( !empty( $tx_id ) ){
		$transArr = $wpdb->get_results( 'SELECT * FROM nb_transactions WHERE transaction_id='.intval( $tx_id ).' AND student_id='.$student->ID.' LIMIT 1', ARRAY_A );
		$transArr = $transArr[0];
		foreach( $transArr as $tKey => $tVal ){
			$transArr[$tKey] = stripslashes( $tVal );
		}
	}

	$bill_url = home_url( 'billing/' );
	$bill_return_string =  "<p><a href='{$bill_url}'>&larr; Return to Billing Overview</a></p>";
	$trans_date = date( 'D, F j, Y g:i:s A', strtotime( $transArr[ 'trans_time' ] ) );
	$ttype = $transArr[ 'trans_type' ];
	$shortcode .= $bill_return_string;
	
	$shortcode .= "<pre>
##
	
New Beginnings Childbirth Services, LLC
37465 W Amalfi Ave 
Maricopa, AZ 85138 USA
phone: (+1) 520-568-5955
email: office@trainingdoulas.com

Invoice #:	{$transArr[ 'transaction_id' ]} 
Invoice Date: 	{$trans_date}
-------------

Bill To: 
-------------
{$student->first_name} {$student->last_name}
{$student->student_address}";
$shortcode .= ( empty( $student->student_address2 ) )? : "".$student->student_address2; 
$shortcode .="
{$student->student_city}, {$student->student_state} {$student->student_postalcode}
phone: {$student->student_phone}
email: {$student->data->user_email}

-------------

Type:		{$ttype}					
Description:	{$transArr[ 'trans_label' ]} 

Amount:		{$transArr[ 'trans_amount' ]}

--------------";
if( !empty( $transArr[ 'pp_txn_id' ] ) ){
	$shortcode .= "
PayPal Transaction ID: {$transArr[ 'pp_txn_id' ]}
Payment sent via PayPal.com to rachel.leavitt@gmail.com 
";
}
$shortcode .= "
	
##</pre>";
	
	$shortcode .= $bill_return_string;
	
	return $shortcode;
?>