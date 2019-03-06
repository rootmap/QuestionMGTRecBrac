

<h1 class="heading">Review Results</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<div id="select-exam">
<?php echo form_open('administrator/result', array('class' => 'form-inline')); ?>

    <label>
        Select Exam:&nbsp;&nbsp;&nbsp;
        <?php echo form_dropdown('exam_id', $this->exam_list, $this->form_data->exam_id, 'id="exam_id" class="chosen-select"'); ?>&nbsp;&nbsp;&nbsp;
    </label>
    <label>
        Select User Team:&nbsp;&nbsp;&nbsp;
        <?php echo form_dropdown('user_team_id', $this->user_team_list, $this->form_data->user_team_id, 'id="user_team_id" class="chosen-select"'); ?>&nbsp;&nbsp;&nbsp;
    </label>

    <input type="submit" name="filter_results_submit" value="Show Results" class="btn btn-primary" />&nbsp;&nbsp;&nbsp;

<?php echo form_close(); ?>
</div>


<?php if (isset($records_table) && $records_table != '') : ?>
<div class="row-fluid"><div class="span12">
    
    <?php echo $records_table; ?>

</div></div>
<?php endif; ?>