

<h1 class="heading">Assign Exam to Users</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div id="select-exam">
    <?php echo form_open('administrator/assign_exam', array('class' => 'form-inline')); ?>

        <label>
            Select an Exam from the list:&nbsp;&nbsp;&nbsp;
            <?php echo form_dropdown('exam_id', $this->exam_list, $this->form_data->exam_id, 'id="exam_id" class="chosen-select"'); ?>&nbsp;&nbsp;&nbsp;
            <!--<input type="submit" name="view_assigned_exam_submit" value="View Assigned Exam Status" class="btn" />&nbsp;&nbsp;&nbsp;-->
            <input type="submit" name="assign_exam_submit" value="Assign Exam" class="btn btn-primary" />&nbsp;&nbsp;&nbsp;
        </label>

    <?php echo form_close(); ?>
</div>