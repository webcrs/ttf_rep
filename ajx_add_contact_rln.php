<?php
session_start();
include("config.php");
include("db.php");
dbconnect() or send_err_mail("Cannot connect to server".mysql_error(),$PHP_SELF);
$user_id	= $_SESSION[vis_user_id];  
$organiser_id	= $_SESSION[vis_organiser_id];  
$contact_rln_id = add_contact($parent_contact_id,$contact_id);
echo $contact_rln_id;
?>