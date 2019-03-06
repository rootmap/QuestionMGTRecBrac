
<h1 class="heading">Email Settings</h1>

<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>
<?php if ($message_info != '') { echo '<div class="well well-small"><a data-dismiss="alert" class="close">&times;</a>'. $message_info .'</div>'; } ?>


<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open('administrator/settings/update_email', array('class' => 'form-horizontal')); ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="email_from_name">From Name</label>
            <div class="controls">
                <input type="text" name="email_from_name" id="email_from_name" value="<?php echo set_value('email_from_name', $this->form_data->email_from_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline"></span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="email_smtp_host">SMTP Host</label>
            <div class="controls">
                <input type="text" name="email_smtp_host" id="email_smtp_host" value="<?php echo set_value('email_smtp_host', $this->form_data->email_smtp_host); ?>" class="input-xlarge" />
                <!--<span class="help-inline"></span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="email_smtp_port">SMTP Port</label>
            <div class="controls">
                <input type="text" name="email_smtp_port" id="email_smtp_port" value="<?php echo set_value('email_smtp_port', $this->form_data->email_smtp_port); ?>" class="input-xlarge" />
                <!--<span class="help-inline"></span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="email_smtp_user">SMTP User Email Address</label>
            <div class="controls">
                <input type="text" name="email_smtp_user" id="email_smtp_user" value="<?php echo set_value('email_smtp_user', $this->form_data->email_smtp_user); ?>" class="input-xlarge" />
                <!--<span class="help-inline"></span>-->
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="email_smtp_pass">SMTP User Password</label>
            <div class="controls">
                <input type="password" name="email_smtp_pass" id="email_smtp_pass" value="<?php echo set_value('email_smtp_pass', $this->form_data->email_smtp_pass); ?>" class="input-xlarge" />
                <!--<span class="help-inline"></span>-->
            </div>
        </div>


        <div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="myModalLabel">Send test email to...</h3>
            </div>
            <div class="modal-body">

                <div class="control-group">
                    <label class="control-label" for="email_send_to">Send test email to</label>
                    <div class="controls">
                        <input type="text" name="email_send_to" id="email_send_to" value="" class="input-xlarge" />
                        <!--<span class="help-inline"></span>-->
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <input type="submit" name="send_test_email_submit" id="send_test_email_submit" value="Send Email" class="btn btn-primary" />
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
            </div>
        </div>
        <div class="form-actions">
            <input type="submit" name="" value="Update Email Settings" class="btn btn-primary btn-large" />&nbsp;&nbsp;&nbsp;
            <button type="submit" name="send_test_email" id="send_test_email" class="btn btn-large" data-toggle="modal" data-target="#myModal">Send Test Eamil</button>
        </div>

    </fieldset>

<?php echo form_close(); ?>


<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#myModal').on('shown', function () {
        jQuery('#email_send_to').focus();
    });
});
</script>