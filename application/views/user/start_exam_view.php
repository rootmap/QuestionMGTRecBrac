<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//print_r_pre($exam); die();
$exam_name = $exam->exam_title;
$exam_desc = $exam->exam_description;
$exam_mark = (int)$exam->exam_score;
$exam_total_questions = (int)$exam->exam_total_questions;
$exam_categories = $exam->exam_categories;

$exam_allow_dontknow = (int)$exam->exam_allow_dontknow;
$exam_allow_negative_marking = (int)$exam->exam_allow_negative_marking;
$exam_negative_mark_weight = (int)$exam->exam_negative_mark_weight;
$exam_negative_mark = 0;
if ($exam_total_questions > 0) {
    $exam_negative_mark = ($exam_mark / $exam_total_questions) * ($exam_negative_mark_weight / 100);
}
if ( ! is_int($exam_negative_mark) ) {
    $exam_negative_mark = number_format($exam_negative_mark, 2);
}

$error_text = '';

$exam_type = $exam->exam_type;
if ($exam_type == 'mcq') {

    $exam_type = 'Multiple Choice Questions (MCQ)';

    if ($exam_allow_negative_marking == 1) {
        $error_text .= 'For each wrong answer, your score will be deducted by '. $exam_negative_mark .'.';
    }
    if ($exam_allow_dontknow == 1) {
        if ($error_text != '') { $error_text .= '<br />'; }
        $error_text .= 'If you are not sure of an answer, you can pass that question by selecting "I don\'t know" option.';
    }
    if ($error_text != '') { $error_text = '<strong>Note:</strong> '. $error_text; }

} elseif($exam_type == 'descriptive') {
    $exam_type = 'Descriptive';
}

/* calculate exam time */
$exam_time = (int)$exam->exam_time;
/*if ($exam_time <= 0) {
    $exam_time = 'No time limit';
} elseif($exam_time == 1) {
    $exam_time = '1 minute';
} else {
    $exam_time = $exam_time .' minutes';
}*/



?>


<div id="running-exam">

    <!-- <h2 class="exam-title title"><?php //echo $exam_name; ?></h2>
    <?php //if ($exam_desc != ''): ?><div class="exam-desc"><?php //echo nl2br($exam_desc); ?></div><?php //endif; ?> -->

    <div class="exam-info">

        <!-- custom form start -->
        <table border="0" width="100%" align="center">
        <tbody>
            <tr>
                <td width="33%"></td>
                <td width="33%" align="center"><img width="50%" src="<?=base_url('assets/images/brac_bank.png')?>"></td>
                <td width="33%" align="right">
                    <!-- Set Code : <?php //$setID?> -->
                        
                    </td>
            </tr>
        </tbody>
    </table>
   <!--  <h3 style="border: 3px #ccc solid; padding-top: 10px; padding-bottom: 10px;" align="center">
        DO NOT OPEN THE QUESTIONNAIRE UNTIL YOU ARE DIRECTED TO DO SO
    </h3> -->
    <table border="0" width="100%" align="center">
        <tbody>
            <tr>
                <td width="50%" align="left"><b>Time</b> …… <?=date('H:i', mktime(0,$exam_time)).':00';?>   </td>
                <td width="50%" align="right"><b>Total Marks</b> : <?php if($this->setmark){ echo $this->setmark; }else{ echo 0; }  ?></td>
            </tr>
        </tbody>
    </table>
    <h4 style="padding-top: 5px; padding-bottom: 5px;" align="center">
        Written Examination for <?=$exam->exam_nop?$exam->exam_nop:' Not Mention.'?>
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
                                    <strong>Online</strong>
                                </td>
                            </tr>
                            <tr>
                                <td style="border: 1px #ccc solid;">
                                    <strong>Exam Location</strong>
                                </td>
                                <td style="border: 1px #ccc solid;">
                                    <strong>Online</strong>
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
                                <td style="border: 1px #ccc solid; height: 150px;" width="30%">
                                    <?php 
                                    $UpFilePath=base_url().'/assets/qrcode/index.php?data='.substr($exam->exam_title,0,2).$exam->id.'_'.$setID;
                                    $GenFileQR=file_get_contents($UpFilePath);
                                    $filePath=base_url().'/assets/qrcode/'.$GenFileQR;
                                    ?>
                                    <img src ="<?=$filePath?>" width="100%" height="100%">
                                </td>
                                <td style="border: 1px #ccc solid; height: 150px;" valign="middle" align="center">
                                    <?php 
                                    $filePath=base_url().'/uploads/user/'.$this->user_model->get_user_profile_path();
                                    $fileExists=file_exists('uploads/user/'.$this->user_model->get_user_profile_path());
                                    if(!$fileExists)
                                    {
                                        $filePath=base_url().'/assets/images/avatar.png';
                                    }
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

        <?php if ($error_text != ''): ?>
        <div class="notice alert alert-error">
            <?php echo $error_text; ?>
        </div>
        <?php endif; ?>
        
        <!-- <div class="notice alert alert-info">
            Please ensure that you have enough time and resource available to complete the exam. Once you start the
            exam you can't retake it, if any problem occurred in the middle of the exam.<br /><br />
            Please don't close the browser window before completing the exam.
        </div> -->

        <div class="start-exam"><?php echo form_open('exam'); ?>
            <input type="hidden" name="exam_is_started" value="1" />
            <a href="<?php echo base_url('home'); ?>" class="btn btn-large"><i class="icon-arrow-left"></i> Take Another Exam</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <!--<input type="submit"  value="" class="" />-->
            <button type="submit" name="start_exam_button" value="Start Exam" class="btn btn-danger btn-large">Start Exam <i class="icon-play icon-white"></i></button>
        <?php echo form_close(); ?></div>

        

    </div>

</div><!--running-exam ends-->


<!--<pre>
<?php /*print_r( $exam ); */?>
</pre>-->
