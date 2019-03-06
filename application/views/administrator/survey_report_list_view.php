
<h1 class="heading">Survey Reports</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">

        <div class="row control-row control-row-top">
            <div class="span6 left">
            <?php echo form_open('administrator/survey_report/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <?php echo form_dropdown('filter_survey', $this->survey_list, $this->form_data->filter_survey, 'id="filter_survey" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_question', $this->question_list, $this->form_data->filter_question, 'id="filter_question" class="chosen-select"'); ?>
                &nbsp;
                <span class="btn-group">
                    <input type="submit" value="Filter" class="btn" />
                    <?php if (count($filter) > 0): ?>
                    <button type="submit" name="filter_clear" value="Clear" title="Clear Filter" class="btn"><i class="icon-remove"></i></button>
                    <?php endif; ?>
                </span>

            <?php echo form_close(); ?>
            </div>
            
            <div class="span2">
                <?php if ($records && count($records) > 0): ?>
                    <?php echo form_open('administrator/survey_report/export_data'); ?>
                    <button type="submit" class="btn"><i class="icon-download-alt"></i>Export Data</button>
                    <?php echo form_close(); ?>
                <?php endif; ?> 
            </div>
            
            
            <div class="span4 right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

        <?php echo $records_table; ?>

        <div class="row">
            <div class="span6"></div>
            <div class="span6" style="text-align: right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

    </div>
</div>

