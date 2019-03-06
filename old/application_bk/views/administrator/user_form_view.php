

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> User</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open_multipart('administrator/user/add_user', array('class' => 'form-horizontal user_form')); ?>
<?php else : ?>
<?php echo form_open_multipart('administrator/user/update_user', array('class' => 'form-horizontal user_form')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="user_login">Login ID</label>
            <div class="controls">
                <input type="text" name="user_login" id="user_login" value="<?php echo set_value('user_login', $this->form_data->user_login); ?>" class="input-xlarge" <?php if ($is_edit) { echo ' readonly="readonly"'; } ?> />
                <span class="help-inline">Login Id is unique for each user.</span>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="user_password">Password</label>
            <div class="controls">
                <input type="password" name="user_password" id="user_password" value="<?php echo set_value('user_password', $this->form_data->user_password); ?>" class="input-xlarge" />
                <span class="help-block">At least 8 (eight) characters. Must have at least one lowercase, uppercase, numeric and symbol letter.</span>
            </div>
        </div>
        <div class="control-group formSep">
            <label class="control-label" for="user_confirm_password">Confirm Password</label>
            <div class="controls">
                <input type="password" name="user_confirm_password" id="user_confirm_password" value="<?php echo set_value('user_confirm_password', $this->form_data->user_confirm_password); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="user_team_id">Team</label>
            <div class="controls">
                <?php echo form_dropdown('user_team_id', $this->user_team_list, $this->form_data->user_team_id, 'id="user_team_id" class="chosen-select"'); ?>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="user_first_name">First Name</label>
            <div class="controls">
                <input type="text" name="user_first_name" id="user_first_name" value="<?php echo set_value('user_first_name', $this->form_data->user_first_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="user_last_name">Last Name</label>
            <div class="controls">
                <input type="text" name="user_last_name" id="user_last_name" value="<?php echo set_value('user_last_name', $this->form_data->user_last_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="user_email">Email</label>
            <div class="controls">
                <input type="text" name="user_email" id="user_email" value="<?php echo set_value('user_email', $this->form_data->user_email); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="user_type">Type</label>
            <div class="controls">
                <?php echo form_dropdown('user_type', $this->user_type_list, $this->form_data->user_type, 'id="user_type" class="chosen-select"'); ?>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>
        
        <div class="control-group formSep admin_group">
            <label class="control-label" for="admin_group_id">Admin Group</label>
            <div class="controls">
                <?php echo form_dropdown('admin_group_id', $this->admin_group_list, $this->form_data->admin_group_id, 'id="admin_group_id" class="chosen-select"'); ?>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="user_competency1">Competency Level</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="user_competency" id="user_competency1" value="1" <?php echo set_radio('user_competency', 'Front Office', $this->form_data->user_competency == 'Front Office'); ?> /> Front Office
                </label>
                <label class="radio inline">
                    <input type="radio" name="user_competency" id="user_competency2" value="0" <?php echo set_radio('user_competency', 'Back Office', $this->form_data->user_competency == 'Back Office'); ?> /> Back Office
                </label>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="user_phone">Phone Number</label>
            <div class="controls">
                <input type="text" name="user_phone" id="user_phone" value="<?php echo set_value('user_phone', $this->form_data->phone); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Login Id must be unique for each user.</span>-->
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
        
        <?php 
        if ($is_edit){
            if(!isset($this->form_data->user_is_admit_card))
            {
                $this->form_data->user_is_admit_card=0;
            }
        }
        ?>
        <div class="control-group">
            <label class="control-label" for="user_is_active1">Is Active?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="user_is_active" id="user_is_active1" value="1" <?php echo set_radio('user_is_active', '1', $this->form_data->user_is_active == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="user_is_active" id="user_is_active2" value="0" <?php echo set_radio('user_is_active', '0', $this->form_data->user_is_active == '0'); ?> /> No
                </label>
            </div>
        </div>
        

        <div class="control-group">
            <label class="control-label" for="user_is_lock1">Is Locked?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="user_is_lock" id="user_is_lock1" value="1" <?php echo set_radio('user_is_lock', '1', $this->form_data->user_is_lock == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="user_is_lock" id="user_is_lock2" value="0" <?php echo set_radio('user_is_lock', '0', $this->form_data->user_is_lock == '0'); ?> /> No
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="user_is_lock1">Is Admit Card Verifier?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="user_is_admit_card" id="user_is_admit_card1" value="1" <?php echo set_radio('user_is_admit_card', '1', $this->form_data->user_is_admit_card == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="user_is_admit_card" id="user_is_admit_card2" value="0" <?php echo set_radio('user_is_admit_card', '0', $this->form_data->user_is_admit_card == '0'); ?> /> No
                </label>
            </div>
        </div>


        <div class="control-group">
            <label class="control-label" for="is_password_reset">Is Password Reset eligible?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="is_password_reset" id="is_password_reset1" value="1" <?php echo set_radio('user_is_admit_card', '1', $this->form_data->is_password_reset == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="is_password_reset" id="is_password_reset2" value="0" <?php echo set_radio('user_is_admit_card', '0', $this->form_data->is_password_reset == '0'); ?> /> No
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="department">Department</label>
            <div class="controls">
                <input type="text" name="department" id="department" value="<?php 
                if(isset($this->form_data->department))
                echo set_value('department', $this->form_data->department);

                 ?>" class="input-xlarge" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="designation">Designation</label>
            <div class="controls">
                <input type="text" name="designation" id="designation" value="<?php 
                if(isset($this->form_data->designation))
                echo set_value('designation', $this->form_data->designation);

                 ?>" class="input-xlarge" />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="profile_image">Profile Picture</label>
            <div class="controls">
                <input type="file" name="profile_image" id="profile_image">
                <?php if(!empty($this->form_data->profile_image)){
                    ?>
                    <img height="100" style="height: 100px;" src="<?=base_url('uploads/user').'/'.$this->form_data->profile_image?>">
                    <input type="hidden" name="profile_image_ex" value="<?=$this->form_data->profile_image?>" />
                    <?php
                } ?>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="signature_image">Signature Upload</label>
            <div class="controls">
                <input type="file" name="signature_image" id="profile_image">
                <?php if(!empty($this->form_data->signature_image)){
                    ?>
                    <img height="100" style="height: 100px;" src="<?=base_url('uploads/signature').'/'.$this->form_data->signature_image?>">
                    <input type="hidden" name="signature_image_ex" value="<?=$this->form_data->signature_image?>" />
                    <?php
                } ?>
                

            </div>
        </div>


        <div class="form-actions">
            <input type="hidden" class="is_edit_form" name="user_id" value="<?php echo set_value('user_id', $this->form_data->user_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> User" class="btn btn-primary btn-large" />&nbsp;&nbsp;
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
</script>
