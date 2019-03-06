
<h1 class="heading">Assigned Exam Status</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open('administrator/assign_status/get_list', array('class' => 'form-horizontal')); ?>
    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="exam_id">Select an Exam</label>
            <div class="controls">
                <?php echo form_dropdown('exam_id', $this->exam_list, $this->form_data->exam_id, 'id="exam_id" class="chosen-select input-xxlarge"'); ?>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

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

        <div class="control-group">
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
        

        <div class="form-actions">
            <input type="submit" value="Show Assigned Exam Status" id="show_assigned_exam_status_submit" name="show_assigned_exam_status_submit" class="btn btn-primary btn-large" />
            <input type="submit" value="Update Date and Time of Assigned Exam" id="update_date_time_assigned_exam_submit" name="update_date_time_assigned_exam_submit" class="btn btn-primary btn-large" />
        </div>

    </fieldset>
<?php echo form_close(); ?>


<?php if (isset($records_table) && $records_table != '') : ?>

    <div class="row control-row control-row-top">
        <div class="span6 left">
        <?php if ($records && count($records) > 0): ?>
        <?php echo form_open('administrator/assign_status/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

            <input type="text" name="filter_login" id="filter_login" value="<?php echo set_value('filter_login', $this->form_data->filter_login); ?>" placeholder="User ID" class="input-medium" />
            <?php echo form_dropdown('filter_team', $this->team_list_filter, $this->form_data->filter_team, 'id="filter_team" class="chosen-select"'); ?>
            <?php echo form_dropdown('filter_status', $this->status_list_filter, $this->form_data->filter_status, 'id="filter_status" class="chosen-select"'); ?>

            &nbsp;
            <input type="submit" value="Filter" class="btn" />

        <?php echo form_close(); ?>
        <?php endif; ?>
        </div>
        <div class="span6 right">

            <?php echo $pagin_links; ?>

        </div>
    </div>

    <?php echo $records_table; ?>

    <div class="row control-row control-row-bottom">
        <div class="span6 left">&nbsp;</div>
        <div class="span6 right">

            <?php echo $pagin_links; ?>

        </div>
    </div>

<?php endif; ?>

<div class="modal hide fade" id="ReassignModal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Change Date & Time to reassign the exam.</h3>
    </div>
    <div class="modal-body">
        <?php echo form_open('administrator/assign_status/reassign', array('class' => 'form-horizontal')); ?>  
        <input type="hidden" value="" id="user_exam_id" name="user_exam_id" />
        <input type="hidden" value="" id="page" name="page" />
        
        <div class="control-group formSep">
            <label class="control-label" for="start_date">Start Date and Time</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="start_date" id="start_date" data-date-format="dd/mm/yyyy"
                        value=""
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>&nbsp;&nbsp;

                <div class="input-append bootstrap-timepicker-component">
                    <input type="text" name="start_time" id="start_time"
                        value="" class="time input-small" /><span class="add-on"><i class="icon-time"></i></span>
                </div>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="end_date">End Date and Time</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="end_date" id="end_date" data-date-format="dd/mm/yyyy"
                        value=""
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>&nbsp;&nbsp;

                <div class="input-append bootstrap-timepicker-component">
                    <input type="text" name="end_time" id="end_time"
                        value="" class="time input-small" /><span class="add-on"><i class="icon-time"></i></span>
                </div>
            </div>
        </div>
        
        <div class="controls">
            <input type="submit" value="Reassign" id="reassign_exam" class="btn btn-success" />
        </div>
        <?php echo form_close(); ?>
    </div>
    <div class="modal-footer">
    </div>
</div>

<style type="text/css">
    .row-fluid { margin-top: 0!important; }
    .span10 form { margin-bottom: 0; }
    .export-buttons {
        width: 100%;
        margin-bottom: 10px;
        overflow: hidden;
    }
    .export-buttons form {
        display: block;
        float: right;
        margin-left: 10px;
        margin-bottom: 0;
    }
    .pagination { margin: 0; }
    .pagination-top { margin: 0 0 15px 0; }
    .pagination-bottom { margin: 15px 0 0 0; }
</style>
<script type="text/javascript">
jQuery(document).ready(function(){
  
    jQuery('.action-retake').click(function(){
        var response = confirm('Retaking an exam will delete result of the exam and set the exam in open state.\nAre you sure you want to continue?');
        if (!response) {
            return false;
        }
    });

    jQuery('.action-delete').click(function(){
        var response = confirm('Deleting an exam will delete result of the exam, as well as the exam itself.\nAre you sure you want to continue?');
        if (!response) {
            return false;
        }
    });    
})
</script>

<script>
    $(document).on("click" ,"#reassign_button", function(){
        var start_date = $(this).data('start_date');
        var end_date = $(this).data('end_date');
        var start_time = $(this).data('start_time');
        var end_time = $(this).data('end_time');
        var user_exam_id = $(this).data('user_exam_id');
        var page = $(this).data('page');
        
        $("#start_date").val(start_date);
        $("#end_date").val(end_date);
        $("#start_time").val(start_time);
        $("#end_time").val(end_time);
        $("#user_exam_id").val(user_exam_id);
        $("#page").val(page);
    });
</script>