<?php
	global $wpdb;
	$student = nb_get_student_meta(); 
	
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
	echo $bill_return_string;
	
	echo "<pre>
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
echo ( empty( $student->student_address2 ) )? : "".$student->student_address2; 
echo"
{$student->student_city}, {$student->student_state} {$student->student_postalcode}
phone: {$student->student_phone}
email: {$student->data->user_email}

-------------

Type:		{$ttype}					
Description:	{$transArr[ 'trans_label' ]} 

Amount:		{$transArr[ 'trans_amount' ]}

--------------";
if( !empty( $transArr[ 'pp_txn_id' ] ) ){
	echo "
PayPal Transaction ID: {$transArr[ 'pp_txn_id' ]}
Payment sent via PayPal.com to rachel.leavitt@gmail.com 
";
}
echo "
	
##</pre>";
	
	echo $bill_return_string;
	
	
?>