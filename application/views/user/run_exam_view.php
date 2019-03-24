<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


$exam_name = $exam->exam_title;
$exam_allow_previous = (int)$exam->exam_allow_previous;
$exam_allow_pause = (int)$exam->exam_allow_pause;
//$exam_allow_negative_marking = (int)$exam->exam_allow_negative_marking;
//$exam_negative_mark_weight = (int)$exam->exam_negative_mark_weight;


$current_question = $exam->current_question;

$exam_time = 0;
$exam_time_remaining_milliseconds = 0;
$exam_time_remaining_formatted = '';

if ((int)$exam->exam_time > 0) {

    $exam_time = (int)$exam->exam_time * 60;
    $exam_start_time = (int)$exam->exam_time_start * 60;
    $exam_time_spent = (int)$exam->exam_time_spent;

    $exam_time_remaining = $exam_time - $exam_time_spent;
    $exam_time_remaining_milliseconds = $exam_time_remaining * 1000;

    $time_format = '';
    if ($exam_time_remaining > 60*60) { $time_format = 'H:i:s'; }
    else { $time_format = 'i:s'; }
    $exam_time_remaining_formatted = ltrim(gmdate($time_format, $exam_time_remaining), '0');

}
//print_r_pre($exam);
$total_questions = $exam->exam_total_questions;
$current_question_number = $exam->current_question_index + 1;
$question_number_str = 'Question No: '. $current_question_number .' of '. $total_questions;


$question = $exam->current_question;

$is_mcq = false;
if ($question->ques_type == 'mcq') {
    $is_mcq = true;
}

