<?php 
// Loads a list of all available and applicable classes for
// New Beginnings Doula Training interface. 

//Transactions
require_once('classes/nb_transaction.class.php');

//Students 
require_once('classes/nb_student.class.php');

//Admin Tables
require_once('classes/nb_tables.class.php');


//Admin Editors
//require_once('nb_editor.class.php');
//These aren't actually classes, so they are being moved to the main functions folder.

//Assignment Map
include_once('classes/nb_assignment_map.class.php');

//Assignment 
include_once('classes/nb_assignment.class.php');

//Messages
include_once('classes/nb_message.class.php');

//Walker for Manuals
include_once('classes/nb_section_list.class.php');

//Widgets
include_once('classes/nb_widgets.class.php');

//$nbEditor = new NB_Editor();


?>