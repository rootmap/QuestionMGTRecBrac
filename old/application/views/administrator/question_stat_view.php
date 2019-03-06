
<h1 class="heading" style="width: 100%; overflow: hidden;">
    Question Statistics
    <a href="<?php echo base_url('administrator/question'); ?>" class="btn pull-right"><i class="icon-arrow-left"></i> Back to Questions</a>
</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">


        <div class="well">

            <div style="font-size:18px;" class="row-fluid"><div class="span12 center">
                Appeared to
                <span style="font-size:40px;line-height:1em;"><?php echo (int)$user_count; ?></span>
                user(s) and in
                <span style="font-size:40px;line-height:1em;"><?php echo (int)$exam_count; ?></span>
                exam(s)
            </div></div>

            <div style="font-size:18px;padding-bottom:65px;background: url(<?php echo base_url('assets/backend/images'); ?>/divider-line.png) no-repeat bottom center" class="row-fluid"><div class="span12 center">
                this question appeared total <span style="font-size:40px;line-height:1em;"><?php echo ($correct_count + $wrong_count + $dontknow_count + $unanswered_count); ?></span> times<br />
                total <span style="font-size:40px;line-height:1em;"><?php echo $total_used_question_in_category_count; ?></span> questions appeared from the question's category<br />
                total <span style="font-size:40px;line-height:1em;"><?php echo $total_used_question_count; ?></span> questions appeared overall from the question bank
            </div></div>

            <div style="margin-top: 0;" class="row-fluid result_summary">
                <div style="text-align: center;" class="span3">
                    <div class="big_text success"><?php echo $correct_count; ?></div>
                    <div>correct answer(s)</div>
                    <div style="margin-top: 7px;">
                        <a href="<?php echo base_url('administrator/question/stats/correct/'. $question_id); ?>" class="btn btn-success">Details</a>
                    </div>
                </div>
                <div style="text-align: center;" class="span3">
                    <div class="big_text error"><?php echo $wrong_count; ?></div>
                    <div>wrong answer(s)</div>
                    <div style="margin-top: 7px;">
                        <a href="<?php echo base_url('administrator/question/stats/wrong/'. $question_id); ?>" class="btn btn-danger">Details</a>
                    </div>
                </div>
                <div style="text-align: center;" class="span3">
                    <div class="big_text info"><?php echo $dontknow_count; ?></div>
                    <div>dont know answer(s)</div>
                    <div style="margin-top: 7px;">
                        <a href="<?php echo base_url('administrator/question/stats/dontknow/'. $question_id); ?>" class="btn btn-info">Details</a>
                    </div>
                </div>
                <div style="text-align: center;" class="span3">
                    <div class="big_text"><?php echo $unanswered_count; ?></div>
                    <div>not answered</div>
                    <div style="margin-top: 7px;">
                        <a href="<?php echo base_url('administrator/question/stats/unanswered/'. $question_id); ?>" class="btn">Details</a>
                    </div>
                </div>
            </div>

        </div>

        
        <div class="row control-row control-row-top">
            <div class="span6 left">
            <?php /*echo form_open('administrator/question/filter', array('class' => 'form-inline', 'id' => 'filter-form')); */?><!--

                <?php /*echo form_dropdown('filter_category', $this->cat_list_filter, $this->form_data->filter_category, 'id="filter_category" class="chosen-select"'); */?>
                <?php /*echo form_dropdown('filter_type', $this->type_list_filter, $this->form_data->filter_type, 'id="filter_type" class="chosen-select"'); */?>
                <?php /*echo form_dropdown('filter_expired', $this->expired_list_filter, $this->form_data->filter_expired, 'id="filter_expired" class="chosen-select"'); */?>

                &nbsp;
                <input type="submit" value="Filter" class="btn" />

            --><?php /*echo form_close(); */?>
            </div>
            <div class="span6 right">
                
                <?php echo $pagin_links; ?>

            </div>
        </div>

        <?php echo $records_table; ?>

        <div class="row control-row control-row-bottom">
            <div class="span6 left">&nbsp;</div>
            <div class="span6 right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

    </div>
</div>

<style type="text/css">

    .result_summary { font-size: 16px; font-weight: bold; }
    .big_text { color: #999999; font-weight: bold; font-size: 60px; text-align: center; line-height: 1em; text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25); }
    .big_text.success { color: #70A415; }
    .big_text.error { color: #B94A48; }
    .big_text.info { color: #3A87AD; }

</style>