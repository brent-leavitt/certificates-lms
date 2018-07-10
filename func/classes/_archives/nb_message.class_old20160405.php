<?php 

/*
 *  New Beginnings Message PHP Class
 *	Created on 3 Dec 2015
 *  Updated on 3 Dec 2015
 *
 *	The purpose of this class is to handle recurring processes related to 
 *	messages being sent and handled throughout the New Beginnings Doula Course plugin
 *
 */

//check if WP_user is set.  
 
 
class NB_Message { //Not sure we need the WP_User class, but it could be helpful.
	
	const INSTRUCTOR = 1;
	const WEB_ADMIN = 1; //change on live. 
	
	//Properties first
	public $id = 0;	
	public $mess_date = '00-00-00 00:00:00'; 
	public $type = NULL; //billing, assignment, etc
	public $subject = '';
	public $content = '';
	public $recipient = 0; //user_id
	public $status = 'UNSENT'; 
	public $archived = FALSE; 
	public $active = 'y';
	public $system_ftr_msg = '
--
This notice is an automated message sent from our friendly, system notifications robot. 
New Beginnings Doula Training';
	
	
	//Then Methods

	/**
	 * _construct
	 *
	 * @since 0.1
	 **/
	 
	public function __construct(){
	
		$this->set_date();
		
	}
	
	/**
	 * set_date
	 *
	 * @since 0.1
	 **/
	
	private function set_date(){
	
		$dtz = new DateTimeZone( "America/Phoenix" ); //Your timezone
		$mess_date = new DateTime( date("Y-m-d H:i:s"), $dtz );
		$this->mess_date = $mess_date->format("Y-m-d H:i:s");
		
	}
	
	
	
	/**
	 * Initialization
	 *
	 * @since 0.1
	 **/
	 
	public function init() {

		
	
	}
	
	

	/**
	 * send email
	 *
	 * descrip: email message to recipient
	 *
	 * @since 0.1
	 **/
	public function send_email(){
		
		$student = get_userdata( $this->recipient );
		$email = $student->user_email;
		
		$emlHdrs = array();
		$emlHdrs[] = 'From: New Beginnings <office@trainingdoulas.com>' . "\r\n";
		$emlHdrs[] = 'Reply-To: Rachel Leavitt <rachel@trainingdoulas.com>' . "\r\n"; 
			
		if( ( !empty($this->subject) ) && ( !empty ($this->content) ) )	
			$sent = wp_mail( $email, $this->subject, $this->content, $emlHdrs );
		
		// echo "The value of sent is: $sent.  Email is: $email. \r\n Subject is: {$this->subject}. \r\n Content is {$this->content}. \r\n Headers is $headers.";
		
		return $sent;
		
	}
	
	/**
	 * COMMIT
	 *
	 * descrip: commit message in database for later use
	 *
	 * @since 0.1
	 **/
	 
	public function commit(){
		global $wpdb;
		
		$inserted = $wpdb->insert( 
			'nb_messages', 
			array( 
				'message_date' => $this->mess_date, 
				'message_type' => $this->type, 
				'message_content' => $this->content, 
				'message_recipient' => $this->recipient, 
				'message_status' => $this->status, 
				'message_active' => $this->active
			), 
			array( 
				'%s',
				'%s',
				'%s',
				'%d',
				'%s',
				'%s'
			) 
		);
		
		if( !empty( $inserted ) )
			$this->ID = $wpdb->insert_id;
			
	}
	
	/**
	 * archive
	 *
	 * descrip: make a database record of the message being sent. 
	 *
	 * @since 0.1
	 **/	
	public function archive(){
	
		//add subject line to the beginning of the message. 
		$this->content = "Subject: ". $this->subject ."
=================================
". $this->content;
		
		$this->commit();	
		$this->archived = ( !empty( $this->ID ) )? true : false ; 
	
	}
	
	
	
	/**
	 * mark status
	 *
	 * descrip: updates the status of the message if already recorded in database. 
	 *
	 * @since 0.1
	 **/	
	 public function mark_status( $status ){
		
		if( strcmp( $status, $this->status ) !== 0 ){
			$this->status = $status;
			
			if( !empty( $this->ID ) ){
				
			}
			
		} 
		//check if message is recorded in databse. 
		
		//check status of message in database. 
		
		//check if status is not the same as $status
	
	}
	
	
	/**
	 * COMMENT NOTIFY
	 *
	 * descrip: This processes notifications for when a comment is submitted from either admin or student. 
	 *
	 * @since 2.0
	 **/	
	 
