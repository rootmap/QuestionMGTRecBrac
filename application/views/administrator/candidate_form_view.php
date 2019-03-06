

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> Candidate</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open_multipart('administrator/candidate/add_candidate', array('class' => 'form-horizontal user_form')); ?>
<?php else : ?>
<?php echo form_open_multipart('administrator/candidate/update_candidate', array('class' => 'form-horizontal user_form')); ?>
<?php endif; ?>

    <fieldset>


        <div class="control-group formSep">
            <label class="control-label" for="cand_login">Login ID</label>
            <div class="controls">
                <input type="text" name="cand_login" id="cand_login" value="<?php echo set_value('cand_login', $this->form_data->cand_login); ?>" class="input-xlarge" <?php if ($is_edit) { echo ' readonly="readonly"'; } ?> />
                <span class="help-inline">Login Id is unique for each Candidate.</span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="cand_name">Full Name</label>
            <div class="controls">
                <input type="text" required name="cand_name" id="cand_name" value="<?php echo set_value('cand_name', $this->form_data->cand_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
            </div>
        </div>


        <div class="control-group">
            <label class="control-label" for="cand_password">Password</label>
            <div class="controls">
                <input type="password" name="cand_password" id="cand_password" value="<?php echo set_value('user_password', $this->form_data->cand_password); ?>" class="input-xlarge" />
                <span class="help-block">At least 8 (eight) characters. Must have at least one lowercase, uppercase, numeric and symbol letter.</span>
            </div>
        </div>
        <div class="control-group formSep">
            <label class="control-label" for="cand_confirm_password">Confirm Password</label>
            <div class="controls">
                <input type="password" name="cand_confirm_password" id="cand_confirm_password" value="<?php echo set_value('user_confirm_password', $this->form_data->cand_confirm_password); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
            </div>
        </div>


        <div class="control-group formSep">
            <label class="control-label" for="cand_email">Email</label>
            <div class="controls">
                <input type="text" name="cand_email" id="cand_email" value="<?php echo set_value('cand_email', $this->form_data->cand_email); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
            </div>
        </div>

        <!--
        <div class="control-group formSep">
            <label class="control-label" for="cand_address">Full Address</label>
            <div class="controls">
                <input type="text" name="cand_address" id="cand_address" value="<?php //echo //set_value('user_email', $this->form_data->cand_address); ?>" class="input-xlarge" />

            </div>
        </div>

        -->

        <div class="control-group formSep">
            <label class="control-label" for="cand_phone">Phone Number</label>
            <div class="controls">
                <input type="text" name="cand_phone" id="cand_phone" value="<?php echo set_value('cand_phone', $this->form_data->phone); ?>" class="input-xlarge" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="nid_passport_no">NID/Passport No.</label>
            <div class="controls">
                <input type="text" name="nid_passport_no" id="nid_passport_no" value="<?php 
                if(isset($this->form_data->nid_passport_no))
                echo set_value('nid_passport_no', $this->form_data->nid_passport_no);

                 ?>" class="input-xlarge" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="cand_is_active">Is Active?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="cand_is_active" id="cand_is_active1" value="1" <?php echo set_radio('cand_is_active', '1', $this->form_data->cand_is_active == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="cand_is_active" id="cand_is_active2" value="0" <?php echo set_radio('cand_is_active', '0', $this->form_data->cand_is_active == '0'); ?> /> No
                </label>
            </div>
        </div>



        <div class="control-group">
            <label class="control-label" for="cand_is_lock1">Is Locked?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="cand_is_lock" id="cand_is_lock1" value="1" <?php echo set_radio('cand_is_lock', '1', $this->form_data->cand_is_lock == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="cand_is_lock" id="cand_is_lock2" value="0" <?php echo set_radio('cand_is_lock', '0', $this->form_data->cand_is_lock == '0'); ?> /> No
                </label>
            </div>
        </div>


        <div class="control-group">
            <label class="control-label" for="profile_image">Profile Picture</label>
            <div class="controls">
                <input type="file" name="profile_image" id="profile_image">
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="signature_image">Signature Upload</label>
            <div class="controls">
                <input type="file" name="signature_image" id="signature_image">
            </div>
        </div>

        <div class="form-actions">
            <input type="hidden" class="is_edit_form" name="cand_id" value="<?php echo set_value('cand_id', $this->form_data->id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Candiate" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>

<script type="text/javascript">
jQuery(document).ready(function(){
    $('.admin_group').hide();
    
    if( $('.is_edit_form').val() > 0 && $("#user_type option:selected").text() == 'Administrator' ){
       $('.admin_group').show(); 
    }
    
    $('#user_type').on('change', function (e) {
        var optionSelected = $("option:selected", this);
        var valueSelected = this.value;
        if(valueSelected == 'Administrator'){
            $('.admin_group').show();
        }else{
            $('.admin_group').val('Select an Admin Group');
            $('.admin_group').hide();
        }
    });
    
    $( ".user_form" ).submit(function( event ) {
        if( $("#user_type option:selected").text() == 'Administrator' ){
            if( $(".admin_group option:selected").text() == 'Select an Admin Group' ){
                alert('Please select and admin group');
                event.preventDefault();
            }
        }
    });
})

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#blah')
                .attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
        }
    }
</script>
