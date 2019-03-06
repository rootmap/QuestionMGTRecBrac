

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> User Team</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/team/add_user_team', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/team/update_user_team', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="team_name">User Team Name</label>
            <div class="controls">
                <input type="text" name="team_name" id="team_name" value="<?php echo set_value('team_name', $this->form_data->team_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="group_id">Group</label>
            <div class="controls">
                <?php echo form_dropdown('group_id', $this->user_group_list, $this->form_data->group_id, 'id="group_id" class="chosen-select input-xlarge"'); ?>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>


        <div class="form-actions">
            <input type="hidden" name="team_id" value="<?php echo set_value('team_id', $this->form_data->team_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> User Team" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>