

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> User Group</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/group/add_user_group', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/group/update_user_group', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group">
            <label class="control-label" for="group_name">User Group Name</label>
            <div class="controls">
                <input type="text" name="group_name" id="group_name" value="<?php echo set_value('group_name', $this->form_data->group_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>
        
        
        <div class="control-group">
            <label class="control-label" for="priv_ids">Privileges</label>
            <div class="controls">
                <?php echo form_multiselect('priv_ids[]', $this->priv_list, $this->form_data->priv_ids, 'id="priv_ids" class="chosen-select" style="width:40%;"'); ?>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="user_group_ids">User Group</label>
            <div class="controls">
                <?php echo form_multiselect('user_group_ids[]', $this->user_group_list, $this->form_data->user_group_ids, 'id="user_group_ids" class="chosen-select" style="width:40%;"'); ?>
                <span class="help-inline"><b>User Group for user password change</b></span>
            </div>
        </div>

        <div class="form-actions">
            <input type="hidden" name="group_id" value="<?php echo set_value('group_id', $this->form_data->group_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> User Group" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>