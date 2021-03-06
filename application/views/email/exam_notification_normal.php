<?php

//echo '<pre>'; print_r( strtotime($user_exam->ue_end_date) ); echo '</pre>'; die();

if ($site_name == '') {
    $site_name = 'Robi Jana Ojana';
}

$user_name = trim($user->user_first_name .' '. $user->user_last_name);
if ($user_name == '') {
    $user_name = 'User (' .trim($user->user_login) .')';
}

$exam_name = $exam->exam_title;

$start_time = date('jS F, Y \a\t g:i a', strtotime($user_exam->ue_start_date));
$end_time = date('jS F, Y \a\t g:i a', strtotime($user_exam->ue_end_date));

?>

<style type="text/css">
*{ padding: 0; margin: 0; }
body, .robi-email td {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 14px;
	color: #333333;
}
</style>


<div style="margin:0px;padding:0px;background:#e7e7e7;" class="robi-email">
<table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td align="center">


	<!--empty space-->
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700"><tbody><tr>
        <td style="font-size:1px;" height="15" width="700"><font style="font-size:1px;">&nbsp;</font></td>
	</tr></tbody></table>
    
	<!--header starts-->
    <table bgcolor="#b92323" border="0" cellpadding="0" cellspacing="0" width="700" height="40"><tbody><tr>
    
        <td height="40" width="20"><font style="font-size:1px;">&nbsp;</font></td>
        <td height="40" width="660"><font style="color:#ffffff;font-size:24px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#ffffff">
        	<strong>
                <?php
                    echo $site_name;
                ?>
            </strong>
        </font></td>
        <td height="40" width="20"><font style="font-size:1px;">&nbsp;</font></td>

	</tr></tbody></table>
    <!--header ends-->
    
    
    <!--mainbody starts-->
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700"><tbody><tr>
    
        <td width="4" bgcolor="#ff1010" style="background:#ff1010"><font style="font-size:1px;">&nbsp;</font></td>
        <td width="16"><font style="font-size:1px;">&nbsp;</font></td>
        <td width="660" valign="top">
        
        	<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="660"><tbody>
            <tr><td height="20"><font style="font-size:1px;">&nbsp;</font></td></tr>
            <tr><td>
            
            	<font style="color:#333333;font-size:14px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                
                	Dear
                    <?php
                        echo $user_name .',';
                    ?>
                    <br /><br />
                    
                    You have been assigned to take an exam titled, <strong>
                    <?php
                        echo $exam_name;
                    ?>
                    </strong>. The exam will
                    be available to you on following dates:<br /><br />

                    Portal URL:
                    <?php
                        echo base_url();
                    ?>
                    <br />
                    Exam start time:
                    <?php
                        echo $start_time;
                    ?>
                    <br />
                    Exam expired time:
                    <?php
                        echo $end_time;
                    ?>
                    <br /><br />

                    You are requested to take the exam within specified time.<br /><br />


                    Visit the following URL to login to the
                    <?php
                        echo $site_name;
                    ?>
                    portal and take the exam.<br />

                    <?php
                        echo $site_url;
                    ?>
                    <br /><br />
                    
                </font>
                
            </td></tr>
            <tr><td height="10"><font style="font-size:1px;">&nbsp;</font></td></tr>
            </tbody></table>
        
        </td>
        <td width="16"><font style="font-size:1px;">&nbsp;</font></td>
        <td width="4" bgcolor="#b92323" style="background:#b92323"><font style="font-size:1px;">&nbsp;</font></td>

	</tr></tbody></table>
    <!--mainbody ends-->
    

	<!--footer starts-->
    <table bgcolor="#ec0000" border="0" cellpadding="0" cellspacing="0" width="700" height="40"><tbody><tr>

        <td height="40" width="20"><font style="font-size: 1px;">&nbsp;</font></td>
        <td height="40" width="660" align="right"><font style="color: #ffffff; font-size: 18px; line-height: 18px; font-family: Arial,Helvetica,sans-serif; " color="#ffffff">
        	জ্বলে উঠুন আপন শক্তিতে
        </font></td>
        <td height="40" width="20"><font style="font-size: 1px;">&nbsp;</font></td>

	</tr></tbody></table>
    <!--footer ends-->


	<!--empty space-->
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700"><tbody><tr>
        <td style="font-size:1px;" height="15" width="700"><font style="font-size:1px;">&nbsp;</font></td>
	</tr></tbody></table>


</td></tr></tbody></table>
</div>