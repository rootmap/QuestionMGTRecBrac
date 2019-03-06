
<h1 class="heading">Assigned Survey Status</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open('administrator/assign_survey_status/get_list', array('class' => 'form-horizontal')); ?>
    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="survey_id">Select a Survey</label>
            <div class="controls">
                <?php echo form_dropdown('survey_id', $this->survey_list, @$this->form_data->survey_id, 'id="survey_id" class="chosen-select input-xxlarge"'); ?>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

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

        <div class="control-group">
            <label class="control-label" for="end_date">End Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="end_date" id="end_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('end_date', $this->form_data->end_date); ?>"
                        class="input-small date" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>
        

        <div class="form-actions">
            <input type="submit" value="Show Assigned Survey Status" id="show_assigned_survey_status_submit" name="show_assigned_survey_status_submit" class="btn btn-primary btn-large" />            
        </div>

    </fieldset>
<?php echo form_close(); ?>


<?php if (isset($records_table) && $records_table != '') : ?>

    <div class="row control-row control-row-top">
        <div class="span6 left">
            <?php if ($records && count($records) > 0): ?>
            <?php echo form_open('administrator/assign_survey_status/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_login" id="filter_login" value="<?php echo set_value('filter_login', $this->form_data->filter_login); ?>" placeholder="User ID" class="input-medium" />
                <?php echo form_dropdown('filter_group', $this->group_list_filter, $this->form_data->filter_group, 'id="filter_group" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_status', $this->status_list_filter, $this->form_data->filter_status, 'id="filter_status" class="chosen-select"'); ?>

                &nbsp;
                <input type="submit" value="Filter" class="btn" />

            <?php echo form_close(); ?>
            <?php endif; ?>
        </div>
        
        <div class="span2">
            <?php if ($records && count($records) > 0): ?>
                <?php echo form_open('administrator/assign_survey_status/export_data'); ?>
                <button type="submit" class="btn"><i class="icon-download-alt"></i>Export Data</button>
                <?php echo form_close(); ?>
            <?php endif; ?> 
        </div>
        
        <div class="span4 right">

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
    jQuery('.action-delete').click(function(){
        var response = confirm('Are you sure you want to continue?');
        if (!response) {
            return false;
        }
    });    
})
</script>