	public function comment_notify( $comment_obj ){
		
		//This is a message about an assignment
		$this->type = 'comment_notify';
		
		//This code figures out what messages to send related to the assignment being submitted. 
		$asmt = get_post( $comment_obj->comment_post_ID );	
		$author = get_user_by( 'email', $comment_obj->comment_author_email );
		//$asmt_meta = array_map( function( $a ){ return $a[0]; }, get_post_meta( $asmt->ID) );
		$course_id = $asmt->post_parent; //
			
		$asmt_url = home_url('/?p=').$course_id."#comment-".$comment_obj->comment_ID;
		$asmt_admin_url = admin_url('post.php?action=edit&post=').$asmt->ID."#comment-".$comment_obj->comment_ID;
		$std_admin_url = admin_url('admin.php?page=edit_student&student_id=').$author->ID;
		$home_url = home_url();
		
		//determine source of comment (admin or student)
		$is_admin = ( is_super_admin( $author->ID ) )? TRUE : FALSE ;
		
		/* ob_start();
		var_dump($asmt);
		$results = ob_flush(); */
		
		if( $is_admin ){ //if the comment was made by an admin

			//student notify
			$this->content = "A new comment from the course instructor has been posted on the following assignment: 

========================
Assignment Title: {$asmt->post_title}
Assignment ID: {$asmt->ID}
Link: {$asmt_url}
=========================
Note: You may need to log-in first ({$home_url}), before using the direct assignment link. 
{$this->system_ftr_msg}";
 
			$this->subject = "New Instructor Comment on {$asmt->post_title}";
			$this->recipient = $asmt->post_author; 
			$this->status = 'POSTED'; 
			$this->active = 'y';
			
			$sent = $this->send_email();
			$this->status .= ( !$sent )? ' UNSENT' : ' SENT' ;
			
			$this->commit();
		
		} else {
		
			//admin notify
			$this->content = "A new comment from <a href='{$std_admin_url}' target='_blank'>{$author->display_name}</a> has been posted on the following assignment: 
<a href='{$asmt_admin_url}' target='_blank'>{$asmt->post_title}</a>"; 
			$this->recipient = self::INSTRUCTOR; 
			$this->status = 'POSTED'; 
			$this->active = 'y';
			$this->commit();
		}
	}
	
	
	/**
	 * ASSIGNMENT_SUBMITTED
	 *
	 * descrip: 
	 *
	 * @since 2.0
	 **/	
	public function assignment_submitted( $asmt_id ){
		
		//This is a message about an assignment
		$this->type = 'assignment_submitted';
		
		//This code figures out what messages to send related to the assignment being submitted. 
		$asmt = get_post( $asmt_id );
		
		$course_id = $asmt->post_parent; //
			
		$asmt_url = home_url('/?p=').$course_id;
		$asmt_admin_url = admin_url('post.php?action=edit&post=').$asmt_id;
		
		
		
		//prepare message. 
$this->content = "Your assignment has been {$asmt->post_status} and is awaiting instructor feedback. A copy of your assignment is being included here for your personal records. Please retain a personal copy of all submitted assignments.

Please allow two or three days for feedback on assignments. Assignment are graded in the order they are received, oldest to newest. 

========================

Assignment Name: {$asmt->post_title}

Assignment: 
{$asmt->post_content}

=======================

Link: {$asmt_url}
Assignment ID: {$asmt->ID}
Student ID: {$asmt->post_author}
Last submitted: {$asmt->post_modified}

=======================";
		
		//Set message recipient
		$this->recipient = $asmt->post_author;
		
		//Set message subject
		$asmt_status = ucfirst($asmt->post_status);
		$this->subject = "Assignment {$asmt_status} - {$asmt->post_title}";
		
		//Send receipt to user
		$sent = $this->send_email();
		
		//Mark Status 
		$this->status = ( !$sent )? : 'SENT';
		$this->active = ( !$sent )? 'y' : 'n' ;
	
		//Record in Database
		$this->archive();
		
		//If not issues encountered processing the message, return true. 
		
		return ( ( $this->status == 'SENT' ) && ( $this->archived == TRUE ) )? TRUE : FALSE; 
	}
	
		
	/**
	 * ASSIGNMENT_GRADED
	 *
	 * descrip: send student notice when an assignment has been graded. 
	 *
	 * @since 2.0
	 **/	
	public function assignment_graded( $asmt_id ){
	 
		//This is a message about an assignment
		$this->type = 'assignment_graded';
		
		//This code figures out what messages to send related to the assignment being submitted. 
		$asmt = get_post( $asmt_id );
		
		
		$course_id = $asmt->post_parent; //
			
		$asmt_url = home_url('/?p=').$course_id;
		$asmt_admin_url = admin_url('post.php?action=edit&post=').$asmt_id;
		
		
		
		//prepare message. 
$this->content = "Your assignment has been {$asmt->post_status} and is awaiting instructor feedback. A copy of your assignment is being included here for your personal records. Please retain a personal copy of all submitted assignments.

Please allow two or three days for feedback on assignments. Assignment are graded in the order they are received, oldest to newest. 

========================

Assignment Name: {$asmt->post_title}

Assignment: 
{$asmt->post_content}

=======================

Link: {$asmt_url}
Assignment ID: {$asmt->ID}
Student ID: {$asmt->post_author}
Last submitted: {$asmt->post_modified}

=======================";
		
		//Set message recipient
		$this->recipient = $asmt->post_author;
		
		//Set message subject
		$asmt_status = ucfirst($asmt->post_status);
		$this->subject = "Assignment {$asmt_status} - {$asmt->post_title}";
		
		//Send receipt to user
		$sent = $this->send_email();
		
		//Mark Status 
		$this->status = ( !$sent )? : 'SENT';
		$this->active = ( !$sent )? 'y' : 'n' ;
	
		//Record in Database
		$this->archive();
		
		//If not issues encountered processing the message, return true. 
		
		return ( ( $this->status == 'SENT' ) && ( $this->archived == TRUE ) )? TRUE : FALSE; 
	}
	
	
	
