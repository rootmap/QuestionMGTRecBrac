<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<div id="exam-cont" class="row-fluid">

    <div class="span3">

        <h2 class="title">Available Exams</h2>

        <?php if ($open_exams): ?>

            <?php for($i=0; $i<count($open_exams); $i++): ?>

            <?php echo form_open('startexam'); ?>
            <div class="exam">

                <?php

                $user_exam_id = $open_exams[$i]->id;
                $exam_id = $open_exams[$i]->exam_id;
                $exam_name = $open_exams[$i]->exam->exam_title;

               // $exam_type = $open_exams[$i]->exam->exam_type;
                $exam_type = $open_exams[$i]->exam_type_string;
                /*if ($exam_type == 'mcq') {
                    $exam_type = 'Multiple Choice Questions (MCQ)';
                } elseif ($exam_type == 'descriptive') {
                    $exam_type = 'Descriptive';
                }*/

                $exam_time = (int)$open_exams[$i]->exam->exam_time;
                if ($exam_time <= 0) {
                    $exam_time = 'No time limit.';
                } else if ($exam_time == 1) {
                    $exam_time = '1 minute.';
                } else {
                    $exam_time = $exam_time .' minutes.';
                }

                
                $examdetails = $open_exams[$i]->exam->exam_no_of_questions;
                //print_r_pre($examdetails);
                $exam_questions = $examdetails->set_limit;
                if ($exam_questions <= 0) {
                    $exam_questions = '0 question';
                } else if ($exam_questions == 1) {
                    $exam_questions = '1 question';
                } else {
                    $exam_questions = $exam_questions .' questions';
                }

                $exam_mark = $examdetails->total_mark;
                if ($exam_mark <= 0) {
                    $exam_mark = '0 mark';
                } else if ($exam_mark == 1) {
                    $exam_mark = '1 mark';
                } else {
                    $exam_mark = $exam_mark .' marks';
                }

                $Negativemark = $examdetails->neg_mark_per_ques;
                if ($Negativemark <= 0) {
                    $Negativemark = '0 Negative Mark';
                } else if ($Negativemark == 1) {
                    $Negativemark = '1 mark';
                } else {
                    $Negativemark = $Negativemark .' Negative marks';
                }

                $exam_mark_str = $exam_mark .' for '. $exam_questions;

                $exam_expire = 'Exam will expire on '. date('jS F, Y', strtotime($open_exams[$i]->ue_end_date)) .', '. date_when(strtotime($open_exams[$i]->ue_end_date));
                $exam_description = $open_exams[$i]->exam->exam_description;

                ?>

                <h3 class="title"><?php echo $exam_name; ?></h3>
                <div class="meta">
                    <div class="type"><?php echo $exam_type; ?></div>
                    <div class="time"><?php echo $exam_time; ?></div>
                    <div class="mark"><?php echo $exam_mark_str; ?></div>
                    <div class="expire"><?php echo $exam_expire; ?></div>
                    <div class="expire"><?php echo $Negativemark; ?></div>
                </div>

                <?php if ($exam_description != ''): ?>
                <div class="desc">
                    <?php echo nl2br($exam_description); ?>
                </div>
                <?php endif; ?>

                <div class="action">
                    <?php echo form_hidden('exam_id', $exam_id)?>
                    <?php echo form_hidden('user_exam_id', $user_exam_id)?>
                    <?php echo form_hidden('setmark', $examdetails->total_mark)?>
                    <?php echo form_hidden('totalqus', $examdetails->set_limit)?>
                    <?php echo form_hidden('timeQus', $open_exams[$i]->exam->exam_time)?>
                    <?php echo form_hidden('qus_set', $examdetails->category_id)?>
                    <?php echo form_hidden('immediate_result', $open_exams[$i]->immediate_result)?>
                    <input type="submit" name="take_exam_submit" value="Take Exam" class="btn btn-danger pull-right" />
                </div>

            </div>
            <?php echo form_close(); ?>

            <?php endfor; ?>

        <?php else: ?>

            <div class="alert alert-error">No exam is available at this moment</div>

        <?php endif; ?>

    </div>
    <div class=" span3">
        
        <h2 class="title">Available Admit Cards</h2>



        <?php if ($all_open_exams): ?>

            <?php for($i=0; $i<count($all_open_exams); $i++): ?>

                <?php //if(isset($examdetails->category_id)){ ?>
                <div class="exam">

                    <?php

                    $user_exam_id = $all_open_exams[$i]->id;
                    $exam_id = $all_open_exams[$i]->exam_id;
                    $exam_name = $all_open_exams[$i]->exam->exam_title;
                    @$exam_type = $open_exams[$i]->exam_type_string;

                    /*$exam_type = $all_open_exams[$i]->exam->exam_type;
                    if ($exam_type == 'mcq') {
                        $exam_type = 'Multiple Choice Questions (MCQ)';
                    } elseif ($exam_type == 'descriptive') {
                        $exam_type = 'Descriptive';
                    }*/

                    $exam_time = (int)$all_open_exams[$i]->exam->exam_time;
                    if ($exam_time <= 0) {
                        $exam_time = 'No time limit.';
                    } else if ($exam_time == 1) {
                        $exam_time = '1 minute.';
                    } else {
                        $exam_time = $exam_time .' minutes.';
                    }


                    $examdetails = $all_open_exams[$i]->exam->exam_no_of_questions;
                    $exam_expire = 'Exam will expire on '. date('jS F, Y', strtotime($all_open_exams[$i]->ue_end_date)) .', '. date_when(strtotime($all_open_exams[$i]->ue_end_date));
                    $exam_description = $all_open_exams[$i]->exam->exam_description;

                    ?>

                    <h3 class="title"><?php echo $exam_name; ?></h3>
                    <div class="meta">
                        <div class="type"><?php echo $exam_type; ?></div>
                        <div class="time"><?php echo $exam_time; ?></div>

                    </div>

                    <?php if ($exam_description != ''): ?>
                        <div class="desc">
                            <?php echo nl2br($exam_description); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="action">

                        <a href="<?php echo base_url('home/download_admitcard/'.$exam_id.'/'.$examdetails->category_id.'/'.$user_exam_id); ?>" name="download_admit_card"  class="btn btn-danger pull-right"> Download Admit Card </a>
                    </div>



                </div>
                <?php //} ?>

            <?php endfor; ?>

        <?php else: ?>

            <div class="alert alert-error">No exam is available at this moment</div>

        <?php endif; ?>


    </div>

    <div class=" span6" style="padding-left: 10px;">

        <h2 class="title">Completed Exams</h2>

        <?php if ($prev_exams) : ?>

        <div class="alert alert-info">Showing exams which was taken in last 3 months</div>


        <table class="table table-hover table-condensed" id="prev-exams">
            <?php
            for($i=0; $i<count($prev_exams); $i++):

                $exam_title = $prev_exams[$i]->exam_title;
                $start_time = $prev_exams[$i]->result_start_time;

                $exam_score = (int)$prev_exams[$i]->result_exam_score;
                $user_score = $prev_exams[$i]->result_user_score;
                $competency_level = $prev_exams[$i]->result_competency_level;
                $bothScore=$user_score+$exam_score;
                if(!empty($bothScore))
                {
                    $user_score_percent = number_format((float)(($user_score / $exam_score) * 100), 2);
                }
                else
                {
                    $user_score_percent =0;
                }
                

            ?>
            <tr>
                <td width="45%"><?php echo $exam_title; ?></td>
                <td style="width: 90px;">
                    <?php echo $user_score; ?> (<?php echo $user_score_percent; ?>%)
                    <?php if ($competency_level != '') { echo '<br />'. $competency_level; } ?>
                </td>
                <td style="width: 95px;"><?php echo date('jS M Y,', strtotime($start_time)); ?><br />at <?php echo date('g:i a', strtotime($start_time)); ?></td>
                <td style="width: 25px; vertical-align: middle;">
                <?php echo form_open('result'); ?>
                    <input type="hidden" name="referer_page" value="homepage" >
                    <input type="hidden" name="user_exam_id" value="<?php echo (int)$prev_exams[$i]->user_exam_id; ?>" >
                    <button type="submit" style="background: none; border: 0;"><span class="icon-eye-open"></span></button>
                <?php echo form_close(); ?>
                </td>
            </tr>
            <?php endfor; ?>
        </table>

        <?php else: ?>

        <div class="alert alert-info">You haven't taken any exams in last 3 months</div>

        <?php endif; ?>

    </div>