if ($is_mcq) {
    $no_of_right_answers = 0;
    $input_type = 'checkbox';
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
    echo '<input type="hidden" name="action" id="action-exam" value="" />';
?>
<script type="text/javascript" src="<?php echo base_url('assets/editor/generic_wiris/core/display.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/editor/generic_wiris/wirisplugin-generic.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/editor/ckeditor/ckeditor.js'); ?>"></script>
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
                <input type="hidden" name="qusid" value="<?php echo $question->id;?>">
                <?php echo nl2br($question->ques_text); ?>
                <?php if ($input_type == 'checkbox') : ?>
                <br /><br /><strong>Note:</strong> This question has more than one right answers.
                <?php endif; ?>
            </div>
        </div><!--question ends-->
        <div class="answers2">

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
                <script>setValue("example",content);</script>
                <textarea id="example" name="answer" cols="30" rows="10" placeholder="Write your answer here"><?php echo nl2br($current_question->ques_answer); ?></textarea>

            <?php endif; ?>

            </div><!--choices-cont ends-->

        </div><!--answers ends-->
    </div><!--exam-body ends-->

    <div class="exam-footer">
        <div class="row-fluid">

            <div class="span3">

                <?php if ($exam_allow_previous == 1) : ?>
                <?php if(!$exam->is_first_question) : ?>

                    <button type="submit" id="previous-button-exam" class="btn"><span class="icon-arrow-left"></span> Previous Question</button>

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

                    <button type="submit" id="next-button-exam" class="btn btn-danger">Next Question <span class="icon-arrow-right icon-white"></span></button>

                <?php else: ?>

                    <button type="submit" id="finish-button-exam" class="btn btn-danger">Finish Exam <span class="icon-arrow-right icon-white"></span></button>

                <?php endif; ?>
            </div>

        </div>
    </div><!--exam-footer ends-->

<?php echo form_close(); ?>

<div class="mask-layer"></div>
</div><!--running-exam ends-->

<script>
           CKEDITOR.replace( 'answer' );
           //CKEDITOR.replace( 'answer', { toolbar : [ [ 'EqnEditor', 'Bold', 'Italic' ] ] });
           //CKEDITOR.replace( 'answer', { toolbar : [ [ 'EqnEditor', 'Bold', 'Italic' ] ] });



        </script>

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
                if (isAnswered_Exam()) {
                    jQuery('#action-exam').val('next');
                    jQuery('form').submit();
                }
            } else if (jQuery('#finish-button').length > 0) {

                if (isAnswered_Exam()) {
                    jQuery('#action-exam').val('finish');
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


jQuery(document).ready(function () {
    var qmsblock = jQuery(document);

    // Disable Cut + Copy + Paste (input)
    qmsblock.on('copy paste cut', function (e) {
        e.preventDefault(); //disable cut,copy,paste
        return false;
    });

    // Disable Cut + Copy + Paste and Browser Admin Tools (all document)
    qmsblock.keydown(function (e) {
        var forbiddenCtrlKeys = new Array('c', 'x', 'v', 'ins', 'u');
        var forbiddenShiftKeys = new Array('del', 'ins', 'f2', 'f4', 'f7');
        var forbiddenCtrlShiftKeys = new Array('k', 'i', 'm', 's', 'j');
        var keyCode = (e.keyCode) ? e.keyCode : e.which;

        var isCtrl, isShift;
        isCtrl = e.ctrlKey;
        isShift = e.ctrlShift;

        string = getKeyCodeString(keyCode);

        if (string == 'f12')
        {
            e.preventDefault();
            return false;
        }

        if (isCtrl && !isShift) {
            for (i = 0; i < forbiddenCtrlKeys.length; i++) {
                if (forbiddenCtrlKeys[i] == string) {
                    e.preventDefault();
                    return false;
                }
            }
        }

        if (!isCtrl && isShift) {
            for (i = 0; i < forbiddenShiftKeys.length; i++) {
                if (forbiddenShiftKeys[i] == string) {
                    e.preventDefault();
                    return false;
                }
            }
        }

        if (isCtrl && isShift) {
            for (i = 0; i < forbiddenCtrlShiftKeys.length; i++) {
                if (forbiddenCtrlShiftKeys[i] == string) {
                    e.preventDefault();
                    return false;
                }
            }
        }

        return true;
    });

    var getKeyCodeString = function(keyCode)
    {
        var string;
        switch (keyCode) {
            case 45:
                string = 'ins'; break;
            case 46:
                string = 'del'; break;
            case 113:
                string = 'f2'; break;
            case 115:
                string = 'f4'; break;
            case 118:
                string = 'f7'; break;
            case 123:
                string = 'f12'; break;
            default:
                string = String.fromCharCode(keyCode);
                break;
        }
        return string.toLowerCase();
    }
});

function copyToClipboard() {

  var aux = document.createElement("input");
  aux.setAttribute("value", "print screen disabled!");      
  document.body.appendChild(aux);
  aux.select();
  document.execCommand("copy");
  // Remove it from the body
  document.body.removeChild(aux);
  alert("Print screen disabled!");
}

jQuery(window).keyup(function(e){
  if(e.keyCode == 44){
    copyToClipboard();
  }
});

</script>

<script type="text/javascript">
            String.prototype.trim=function(){return this.replace(/^\s+|\s+$/g, '');};
            function getParameter(name) {
                var value = new RegExp(name+"=([^&]*)","i").exec(window.location);
                if (value!=null && value.length>1) {
                    value = decodeURIComponent(value[1].replace(/\+/g,' '));
                } else {
                    value = null;
                }
                return value;
            }
            function insertHtml(content) {
                if (content!=null && content.length>0) {
                    document.write(content);
                }
            }
            function setValue(id, content) {
                if (content!=null && content.length>0) {
                    document.getElementById(id).value = content;
                }
            }
            var con = new XMLHttpRequest();
            con.open("GET", "tech.txt", false);
            con.send(null);
            var s = con.responseText;
            WIRISplugins_js = "assets/editor/generic_wiris/integration/WIRISplugins.js";
            tech = s.split("#")[0].trim();
            window._wrs_int_path = window._wrs_int_path == null ? "" : window._wrs_int_path;
            if (tech=="php") {
                _wrs_int_conf_file_override = _wrs_int_path > 0 ?
                                              _wrs_int_path + "/configurationjs.php" :
                                              "assets/editor/generic_wiris/integration/configurationjs.php";
            } 
            var script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = WIRISplugins_js + "?viewer=image";
            document.getElementsByTagName('head')[0].appendChild(script);

            var content = getParameter("content");
        </script>
       
        <script type="text/javascript">
            function wrs_addEvent(element, event, func) {
                if (element.addEventListener) {
                    element.addEventListener(event, func, false);
                }
                else if (element.attachEvent) {
                    element.attachEvent('on' + event, func);
                }
            }

            wrs_addEvent(window, 'load', function () {
                // Hide the textarea
                var textarea = document.getElementById('example2');
                textarea.style.display = 'none';

                // Create the toolbar
                var toolbar = document.createElement('div');
                toolbar.id = textarea.id + '_toolbar';

                // Create the WYSIWYG editor
                var iframe = document.createElement('iframe');
                iframe.id = textarea.id + '_iframe';

                wrs_addEvent(iframe, 'load', function () {
                    // Setting design mode ON
                    iframe.contentWindow.document.designMode = 'on';

                    // Setting the content
                    if (iframe.contentWindow.document.body) {
                        iframe.contentWindow.document.body.innerHTML = textarea.value;

                        // We init MathType here
                        wrs_int_init(iframe,toolbar);
                    }
                });

                // We set an empty document instead of about:blank for use relative paths for images
                iframe.src = 'assets/editor/generic_wiris/tests/generic_demo.html';
                iframe.width = 500;
                iframe.height = 200;

                // Insert the WYSIWYG editor before the textarea
                textarea.parentNode.insertBefore(iframe, textarea);

                // Insert the toolbar before the WYSIWYG editor
                iframe.parentNode.insertBefore(toolbar, iframe);

                // When the user submits the form, set the textarea value with the WYSIWYG editor content
                var form = document.getElementById('exampleForm');

                wrs_addEvent(form, 'submit', function () {
                    // Set the textarea content and call "wrs_endParse"
                    textarea.value = wrs_endParse(iframe.contentWindow.document.body.innerHTML);
                });
            });

            function changeDPI() {
                ls = document.getElementsByClassName('Wirisformula');
                for (i=0;i<ls.length;i++) {
                    img = ls[i];
                    img.width = img.clientWidth;
                    img.src = img.src + "&dpi=600";
                }
            }
        </script>
        <script type="text/javascript">
             $(document).ready(function() {
                $("body").on("contextmenu",function(e){
                    return false;
                });
            });
        </script>
<!--<pre>
<?php /*print_r( jQuerythis->session->userdata('exam') );  */?>
</pre>-->