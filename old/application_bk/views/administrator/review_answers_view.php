

<h1 class="heading"><small>You are reviewing,</small> <?php echo $exam->exam_title; ?></h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<script type="text/javascript">

var $reviewSlider;

jQuery(document).ready(function(){

    $reviewSlider = jQuery('#qa-items-cont').bxSlider({
        adaptiveHeight: true,
        control: false,
        nextSelector: '#next-button',
        prevSelector: '#previous-button',
        nextText: 'Next Question <span class="icon-arrow-right"></span>',
        prevText: '<span class="icon-arrow-left"></span> Previous Question',
        pagerCustom: '#rq-pager',
        onSliderLoad: function(currentIndex) {
            jQuery('#next-button').children('a').addClass('btn');
            jQuery('#previous-button').children('a').addClass('btn');
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


<div class="review-main">

<?php

$result_is_published = false;
if ($result->result_status == 'published') {
    $result_is_published = true;
}

$exam_is_mcq = false;
if ($exam->exam_type == 'mcq') {
    $exam_is_mcq  = true;
}

$exam_type = $exam->exam_type;
if ($exam_type == 'mcq') {
    $exam_type = 'Multiple Choice Questions (MCQ).';
} elseif($exam_type == 'descriptive') {
    $exam_type = 'Descriptive';
}

$exam_time = (int)$exam->exam_time;
if ($exam_time <= 0) {
    $exam_time = 'No time limit.';
} elseif($exam_time == 1) {
    $exam_time = '1 minute.';
} else {
    $exam_time = $exam_time .' minutes.';
}

$exam_mark = (int)$exam->exam_score;
$exam_total_questions = (int)$exam->exam_total_questions;

$exam_time_spent =  (int)$exam->exam_time_spent;
$exam_time_spent_format = '';
if ($exam_time_spent > 60*60) { $exam_time_spent_format = 'H:i:s'; }
else { $exam_time_spent_format = 'i:s'; }
$exam_time_spent = gmdate($exam_time_spent_format, $exam_time_spent);


if ($exam_is_mcq) {

    $no_of_answered = 0;
    $no_of_correct_answer = 0;
    $correct_answer_percent = 0;

    $total_score = 0;
    $user_score = 0;

    for ($i=0; $i<count($exam->exam_questions); $i++) {

        $answer = $exam->exam_questions[$i]->question->ques_answer;

        $ques_score = $exam->exam_questions[$i]->question->ques_score;
        $ques_user_score = $exam->exam_questions[$i]->question->ques_user_score;

        $total_score += $ques_score;
        $user_score += $ques_user_score;

        if(is_array($answer) && count($answer) > 0) {
            $no_of_answered++;
        }

        if ($ques_score == $ques_user_score) {
            $no_of_correct_answer++;
        }
    }

    $correct_answer_percent = number_format(($no_of_correct_answer * 100) / $exam_total_questions, 0);
}

?>
    <div class="well"><div class="row-fluid">

        <div class="span6">
            <h3 class="title">Exam Information</h3>
            <p>Type of Exam: <?php echo $exam_type; ?></p>
            <p>Duration: <?php echo $exam_time; ?></p>
            <p>Total Marks: <?php echo $exam_mark; ?></p>
        </div>

        <?php if ($exam_is_mcq) : ?>
        <div class="span6">
            <h3 class="title">Result Summary</h3>
            <div class="row-fluid">
                <div class="span6">
                    <p>Total Questions: <?php echo $exam_total_questions; ?></p>
                    <p>Correct Answers: <?php echo $no_of_correct_answer; ?></p>
                    <p>Score: <?php echo $user_score; ?> of <?php echo $total_score; ?></p>
                </div>
                <div class="span6">
                    <p>Questions Answered: <?php echo $no_of_answered; ?></p>
                    <p>Correct Answers (%): <?php echo $correct_answer_percent; ?>%</p>
                    <p>Total time spent: <?php echo $exam_time_spent; ?></p>
                </div>
            </div>
        </div>
        <?php else: ?>

        <div class="span6">
            <h3 class="title">Result Summary</h3>
            <p>Total Questions: <?php echo $exam_total_questions; ?></p>
            <p>Total time spent: <?php echo $exam_time_spent; ?></p>
        </div>

        <?php endif; ?>
        
    </div></div>


    <?php $questions = $exam->exam_questions; ?>

    <?php echo form_open('administrator/result/answer/'. $exam_id .'/'. $user_team_id .'/'. $result_id); ?>

    <input type="hidden" name="current_question_index" id="current_question_index" value="<?php if (isset($current_question_index)) { echo $current_question_index; } else { echo '0'; } ?>" />

    <div class="review-cont">


        <div class="review-header">
            <div class="qn">
                Questions:
                <?php if(count($questions) > 0): ?>
                <div id="rq-pager" style="display: inline-block;">
                    <?php for($i=0; $i<count($questions); $i++): ?>
                    <?php
                    //echo '<pre>'; print_r( $questions[$i] ); echo '</pre>'; die();
                    $badge_class = '';
                    if ($questions[$i]->question->ques_answer_type == 'correct') {
                        $badge_class = ' badge-success';
                    } elseif ($questions[$i]->question->ques_answer_type == 'wrong') {
                        $badge_class = '  badge-important"';
                    } elseif ($questions[$i]->question->ques_answer_type == 'dontknow') {
                        $badge_class = '  badge-info"';
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
            if ($questions[$i]->question->ques_type == 'mcq') { $is_mcq = true; } ?>
                
            <div class="qa-item">

                <div class="question">

                    <p><strong>Question:</strong></p>
                    <div class="text"><?php echo nl2br($questions[$i]->question->ques_text); ?></div>

                </div>

                <div class="answers">

                    <?php if (!$is_mcq) : ?>
                    <p>
                        <strong>Score:</strong>
                        <?php if ($result_is_published): ?>
                        <?php echo (float)$questions[$i]->question->ques_user_score; ?>
                        <?php else : ?>
                        <input type="text" name="user_score[]" class="input-mini right" value="<?php echo (float)$questions[$i]->question->ques_user_score; ?>" autocomplete="off" />
                        <?php endif; ?>
                        /
                        <?php echo (float)$questions[$i]->question->ques_score; ?>
                    </p>
                    <?php endif; ?>

                    <p><strong>Answer:</strong></p>

                    <div class="rchoices-cont">

                        <?php

                        if ($is_mcq) :

                            $num = 'a';
                            $choices = $questions[$i]->question->ques_choices;
                            //echo '<pre>'; print_r( $choices ); echo '</pre>'; die();
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
                    <div id="previous-button"></div>
                </div>

                <div class="span6 center">
                    <?php if ($exam->exam_type == 'descriptive' && !$result_is_published): ?>
                    <button type="submit" name="save_button" id="save-button" value="y" class="btn"><span class="icon-hdd"></span> Save Review</button>&nbsp;&nbsp;
                    <button type="submit" name="publish_button" id="publish-button" value="y" class="btn btn-danger"><span class="icon-bullhorn icon-white"></span> Publish Result</button>
                    <?php endif; ?>
                </div>

                <div class="span3 right">
                    <div id="next-button"></div>
                </div>

            </div>
        </div><!--review-footer ends-->


    </div><!--review-cont ends-->
    <?php echo form_close(); ?>

</div><!--review-main ends-->

<!--<pre><?php /*print_r($exam); */?></pre>-->