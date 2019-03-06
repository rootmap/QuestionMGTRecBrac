<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
$exam_name = $exam->exam_title;
$exam_time=$exam->exam_time;
?>

<div id="running-exam">


    <p align="center">
        <a href="<?php echo base_url('administrator/question_set/printHardcopy/'.$set_id.'/'.$exam_id.'/'.$exam_name) ?>" class="btn btn-info">Download Hardcopy</a>

    </p>




    <div class="exam-info">

        <!-- custom form start -->
        <table border="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="33%"></td>
                <td width="33%" align="center"><img width="50%" src="<?=base_url('assets/images/brac_bank.png')?>"></td>
                <td width="33%" align="right">Set Code : <?=$set_id?></td>
            </tr>
            </tbody>
        </table>
        <h3 style="border: 3px #ccc solid; padding-top: 10px; padding-bottom: 10px;" align="center">
            DO NOT OPEN THE QUESTIONNAIRE UNTIL YOU ARE DIRECTED TO DO SO
        </h3>
        <table border="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="50%" align="left"><b>Time</b> …… <?=date('H:i:s', strtotime($exam_time))?>   </td>
                <td width="50%" align="right"><b>Total Marks</b> : <?php if($set_info->total_mark){ echo (int)$set_info->total_mark; }else{ echo 0; }

                $exam_nop=$exam->exam_nop?$exam->exam_nop:'__________________';
                ?></td>
            </tr>
            </tbody>
        </table>
        <h4 style="padding-top: 5px; padding-bottom: 5px;" align="center">
            Written Examination for <?=$exam_nop?>
        </h4>
        <table border="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="50%" align="left" valign="middle">
                    <table  style="border: 1px #ccc solid;" cellpadding="5" cellspacing="0" width="80%" align="left">
                        <tbody>
                        <tr>
                            <td  style="border: 1px #ccc solid;" width="50%">
                                <strong>Exam Name</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong><?=$exam->exam_title?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Exam ID</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong><?=$exam->id?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Exam Centre</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong><?=$venues?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Exam Location</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong><?=$venue_location?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Candidate ID</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <strong><?=$user->user_login?></strong>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>NID/Passport No.</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <?=$user->nid_passport_no?>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">

                                <strong>Candidate&rsquo;s Signature</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <?php
                                if(!empty($user->signature_image))
                                {
                                    ?>
                                    <img src="<?=base_url('uploads/signature/'.$user->signature_image)?>">
                                    <?php
                                }
                                else
                                {
                                    echo "Not Found";
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px #ccc solid;">
                                <strong>Date</strong>
                            </td>
                            <td style="border: 1px #ccc solid;">
                                <?=date('d/m/Y')?>
                            </td>
                        </tr>
                        </tbody>
                    </table>



                    <table cellpadding="5" cellspacing="0" width="90%" align="center">
                        <tbody>
                        <tr>
                            <td  valign="middle" align="center">
                                <br><br><br><br>
                                <strong>For Official Use Only</strong>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </td>
                <td width="50%" align="right" valign="top">
                    <table cellpadding="5" cellspacing="0" width="100%" align="center">
                        <tbody>
                        <tr>
                            <td width="40%">
                                </td>
                                <td style="height: 150px;" width="30%">
                                    <?php 
                                    $UpFilePath=base_url().'/assets/qrcode/index.php?data='.substr($exam->exam_title,0,2).$exam->id;
                                    $GenFileQR=file_get_contents($UpFilePath);
                                    $filePath=base_url().'/assets/qrcode/'.$GenFileQR;
                                    ?>
                                    <img src ="<?=$filePath?>" width="100%" height="100%">
                            </td>
                            <td style="border: 1px #ccc solid; height: 150px;" valign="middle" align="center">
                                <?php
                                
                                    $filePath=base_url().'/assets/images/avatar.png';
                                
                                ?>
                                <img src ="<?=$filePath?>" width="100%" height="100%">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <br>
                    <table cellpadding="5" cellspacing="0" width="100%" align="center">
                        <tbody>
                        <tr>
                            <td style="border: 1px #ccc solid; padding:20px;" width="70%">
                                <p>
                                    <strong>
                                        <u>Instructions to Candidates</u>
                                    </strong>
                                </p>
                                <?php 
                                    if(!empty(trim($exam->exam_instructions)))
                                    {
                                        $exam_instructions=explode('->',$exam->exam_instructions);
                                        if(substr(trim($exam->exam_instructions),0,2)=="->")
                                        {
                                            $exam_instructions=explode('->',substr(trim($exam->exam_instructions),2,200000));
                                        }

                                        ?>

                                        <ul>
                                            <?php 
                                            foreach($exam_instructions as $ei):
                                                ?>
                                                <li><?=$ei?></li>
                                                <?php
                                            endforeach;
                                            ?>
                                        </ul>

                                        <?php
                                    }
                                    else
                                    {
                                        echo "Not Mention.";
                                    }
                                    ?>
                            </td>

                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <table  cellspacing="0" width="100%" align="center">
            <tbody>
            <tr>
                <td width="90%">
                    <table  style="border: 1px #ccc solid;" cellpadding="5" cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td  style="border: 1px #ccc solid;" width="10%">
                                <strong>Sec.</strong>
                            </td>
                            <td style="border: 1px #ccc solid;" width="29%">
                                <strong>Segment</strong>
                            </td>
                            <td  style="border: 1px #ccc solid;" width="29%">
                                <strong>Allocated Mark</strong>
                            </td>
                            <td  style="border: 1px #ccc solid;" width="29%">
                                <strong>Obtained Mark</strong>
                            </td>
                        </tr>
                        <?php
                        $totalSetMark=0;
                        if(isset($examSetInfo) && !empty($examSetInfo))
                        {
                            foreach ($examSetInfo as $key=>$sinfo) {
                                ?>
                                <tr>
                                    <td  style="border: 1px #ccc solid;">
                                        <?=($key+1)?>
                                    </td>
                                    <td style="border: 1px #ccc solid;">
                                        <?=$sinfo['cat_name']?>
                                    </td>
                                    <td  style="border: 1px #ccc solid;">
                                        <?=$sinfo['summary_row']?>
                                    </td>
                                    <td  style="border: 1px #ccc solid;">

                                    </td>
                                </tr>
                                <?php
                                $totalSetMark+=$sinfo['total_mark'];
                            }
                        }
                        ?>


                        </tbody>
                    </table>
                </td>
                <td style="border: 1px #ccc solid;">

                </td>
            </tr>
            <tr>
                <td>
                    <table   cellspacing="0" width="100%" align="left">
                        <tbody>
                        <tr>
                            <td align="center" width="39%">
                                <strong>Total</strong>
                            </td>
                            <td width="29%">
                                <strong><?=$totalSetMark?></strong>
                            </td>
                            <td width="29%">

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
                <td  align="center">
                    <b>Invigilator&#39;s PIN &amp; Signature</b>
                </td>
            </tr>
            </tbody>
        </table>

        <!-- custom form end-->

        <!-- <div class="row-fluid">
            <div class="span6">

                <h3 class="title">The exam will cover following topics</h3>

                <?php //if ($exam_categories): ?>
                <ul>
                    <?php //for($i=0; $i<count($exam_categories); $i++): ?>
                    <li><?php //echo $exam_categories[$i]['name']; ?></li>
                    <?php //endfor; ?>
                </ul>
                <?php //endif; ?>

            </div>
            <div class="span6">

                <h3 class="title">Exam information</h3>

                <p class="meta">
                    <strong>Type of Exam</strong>
                    <span><?php //echo $exam_type; ?></span>
                </p>
                <p class="meta">
                    <strong>Duration</strong>
                    <span><?php //if($this->timeQus){ echo $this->timeQus; }else{ echo 0; }  ?></span>
                </p>
                <p class="meta">
                    <strong>Total Questions</strong>
                    <span><?php //echo $exam_total_questions; ?></span>
                </p>
                <p class="meta">
                    <strong>Total Marks</strong>
                    <span><?php //if($this->setmark){ echo $this->setmark; }else{ echo 0; }  ?></span>
                </p>

            </div>
        </div> -->



        <!-- <div class="notice alert alert-info">
            Please ensure that you have enough time and resource available to complete the exam. Once you start the
            exam you can't retake it, if any problem occurred in the middle of the exam.<br /><br />
            Please don't close the browser window before completing the exam.
        </div> -->
 <br><br>
        <div class="">
            <h3>Question List :</h3>
            <br><br>
        </div>



    </div>



    <div class="exam-header">
        <!--<div class="qn"><?php //echo $question_number_str; ?></div>

    </div><!--exam-header ends-->

    <?php


    $strHtml= '<div style="page-break-before:always"></div>
      <h4 align="center">Questions Paper </h4><br><br>';

       $questionsCateWithMark= $this->question_set_model->get_question_category_by_ques_set_id($set_id);
      //questionsCateWithMark
      $slqsPart=1;
      foreach($questionsCateWithMark as $catMark):

      $strHtml .='<table border="0" width="100%">
                    <tbody>
                      <tr>
                        <td width="90%" style="border-bottom:2px #000 solid;">
                          <b>'.$slqsPart.'. '.$catMark->cat_name.'</b>
                        </td>
                        <td>
                          <b>'.number_format($catMark->total,2).'</b>
                        </td>
                      </tr>
                    </tbody>    

                  </table><br><br>';

        $questions= $this->question_set_model->get_question_by_ques_cat_set_id($set_id,$catMark->category_id);

        //print_r_pre($questions);

        for($i=0; $i<count($questions); $i++){
            $quesNo=$i+1;
            $strHtml .=  $quesNo. ') ' .$questions[$i]->ques_text . '&nbsp;('.$questions[$i]->mark.')';
            $strHtml .='<br>';

            $char = 'A';
            if($questions[$i]->ques_type=='mcq') {
              $strHtml .='<br>';
              foreach ($questions[$i]->ques_choices as $vaue) {
                $strHtml .='&nbsp;&nbsp;&nbsp;' . $char . ') ';
                $strHtml .=$vaue['text'];
                $strHtml .='<br>';
                $char++;
              }
              $strHtml .='<br>';
              $strHtml .='<br>';
            }
            else
            {
              $strHtml .='<div style="page-break-before:always"></div>';
            }
          }

          $slqsPart++;
      endforeach;

      echo $strHtml;
 ?>


<div class="mask-layer"></div>
</div><!--running-exam ends-->

<style>
    textarea {
        width: auto;
    }

</style>



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