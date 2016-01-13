<?php
error_reporting(0);
session_start();
set_time_limit(0);
if(!isset($_SESSION[vis_user_id]))
    {
    $msg = "<meta http-equiv=\"Refresh\" content=\"0;url=index.php\">";
    echo $msg;
    exit;
    }
include("config.php");
include("db.php");
dbconnect() or send_err_mail("Cannot connect to server".mysql_error(),$PHP_SELF);
$user_id=$_SESSION[vis_user_id];
$profpath = "organiser/contact_prof_pic";

    if(isset($_POST['companyprofile']) && $_POST['companyprofile'] == 'update')
    {
        $contact_name = addslashes($organisation_name);
        $contact_type = 'company';
        $address = addslashes($organisation_address);
        $contact_mobile = $organisation_mobile;
        $contact_email_official = addslashes($organisation_email);
        $contact_desc = addslashes($organisation_desc);
        $txt_url = addslashes($organisation_url);
        $prof_pic = addslashes($contact_pic);
        foreach ($_FILES['prof_pic']['name'] as $key => $file)
        {
        if($file != "") {
            unlink('organiser/contact_prof_pic/'.$prof_pic);
        if (trim($_FILES['prof_pic']['name'][$key])) {
            $prof_pic = $_FILES['prof_pic']['name'][$key];
            $prof_pic = preg_replace('/\s+/', '_', $prof_pic);
            $prof_pic = rand(9999, 99999) . $prof_pic;
            $tmp_name = $_FILES["prof_pic"]["tmp_name"][$key];
            $upldstatus = move_uploaded_file($tmp_name, "$profpath/$prof_pic");
        }
        }
        }
        if(isset($_POST['contact_id']) && $_POST['contact_id'] != "")
        {
            $contactId = $_POST['contact_id'];
        $sql = "UPDATE $tblcontact SET contact_name = '$contact_name', contact_mobile = '$contact_mobile', contact_email_official = '$contact_email_official', contact_address = '$address', contact_prof_pic = '$prof_pic', contact_desc = '$contact_desc', contact_url = '$txt_url' WHERE contact_id = '$contactId'";
        $result = mysql_query($sql) or send_err_mail($sql . mysql_error(), $PHP_SELF);
        update_prop_log($PHP_SELF, $sql, 'UPDATE $tblcontact');
        }
        else
        {
        $insstate_sql = "INSERT INTO $tblcontact (contact_name, contact_type, contact_mobile, contact_email_official, contact_address, contact_prof_pic, contact_desc, contact_url, contact_status, added_by,added_on) VALUES ('$contact_name', '$contact_type', '$contact_mobile', '$contact_email_official', '$address', '$prof_pic', '$contact_desc', '$txt_url', 'active', '$_SESSION[vis_user_id]','$CurGmtDT')";
        $insstate_res = mysql_query($insstate_sql) or send_err_mail($insstate_sql . mysql_error(), $PHP_SELF);
        $contactId = mysql_insert_id();
        update_prop_log($PHP_SELF, $insstate_sql, 'INSERT INTO $tblcontact');
        }
        foreach($_POST['company_type'] as $type)
        {
        $company_type = addslashes($type);
        $chk_sql = "SELECT * FROM $tblcompanymastercompanytype WHERE contact_id='$contactId' AND comp_type_id = '$company_type'";
        $chk_res = mysql_query($chk_sql) or send_err_mail($chk_sql . mysql_error(), $PHP_SELF);
        if (mysql_num_rows($chk_res) == 0) {
            $rln_insert_sql = "INSERT INTO $tblcompanymastercompanytype (contact_id, comp_type_id) VALUES ('$contactId', '$company_type')";
        $rln_insert_res = mysql_query($rln_insert_sql) or send_err_mail($rln_insert_sql . mysql_error(), $PHP_SELF);
        update_prop_log($PHP_SELF, $rln_insert_sql, 'INSERT INTO $tblcompanymastercompanytype');
        }
        else
        {
        $sql1 = "UPDATE $tblcompanymastercompanytype SET comp_type_id = '$company_type' WHERE contact_id = '$contactId'";
        $result1 = mysql_query($sql) or send_err_mail($sql . mysql_error(), $PHP_SELF);
        update_prop_log($PHP_SELF, $sql1, 'UPDATE $tblcompanymastercompanytype');
        }
        }
        foreach($_FILES['organisation_media']['tmp_name'] as $key => $file) {
                $company_id = $contactId;
                $media_type = 'file';
                if (trim($_FILES['organisation_media']['name'][$key])) {
                $media_file_name = $_FILES['organisation_media']['name'][$key];
                $media_file_name = preg_replace('/\s+/', '_', $media_file_name);
                $media_file_name = rand(9999, 99999) . $media_file_name;
                $tmp_name = $_FILES['organisation_media']['name'][$key];
                $upldstatus = move_uploaded_file($tmp_name, "uploads/$media_file_name");
            }
        $insstate_sql = "INSERT INTO $tblcompprofmediarln  (company_id, media_type, media_file_name, added_on,added_by) VALUES ('$company_id', '$media_type', '$media_file_name', '$CurGmtDT', '$_SESSION[vis_user_id]')";
        $insstate_res = mysql_query($insstate_sql) or send_err_mail($insstate_sql . mysql_error(), $PHP_SELF);
        update_prop_log($PHP_SELF, $insstate_sql, 'INSERT INTO $tblcompprofmediarln ');
        }
		
		header('Location:./manage_company_profile.php');
    }
    else
    {
    $chk_sql = "SELECT * FROM $tblcontact WHERE contact_id IN(SELECT parent_contact_id FROM $tblcontactcontactRln WHERE contact_id = '$user_id') AND contact_type = 'company'";
    $chk_res = mysql_query($chk_sql) or send_err_mail($chk_sql . mysql_error(), $PHP_SELF);
    while ($Sql_stat_row = mysql_fetch_array($chk_res)) {
        $company_id = $Sql_stat_row[contact_id];
        $company_name = $Sql_stat_row[contact_name];
        $company_address = $Sql_stat_row[contact_address];
        $company_mobile = $Sql_stat_row[contact_mobile];
        $company_official_email = $Sql_stat_row[contact_email_official];
        $company_url = $Sql_stat_row[contact_url];
        $company_desc = $Sql_stat_row[contact_desc];
        $company_prof_pic = $Sql_stat_row[contact_prof_pic];
        $company_addedby = $Sql_stat_row[contact_added_by];
        $chk_sql1 = "SELECT * FROM $tblcompmastercomptyperln WHERE contact_id='$company_id'";
        $chk_res1 = mysql_query($chk_sql1) or send_err_mail($chk_sql1 . mysql_error(), $PHP_SELF);
        while ($Sql_stat_row1 = mysql_fetch_array($chk_res1)) {
            $company_type[] = $Sql_stat_row1[comp_type_id];
        }
    }
    }
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
    <script>
        NProgress.start();
    </script>
    <style type="text/css">
    .style_new {font-weight: bold}
    </style>
  
