<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h1 class="heading">Update Profile</h1>

<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<?php echo form_open('administrator/profile/update_profile', array('class' => 'form-horizontal')); ?>

    <div class="control-group" style="margin-bottom: 0px;">
        <label class="control-label">Login ID</label>
        <div class="controls">
            <strong style="display: inline-block; padding-top: 5px;"><?php echo $user->user_login; ?></strong>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label">Team Name</label>
        <div class="controls">
            <strong style="display: inline-block; padding-top: 5px;"><?php echo $user->user_team_name; ?></strong>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="user_first_name">First Name</label>
        <div class="controls">
            <input type="text" name="user_first_name" id="user_first_name" value="<?php echo $user->user_first_name; ?>" placeholder="Your first name" />
            <span class="help-inline"></span>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="user_last_name">Last Name</label>
        <div class="controls">
            <input type="text" name="user_last_name" id="user_last_name" value="<?php echo $user->user_last_name; ?>" placeholder="Your last name" />
            <span class="help-inline"></span>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="user_email">Email</label>
        <div class="controls">
            <input type="text" name="user_email" id="user_email" value="<?php echo $user->user_email; ?>" placeholder="Your email address" />
            <span class="help-inline"></span>
        </div>
    </div>


    <div class="form-actions">
        <input type="submit" id="update-profile-submit" value="Update Profile" class="btn btn-primary btn-large" />&nbsp;&nbsp;
        <input type="reset" class="btn btn-large" value="Reset" />
    </div>

<?php echo form_close(); ?>


<script type="text/javascript">
jQuery(document).ready(function(){

    jQuery('#user_first_name').blur(function(){
        if (jQuery(this).val() == '') {
            jQuery(this).parent().parent().addClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('this field is required');
        } else {
            jQuery(this).parent().parent().removeClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('');
        }
    });

    jQuery('#user_last_name').blur(function(){
        if (jQuery(this).val() == '') {
            jQuery(this).parent().parent().addClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('this field is required');
        } else {
            jQuery(this).parent().parent().removeClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('');
        }
    });

    jQuery('#user_email').blur(function(){
        if (jQuery(this).val() == '') {
            jQuery(this).parent().parent().addClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('this field is required');
        } else {
            jQuery(this).parent().parent().removeClass('error');
            jQuery(this).parent().parent().find('.help-inline').html('');
        }
    });

    jQuery('form').submit(function(){

        var hasError = false;

        if (jQuery('#user_email').val() == '') {
            hasError = true;
            jQuery('#user_email').focus();
            jQuery('#user_email').parent().parent().addClass('error');
            jQuery('#user_email').parent().parent().find('.help-inline').html('this field is required');
        }

        if (jQuery('#user_last_name').val() == '') {
            hasError = true;
            jQuery('#user_last_name').focus();
            jQuery('#user_last_name').parent().parent().addClass('error');
            jQuery('#user_last_name').parent().parent().find('.help-inline').html('this field is required');
        }

        if (jQuery('#user_first_name').val() == '') {
            hasError = true;
            jQuery('#user_first_name').focus();
            jQuery('#user_first_name').parent().parent().addClass('error');
            jQuery('#user_first_name').parent().parent().find('.help-inline').html('this field is required');
        }

        if (hasError) {
            return false;
        }

    });

});
</script>