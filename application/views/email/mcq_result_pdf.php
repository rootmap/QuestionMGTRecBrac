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

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Exam Result <?php echo $result['id']; ?></title>

    <style type="text/css">
        body, p, div, .robi-email td {
            font-family: Arial,Helvetica,sans-serif;
            font-size: 14px;
            color: #333333;
        }
        @page {
          size: 8.5in 11in;
          margin: 10%;
          margin-header: 5mm;
          margin-footer: 5mm;
        }
        .robi-email td { vertical-align: top; }
        .robi-email .question-choice { margin: 20px 0 0 0; }
        .robi-email .question { margin-bottom: 10px; }
        .robi-email .choices table { margin-bottom: 5px; }
    </style>

</head>

<body>

<h2><?php echo $site_name; ?></h2>

<p>
	Thank you for taking the exam titled <strong><?php echo $exam_name; ?></strong>.<br />
	You can review your answers below.
</p>


<?php if(count($questions) > 0): ?>
<div class="robi-email">

<?php for($i=0; $i<count($questions); $i++): if ($questions[$i]->question->ques_type == 'mcq') : ?>
<table border="0" cellpadding="0" cellspacing="0" class="question-choice"><tr valign="top"><tr><td>

    <table border="0" cellpadding="0" cellspacing="0" class="question"><tr valign="top">
    	<td width="30"><strong>Q<?php echo ($i+1); ?>.</strong></td>
        <td><?php echo nl2br($questions[$i]->question->ques_text); ?></td>
    </tr></table>

    <div class="choices">
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
            $user_answer_str = '<font style="color: #468847; font-size: 16px; line-height: 18px; font-family: Arial,Helvetica,sans-serif; font-weight: bold;" color="#468847">&radic;</font>';
        }

        $user_wrong_answer = false;
        if ($choices[$j]['is_answer'] == 0 && $choices[$j]['is_user_answer'] == 1) {
            $user_wrong_answer = true;
            $user_answer_str = '<font style="color: #B94A48; font-size: 16px; line-height: 18px; font-family: Arial,Helvetica,sans-serif; font-weight: bold;" color="#B94A48">&times;</font>';
        }

        $user_dontknow_answer = false;
        if ($choices[$j]['is_dontknow'] == 1 && $choices[$j]['is_user_answer'] == 1) {
            $user_dontknow_answer = true;
            $user_answer_str = '<font style="color: #058DC7; font-size: 16px; line-height: 18px; font-family: Arial,Helvetica,sans-serif; font-weight: bold;" color="#058DC7">&lowast;</font>';
        }

        ?>
        <table border="0" cellpadding="0" cellspacing="0"><tr valign="top">
            <td width="30"><strong>&nbsp;</strong></td>
            <td width="30"><?php echo $user_answer_str; ?></td>
            <td width="30"><strong><?php echo $num; ?>.</strong></td>
            <td><?php echo nl2br($choices[$j]['text']); ?> <?php echo $correct_string; ?></strong></td>
        </tr></table>
        <?php $num++; endfor; ?>

    </div><!-- choices ends -->

</td></tr></table>
<?php endif; endfor; ?>

</div><!--robi-email ends-->
<?php endif; ?>


</body>
</html>