<script language="JavaScript" type="text/javascript">

            function validate()
            {
                frm = document.calform;
                if (check_ph_num(frm.phone.value) == false) {
                    alert("Please Enter Digits in Phone Number");
                    frm.phone.value = '';
                    frm.phone.focus();
                    return false;
                }
                else if (check_email('email_id') == false)
                {
                    frm.email_id.value = '';
                    frm.email_id.focus();
                    return false;
                }
                else if (check_email('email_id') == false)
                {
                    frm.email_id.value = '';
                    frm.email_id.focus();
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
                var str = document.getElementById(email_id).value;
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
                            document.getElementById(email_id).focus();
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
        <script src="assets/js/ie8-responsive-file-warning.js"></script>
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
                <?php include('side_menu.php');?>
                <?php include('ileft_footer.php');?> 
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
                    <div class="row">
<div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>Add/Edit Company Profile</h2>
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
                                <span class="form-horizontal form-label-left">
                                <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="organisation-name">Organisation Name <font color="#FF0000">*</font>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="organisation-name" value="<?php echo $company_name; ?>" name="organisation_name" required="required" class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>
                                    <input name="contact_id" value="<?php echo $company_id; ?>" type="hidden"/>
                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="address-name">Address <font color="#FF0000">*</font>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <textarea id="address" required="required" class="form-control" name="organisation_address" ><?php echo $company_address; ?></textarea>
                                            </div>
                                        </div>
                                       

					                    <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="phone">Phone <font color="#FF0000">*</font>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="phone" name="organisation_mobile" value="<?php echo $company_mobile; ?>" required="required" class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>

					                    <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email-id">Email Id <font color="#FF0000">*</font>
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="email_id" name="organisation_email" value="<?php echo $company_official_email; ?>"  required="required" class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="url">URL 
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="text" id="url" name="organisation_url" value="<?php echo $company_url; ?>"  class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="description">Description 
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <textarea id="description" class="form-control" name="organisation_desc" ><?php echo $company_desc; ?></textarea>
                                            </div>
                                        </div>
                                    <div class="form-group">
                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="company-type">Company Type <font color="#FF0000">*</font></label>
                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <select name="company_type" class="form-control"  multiple style="width:90%;" required="required">
                                                            <option value="">Select</option>
                                                            <?php
                                                            $Sql_stat = "SELECT * FROM $tblcompanytypemaster WHERE status='active' order by comp_type";
                                                            $Sql_stat_res = mysql_query($Sql_stat) or send_err_mail($Sql_stat . mysql_error(), $PHP_SELF);
                                                            while ($Sql_stat_row = mysql_fetch_array($Sql_stat_res)) {
                                                                echo "<option value='$Sql_stat_row[comp_type_id]'";
                                                                foreach ($company_type as $t) {
                                                                    if ($t == $Sql_stat_row[comp_type_id])
                                                                        echo "selected";
                                                                }
                                                                echo " >$Sql_stat_row[comp_type]</option> ";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                        <div class="form-group">
                                                    <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last-name">Photo
                                                    </label>
                                                    <div class="col-md-2 col-sm-2 col-xs-2">
                                                        <img style="width:150px;height:70px;float:right;left:-100px;" src="organiser/contact_prof_pic/<?php echo $company_prof_pic; ?>" />
                                                        <input type="hidden" name="contact_pic" value="<?php echo $company_prof_pic; ?>" />
                                                     </div>
                                                    <div class="col-md-3 col-sm-3 col-xs-6">
                                                        <input type="file" id="prof_pic" name="prof_pic[]"  class="form-control col-md-7 col-xs-12" >
                                                    </div>
                                                </div>

                                        <div class="form-group">
                                            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="upload-media">Upload Media
                                            </label>
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <input type="file" id="upload-media"  multiple name="organisation_media[]" class="form-control col-md-7 col-xs-12">
                                            </div>
                                        </div>

                                        <div class="ln_solid"></div>
                                        <div class="form-group">
                                            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                                <input type="hidden" name="companyprofile" value="update"  id="hid_update"  >
                                                <button type="submit" class="btn btn-success">Submit</button>
                                                <button type="button" class="btn btn-primary">Cancel</button>
                                            </div>
                                        </div>
                                
                                </span>
                                
                                </div>
                            </div>
                        </div>
</div>

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


                    
</div>
                <!-- /page content -->

                <?php include("footer.php") ?>

            </div>

        </div>
    </div>
        </form>
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
        <!--<script type="text/javascript" src="js/autocomplete/countries.js"></script>-->
        <!--<?php include("js/autocomplete/countries.php"); ?>
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
                $('#txt_address_name').autocomplete({
                    lookup: countriesArray,
                    appendTo: '#autocomplete-container'
                });
            });
        </script>-->
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
            } catch (err) {}
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
                };

                function showErrorAlert(reason, detail) {
                    var msg = '';
                    if (reason === 'unsupported-file-type') {
                        msg = "Unsupported format " + detail;
                    } else {
                        console.log("error uploading file", reason, detail);
                    }
                    $('<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        '<strong>File upload error</strong> ' + msg + ' </div>').prependTo('#alerts');
                };
                initToolbarBootstrapBindings();
                $('#editor').wysiwyg({
                    fileUploadError: showErrorAlert
                });
                window.prettyPrint && prettyPrint();
            });
        </script>
        <!-- /editor -->
 
</body>

</html>