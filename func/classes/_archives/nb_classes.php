<?php 
// Loads a list of all available and applicable classes for
// New Beginnings Doula Training interface. 

//Transactions
require_once('nb_transaction.class.php');

//Students 
require_once('nb_student.class.php');

//Admin Tables
require_once('nb_tables.class.php');

//Admin Editors
require_once('nb_editor.class.php');

NB_Editor::init();

?>