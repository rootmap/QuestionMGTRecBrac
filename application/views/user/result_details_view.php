<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$questions ="";
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

/**
 * result information
 ********************************************************************************/
if ($exam_is_mcq) {
    $correct_answer_percent = 0;
    $total_answered = (int)$result->result_total_answered;
    $correct_answers = (int)$result->result_total_correct;
    $wrong_answers = (int)$result->result_total_wrong;
    $dontknow_answers = (int)$result->result_total_dontknow;
    if($correct_answers){
        $correct_answer_percent = number_format(($correct_answers * 100) / $exam_total_questions, 0);
    }
    
}

$user_score = number_format((float)$result->result_user_score, 2);
$user_score_percent = number_format((float)(($user_score / $exam_score) * 100), 2);
$competency_level = $result->result_competency_level;

$exam_time_spent =  (int)$exam->exam_time_spent;
$exam_time_spent_format = '';
if ($exam_time_spent > 60*60) { $exam_time_spent_format = 'H:i:s'; }
else { $exam_time_spent_format = 'i:s'; }
$exam_time_spent = gmdate($exam_time_spent_format, $exam_time_spent);

?>


<div id="running-exam">

    <h2 class="exam-title title"><?php echo $exam_name; ?></h2>

    <div class="exam-info">

        <div class="row-fluid">
            <div class="span6">

                <h3 class="title">Result Summary</h3>

                <?php if ($exam_is_mcq) : ?>
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
                    <strong>Score</strong>
                    <span><?php echo $user_score; ?></span>
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


        <?php  $questions = $exam->exam_questions;  ?>
        <input type="hidden" name="current_question_index" id="current_question_index" value="<?php if (isset($current_question_index)) { echo $current_question_index; } else { echo '0'; } ?>" />
        <div id="details">
            <div class="review-cont">


                <div class="review-header">
                    <div class="qn">
                        Questions:
                        <?php if(count($questions) > 0): ?>
                            <div id="rq-pager" style="display: inline-block;">
                                <?php for($i=0; $i<count($questions); $i++): ?>
                                    <?php
                                    //print_r_pre($questions[$i]->question);
                                    $badge_class = '';
                                    if(isset($questions[$i]->question->ques_answer_type)){
                                        if ($questions[$i]->question->ques_answer_type == 'correct') {
                                        $badge_class = ' badge-success';
                                        } elseif ($questions[$i]->question->ques_answer_type == 'wrong') {
                                            $badge_class = '  badge-important"';
                                        } elseif ($questions[$i]->question->ques_answer_type == 'dontknow') {
                                            $badge_class = '  badge-info"';
                                        }
                                    }
                                    
                                    ?>
                                    <a data-slide-index="<?php echo $i; ?>" href=""><span class="badge <?php echo $badge_class; ?>"><?php echo ($i+1); ?></span></a>
                                <?php endfor; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div><!--review-header ends-->


                <?php if(count($questions) > 0): ?>
                <div class="review-body" id="review-body"><div id="qa-items-cont">

                    <?php

                    for($i=0; $i<count($questions); $i++):

                        $is_mcq = false;
                        if(isset($questions[$i]->question->ques_type)){
                          if ($questions[$i]->question->ques_type == 'mcq') { $is_mcq = true; }   
                        }
                        ?>

                        <div class="qa-item">

                            <div class="question">

                                <p><strong>Question:</strong></p>
                                <div class="text"><?php if(isset($questions[$i]->question->ques_text)){ echo nl2br($questions[$i]->question->ques_text); }  ?></div>

                            </div>

                            <div class="answers">

                                <?php if (!$is_mcq) : ?>
                                    <p>
                                        <strong>Score:</strong>
                                        <?php if(isset($questions[$i]->question->ques_user_score)){ echo (float)$questions[$i]->question->ques_user_score; ?>
                                        /
                                        <?php echo (float)$questions[$i]->question->ques_score; ?>
                                    </p>
                                <?php } endif; ?>

                                <p><strong>Answer:</strong></p>

                                <div class="rchoices-cont">

                                    <?php

                                    if ($is_mcq) :

                                        $num = 'a';
                                        $choices = $questions[$i]->question->ques_choices;
                                        ?>

                                        <?php for($j=0; $j<count($choices); $j++): ?>

                                        <?php
                                        $is_correct_answer = false;
                                        if ($choices[$j]['is_answer'] == 1) {
                                            $is_correct_answer = true;
                                        }

                                        $user_answer_str = '&nbsp;';
                                        $user_correct_answer = false;
                                        if ($choices[$j]['is_answer'] == 1 && $choices[$j]['is_user_answer'] == 1) {
                                            $user_correct_answer = true;
                                            $user_answer_str = '<span class="badge badge-success"><i class="icon-ok icon-white"></i></span>';
                                        }

                                        $user_wrong_answer = false;
                                        if ($choices[$j]['is_answer'] == 0 && $choices[$j]['is_user_answer'] == 1) {
                                            $user_wrong_answer = true;
                                            $user_answer_str = '<span class="badge badge-important"><i class="icon-remove icon-white"></i></span>';
                                        }

                                        $user_dontknow_answer = false;
                                        if ($choices[$j]['is_dontknow'] == 1 && $choices[$j]['is_user_answer'] == 1) {
                                            $user_dontknow_answer = true;
                                            $user_answer_str = '<span class="badge badge-info"><i class="icon-asterisk icon-white"></i></span>';
                                        }

                                        ?>

                                        <div class="rchoice <?php if ($is_correct_answer) { echo ' correct-answer'; } ?>">
                                            <div class="signal"><?php echo $user_answer_str; ?></div>
                                            <span class="number"><?php echo $num; ?>.</span>
                                            <div class="text"><?php echo nl2br($choices[$j]['text']); ?></div>
                                        </div>
                                        <?php $num++; endfor; ?>

                                    <?php else: ?>
                                        <div style="padding: 0 30px;"><?php echo nl2br($questions[$i]->question->ques_answer); ?></div>
                                    <?php endif; ?>

                                </div><!--choices-cont ends-->

                            </div>

                        </div>
                    <?php endfor; ?>


                </div></div><!--review-body ends-->
                <?php endif; ?>


                <div class="review-footer">
                    <div class="row-fluid">

                        <div class="span3">
                            <div id="review-previous-button"></div>
                        </div>
                        <div class="span6 center"></div>
                        <div class="span3 right">
                            <div id="review-next-button"></div>
                        </div>

                    </div>
                </div><!--review-footer ends-->


            </div><!--review-cont ends-->
        </div><!-- result details -->


        <div class="start-exam">
            <a href="<?php echo base_url('home'); ?>" class="btn btn-large"><span class="icon-arrow-left"></span> Back to Homepage</a>&nbsp;&nbsp;&nbsp;
            <a href="<?php echo base_url('result'); ?>" class="btn btn-large btn-danger">Show Summary Result</a>
        </div>

    </div>

