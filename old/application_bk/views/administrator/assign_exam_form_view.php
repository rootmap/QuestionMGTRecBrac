

<h1 class="heading">Assign Exam</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div id="select-exam">
    <?php echo form_open('administrator/assign_exam', array('class' => 'form-inline')); ?>

        <label>
            Select an Exam from the list:&nbsp;&nbsp;&nbsp;
            <?php echo form_dropdown('exam_id', $this->exam_list, '', 'id="exam_id" class="chosen-select input-xxlarge"'); ?>&nbsp;&nbsp;&nbsp;
            <input type="submit" name="assign_exam_submit" value="Select Exam" class="btn btn-primary btn-large" />&nbsp;&nbsp;&nbsp;
        </label>

    <?php echo form_close(); ?>
</div>