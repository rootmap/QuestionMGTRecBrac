<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$exam_name = $exam->exam_title;
$exam_is_mcq = false;
$is_contain_descri = false;
if ($exam->exam_type == 'mcq') {
    $exam_is_mcq  = true;
    //var_dump($exam_is_mcq);die;
}

if ($exam->is_contain_descri == true) {
    $is_contain_descri = true;
   //var_dump('hi');die;
}

//var_dump($exam->immediate_result);die;

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

/**
 * result information
 ********************************************************************************/

//echo print_r_pre($result); die();

if ($exam_is_mcq) {
    $correct_answer_percent = 0;
    $total_answered = (int)$result->result_total_answered;
    $correct_answers = (int)$result->result_total_correct;
    $wrong_answers = (int)$result->result_total_wrong;
    $dontknow_answers = (int)$result->result_total_dontknow;
    $neg_mark = (float)$result->neg_mark;
    if($correct_answers){
        $correct_answer_percent = number_format(($correct_answers * 100) / $exam_total_questions, 0);
    }
    
}

$user_score = number_format((float)$result->result_user_score, 2);



$total_negative_mark = $neg_mark * $wrong_answers ;
$user_final_score = $user_score - $total_negative_mark;
if($user_final_score<0)
    $user_final_score = 0;
$bothScore=$user_score+$exam_score;
if(!empty($bothScore))
{
    $user_score_percent = number_format((float)(($user_final_score / $exam_score) * 100), 2);
}
else
{
    $user_score_percent =0;
}

$competency_level = $result->result_competency_level;

$exam_time_spent =  (int)$exam->exam_time_spent;
$exam_time_spent_format = '';
if ($exam_time_spent > 60*60) { $exam_time_spent_format = 'H:i:s'; }
else { $exam_time_spent_format = 'i:s'; }
$exam_time_spent = gmdate($exam_time_spent_format, $exam_time_spent);


// back button label
if ($referer_page == 'homepage') {
    $button_label = '<span class="icon-arrow-left"></span> Back to Homepage';
} else {
    $button_label = 'Take Another Exam';
}

?>


<div id="running-exam">

    <h2 class="exam-title title"><?php echo $exam_name; ?></h2>

    <div class="exam-info">

        <div class="row-fluid">
            <div class="span6">

                <h3 class="title">Result Summary</h3>

                <?php if (!$is_contain_descri && ($exam->immediate_result==1)) : ?>
                <p class="meta">
                    <strong>Questions Answered</strong>
                    <span><?php echo $total_answered; ?></span>
                </p>
                <p class="meta">
                    <strong>Correct Answers</strong>
                    <span><?php echo $correct_answers; ?></span>
                </p>
                <p class="meta">
                    <strong>Wrong Answers</strong>
                    <span><?php echo $wrong_answers; ?></span>
                </p>
                <?php if ((int)$exam->exam_allow_dontknow == 1): ?>
                <p class="meta">
                    <strong>Don't Know Answers</strong>
                    <span><?php echo $dontknow_answers; ?></span>
                </p>
                <?php endif; ?>
                <p class="meta">
                    <strong>Correct Answer (%)</strong>
                    <span><?php echo $correct_answer_percent; ?>%</span>
                </p>
                <p class="meta">
                    <strong>Total marks for right answers</strong>
                    <span><?php echo $user_score; ?></span>
                </p>

                    <p class="meta">
                        <strong>Negative Mark Per MCQ Question</strong>
                        <span><?php echo $neg_mark; ?></span>
                    </p>

                    <p class="meta">
                        <strong>Total negative marks</strong>
                        <span><?php echo $total_negative_mark; ?></span>
                    </p>

                    <p class="meta">
                        <strong>Final Score</strong>
                        <span><?php echo $user_final_score; ?></span>
                    </p>
                    <p class="meta">
                    <strong>Score (%)</strong>
                    <span><?php echo $user_score_percent; ?>%</span>
                </p>
                <?php if ($competency_level != ''): ?>
                <p class="meta">
                    <strong>Competency Level</strong>
                    <span><?php echo $competency_level; ?></span>
                </p>
                <?php endif; ?>
                <p class="meta">
                    <strong>Total time spent:</strong>
                    <span><?php echo $exam_time_spent; ?></span>
                </p>
                <?php else: ?>

                <p>Your result will be published after an examiner reviews your answers. You'll be notified by mail or
                you can login to the system and check the result at a later time.</p>

                <?php endif; ?>

            </div>
            <div class="span6">

                <h3 class="title">Exam Information</h3>

                <p class="meta">
                    <strong>Exam Type</strong>
                    <span><?php echo $exam_type; ?></span>
                </p>
                <p class="meta">
                    <strong>Duration</strong>
                    <span><?php echo $exam_time; ?></span>
                </p>
                <p class="meta">
                    <strong>Total Questions</strong>
                    <span><?php echo $exam_total_questions; ?></span>
                </p>
                <p class="meta">
                    <strong>Total Marks</strong>
                    <span><?php echo $exam_score; ?></span>
                </p>

            </div>
        </div>


        <div class="start-exam">
            <a href="<?php echo base_url('home'); ?>" class="btn btn-large"><?php echo $button_label; ?></a>&nbsp;&nbsp;&nbsp;
            <?php if((!empty($exam_score) || !empty($exam_total_questions) ) && (!$is_contain_descri && $exam->immediate_result==1)){ ?>
            <a href="<?php echo base_url('result/details#details'); ?>" class="btn btn-large btn-danger">Show Detail Result</a>
            <?php } ?>
        </div>

    </div>

</div><!--running-exam ends-->
