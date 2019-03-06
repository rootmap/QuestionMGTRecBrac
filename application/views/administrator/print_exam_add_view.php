

<h1 class="heading">Print Exam Qestion</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<div id="select-exam">
    <?php echo form_open('administrator/print_exam', array('class' => 'form-inline')); ?>

        <label>
            Select an Exam from the list:&nbsp;&nbsp;&nbsp;
            <?php echo form_dropdown('exam_id', $this->exam_list, dencrypt($this->form_data->exam_id), 'id="exam_id" class="chosen-select input-xxlarge"'); ?>&nbsp;&nbsp;&nbsp;
            <input type="submit" name="assign_exam_submit" value="Select Exam" class="btn btn-primary btn-large" />&nbsp;&nbsp;&nbsp;
        </label>

    <?php echo form_close(); ?>
</div>


<?php echo form_open('administrator/print_exam/do_assign/'. $this->form_data->exam_id, array('class' => 'form-horizontal')); ?>
    <fieldset>
		
		 <div class="control-group formSep">
            <label class="control-label" for="exam_title">New Exam Title</label>
            <div class="controls">
                <input type="text" name="new_name" id="exam_title" value="<?php echo set_value('new_name', $this->form_data->new_name); ?>" class="input-xxlarge" />
            </div>
        </div>
        
        <div class="control-group formSep">
            <label class="control-label" for="ue_start_date">Exam Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="ue_start_date" id="ue_start_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('ue_start_date', $this->form_data->ue_start_date); ?>"
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div> 
         
        <div class="control-group formSep">
            <label class="control-label" for="exam_description">Short Description</label>
            <div class="controls">
                <textarea name="exam_description" id="exam_description" rows="4" cols="30" class="input-xxlarge"><?php echo set_value('exam_description', $this->form_data->exam_description); ?></textarea>
            </div>
        </div>
         

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