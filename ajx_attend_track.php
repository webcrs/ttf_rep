<?php
session_start();
include("config.php");
include("db.php");
dbconnect() or send_err_mail("Cannot connect to server".mysql_error(),$PHP_SELF);
$user_id	= $_SESSION[vis_user_id];  
$organiser_id	= $_SESSION[vis_organiser_id];  

if($type=="speaker") {
	
  $reg_sql = "SELECT contact_id FROM $tbltrackSpkRln WHERE contact_id='$contact_id' AND track_id='$track_id' AND track_user_type='$type' ";
  $reg_res = mysql_query($reg_sql) or send_err_mail($reg_sql.mysql_error(),$PHP_SELF);
  $reg_total = mysql_num_rows($reg_res);
  if($reg_total == "0") {
  				$reg_sql = "SELECT contact_id FROM $tbltrackSpkRln WHERE contact_id='$contact_id' AND track_id='$track_id' AND track_user_type='attendee' ";
  				$reg_res = mysql_query($reg_sql) or send_err_mail($reg_sql.mysql_error(),$PHP_SELF);
  				$reg_total = mysql_num_rows($reg_res);
  				if($reg_total == "0") {
    			$insstate_sql = "INSERT INTO $tbltrackSpkRln (track_id, contact_id, track_user_type, status) VALUES ('$track_id', '$contact_id', '$type', 'active')";
    			$insstate_res=mysql_query($insstate_sql) or send_err_mail($insstate_sql.mysql_error(),$PHP_SELF);
    			update_prop_log($PHP_SELF,$insstate_sql, 'INSERT INTO $tbltrackSpkRln');
				$error_str = "0";
	            $error_str = "<font color='#FF0000'>"."Successfully registered.</font>";
				} else {
				$error="2";
				$name = get_contact_name($contact_id);
				$error_str = $name;
				$error_id_str = $contact_id;
                $opp_type='Attendee';    
                $error_str = '<font color="#FF0000">'.$error_str.' is already Registered as '.$opp_type.', If you continue then he will no longer available as '.$opp_type.'. Do you want to continue?</font><br>';
				
				$error_str .= '<button class="btn btn-primary" type="button" name="set_rln_contact" id="set_rln_contact" onClick=javascript:attendTrackReg("'.$user_id.'","'.$track_id.'","'.$type.'","continue"); >Continue</button>'; 
				$error_str .= '&nbsp; &nbsp; <button class="btn btn-success" type="button" name="set_rln_contact" id="set_rln_contact" onClick=javascript:attendTrackReg("'.$user_id.'","'.$track_id.'","'.$type.'","cancel"); >Cancel</button>';
				}
  } else {
	  $error_str = "<font color='#FF0000'>"."You have already registered.</font>";
  }
  
} else {
	
	    //checking track is free or not
	    $track_sql = "SELECT is_free FROM $tbltracks WHERE $tbltracks.track_id='$track_id'";
    	$track_res = mysql_query($track_sql) or send_err_mail($track_sql.mysql_error(),$PHP_SELF); 
    	$track_row = mysql_fetch_array($track_res);
		$is_free = $track_row[is_free];
		
		$reg_sql = "SELECT registrant_type_id FROM $tblregistrants  WHERE contact_id='$contact_id'  AND event_id='$event_id' AND registrant_type_id IN (2,3) AND registrant_status='active'";
     	$reg_res = mysql_query($reg_sql) or send_err_mail($reg_sql.mysql_error(),$PHP_SELF);   
		$reg_total = mysql_num_rows($reg_res);
		
		$date_track_sql = "SELECT track_date FROM $tbltracks WHERE track_id='$track_id' ";
		$date_track_res = mysql_query($date_track_sql) or send_err_mail($date_track_sql.mysql_error(),$PHP_SELF);
		$date_track_row = mysql_fetch_array($date_track_res);   
		$track_date = $date_track_row[track_date];
		
		$track_ticket_sql = "SELECT $tblticketcheckin.ticket_id FROM $tbltickettrackRln,$tblticketcheckin WHERE 		$tbltickettrackRln.ticket_id=$tblticketcheckin.ticket_id AND $tbltickettrackRln.track_id='$track_id' AND $tblticketcheckin.contact_id='$contact_id' AND $tbltickettrackRln.ticket_event_date='$track_date'";
	    $track_ticket_res = mysql_query($track_ticket_sql) or send_err_mail($track_ticket_sql.mysql_error(),$PHP_SELF); 
		$track_ticket_total = mysql_num_rows($track_ticket_res); 
		
		if($reg_total>0 || $is_free==1 || $track_ticket_total>0) { //if reg type is 'seller or speaker' Or track is free
						  
  			$reg_sql = "SELECT contact_id FROM $tbltrackSpkRln WHERE contact_id='$contact_id' AND track_id='$track_id' AND track_user_type='speaker' ";
  			$reg_res = mysql_query($reg_sql) or send_err_mail($reg_sql.mysql_error(),$PHP_SELF);
  			$reg_total = mysql_num_rows($reg_res);
  			if($reg_total == "0") {
    			$insstate_sql = "INSERT INTO $tbltrackSpkRln (track_id, contact_id, track_user_type, status) VALUES ('$track_id', '$contact_id', '$type', 'active')";
    			$insstate_res=mysql_query($insstate_sql) or send_err_mail($insstate_sql.mysql_error(),$PHP_SELF);
    			update_prop_log($PHP_SELF,$insstate_sql, 'INSERT INTO $tbltrackSpkRln');
			} else {
				$name = get_contact_name($contact_id);
				$error_str = $name;
				$error_id_str = $contact_id;
                $opp_type='Speaker';    
                $error_str = '<font color="#FF0000">'.$error_str.' is already Registered as '.$opp_type.', If you continue then he will no longer available as '.$opp_type.'. Do you want to continue?</font><br>';
				
				$error_str .= '<button class="btn btn-primary" type="button" name="set_rln_contact" id="set_rln_contact" onClick=javascript:attendTrackReg("'.$user_id.'","'.$track_id.'","'.$type.'","continue"); >Continue</button>'; 
				$error_str .= '&nbsp; &nbsp; <button class="btn btn-success" type="button" name="set_rln_contact" id="set_rln_contact" onClick=javascript:attendTrackReg("'.$user_id.'","'.$track_id.'","'.$type.'","cancel"); >Cancel</button>';
			}
							
		} else { //if reg type is 'seller or speaker' Or track is free
						  
			$error_str = '<font color="#FF0000">'.get_contact_name($contact_id)." have no permission to attend this Track, Please Check your Tickets</font>";
		}
	
}

echo $error_str;
?>