<?php
session_start();
include("config.php");
include("db.php");
dbconnect() or send_err_mail("Cannot connect to server".mysql_error(),$PHP_SELF);
$user_id	= $_SESSION[vis_user_id];  
$organiser_id	= $_SESSION[vis_organiser_id];
$track_discussion = addslashes($track_discussion); 
$result = addComment($discus_track_id,$discus_event_id,$track_discussion);
?>

                   <table border='0' width='100%' cellspacing="0" cellpadding="0" style="padding:100px;" > 
                    <?php
					$Discus_track_sql = "SELECT $tbltrackDiscussion.contact_id,$tbltrackDiscussion.track_discussion,$tblcontact.contact_prof_pic,$tblcontact.contact_name,$tblcontact.salutation,$tblcontact.first_name,$tblcontact.last_name FROM $tbltrackDiscussion,$tblcontact WHERE $tbltrackDiscussion.status IN ('active') AND $tbltrackDiscussion.track_id='$discus_track_id' AND $tbltrackDiscussion.event_id='$discus_event_id' AND $tbltrackDiscussion.contact_id=$tblcontact.contact_id ORDER BY $tbltrackDiscussion.added_on";
                    $Discus_track_res = mysql_query($Discus_track_sql) or send_err_mail($Discus_track_sql.mysql_error(),$PHP_SELF);
					$Discus_track_total = mysql_num_rows($Discus_track_res);
					if($Discus_track_total > 0) {
                    while($Discus_track_row = mysql_fetch_array($Discus_track_res)) {
					$discuss_contact_id = $Discus_track_row['contact_id'];	 
					$registarnt_type_id = get_registarnt_type_of_this_event($event_id, $discuss_contact_id);
					if($registarnt_type_id=="1") $discuss_profile_page = "buyer_profile.php";
					if($registarnt_type_id=="2") $discuss_profile_page = "seller_profile.php";
					if($registarnt_type_id=="3") $discuss_profile_page = "speaker_profile.php";
					if($registarnt_type_id=="4") $discuss_profile_page = "attendee_profile.php";
					?>
                    <tr>         
                        <td width="31%" align="center"><a href="<?=$discuss_profile_page?>?event_id=<?=$event_id?>&contact_id=<?=$discuss_contact_id?>">
                        <?php if($Discus_track_row['contact_prof_pic'] == '') {?>
                        <img src="no_image.jpg" width="70px" height="80px">
                        <?php } else { ?>
                        <img src="contact_prof_pic/<?=$Discus_track_row['contact_prof_pic']?>" width="70px" height="80px">
                        <?php } ?>
                        <br>
						<?php echo stripslashes($Discus_track_row['salutation'])." ".stripslashes($Discus_track_row['first_name'])." ".stripslashes($Discus_track_row['last_name']); ?></a></td>       
                        <td width="69%" align="left"><?=stripslashes($Discus_track_row['track_discussion'])?></td>    
                    </tr> 
                    <tr >    
                        <td height="10px" colspan="2" align="center" valign="top"></td>
                    </tr> 
                    <?php }  } ?>
                </table>