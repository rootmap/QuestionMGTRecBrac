<?php

// only supports mcq email

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

<div style="margin:0px;padding:0px;background:#e7e7e7;" class="robi-email">
<table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody><tr><td align="center">


	<!--empty space-->
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700"><tbody><tr>
        <td style="font-size:1px;" height="15" width="700"><font style="font-size:1px;">&nbsp;</font></td>
	</tr></tbody></table>
    
	<!--header starts-->
    <table bgcolor="#b92323" border="0" cellpadding="0" cellspacing="0" width="700" height="40"><tbody><tr>
    
        <td height="40" width="8"><font style="font-size:1px;">&nbsp;</font></td>
        <td height="40" width="79"><a href="http://www.robi.com.bd/" style="font-size:20px;color:#ffffff;"><img src="<?php echo $image_url; ?>/logo.jpg" alt="ROBI" style="display:block;margin:0;border:0;" /></a></td>
        <td height="40" width="10"><font style="font-size:1px;">&nbsp;</font></td>
        <td height="40" width="575"><font style="color:#ffffff;font-size:24px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#ffffff">
        	<strong><?php echo $site_name; ?></strong>
        </font></td>
        <td height="40" width="28"><img src="<?php echo $image_url; ?>/header-corner.gif" alt="" style="display:block;margin:0;border:0;" /></td>

	</tr></tbody></table>
    <!--header ends-->
    
    
    <!--mainbody starts-->
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700"><tbody><tr>
    
        <td width="4" bgcolor="#ff1010" style="background:#ff1010"><font style="font-size:1px;">&nbsp;</font></td>
        <td width="16"><font style="font-size:1px;">&nbsp;</font></td>
        <td width="460" valign="top">
        
        	<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="460"><tbody>
            <tr><td height="20"><font style="font-size:1px;">&nbsp;</font></td></tr>
            <tr><td valign="top">
            
                <font style="color:#333333;font-size:14px;line-height:18px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                    
                    Dear <?php echo $user_name; ?>,<br /><br />
                    
                    Thank you for taking the exam titled <strong><?php echo $exam_name; ?></strong>.
                    You can review your answers in this mail.<br /><br />
                    
                </font>

                <?php if(count($questions) > 0): ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">


                    <?php
                    for($i=0; $i<count($questions); $i++):
                        if ($questions[$i]->question->ques_type == 'mcq') : ?>

                	<tr><td>
                    	<table border="0" cellpadding="0" cellspacing="0" width="100%">

                            <!--question-->
                            <tr valign="top">
                            	<td width="40" align="center"><font style="color:#333333;font-size:14px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                                	<strong>Q<?php echo ($i+1); ?>.</strong>
                                </font></td>
                                <td colspan="2"><font style="color:#333333;font-size:14px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                                	<?php echo nl2br($questions[$i]->question->ques_text); ?>
                                </font></td>
                            </tr>
                            <tr><td colspan="3" height="7"><font style="font-size:1px;">&nbsp;</font></td></tr>

                            <!--choices-->
                            <?php

                            $num = 'a';
                            $choices = $questions[$i]->question->ques_choices;

                            ?>

                            <?php for($j=0; $j<count($choices); $j++): ?>

                            <?php

                            $is_correct_answer = false;
                            $correct_string = '';
                            if ($choices[$j]['is_answer'] == 1) {
                                $is_correct_answer = true;
                                $correct_string = '<strong>(correct answer)</strong>';
                            }

                            $user_answer_str = '&nbsp;';
                            $user_correct_answer = false;
                            if ($choices[$j]['is_answer'] == 1 && $choices[$j]['is_user_answer'] == 1) {
                                $user_correct_answer = true;
                                $user_answer_str = '<img src="'. $image_url .'/icon-tick.gif" alt="&radic;" style="display:block;margin:0;border:0;" />';
                            }

                            $user_wrong_answer = false;
                            if ($choices[$j]['is_answer'] == 0 && $choices[$j]['is_user_answer'] == 1) {
                                $user_wrong_answer = true;
                                $user_answer_str = '<img src="'. $image_url .'/icon-cross.gif" alt="&times;" style="display:block;margin:0;border:0;" />';
                            }

                            $user_dontknow_answer = false;
                            if ($choices[$j]['is_dontknow'] == 1 && $choices[$j]['is_user_answer'] == 1) {
                                $user_dontknow_answer = true;
                                $user_answer_str = '<img src="'. $image_url .'/icon-star.gif" alt="&lowast;" style="display:block;margin:0;border:0;" />';
                            }
                            
                            ?>

                            <tr valign="top">
                            	<td width="20" align="center">
                                    <?php echo $user_answer_str; ?>
                                </td>
                                <td width="20"><font style="color:#333333;font-size:14px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                                	<strong><?php echo $num; ?>.</strong>
                                </font></td>
                                <td><font style="color:#333333;font-size:14px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                                	<?php echo nl2br($choices[$j]['text']); ?> <?php echo $correct_string; ?>
                                </font></td>
                            </tr>
                            <tr><td colspan="3" height="7"><font style="font-size:1px;">&nbsp;</font></td></tr>
                            <?php $num++; endfor; ?>

                        </table>
                    </td></tr>
                    <tr><td height="20"><font style="font-size:1px;">&nbsp;</font></td></tr>

                    <?php endif; endfor; ?>

                </table>
                <?php endif; ?>
            	
            </td></tr>
            <tr><td height="10"><font style="font-size:1px;">&nbsp;</font></td></tr>
            </tbody></table>
        
        </td>
        <td width="20" background="<?php echo $image_url; ?>/divider-bg.gif" style="background:url(<?php echo $image_url; ?>/divider-bg.gif)"><font style="font-size:1px;">&nbsp;</font></td>
        <td width="196" bgcolor="#f2c9c9" style="background:#f2c9c9;" valign="top">
        
        	<table border="0" cellpadding="0" cellspacing="0" width="196"><tbody>
            <tr><td height="15" colspan="3"><font style="font-size:1px;">&nbsp;</font></td></tr>
            <tr>
            	<td width="10"><font style="font-size:1px;">&nbsp;</font></td>
                <td valign="top">
                    
                    <font style="color:#333333;font-size:16px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                    	<strong>Result Summary</strong>
                    </font><br /><br />
                    
                    <font style="color:#333333;font-size:14px;line-height:18px;font-family:Arial,Helvetica,sans-serif;" color="#333333">
                        
                        Questions Answered<br />
                        <strong><?php echo $total_answered; ?></strong><br /><br />
                        
                        Correct Answers<br />
                        <strong><?php echo $correct_answers; ?></strong><br /><br />
                        
                        Wrong Answers<br />
                        <strong><?php echo $wrong_answers; ?></strong><br /><br />
                        
                        Don't Know Answers<br />
                        <strong><?php echo $dontknow_answers; ?></strong><br /><br />
                        
                        Correct Answer (%)<br />
                        <strong><?php echo $correct_answer_percent; ?>%</strong><br /><br />
                        
                        Score (%)<br />
                        <strong><?php echo $user_score_percent; ?>%</strong><br /><br />
                        
                        Competency Level<br />
                        <strong><?php echo $competency_level; ?></strong><br /><br />
                        
                        Total time spent<br />
                        <strong><?php echo $exam_time_spent; ?></strong><br /><br />
                        
                    </font>
                    
                </td>
                <td width="10"><font style="font-size:1px;">&nbsp;</font></td>
            </tr>
            <tr><td height="10" colspan="3"><font style="font-size:1px;">&nbsp;</font></td></tr>
            </tbody></table>
        
        </td>
        <td width="4" bgcolor="#b92323" style="background:#b92323"><font style="font-size:1px;">&nbsp;</font></td>

	</tr></tbody></table>
    <!--mainbody ends-->
    
    
    <!--footer starts-->
    <table bgcolor="#ec0000" border="0" cellpadding="0" cellspacing="0" width="700" height="70"><tbody><tr>
    
        <td height="70" width="236"><img src="<?php echo $image_url; ?>/footer-alpona.gif" alt="" style="display:block;margin:0;border:0;" /></td>
        <td height="70" width="244"><img src="<?php echo $image_url; ?>/footer-01.gif" alt="" style="display:block;margin:0;border:0;" /></td>
        <td height="70" width="220">
        	<table border="0" cellpadding="0" cellspacing="0" width="220"><tbody>
            	<tr><td width="220" height="33">
                	<table border="0" cellpadding="0" cellspacing="0" width="220" height="33"><tbody><tr>
                    	<td height="33" width="20" bgcolor="#ec0000" background="<?php echo $image_url; ?>/divider-bg.gif" style="background:url(<?php echo $image_url; ?>/divider-bg.gif)">
                        	<img src="<?php echo $image_url; ?>/footer-02.png" alt="" style="display:block;margin:0;border:0;" />
                        </td>
                        <td height="33" width="196" bgcolor="#f2c9c9"><img src="<?php echo $image_url; ?>/footer-03.gif" alt="" style="display:block;margin:0;border:0;" /></td>
                        <td height="33" width="4" bgcolor="#b92323"><font style="font-size:1px;">&nbsp;</font></td>
                    </tr></tbody></table>
                </td></tr>
                <tr><td width="220" height="37" bgcolor="#ec0000">
                	<img src="<?php echo $image_url; ?>/footer-text.gif" alt="জ্বলে উঠুন আপন শক্তিতে" style="display:block;margin:0;border:0;font-size:16px;line-height:18px;color:#ffffff;" />
                </td></tr>
            </tbody></table>
        </td>

	</tr></tbody></table>
    <!--footer ends-->


	<!--empty space-->
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="700"><tbody><tr>
        <td style="font-size:1px;" height="15" width="700"><font style="font-size:1px;">&nbsp;</font></td>
	</tr></tbody></table>


</td></tr></tbody></table>
</div>
<?php

//echo '<pre>'; print_r( $user ); echo '</pre>';
//echo '<pre>'; print_r( $exam ); echo '</pre>';
//echo '<pre>'; print_r( $result ); echo '</pre>';

?>