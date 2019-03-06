

<h1 class="heading">Assign Exam</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<div id="select-exam">
    <?php echo form_open('administrator/assign_exam', array('class' => 'form-inline')); ?>

        <label>
            Select an Exam from the list:&nbsp;&nbsp;&nbsp;
            <?php echo form_dropdown('exam_id', $this->exam_list, $this->form_data->exam_id, 'id="exam_id" class="chosen-select input-xxlarge"'); ?>&nbsp;&nbsp;&nbsp;
            <input type="submit" name="assign_exam_submit" value="Select Exam" class="btn btn-primary btn-large" />&nbsp;&nbsp;&nbsp;
        </label>

    <?php echo form_close(); ?>
</div>


<?php echo form_open('administrator/assign_exam/do_assign/'. $this->form_data->exam_id, array('class' => 'form-horizontal')); ?>
    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="ue_start_date">Start Date and Time</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="ue_start_date" id="ue_start_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('ue_start_date', $this->form_data->ue_start_date); ?>"
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>&nbsp;&nbsp;

                <div class="input-append bootstrap-timepicker-component">
                    <input type="text" name="ue_start_time" id="ue_start_time"
                        value="<?php echo set_value('ue_start_time', $this->form_data->ue_start_time); ?>" class="time input-small" /><span class="add-on"><i class="icon-time"></i></span>
                </div>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="ue_end_date">End Date and Time</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="ue_end_date" id="ue_end_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('ue_end_date', $this->form_data->ue_end_date); ?>"
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>&nbsp;&nbsp;

                <div class="input-append bootstrap-timepicker-component">
                    <input type="text" name="ue_end_time" id="ue_end_time"
                        value="<?php echo set_value('ue_end_time', $this->form_data->ue_end_time); ?>" class="time input-small" /><span class="add-on"><i class="icon-time"></i></span>
                </div>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep" id="user-group-field">
            <label class="control-label" for="user_group_id">User Group</label>
            <div class="controls">
                <div class="input-append">
                    <?php echo form_dropdown('user_group_id', $this->user_group_list, $this->form_data->user_group_id, 'id="user_group_id" class="chosen-select input-xxlarge"'); ?>
                </div>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep" id="user-team-field">
            <label class="control-label" for="user_team_id">User Team</label>
            <div class="controls">
                <div class="input-append">
                    <?php echo form_dropdown('user_team_id', $this->user_team_list, $this->form_data->user_team_id, 'id="user_team_id" class="chosen-select input-xxlarge"'); ?>
                </div>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group" id="users-field">
            <label class="control-label" for="user_ids">Users</label>
            <div class="controls">
                <div class="input-append">
                    <?php echo form_dropdown('user_ids[]', $this->user_list, '', 'id="user_ids" multiple="multiple" class="chosen-select input-xxlarge"'); ?>
                </div>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="exam_immediate_result1">Show MCQ Exam Result After Completion</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="exam_immediate_result" onchange=""  id="exam_immediate_result1" value="1" checked /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="exam_immediate_result" id="exam_immediate_result2" value="0"  /> No
                </label>
            </div>
        </div>

        <?php //print_r_pre($this->form_data->exam_id_hidden); ?>
        <div class="form-actions">
            <input type="hidden" name="exam_id_hidden" value="<?php echo set_value('exam_id_hidden', $this->form_data->exam_id_hidden); ?>" />
            <input type="submit" value="Assign Exam" id="assign_exam_submit" class="btn btn-primary btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>

<script type="text/javascript">

jQuery(document).ready(function(){

    jQuery('#user_group_id').change(function(){

        var user_group_id = parseInt(jQuery('#user_group_id').val());
        if (user_group_id >= 0) {

            jQuery('#assign_exam_submit').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: siteUrlJs +"administrator/ajax/user_team_select_box_by_group/"+ user_group_id,
                success: function(msg){
                    msg = jQuery.trim(msg);
                    if (msg != '') {
                        jQuery('#user-team-field').show();
                        jQuery('#user-team-field .input-append').html(msg);
                        jQuery(".chosen-select").chosen();
                        jQuery("#user_team_id").trigger("liszt:updated");
                    } else {
                        //jQuery('#user-team-field .input-append').html('');
                        //jQuery('#user-team-field').hide();
                    }
                    jQuery('#assign_exam_submit').removeAttr('disabled');
                }
            });
            jQuery.ajax({
                type: "POST",
                url: siteUrlJs +"administrator/ajax/user_select_box_by_team/"+ user_team_id,
                success: function(msg){
                    msg = jQuery.trim(msg);
                    if (msg != '') {
                        jQuery('#users-field').show();
                        jQuery('#users-field .input-append').html(msg);
                        jQuery(".chosen-select").chosen();
                        jQuery("#user_ids").trigger("liszt:updated");
                    } else {
                        //jQuery('#users-field .input-append').html('');
                        //jQuery('#users-field').hide();
                    }
                    jQuery('#assign_exam_submit').removeAttr('disabled');
                }
            });

        } else {
            jQuery('#user-team-field .input-append').html('');
            jQuery('#user-team-field').hide();
        }

    });

    jQuery('#user_team_id').live('change', function(){

        var user_team_id = parseInt(jQuery('#user_team_id').val());
        if (user_team_id >= 0) {

            jQuery('#assign_exam_submit').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: siteUrlJs +"administrator/ajax/user_select_box_by_team/"+ user_team_id,
                success: function(msg){
                    msg = jQuery.trim(msg);
                    if (msg != '') {
                        jQuery('#users-field').show();
                        jQuery('#users-field .input-append').html(msg);
                        jQuery(".chosen-select").chosen();
                        jQuery("#user_ids").trigger("liszt:updated");
                    } else {
                        //jQuery('#users-field .input-append').html('');
                        //jQuery('#users-field').hide();
                    }
                    jQuery('#assign_exam_submit').removeAttr('disabled');
                }
            });

        } else {
            jQuery('#users-field .input-append').html('');
            jQuery('#users-field').hide();
        }
        
    });

})

</script>