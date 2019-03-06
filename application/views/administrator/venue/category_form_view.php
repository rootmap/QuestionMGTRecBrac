

<h3 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> <?=$view_controller?></h3>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/'.$view_controller.'/add_venue', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/'.$view_controller.'/update_venue', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="name">Venue Name</label>
            <div class="controls">
                <input type="text" name="name" id="name" value="<?php echo set_value('name', $this->form_data->name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="name">Venue Start Time</label>
            <div class="controls">
                <input type="text" name="start_time" readonly="readonly" id="start_time" value="<?php echo set_value('start_time', $this->form_data->start_time); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="name">Venue End Time</label>
            <div class="controls">
                <input type="text" name="end_time" id="end_time" readonly="readonly" value="<?php echo set_value('end_time', $this->form_data->end_time); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>
        <div class="control-group formSep">
            <label class="control-label" for="name">Venue Address</label>
            <div class="controls">
                <textarea name="address" id="address" class="input-xlarge"><?php echo set_value('address', $this->form_data->address); ?></textarea>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>
        <div class="form-actions">
            <input type="hidden" name="venue_id" value="<?php echo set_value('id', $this->form_data->id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Venue" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>

<script type="text/javascript">
            $('#start_time').timepicker();
            $('#end_time').timepicker();
</script>