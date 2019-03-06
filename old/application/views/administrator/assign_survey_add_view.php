

<h1 class="heading">Assign Survey</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<div id="select-exam">
    <?php echo form_open('administrator/assign_survey', array('class' => 'form-inline')); ?>

        <label>
            Select a Survey from the list:&nbsp;&nbsp;&nbsp;
            <?php echo form_dropdown('survey_id', $this->survey_list, $this->form_data->survey_id, 'id="survey_id" class="chosen-select input-xxlarge"'); ?>&nbsp;&nbsp;&nbsp;
            <input type="submit" name="assign_survey_submit" value="Select Survey" class="btn btn-primary btn-large" />&nbsp;&nbsp;&nbsp;
        </label>

    <?php echo form_close(); ?>
</div>


<?php echo form_open('administrator/assign_survey/do_assign/'. $this->form_data->survey_id, array('class' => 'form-horizontal')); ?>
    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="start_date">Start Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="start_date" id="start_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('start_date', $this->form_data->start_date); ?>"
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="end_date">End Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="end_date" id="end_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('end_date', $this->form_data->end_date); ?>"
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="survey_status1">Is Anonymous</label>
            <div class="controls">
                <label class="radio inline">
                    <input value="yes" type="radio" name="survey_anms" id="survey_anms1" /> Yes
                </label>
                <label class="radio inline">
                    <input value="no" checked type="radio" name="survey_anms" id="survey_anms2"  /> No
                </label>
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

        <div class="control-group" id="users-field">
            <label class="control-label" for="user_ids">Users</label>
            <div class="controls">
                <div class="input-append">
                    <?php echo form_dropdown('user_ids[]', $this->user_list, '', 'id="user_ids" multiple="multiple" class="chosen-select input-xxlarge"'); ?>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <input type="hidden" name="survey_id_hidden" value="<?php echo set_value('survey_id_hidden', $this->form_data->survey_id_hidden); ?>" />
            <input type="submit" value="Assign Survey" id="assign_survey_submit" class="btn btn-primary btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>

<script type="text/javascript">

jQuery(document).ready(function(){

    jQuery('#user_group_id').change(function(){

        var user_group_id = parseInt(jQuery('#user_group_id').val());

        if (user_group_id >= 0) {

            jQuery('#assign_survey_submit').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: siteUrlJs +"administrator/ajax/user_select_box_by_group/"+ user_group_id,
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
                    jQuery('#assign_survey_submit').removeAttr('disabled');
                }
            });

        } else {
            jQuery('#users-field .input-append').html('');
            jQuery('#users-field').hide();
        }
        
    });

})

</script>