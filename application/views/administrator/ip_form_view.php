

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?>  IP</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/settings/do_add_ip', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/settings/update_ip', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>
 
        <div class="control-group">
            <label class="control-label" for="priv_ids">Admin Group</label>
            <div class="controls">
                <?php echo form_dropdown('admin_list', $this->admin_list, $this->form_data->admin_list, 'id="priv_ids" class="chosen-select" style="width:40%;"'); ?>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="allowed_ip">IP</label>
            <div class="controls"> 
             <input type="text" name="allowed_ip" id="allowed_ip" value="<?php echo set_value('allowed_ip', $this->form_data->allowed_ip); ?>" class="input-xlarge" />
            </div>
        </div>  
        
        <div class="control-group">
            <label class="control-label" for="ip_is_active">Is Active?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="ip_is_active" id="ip_is_active" value="1" <?php echo set_radio('ip_is_active', '1', $this->form_data->ip_is_active== '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="ip_is_active" id="ip_is_active" value="0" <?php echo set_radio('ip_is_active', '0', $this->form_data->ip_is_active== '0'); ?> /> No
                </label>
            </div>
        </div>
        
        <div class="form-actions">
            <input type="hidden" name="allowed_ip_id" value="<?php echo set_value('allowed_ip_id', $this->form_data->allowed_ip_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Admin IP" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>