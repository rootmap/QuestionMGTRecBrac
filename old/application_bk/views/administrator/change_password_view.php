<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h1 class="heading">Change Password </h1>
<code style="width: 400px; position:absolute; right: 30px;">
        <h4>New password should contain.</h4>
        <br>
        <ul>
            <li>Minimum length (8)</li>
            <li>Should contain complexity</li>
            <li>Should contain capital & lower alphabet</li>
            <li>Should contain Special, Symbolic, Numaric Character</li>
            <li>e.g. [123@<]Aa</li>
        </ul>
</code>
<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<?php echo form_open('administrator/profile/change_password', array('class' => 'form-horizontal')); ?>

    <div class="control-group">
        <label class="control-label" for="old_password">Current Password</label>
        <div class="controls">
            <input type="password" name="old_password" id="old_password" placeholder="Current Password" />
            <span class="help-inline"></span>
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
</div>

<?php echo form_close(); ?>


<script type="text/javascript">
jQuery(document).ready(function(){

    jQuery('#old_password').blur(function(){
        if (jQuery(this).val() == '') {
            jQuery(this).parent().parent().addClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('this field is required');
        } else {
            jQuery(this).parent().parent().removeClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('');
        }
    });

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

        if (jQuery('#old_password').val() == '') {
            hasError = true;
            jQuery('#old_password').parent().parent().addClass('error');
            jQuery('#old_password').parent().parent().find('.help-inline').html('this field is required');
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
