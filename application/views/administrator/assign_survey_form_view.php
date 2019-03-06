

<h1 class="heading">Assign Survey</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div id="select-training">
    <?php echo form_open('administrator/assign_survey', array('class' => 'form-inline')); ?>

        <label>
            Select a Survey from the list:&nbsp;&nbsp;&nbsp;
            <?php echo form_dropdown('survey_id', $this->survey_list, '', 'id="survey_id" class="chosen-select input-xxlarge"'); ?>&nbsp;&nbsp;&nbsp;
            <input type="submit" name="assign_survey_submit" value="Select Survey" class="btn btn-primary btn-large" />&nbsp;&nbsp;&nbsp;
        </label>

    <?php echo form_close(); ?>
</div>