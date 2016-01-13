<?php
session_start();
include("config.php");
include("db.php");
dbconnect() or send_err_mail("dbconnect - Cannot connect to the Server",$PHP_SELF); 

     $id=intval($_REQUEST['id']);
     $organiser_sql = "select * from $tblcontact where contact_id='$id'";
     $organiser_res = mysql_query($organiser_sql) or send_err_mail($organiser_sql.mysql_error(),$PHP_SELF);  
	 $organiser_row = mysql_fetch_array($organiser_res);
     $contact_prof_pic = $organiser_row[contact_prof_pic];
	 unlink("organiser/contact_prof_pic/".$contact_prof_pic);
	 
	 $insstate_sql = "update $tblcontact set contact_prof_pic='' where contact_id ='$id'";
	 $insstate_res=mysql_query($insstate_sql) or send_err_mail($insstate_sql.mysql_error(),$PHP_SELF); 
	 
?>
	  <input type="file" id="prof_pic" name="prof_pic"  required="required" class="form-control col-md-7 col-xs-12" value=""  ><input type="hidden" name="hid_prof_pic_flag"  id="hid_prof_pic_flag" value="0" >