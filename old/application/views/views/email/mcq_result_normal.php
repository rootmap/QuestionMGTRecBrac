<?php

// only supports mcq exam

?><?php

if ($site_name == '') {
    $site_name = 'Robi Jana Ojana';
}

$user_name = trim($user->user_first_name .' '. $user->user_last_name);
if ($user_name == '') {
    $user_name = 'User (' .trim($user->user_login) .')';
}

$result_is_published = false;
if ($result['result_status'] == 'published') {
    $result_is_published = true;
}

$exam_name = $exam->exam_title;
$exam_is_mcq = false;
if ($exam->exam_type == 'mcq') {
    $exam_is_mcq  = true;
}

/**
 * exam information
 ********************************************************************************/

$exam_type = $exam->exam_type;
if ($exam_type == 'mcq') {
    $exam_type = 'Multiple Choice Questions (MCQ).';
} elseif($exam_type == 'descriptive') {
    $exam_type = 'Descriptive';
}

/* calculate exam time */
$exam_time = (int)$exam->exam_time;
if ($exam_time <= 0) {
    $exam_time = 'No time limit';
} elseif($exam_time == 1) {
    $exam_time = '1 minute';
} else {
    $exam_time = $exam_time .' minutes';
}

$exam_score = (int)$exam->exam_score;
$exam_total_questions = (int)$exam->exam_total_questions;
//echo '<pre>'; print_r( $exam ); echo '</pre>'; die();

/**
 * result information
 ********************************************************************************/
if ($exam_is_mcq) {
    $total_answered = (int)$result['result_total_answered'];
    $correct_answers = (int)$result['result_total_correct'];
    $wrong_answers = (int)$result['result_total_wrong'];
    $dontknow_answers = (int)$result['result_total_dontknow'];
    $correct_answer_percent = number_format(($correct_answers * 100) / $exam_total_questions, 0);
}

$user_score = number_format((float)$result['result_user_score'], 2);
$user_score_percent = number_format((float)(($user_score / $exam_score) * 100), 2);
$competency_level = $result['result_competency_level'];

$exam_time_spent =  (int)$exam->exam_time_spent;
$exam_time_spent_format = '';
if ($exam_time_spent > 60*60) { $exam_time_spent_format = 'H:i:s'; }
else { $exam_time_spent_format = 'i:s'; }
$exam_time_spent = gmdate($exam_time_spent_format, $exam_time_spent);


$questions = $exam->exam_questions;

?>

<style type="text/css">
*{ padding: 0; margin: 0; }
body, .robi-email td {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 14px;
	color: #333333; 
}
</style>

<div style="margin: 0px; padding: 0px; background: #e7e7e7;" class="robi-email">
<table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td align="center">


	<!--empty space-->
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700"><tbody><tr>
        <td style="font-size:1px;" height="15" width="700"><font style="font-size:1px;">&nbsp;</font></td>
	</tr></tbody></table>
    
	<!--header starts-->
    <table bgcolor="#b92323" border="0" cellpadding="0" cellspacing="0" width="700" height="40"><tbody><tr>

        <td height="40" width="20"><font style="font-size:1px;">&nbsp;</font></td>
        <td height="40" width="660"><font style="color:#ffffff;font-size:24px;line-height: 18px;  font-family: Arial,Helvetica,sans-serif;" color="#ffffff">
        	<strong>
<?php
echo $site_name;
?></strong>
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
    echo $user_name;
?>
,<br /><br />

                    Thank you for taking the exam titled <strong>
<?php
    echo $exam_name;
?> </strong>.
                    You can review your summary result in this mail. Also your detail result is attached with the mail.<br /><br />

                    <table>
                        <tr>
                            <td>Questions Answered</td>
                            <td><strong>
<?php
echo $total_answered;
?> </strong></td>
                        </tr>
                        <tr>
                            <td>Correct Answers</td>
                            <td><strong>
<?php
echo $correct_answers;
?> </strong></td>
                        </tr>
                        <tr>
                            <td>Wrong Answers</td>
                            <td><strong>
<?php
echo $wrong_answers;
?> </strong></td>
                        </tr>
                        <tr>
                            <td>Don't Know Answers</td>
                            <td><strong>
<?php
echo $dontknow_answers;
?> </strong></td>
                        </tr>
                        <tr>
                            <td>Correct Answer (%)</td>
                            <td><strong>
<?php
echo $correct_answer_percent;
?> </strong></td>
                        </tr>
                        <tr>
                            <td>Score (%)</td>
                            <td><strong>
<?php
echo $user_score_percent;
?> </strong></td>
                        </tr>
                        <tr>
                            <td>Competency Level</td>
                            <td><strong>
<?php
echo $competency_level;
?> </strong></td>
                        </tr>
                        <tr>
                            <td>Total time spent</td>
                            <td><strong>
<?php
echo $exam_time_spent;
?> </strong></td>
                        </tr>

                    </table>

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