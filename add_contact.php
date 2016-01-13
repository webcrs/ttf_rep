<?php
error_reporting(0);
session_start();
set_time_limit(0);
if (!isset($_SESSION[vis_user_id])) {
    $msg = "<meta http-equiv=\"Refresh\" content=\"0;url=index.php\">";
    echo $msg;
    exit;
}
include("config.php");
include("db.php");
dbconnect() or send_err_mail("Cannot connect to server" . mysql_error(), $PHP_SELF);
$user_id = $_SESSION[vis_user_id];
$profpath = "contact_prof_pic";
$err = 0;
if (isset($_POST['hid_update']) && $_POST['hid_update'] == "addnew") {
    $email_official = addslashes($email_official);
     $chk_sql1 = "SELECT * FROM $tblorganisercontactRln WHERE contact_id='$_SESSION[vis_user_id]'";
        $chk_res1 = mysql_query($chk_sql1) or send_err_mail($chk_sql1 . mysql_error(), $PHP_SELF);
        while ($Sql_stat_row1 = mysql_fetch_array($chk_res1)) {
            $organiser_id = $Sql_stat_row1[organiser_id];
        }
    $chk_sql = "SELECT contact_id FROM $tblcontact WHERE contact_email_official='$email_official'";
    $chk_res = mysql_query($chk_sql) or send_err_mail($chk_sql . mysql_error(), $PHP_SELF);
    if (mysql_num_rows($chk_res) == 0) {
        
        $salutation = addslashes($txt_salutation);
        $first_name = addslashes($txt_first_name);
        $last_name = addslashes($txt_last_name);
        $name = $first_name.' '.$last_name;
        $contact_type = 'individual';
        $address = addslashes($txt_contact_address);
        $contact_mobile = $txt_contact_mobile;
        $contact_email_personal = addslashes($email_personal);
        $contact_designation = addslashes($contact_designation);
        $contact_desc = addslashes($txt_desc);
        $txt_url = addslashes($txt_url);
        $linkedin_id = addslashes($linkedin_id);
        $fb_id = addslashes($fb_id);
        $twitter_id = addslashes($twitter_id);
        $instagram_id = addslashes($instagram_id);

        // ********************** UPLOAD Photo starts
        $prof_pic = "";
        if (trim($_FILES['prof_pic']['name'])) {
            $prof_pic = $_FILES['prof_pic']['name'];
            $prof_pic = preg_replace('/\s+/', '_', $prof_pic);
            $prof_pic = rand(9999, 99999) . $prof_pic;
            $tmp_name = $_FILES["prof_pic"]["tmp_name"];
            $upldstatus = move_uploaded_file($tmp_name, "$profpath/$prof_pic");
        }
        // ************************ Photo UPLOADING ends

        $insstate_sql = "INSERT INTO $tblcontact (salutation, contact_name, first_name, last_name, contact_type, contact_mobile, contact_email_official, contact_email_personal, contact_address, contact_country, contact_state, contact_city, contact_linkedin_id, contact_fb_id, contact_twitter_id, contact_instagram_id, contact_prof_pic, contact_desc, contact_url, contact_designation, contact_status, added_by,added_on) VALUES ('$salutation','$name', '$first_name', '$last_name','$contact_type', '$contact_mobile', '$email_official', '$contact_email_personal', '$address', '$sel_country', '$sel_state', '$sel_city', '$linkedin_id', '$fb_id', '$twitter_id', '$instagram_id', '$prof_pic', '$contact_desc', '$txt_url', '$contact_designation', 'active', '$_SESSION[user_id]','$CurGmtDT')";
        $insstate_res = mysql_query($insstate_sql) or send_err_mail($insstate_sql . mysql_error(), $PHP_SELF);
        $ref_contact_id = mysql_insert_id();
        update_prop_log($PHP_SELF, $insstate_sql, 'INSERT INTO $tblcontact');
      
       
        //$txt_username = addslashes($txt_username);
        //$txt_password = addslashes($txt_password);
        $txt_objective = addslashes($txt_objective);
        $rln_insert_sql = "INSERT INTO $tblorganisercontactRln (organiser_id, contact_id, objective, added_by, added_on) VALUES ('$organiser_id', '$ref_contact_id', '$txt_objective', '$_SESSION[vis_user_id]','$CurGmtDT')";
        $rln_insert_res = mysql_query($rln_insert_sql) or send_err_mail($rln_insert_sql . mysql_error(), $PHP_SELF);
        update_prop_log($PHP_SELF, $rln_insert_sql, 'INSERT INTO $tblorganisercontactRln');
        //$errmsg='Contact added successfully';
        //$err=1;
        
         $is_prim_contact = 0;
         $is_contact_person = 0;
         $rln_insert_sql = "INSERT INTO $tblcontactcontactRln (parent_contact_id, contact_id, relationship, is_prim_contact, is_contact_person) VALUES ('$_SESSION[vis_user_id]', '$ref_contact_id', '$contact_designation', '$is_prim_contact', '$is_contact_person')";
         $rln_insert_res = mysql_query($rln_insert_sql) or send_err_mail($rln_insert_sql . mysql_error(), $PHP_SELF);
         update_prop_log($PHP_SELF, $rln_insert_sql, 'INSERT INTO $tblcontactcontactRln');
    } else {

        $chk_row = mysql_fetch_array($chk_res);
        $contact_id = $chk_row['contact_id'];
        $chk_rln_sql = "SELECT contact_id FROM $tblorganisercontactRln WHERE contact_id='$contact_id' AND organiser_id='$organiser_id'";
        $chk_rln_res = mysql_query($chk_rln_sql) or send_err_mail($chk_rln_sql . mysql_error(), $PHP_SELF);
        if (mysql_num_rows($chk_rln_res) == 0) {

//            $txt_username = addslashes($txt_username);
//            $txt_password = addslashes($txt_password);
            $txt_objective = addslashes($txt_objective);
            $rln_insert_sql = "INSERT INTO $tblorganisercontactRln (organiser_id, contact_id, objective, added_by, added_on) VALUES ('$organiser_id', '$contact_id', '$txt_objective', '$_SESSION[vis_user_id]','$CurGmtDT')";
            $rln_insert_res = mysql_query($rln_insert_sql) or send_err_mail($rln_insert_sql . mysql_error(), $PHP_SELF);
            update_prop_log($PHP_SELF, $rln_insert_sql, 'INSERT INTO $tblorganisercontactRln');
            $errmsg='Contact added successfully';
            $err=1;
        }
        else{
            $errmsg='Contact is already exist with this Email Id';
            $err=1;
        }
    }
    header('Location:./manage_contacts.php');
} // if($submit == "Submit") ENDS
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- Meta, title, CSS, favicons, etc. -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Event System | </title>

        <!-- Bootstrap core CSS -->

        <link href="css/bootstrap.min.css" rel="stylesheet">

        <link href="fonts/css/font-awesome.min.css" rel="stylesheet">
        <link href="css/animate.min.css" rel="stylesheet">

        <!-- Custom styling plus plugins -->
        <link href="css/custom.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/maps/jquery-jvectormap-2.0.1.css" />
        <link href="css/icheck/flat/green.css" rel="stylesheet" />
        <link href="css/floatexamples.css" rel="stylesheet" type="text/css" />

        <script src="js/jquery.min.js"></script>
        <script src="js/nprogress.js"></script>
        <script >
            NProgress.start();
        </script>
        <style type="text/css">
            a:hover{text-decoration: none;}
            .fontHigh {
                color: #2A3F54;
            }
            .style_new {font-weight: bold}
        </style>
        
        <script language="JavaScript" type="text/javascript">

            function validate()
            {
                frm = document.calform;
                if (check_ph_num(frm.txt_contact_mobile.value) == false) {
                    alert("Please Enter Digits in Phone Number");
                    frm.txt_contact_mobile.value = '';
                    frm.txt_contact_mobile.focus();
                    return false;
                }
                else if (check_email('email_official') == false)
                {
                    frm.email_official.value = '';
                    frm.email_official.focus();
                    return false;
                }
                else if (check_email('email_personal') == false)
                {
                    frm.email_personal.value = '';
                    frm.email_personal.focus();
                    return false;
                }
                else
                {
                    frm.hid_update.value = "addnew";
                    frm.submit();
                    return true;
                }
            }

            function check_email(email) {

                frm = document.calform;
                var str = document.getElementById(email).value;
                var strArr = new Array();
                strArr = str.split(',');
                var arrLen = strArr.length;
                var x = 0;
                var emailCheck = 1;
                for (x = 0; x < arrLen; x++) {
                    emailAddress = strArr[x];
                    emailAddress = emailAddress.replace(/^\s+|\s+$/g, '');
                    if (emailAddress != '') {
                        if (echeck(emailAddress) == false) {
                            emailCheck = 0;
                            document.getElementById(email).focus();
                            break;
                        }
                    }
                }
                if (emailCheck == 0) {
                    return false;
                }
                else if (emailCheck == 1) {
                    return true;
                }
            }
            function echeck(str)
            {
                var at = "@"
                var dot = "."
                var lat = str.indexOf(at)
                var lstr = str.length
                var ldot = str.indexOf(dot)
                if (str.indexOf(at) == -1) {
                    alert("Invalid E-mail ID")
                    return false
                }
                if (str.indexOf(at) == -1 || str.indexOf(at) == 0 || str.indexOf(at) == lstr) {
                    alert("Invalid E-mail ID")
                    return false
                }
                if (str.indexOf(dot) == -1 || str.indexOf(dot) == 0 || str.indexOf(dot) == lstr) {
                    alert("Invalid E-mail ID")
                    return false
                }
                if (str.indexOf(at, (lat + 1)) != -1) {
                    alert("Invalid E-mail ID")
                    return false
                }
                if (str.substring(lat - 1, lat) == dot || str.substring(lat + 1, lat + 2) == dot) {
                    alert("Invalid E-mail ID")
                    return false
                }
                if (str.indexOf(dot, (lat + 2)) == -1) {
                    alert("Invalid E-mail ID")
                    return false
                }
                if (str.indexOf(dot, (lat + 2)) == -1) {
                    alert("Invalid E-mail ID")
                    return false
                }
                if (lstr == ldot || (ldot + 1) == lstr) {
                    alert("Invalid E-mail ID")
                    return false
                }
                return true
            }

            function changeStatus(val)
            {
                if (val == '0')
                {
                    alert('Select Status');
                    return false;
                }
                hid_contact_ids = document.getElementById('hid_contact_ids').value;
                product_idArr = new Array();
                product_idArr = hid_contact_ids.split(",");
                flag = 0;
                for (i = 0; i < product_idArr.length; i++)
                {
                    if (document.getElementById('chk_contact_id_' + product_idArr[i]).checked == true)
                    {
                        flag = 1;
                    }
                }
                if (flag == 0)
                {
                    alert('Contacts are not selected');
                    return false;
                }
                form1 = document.getElementById('calform');
                document.getElementById('hid_update').value = 'update';
                form1.submit();
            }

            function popup_edit(contact_id)
            {
                window.open('contact_add.php?contact_id=' + contact_id, 'Add Contact', 'width=950,height=800,scrollbars=yes,resizable=yes');
            }

            function donone()
            {
            }

            function stripslashes(str) {

                return (str + '')
                        .replace(/\\(.?)/g, function (s, n1) {
                            switch (n1) {
                                case '\\':
                                    return '\\';
                                case '0':
                                    return '\u0000';
                                case '':
                                    return '';
                                default:
                                    return n1;
                            }
                        });
            }


            function check_ph_num(fieldvalue) {
                frm = document.calform;
                var valid = "0123456789";
                var ok = "yes";
                var temp;
                for (var i = 0; i < fieldvalue.length; i++) {
                    temp = "" + fieldvalue.substring(i, i + 1);
                    if (valid.indexOf(temp) == "-1")
                        ok = "no";
                }
                if (ok == "no")
                    return false;
                else
                    return true;
            }


            function getstate_fil(country_id)
            {
                var strURL = "getstate.php?country_id=" + country_id;
                var req = GetXmlHttpObject();
                if (req)
                {
                    req.onreadystatechange = function ()
                    {
                        if (req.readyState == 4)
                        {
                            if (req.status == 200)
                            {
                                document.getElementById('statediv_fil').innerHTML = req.responseText;
                            }
                            else
                            {
                                alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                            }
                        }
                    }
                    req.open("GET", strURL, true);
                    req.send(null);
                }
            }


            function getcity_fil(state_id)
            {
                var strURL = "getcity.php?state_id=" + state_id;
                var req = GetXmlHttpObject();
                if (req)
                {
                    req.onreadystatechange = function ()
                    {
                        if (req.readyState == 4)
                        {
                            if (req.status == 200)
                            {
                                document.getElementById('citydiv_fil').innerHTML = req.responseText;
                            }
                            else
                            {
                                alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                            }
                        }
                    }
                    req.open("GET", strURL, true);
                    req.send(null);
                }
            }
            function getproduct_fil(contact_id)
            {
                var strURL = "getproduct.php?contact_id=" + contact_id;
                var req = GetXmlHttpObject();
                if (req)
                {
                    req.onreadystatechange = function ()
                    {
                        if (req.readyState == 4)
                        {
                            if (req.status == 200)
                            {
                                document.getElementById('productdiv_fil').innerHTML = req.responseText;
                            }
                            else
                            {
                                alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                            }
                        }
                    }
                    req.open("GET", strURL, true);
                    req.send(null);
                }
            }
            function email_exists(email)
            {
                var strURL = "check_email.php?email=" + email;
                var req = GetXmlHttpObject();
                if (req)
                {
                    req.onreadystatechange = function ()
                    {
                        if (req.readyState == 4)
                        {
                            if (req.status == 200)
                            {
                                var a = req.responseText;
                                if(a == 1)
                                {
                                    alert('Email Already Exists!!');
                                    $('#email_official').val('');
                                    $('#email_official').focus();
                                }
                            }
                            else
                            {
                                alert("There was a problem while using XMLHTTP:\n" + req.statusText);
                            }
                        }
                    }
                    req.open("GET", strURL, true);
                    req.send(null);
                }
            }
            function GetXmlHttpObject()
            {
                var xmlHttp = null;
                try
                {
                    // Firefox, Opera 8.0+, Safari
                    xmlHttp = new XMLHttpRequest();
                }
                catch (e)
                {
                    // Internet Explorer
                    try
                    {
                        xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
                    }
                    catch (e)
                    {
                        xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
                    }
                }
                return xmlHttp;
            }
        </script>

        <!--[if lt IE 9]>
            <script src="../assets/js/ie8-responsive-file-warning.js"></script>
            <![endif]-->

        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
              <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
              <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
            <![endif]-->

    </head>


    <body class="nav-md">
        <form name="calform" id="calform" method="post" action="<? echo $PHP_SELF; ?>" enctype="multipart/form-data" onsubmit="return validate();">
            <div class="container body">


                <div class="main_container">

                    <div class="col-md-3 left_col">
                        <div class="left_col scroll-view">
                            <?php include('side_menu.php'); ?>
                            <?php include('ileft_footer.php'); ?> 
                        </div>
                    </div>

                    <!-- top navigation -->
                    <?php include("itop.php"); ?>
                    <!-- /top navigation -->

                    <!-- page content -->
                    <div class="right_col" role="main">
                        <div class="">

                            <div class="page-title">
                                <div class="title_left" style="width:99%; padding-bottom:10px;"><?php include("include_main_link.php"); ?></div>
                                <div class="title_right" style="width:1%; padding-bottom:10px;"></div>
                            </div>
                            <div class="clearfix"></div>

                            <script type="text/javascript">
                                $(document).ready(function () {
                                    $('#birthday').daterangepicker({
                                        singleDatePicker: true,
                                        calender_style: "picker_4"
                                    }, function (start, end, label) {
                                        console.log(start.toISOString(), end.toISOString(), label);
                                    });
                                });
                            </script>
                            <div class="row">
                                <div class="col-md-12 col-xs-12">
                                    <div class="x_panel">
                                        <div class="x_title" id="add_company">
                                            <h2>Add Contact</h2>
                                            <ul class="nav navbar-right panel_toolbox">
                                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                                </li>
                                                <li class="dropdown">
                                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                                    <ul class="dropdown-menu" role="menu">
                                                        <li><a href="#">Settings 1</a>
                                                        </li>
                                                        <li><a href="#">Settings 2</a>
                                                        </li>
                                                    </ul>
                                                </li>
                                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                                </li>
                                            </ul>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="x_content">
                                            <!-- content starts-->

                                            <span class="form-horizontal form-label-left">
                                                <br />
                                                <?php if ($err == "1") { ?>
                                                    <div class="form-group">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <strong>
                                                                <font color="#FF0000">
                                                                <?php echo $errmsg; ?>
                                                                </font>
                                                            </strong>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="contact-name">Salutation <font color="#FF0000">*</font>
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="txt_contact_name" name="txt_salutation" class="form-control col-md-7 col-xs-12" required="required">
                                                        </div>
                                                    </div>
                                                <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="contact-name">First Name <font color="#FF0000">*</font>
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="txt_contact_name" name="txt_first_name" class="form-control col-md-7 col-xs-12" required="required">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="contact-name">Last Name <font color="#FF0000">*</font>
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="txt_contact_name" name="txt_last_name" class="form-control col-md-7 col-xs-12" required="required">
                                                        </div>
                                                    </div>
                                                <input type="hidden" id="sel_contact_type" name="sel_contact_type" value="company" class="form-control col-md-7 col-xs-12" required="required">
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Address </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <textarea id="txt_contact_address" class="form-control" name="txt_contact_address"  ></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                    <?php
                                                    if (!isset($sel_country) || !trim($sel_country))
                                                        $sel_country = 101;
                                                    if (!isset($state_fil) || !trim($state_fil))
                                                        $state_fil = 'all';
                                                    ?>
                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Country <font color="#FF0000">*</font></label>
                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <select name="sel_country" class="form-control" id="sel_country" onChange="javascript: getstate_fil(this.value);" style="width:90%;" required="required">
                                                            <?php
                                                            $sQl_1 = "select * from $tblcountry order by country_name ";
                                                            $sQl_1_res = mysql_query($sQl_1)
                                                                    or send_err_mail($sQl_1 . mysql_error(), $PHP_SELF);
                                                            while ($sQl_1_row = mysql_fetch_array($sQl_1_res)) {
                                                                echo "<option value='$sQl_1_row[country_id]' ";
                                                                if ($sel_country == $sQl_1_row[country_id])
                                                                    echo " selected ";
                                                                echo " >$sQl_1_row[country_name]</option> ";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">State <font color="#FF0000">*</font></label>
                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <span id="statediv_fil">
                                                            <select name="sel_state" class="form-control"  id="sel_state"  style="width:90%;" onChange="javascript: getcity_fil(this.value);" required="required">
                                                                <option value="">Select State</option>
                                                                <?php
                                                                $Sql_stat = "SELECT * FROM $tblstate WHERE country_id='$sel_country' and state_status='Active' order by state_name";
                                                                $Sql_stat_res = mysql_query($Sql_stat) or send_err_mail($Sql_stat . mysql_error(), $PHP_SELF);
                                                                while ($Sql_stat_row = mysql_fetch_array($Sql_stat_res)) {
                                                                    echo "<option value='$Sql_stat_row[state_id]'";
                                                                    if ($sel_state == $Sql_stat_row[state_id])
                                                                        echo "selected ";
                                                                    echo " >$Sql_stat_row[state_name]</option> ";
                                                                }
                                                                ?>
                                                            </select>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">City <font color="#FF0000">*</font></label>
                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <span id="citydiv_fil">
                                                            <select name="sel_city" class="form-control"  id="sel_city"  style="width:90%;" required="required">
                                                                <option value="">Select City</option>
                                                                <?php
                                                                $Sql_stat = "SELECT * FROM $tblcity WHERE state_id='$sel_state' and city_status='Active' order by city_name";
                                                                $Sql_stat_res = mysql_query($Sql_stat) or send_err_mail($Sql_stat . mysql_error(), $PHP_SELF);
                                                                while ($Sql_stat_row = mysql_fetch_array($Sql_stat_res)) {
                                                                    echo "<option value='$Sql_stat_row[city_id]'";
                                                                    if ($sel_city == $Sql_stat_row[city_id])
                                                                        echo "selected ";
                                                                    echo " >$Sql_stat_row[city_name]</option> ";
                                                                }
                                                                ?>
                                                            </select>
                                                        </span>
                                                    </div>
                                                </div>

                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Mobile  <font color="#FF0000">*</font>
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="txt_contact_mobile" name="txt_contact_mobile"   class="form-control col-md-7 col-xs-12"  required="required">
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Official Email Id  <font color="#FF0000">*</font>
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="email_official" name="email_official"   class="form-control col-md-7 col-xs-12" required="required" >
                                                        </div>
                                                    </div>
                                                <script>
                                                $('#email_official').change(function() {
                                                    var email = $('#email_official').val();
                                                    email_exists(email);
                                                });
                                                </script>

                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Personal Email Id  
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="email_personal" name="email_personal"   class="form-control col-md-7 col-xs-12"  >
                                                        </div>
                                                    </div>
                                                <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Designation  
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="contact_designation" name="contact_designation"   class="form-control col-md-7 col-xs-12"  >
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Linkedin Id  
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="linkedin_id" name="linkedin_id"   class="form-control col-md-7 col-xs-12"  >
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">FB Id 
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="fb_id" name="fb_id"   class="form-control col-md-7 col-xs-12"  >
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Twitter Id
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="twitter_id" name="twitter_id"   class="form-control col-md-7 col-xs-12"  >
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Instagram Id  
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="instagram_id" name="instagram_id"   class="form-control col-md-7 col-xs-12"  >
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Url
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="text" id="txt_url" name="txt_url"   class="form-control col-md-7 col-xs-12"  >
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Description
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <textarea id="txt_desc" name="txt_desc"   class="form-control col-md-7 col-xs-12" ></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Objective
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <textarea id="txt_objective" name="txt_objective"   class="form-control col-md-7 col-xs-12" ></textarea>
                                                        </div>
                                                    </div>
<!--                                                <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">User Name  <font color="#FF0000">*</font>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="txt_username" name="txt_username"   class="form-control col-md-7 col-xs-12" required="required">
                                            </div>
                                        </div>

					<div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Password <font color="#FF0000">*</font> 
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="txt_password" name="txt_password"   class="form-control col-md-7 col-xs-12" required="required" >
                                            </div>
                                        </div>-->
                                                    <div class="form-group">
                                                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Photo
                                                        </label>
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <input type="file" id="prof_pic" name="prof_pic"   class="form-control col-md-7 col-xs-12" required="required" >
                                                        </div>
                                                    </div>
                                                    <div class="ln_solid"></div>
                                                    <div class="form-group">
                                                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                                            <button type="submit" class="btn btn-primary">Cancel</button>
                                                            <button name="sub_button" type="submit" class="btn btn-success" >Submit</button>
                                                            <input type="hidden" name="hid_update" value="addnew"  id="hid_update"  >
                                                        </div>
                                                    </div>

                                            </span><br /><br />

                                            <!-- content ends-->
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <!-- /page content -->

                        <?php include("footer.php") ?>

                    </div>

                </div>
            </div>


            <script src="js/bootstrap.min.js"></script>

            <!-- chart js -->
            <script src="js/chartjs/chart.min.js"></script>
            <!-- bootstrap progress js -->
            <script src="js/progressbar/bootstrap-progressbar.min.js"></script>
            <script src="js/nicescroll/jquery.nicescroll.min.js"></script>
            <!-- icheck -->
            <script src="js/icheck/icheck.min.js"></script>
            <!-- tags -->
            <script src="js/tags/jquery.tagsinput.min.js"></script>
            <!-- switchery -->
            <script src="js/switchery/switchery.min.js"></script>
            <!-- daterangepicker -->
            <script type="text/javascript" src="js/moment.min2.js"></script>
            <script type="text/javascript" src="js/datepicker/daterangepicker.js"></script>
            <!-- richtext editor -->
            <script src="js/editor/bootstrap-wysiwyg.js"></script>
            <script src="js/editor/external/jquery.hotkeys.js"></script>
            <script src="js/editor/external/google-code-prettify/prettify.js"></script>
            <!-- select2 -->
            <script src="js/select/select2.full.js"></script>
            <!-- form validation -->
            <script type="text/javascript" src="js/parsley/parsley.min.js"></script>
            <!-- textarea resize -->
            <script src="js/textarea/autosize.min.js"></script>
            <script>
                                                                            autosize($('.resizable_textarea'));
            </script>
            <!-- Autocomplete -->
            <script type="text/javascript" src="js/autocomplete/countries.js"></script>
            <script src="js/autocomplete/jquery.autocomplete.js"></script>
            <script type="text/javascript">
                                                                            $(function () {
                                                                                'use strict';
                                                                                var countriesArray = $.map(countries, function (value, key) {
                                                                                    return {
                                                                                        value: value,
                                                                                        data: key
                                                                                    };
                                                                                });
                                                                                // Initialize autocomplete with custom appendTo:
                                                                                $('#autocomplete-custom-append').autocomplete({
                                                                                    lookup: countriesArray,
                                                                                    appendTo: '#autocomplete-container'
                                                                                });
                                                                            });
            </script>
            <script src="js/custom.js"></script>


            <!-- select2 -->
            <script>
                                                                            $(document).ready(function () {
                                                                                $(".select2_single").select2({
                                                                                    placeholder: "Select a state",
                                                                                    allowClear: true
                                                                                });
                                                                                $(".select2_group").select2({});
                                                                                $(".select2_multiple").select2({
                                                                                    maximumSelectionLength: 4,
                                                                                    placeholder: "With Max Selection limit 4",
                                                                                    allowClear: true
                                                                                });
                                                                            });
            </script>
            <!-- /select2 -->
            <!-- input tags -->
            <script>
                function onAddTag(tag) {
                    alert("Added a tag: " + tag);
                }

                function onRemoveTag(tag) {
                    alert("Removed a tag: " + tag);
                }

                function onChangeTag(input, tag) {
                    alert("Changed a tag: " + tag);
                }

                $(function () {
                    $('#tags_1').tagsInput({
                        width: 'auto'
                    });
                });
            </script>
            <!-- /input tags -->
            <!-- form validation -->
            <script type="text/javascript">
                $(document).ready(function () {
                    $.listen('parsley:field:validate', function () {
                        validateFront();
                    });
                    $('#demo-form .btn').on('click', function () {
                        $('#demo-form').parsley().validate();
                        validateFront();
                    });
                    var validateFront = function () {
                        if (true === $('#demo-form').parsley().isValid()) {
                            $('.bs-callout-info').removeClass('hidden');
                            $('.bs-callout-warning').addClass('hidden');
                        } else {
                            $('.bs-callout-info').addClass('hidden');
                            $('.bs-callout-warning').removeClass('hidden');
                        }
                    };
                });

                $(document).ready(function () {
                    $.listen('parsley:field:validate', function () {
                        validateFront();
                    });
                    $('#demo-form2 .btn').on('click', function () {
                        $('#demo-form2').parsley().validate();
                        validateFront();
                    });
                    var validateFront = function () {
                        if (true === $('#demo-form2').parsley().isValid()) {
                            $('.bs-callout-info').removeClass('hidden');
                            $('.bs-callout-warning').addClass('hidden');
                        } else {
                            $('.bs-callout-info').addClass('hidden');
                            $('.bs-callout-warning').removeClass('hidden');
                        }
                    };
                });
                try {
                    hljs.initHighlightingOnLoad();
                } catch (err) {
                }
            </script>
            <!-- /form validation -->
            <!-- editor -->
            <script>
                $(document).ready(function () {
                    $('.xcxc').click(function () {
                        $('#descr').val($('#editor').html());
                    });
                });

                $(function () {
                    function initToolbarBootstrapBindings() {
                        var fonts = ['Serif', 'Sans', 'Arial', 'Arial Black', 'Courier',
                            'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times',
                            'Times New Roman', 'Verdana'],
                                fontTarget = $('[title=Font]').siblings('.dropdown-menu');
                        $.each(fonts, function (idx, fontName) {
                            fontTarget.append($('<li><a data-edit="fontName ' + fontName + '" style="font-family:\'' + fontName + '\'">' + fontName + '</a></li>'));
                        });
                        $('a[title]').tooltip({
                            container: 'body'
                        });
                        $('.dropdown-menu input').click(function () {
                            return false;
                        })
                                .change(function () {
                                    $(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');
                                })
                                .keydown('esc', function () {
                                    this.value = '';
                                    $(this).change();
                                });

                        $('[data-role=magic-overlay]').each(function () {
                            var overlay = $(this),
                                    target = $(overlay.data('target'));
                            overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
                        });
                        if ("onwebkitspeechchange" in document.createElement("input")) {
                            var editorOffset = $('#editor').offset();
                            $('#voiceBtn').css('position', 'absolute').offset({
                                top: editorOffset.top,
                                left: editorOffset.left + $('#editor').innerWidth() - 35
                            });
                        } else {
                            $('#voiceBtn').hide();
                        }
                    }
                    ;

                    function showErrorAlert(reason, detail) {
                        var msg = '';
                        if (reason === 'unsupported-file-type') {
                            msg = "Unsupported format " + detail;
                        } else {
                            console.log("error uploading file", reason, detail);
                        }
                        $('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>' +
                                '<strong>File upload error</strong> ' + msg + ' </div>').prependTo('#alerts');
                    }
                    ;
                    initToolbarBootstrapBindings();
                    $('#editor').wysiwyg({
                        fileUploadError: showErrorAlert
                    });
                    window.prettyPrint && prettyPrint();
                });
            </script>
            <!-- /editor -->
        </form>
    </body>

</html>
