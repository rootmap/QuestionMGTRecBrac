<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$survey_name = $survey->survey_title;
$current_question = $survey->current_question;

$total_questions = $survey->survey_total_questions;
$current_question_number = $survey->current_question_index + 1;
$question_number_str = 'Question No: '. $current_question_number .' of '. $total_questions;


$question = $survey->current_question;
$is_mcq = false;
if ($question->ques_type == 'mcq') {
    $is_mcq = true;
}

if ($is_mcq) {
    $no_of_right_answers = 0;
    $input_type = 'radio';
    for ($i=0; $i<count($question->ques_choices); $i++) {
        if ($question->ques_choices[$i]['is_answer'] == 1) {
            $no_of_right_answers++;
        }
    }
    if ($no_of_right_answers > 1) {
        $input_type = 'checkbox';
    }
}

?>

<div id="running-exam">

<?php
    echo form_open('exam/action');
    echo '<input type="hidden" name="action" id="action" value="" />';
?>

    <h2 class="exam-title"><?php echo $exam_name; ?></h2>


    <div class="exam-header">
        <div class="qn"><?php echo $question_number_str; ?></div>
        <?php if ((int)$exam->exam_time > 0) : ?>
        <div class="time">Time Remaining: <?php echo $exam_time_remaining_formatted; ?></div>
        <?php endif; ?>
    </div><!--exam-header ends-->


    <div class="exam-body">
        <div class="question">
            <p><strong>Question:</strong></p>
            <div class="text">
                <?php echo nl2br($question->ques_text); ?>
                <?php if ($input_type == 'checkbox') : ?>
                <br /><br /><strong>Note:</strong> This question has more than one right answers.
                <?php endif; ?>
            </div>
        </div><!--question ends-->
        <div class="answers">

            <p><strong>Answer:</strong></p>

            <div class="choices-cont">

            <?php if($is_mcq) : $num = 96; ?>

                <?php for($i=0; $i<count($question->ques_choices); $i++) : $num++; ?>

                <div class="choice">
                    <input name="answer[]" id="answer<?php echo $i; ?>" value="<?php echo $i; ?>" type="<?php echo $input_type; ?>"
                        <?php if($question->ques_choices[$i]['is_user_answer']==1) { echo ' checked="checked"'; } ?> />
                    <span class="number"><?php echo chr($num). '.'; ?></span>
                    <label for="answer<?php echo $i; ?>" class="text"><?php echo nl2br($question->ques_choices[$i]['text']); ?></label>
                </div>

                <?php endfor; ?>

            <?php else: ?>

                <textarea name="answer" cols="30" rows="10" placeholder="Write your answer here"><?php echo nl2br($current_question->ques_answer); ?></textarea>

            <?php endif; ?>

            </div><!--choices-cont ends-->

        </div><!--answers ends-->
    </div><!--exam-body ends-->


    <div class="exam-footer">
        <div class="row-fluid">

            <div class="span3">

                <?php if ($exam_allow_previous == 1) : ?>
                <?php if(!$exam->is_first_question) : ?>

                    <button type="submit" id="previous-button" class="btn"><span class="icon-arrow-left"></span> Previous Question</button>

                <?php endif; ?>
                <?php endif; ?>
                
            </div>

            <div class="span6 center">

                <?php if ($exam_allow_pause == 1): ?>
                <!--<button type="submit" id="pause-button" class="btn"><span class="icon-pause"></span> Pause Exam</button>-->&nbsp;&nbsp;&nbsp;
                <?php endif; ?>

                <!--<button type="submit" id="quit-button" class="btn"><span class="icon-stop"></span> Quit Exam</button>-->

            </div>

            <div class="span3 right">
                <?php if(!$exam->is_last_question) : ?>

                    <button type="submit" id="next-button" class="btn btn-danger">Next Question <span class="icon-arrow-right icon-white"></span></button>

                <?php else: ?>
                    
                    <button type="submit" id="finish-button" class="btn btn-danger">Finish Exam <span class="icon-arrow-right icon-white"></span></button>

                <?php endif; ?>
            </div>

        </div>
    </div><!--exam-footer ends-->

<?php echo form_close(); ?>

<div class="mask-layer"></div>
</div><!--running-exam ends-->


<script type="text/javascript">

jQuery(document).ready(function(){

    /*jQuery('#running-exam').attr('unselectable', 'on');
    jQuery('#running-exam').css('user-select', 'none');
    jQuery('#running-exam').css('-moz-user-select', 'none');
    jQuery('#running-exam').on('selectstart', false);*/

    // hotkey
    if (jQuery('.exam-body .choices-cont .choice input').length > 0) {

        jwerty.key('enter', function() {
            if (jQuery('#next-button').length > 0) {
                if (isAnswered()) {
                    jQuery('#action').val('next');
                    jQuery('form').submit();
                }
            } else if (jQuery('#finish-button').length > 0) {
                if (isAnswered()) {
                    jQuery('#action').val('finish');
                    disableExam();
                    jQuery('form').submit();
                }
            }
        });

        jwerty.key('1/num-1', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(1) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('2/num-2', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(2) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('3/num-3', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(3) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('4/num-4', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(4) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('5/num-5', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(5) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('6/num-6', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(6) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('7/num-7', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(7) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('8/num-8', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(8) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
        jwerty.key('9/num-9', function() {
            var inputObj = jQuery('.exam-body .choices-cont .choice:nth-child(9) input');
            jQuery(inputObj).attr('checked', 'checked');
        });
    }

});

function disableExam() {
    jQuery('#running-exam input, #running-exam button').addClass('disabled', 'disabled');
    showBusyText();
    showMaskLayer();
}
function showBusyText() {
    var imgSrc = '<?php echo base_url('assets/frontend/images/busy-red.gif'); ?>';
    jQuery('.exam-footer .span6').html('<img src="'+ imgSrc +'" /> <span>Processing, please wait...</span>');
}
function showMaskLayer() {
    var w = jQuery('form').width();
    var h = jQuery('form').height();
    
    jQuery('.mask-layer').width(w);
    jQuery('.mask-layer').height(h);
    jQuery('.mask-layer').show();
}


<?php if ((int)$exam->exam_time > 0) : ?>
jQuery(document).ready(function(){

    displayTimeIntervalVar = setInterval('displayTime()', 1000);

});

var timeLeft = parseInt(<?php echo (int)$exam_time_remaining_milliseconds; ?>);
var displayTimeIntervalVar;

function displayTime() {
    timeLeft = timeLeft - 1000;
    jQuery('.exam-header .time').text('Time Remaining: '+ secondsToHms(timeLeft / 1000));
    if (timeLeft <= 0) {
        window.clearInterval(displayTimeIntervalVar);
        jQuery('.exam-header .time').text('Time Remaining: '+ secondsToHms(0));
        callFinishExam();
    }
}

function callFinishExam() {
    disableExam();
    jQuery.ajax({
        type: "POST",
        url: baseUrlJs +"exam/action",
        data: "action=finish&type=force",
        success: function(msg){
            window.location.replace(baseUrlJs +'result')
        }
    });
}
<?php endif; ?>

function secondsToHms(d) {
    d = Number(d);
    var h = Math.floor(d / 3600);
    var m = Math.floor(d % 3600 / 60);
    var s = Math.floor(d % 3600 % 60);
    return ((h > 0 ? h + ":" : "") + (m > 0 ? (h > 0 && m < 10 ? "0" : "") + m + ":" : "00:") + (s < 10 ? "0" : "") + s);
}


</script>


<!--<pre>
<?php /*print_r( $this->session->userdata('exam') );  */?>
</pre>-->