</div>


<!--For Survey-->


            

            <div class="left">
                <div id="accordian_id" class="accordion">                    
                    <div class="accordion-group" id="survey">
                        <div class="accordion-heading">
                            <a href="#" data-parent="#" data-toggle="collapse" class="accordion-toggle">
                                Open Survey
                            </a>
                        </div>
                        <div class="accordion-body in collapse" id="accordian_id6">
                            <div class="accordion-inner">
                                <?php echo $open_survey_html; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-group" id="survey">
                        <div class="accordion-heading">
                            <a href="#" data-parent="#" data-toggle="collapse" class="accordion-toggle">
                                Completed Survey                            </a>
                        </div>
                        <div class="accordion-body in collapse" id="accordian_id8">
                            <div class="accordion-inner">

                                <?php echo $completed_survey_html; ?>

                            </div>
                        </div>
                    </div>
                </div>                 
            </div><!--left ends-->
            

    <!--End Survey-->


<script type="text/javascript">
jQuery(document).ready(function(){
    
})
</script>

<!--<pre>
<?php /*print_r($top_categories);  */?>
</pre>-->




<style>



        ul.list li a,
        ol.list li a {
            text-decoration: none;
            color: #333333;
            font-size: 13px;

        }

        ul.list li a:hover,
        ol.list li a:hover {
            text-decoration: none;
            color: darkred;
            font-size: 15px;

        }




    .heading {
        margin-bottom: 18px;
        padding-bottom: 5px;
        font-weight: normal;
        border-bottom:  1px solid #dcdcdc;
    }

    .accordion-heading .accordion-toggle {
        background-color: #f5f5f5;
        background-image: url(assets/frontend/images/acc_icons.png);
        background-position: 98% 12px;
        background-repeat: no-repeat;
        color: #222222;
        text-decoration: none;
    }
    .accordion-heading .accordion-toggle:hover {
        background-color: #e5e5e5;
    }
    .accordion-heading .acc-in { background-position: 98% -34px; }
    .accordion-toggle { -moz-transition: background-color 0.2s ease-in-out 0s; }

</style>