</div><!--running-exam ends-->

<script type="text/javascript">

    var $reviewSlider;

    jQuery(document).ready(function(){

        $reviewSlider = jQuery('#qa-items-cont').bxSlider({
            adaptiveHeight: true,
            control: false,
            nextSelector: '#review-next-button',
            prevSelector: '#review-previous-button',
            nextText: 'Next Question <span class="icon-arrow-right"></span>',
            prevText: '<span class="icon-arrow-left"></span> Previous Question',
            pagerCustom: '#rq-pager',
            onSliderLoad: function(currentIndex) {
                jQuery('#review-next-button').children('a').addClass('btn');
                jQuery('#review-previous-button').children('a').addClass('btn');
                gotoCurrentSlide();
            },
            onSlideAfter: function($slideElement, oldIndex, newIndex) {
                jQuery('#current_question_index').val(newIndex);
            }
        });

        jQuery('#save-button, #publish-button').click(function() {
            jQuery('#qa-items-cont .bx-clone').remove();
        });

    });

    function gotoCurrentSlide() {
        var currentIndex = jQuery('#current_question_index').val();
        $reviewSlider.goToSlide(currentIndex);
    }

</script>


<!--<pre>
<?php /*print_r( $result ); */?>
</pre>
<pre>
<?php /*print_r( $exam ); */?>
</pre>-->
