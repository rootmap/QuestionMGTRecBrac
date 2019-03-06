<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h1 class="heading">Change Password</h1>

<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<?php echo form_open('administrator/profile/user_change_password', array('class' => 'form-horizontal')); ?>

    <div class="control-group">
       <label class="control-label" for="user_id">User</label>
       <div class="controls">
           <?php echo form_dropdown('user_id', $this->user_list, @$this->form_data->user_id, 'id="user_id" class="chosen-select" style="width:40%;"'); ?>
           
       </div>
    </div>

    <div class="control-group" style="margin-bottom: 10px;">
        <label class="control-label" for="new_password">New Password</label>
        <div class="controls">
            <input type="password" name="new_password" id="new_password" placeholder="New Password" />
            <span class="help-inline"></span>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="old_password">&nbsp;</label>
        <div class="controls">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" />
            <span class="help-inline"></span>
        </div>
    </div>


    <div class="form-actions">
        <input type="submit" id="change-password-submit" value="Change Password" class="btn btn-primary btn-large" />&nbsp;&nbsp;
        <input type="reset" class="btn btn-large" value="Reset" />
    </div>

<?php echo form_close(); ?>


<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#new_password').blur(function(){
        if (jQuery(this).val() == '') {
            jQuery(this).parent().parent().addClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('this field is required');
        } else {
            jQuery(this).parent().parent().removeClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('');
        }
    });

    jQuery('#confirm_password').blur(function(){
        if (jQuery(this).val() == '') {
            jQuery(this).parent().parent().addClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('this field is required');
        } else if (jQuery('#new_password').val() != jQuery('#confirm_password').val()) {
            jQuery(this).parent().parent().addClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('new password and confirm password does not match');
        } else {
            jQuery(this).parent().parent().removeClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('');
        }
    });

    jQuery('form').submit(function(){

        var hasError = false;
        
        if (jQuery('#user_id').find(":selected").val() == '' || jQuery('#user_id').find(":selected").val() == 0) {
            hasError = true;
            jQuery('#user_id').parent().parent().addClass('error');
            jQuery('#user_id').parent().parent().find('.help-inline').html('this field is required');
        }
        
        if (jQuery('#new_password').val() == '') {
            hasError = true;
            jQuery('#new_password').parent().parent().addClass('error');
            jQuery('#new_password').parent().parent().find('.help-inline').html('this field is required');
        }

        if (jQuery('#confirm_password').val() == '') {
            hasError = true;
            jQuery('#confirm_password').parent().parent().addClass('error');
            jQuery('#confirm_password').parent().parent().find('.help-inline').html('this field is required');
        } else if (jQuery('#new_password').val() != jQuery('#confirm_password').val()) {
            hasError = true;
            jQuery('#confirm_password').parent().parent().addClass('error');
            jQuery('#confirm_password').parent().parent().find('.help-inline').html('new password and confirm password does not match');
        }

        if (hasError) {
            return false;
        }

    });

});
</script>
