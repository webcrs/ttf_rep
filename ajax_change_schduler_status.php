<?php
session_start();
set_time_limit(0);
include("config.php");
include("db.php");
dbconnect() or send_err_mail("Cannot connect to server".mysql_error(),$PHP_SELF);

$user_id	  = $_SESSION[vis_user_id];
$organiser_id = $_SESSION[vis_organiser_id];
$status 	  = $_REQUEST['status'];
$schd_id 	  = $_REQUEST['schd_id'];
$statusid 	  = $_REQUEST['statusid'];
$act_type 	  = $_REQUEST['act_type'];

$start_hrs 	  = $_REQUEST['start_hrs'];
$start_minute = $_REQUEST['start_minute'];

$alt_frm_time  = "$start_hrs:$start_minute";
$meet_duration = $_REQUEST['meet_duration'];
$alt_to_time   = strtotime("+$meet_duration minutes", strtotime($alt_frm_time));
$alt_to_time   = date('H:i:s', $alt_to_time);



$updtStr ='';
if($status == 'cancel'){
		if($statusid==2){
			$updateStatusId_sender   = 4;
			$updateStatusId_receiver = 4;
		}
		else if($statusid==5){
			$updateStatusId_sender   = 7;
			$updateStatusId_receiver = 7;
		}   
		else{
			$updateStatusId_sender   = 9;
			$updateStatusId_receiver = 9;
		}
}
else if($status == 'reschedule'){
		if($statusid==2){
			$updateStatusId_sender	=5; // resch req received
			$updateStatusId_receiver=6; // resch req sent
		}
		else if($statusid==5 && $act_type=='sender'){
			$updateStatusId_sender	=6;
			$updateStatusId_receiver=5;
		}
		else if($statusid==5 && $act_type=='receiver'){
			$updateStatusId_sender	=5;
			$updateStatusId_receiver=6;
		}
		$updtStr =", alternate_from_time1='$alt_frm_time', alternate_to_time1='$alt_to_time'";
}
else if($status == 'accept'){
		if($statusid==2){
			$updateStatusId_sender	=3;
			$updateStatusId_receiver=3;
		}
		else if($statusid==5){
			$updateStatusId_sender	=8;
			$updateStatusId_receiver=8;
			$updtStr =", from_time=alternate_from_time1, to_time=alternate_to_time1";
		}
}

$selRtQry2 = "select * from $tblschedules WHERE schedule_id='$schd_id'"; 
$selRtRes2 = mysql_query($selRtQry2) or send_err_mail($selRtQry2.mysql_error(),addslashes($PHP_SELF));	
while($selRtRow2= mysql_fetch_assoc($selRtRes2)){
		$i=0;$fields2=$colvalues2='';
		$fields2=$colvalues2=array();

		foreach ($selRtRow2 as $key2 => $value2){ 
			if($key2 != 'schedule_id') { 
				$fields2[$i]	= $key2; 
				$colvalues2[$i] = "'".$value2."'"; 
			} 
			$i++; 
		} 		
		$fields2    = implode(",",$fields2); 
		$colvalues2 = implode(",",$colvalues2);
		 
		$addRt_sql2 = "insert into `$tblschedules` ($fields2) values ($colvalues2) "; 
		$addRt_res2 = mysql_query($addRt_sql2) or send_err_mail($addRt_sql2.mysql_error(),addslashes($_SERVER['PHP_SELF'])); 
		$new_sch_id = mysql_insert_id();
		//update_prop_log($addRt_sql2, 'inserted booking details while block- confirm option from viewall page'); 	
}	


 $schduleListQry ="UPDATE $tblschedules SET schedule_status='edited' WHERE schedule_id='$schd_id'";
 $schduleListRes =mysql_query($schduleListQry); 
 
 $schduleListQry ="UPDATE $tblschedules SET parent_id='$schd_id', sender_status='$updateStatusId_sender', receiver_status='$updateStatusId_receiver', 
 last_modified_user='$user_id', is_overlap_req=0  $updtStr WHERE schedule_id='$new_sch_id'";
 $schduleListRes =mysql_query($schduleListQry); 

if($act_type=='sender')			$updateStatusId = $updateStatusId_sender;
else if($act_type=='receiver')	$updateStatusId = $updateStatusId_sender;


 $selstatusQ = "SELECT * FROM $tblScheduleStatusMaster WHERE status_id='$updateStatusId'";
 $selstatusRes = mysql_query($selstatusQ);
 $selstatusROW = mysql_fetch_array($selstatusRes);
 echo $selstatusROW['status_name'];
?>