	/**
	 * EMAIL RECEIPT
	 *
	 * @since 2.0
	 *
	 * This function defines who is receiving the emails and what information to send the receiver.  
	 *
	 **/
	 
	public function payment_received( $action, $info ){
		
		//If no user ID is set before this is called and the action is not admin, then abort. 
		if ( empty( $this->recipient ) && ( $action !== 'admin' ) ){
			
			$sent = false;
			
		} else {
		
			$nbti = ( isset( $info['nbti'] ) )? $info['nbti'] : 0 ; //This isn't set. 
			$tDate = $info['payment_date'];
			$tFrom = $info['first_name'].' '. $info['last_name'];
			$tEmail = $info['payer_email'];
			$tAmount = $info['mc_gross'];
			$tDes = ( isset( $info['item_name'] ) )? $info['item_name'] : $info['txn_type'] ;
			$tPpTxnId = $info['txn_id'];
			$tPayStat = $info['payment_status'];
			$registration_url = home_url('/complete-registration?tx_id=');
			//Standard Headers? 
			
			
			$rcptText = "----------------------------------------------------- 

New Beginnings Transaction ID: $nbti
Date: $tDate 
From:  $tFrom <$tEmail> 		
Amount: $tAmount 
Description: $tDes 
Paypal Transaction ID: $tPpTxnId 
Payment Status: $tPayStat 

-----------------------------------------------------  ";
		
			//$action is the type of email to send.
			switch( $action ){
				case 'new_student':
					$this->subject = 'New Beginnings Doula Training Registration - One More Step';	
					$txnID = $info['txn_id'];
					$this->content = "
Thank you for registering with New Beginnings Doula 
Training! Please complete the registration process 
by clicking on the following link: 
\r\n
{$registration_url}{$txnID} 
(NOTE: You may have already completed this step if 
you were automatically redirected to the registration 
form after your payment was received.)
\r\n
IS THIS A GIFT REGISTRATION? During the holiday season, we 
will take the registration link from above and \"gift wrap\" 
it on a digital gift certificate that you can print off or 
send directly as an email attachment. We will contact you 
within one business day for details regarding the gift 
certificate. 
\r\n
Not interested in the gift certicate? Just forward this 
email to the recipient, or click on the link above and 
complete the registration process for them. 
\r\n
After completing the registration, a username and 
password will be sent to the primary email address 
specified in the registration process. The username 
and password will be used to access course materials 
online. 
\r\n
A copy of the Paypal transaction is include below 
for your records: \r\n
-----------------------------------------------------";				
					break;
					
				case 'admin':
					
					$this->set_admin_recipient(); //Set Web Administrator ID
					$this->subject = 'Admin Notification - NB Doula Training - Payment Receipt';
					$this->content = "
A payment has been processed on 
the trainingdoulas.com website. \r\n
-----------------------------------------------------";
					break;			
					
				case 'payment':
				default:
					$this->subject = 'New Beginnings Doula Training - Payment Receipt';
					$this->content = "
Thank you for your payment to New Beginnings Doula
Training. A copy of your payment receipt is included
below for your records. \r\n
-----------------------------------------------------";
					break;
			}
		
			//Append reciept to the end of the message.
			$this->content .= $rcptText; 

			$sent =  !empty( $this->recipient ) ? (  $this->send_email()  ? : FALSE ) : FALSE ;
			 
		} 
		
		return $sent;
	}

	
	
	/**
	 * 
	 *
	 * descrip: System notices sent to web administrator only. 
	 *
	 * @since 0.1
	 **/	
	public function admin_notice( $subject, $message ){
		
		//Set Web Administrator ID
		$old_send_to = $this->set_admin_recipient();
		
		$this->subject = $subject;
		
		$this->content = $message;

		//Send receipt to user
		$sent = $this->send_email();		
		
		$this->reset_recipient( $old_send_to ); 
		
		return $sent;
	}
	
	
	/**
	 * SET ADMIN RECIPIENT
	 *
	 * descrip: Set Recipient ID to an administrator's ID. Returns original recipient ID. 
	 *
	 * @since 0.1
	 **/	
 	private function set_admin_recipient(){
		//This should be the ID for the web administrator. On course DEV that is 1. 	
		$old_send_to = $this->recipient;
		$this->recipient = self::WEB_ADMIN;
		return $old_send_to; 
	}
		
	/**
	 * RESET RECIPIENT
	 *
	 * descrip: Restores OLD RECIPIENT ID. Used in the admin_notice method. 
	 *
	 * @since 0.1
	 **/	
 	private function reset_recipient( $old_send_to ){
		//This should be reset to whatever ID is sent.  	
		$this->recipient = $old_send_to;
		
	}
	
	
	
}
?>