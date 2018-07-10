<?php

echo "Cron is being called!";
//Get Day of the Month
date_default_timezone_set( 'America/Phoenix' );


$cron_day_of_month = date('j');//	Day of the month without leading zeros 	1 to 31
$cron_day_of_week = date('w');// 	Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
$cron_month = date('n');//		 	Numeric representation of a month, without leading zeros 	1 through 12


//Registration Invite Management
/*		-1st day of the month (send invites)
		-3rd day of the month (invite reminders)
		-6th day of the month (expire invites, send new invites)
		-8th day of the month (invite reminders)
		-11th day of the month (expire invites, send new invites)
		-13th day of the month (invite reminders)
		-16th day of the month (expire invites, send new invites)
		-18th day of the month (invite reminders)
		-21th day of the month (expire invites, send new invites)
		-23th day of the month (invite reminders)
		-26th day of the month (expire invites)
*/
echo "<br> Today's date is $cron_day_of_month ";
switch($cron_day_of_month){

	case 1:
		$result = load_invite_processor('send_invites_only');
		break;
	
	case 3:
	case 8:
	case 13:
	case 18:
	case 23:
		$result = load_invite_processor('invite_reminders');
		break;
	
	case 6:
	case 11:
	case 16:
	case 21:
		$result = load_invite_processor('expire_invites_and_send_new');
		break;
		
	case 26:
		$result = load_invite_processor('expire_invites_only');
		break;
		
	default:
		//do nothing. 
		$result = 'Not an date to do anything on.';
		break;

}


email_notice( $result );

function email_notice($emlMsg){

		$sent = wp_mail('brent@trainingdoulas.com', 'Notice from DoulaTraining.com NB Crons', $emlMsg);
		
		//If we can't email a message to admin, let's at least log it in the database. 
		if(!$sent){
			$emlMsg = "Failed to send email notice to admin. \r\n". $emlMsg;
		
			//insert nb_log table
			$adminLogRspns = $wpdb->query('CREATE TABLE IF NOT EXISTS nb_admin_log ( `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, `created` TIMESTAMP DEFAULT NOW(), `key` varchar(256), `val` longtext)');

			$dbRspns = $wpdb->query($wpdb->prepare("INSERT INTO nb_admin_log (`key`, `val`) VALUES ('email_notice',  %s ) ", $emlMsg));
		} 
		

} 


function load_invite_processor( $action ){
/* 	include_once('crons/nb_reg_invite.php');
	$nb_invite = new NB_Reg_Invite( $action );
	
	if( $nb_invite->processed() ){
	
		$result = "The Registration Invite Process was successfully sent!";
	
	} else {
		
		$result = "The Registration Invite Process did not successfully send. PLEASE INVESTIGATE.";
		
	}
	echo $result
	return $result; */
}

?>