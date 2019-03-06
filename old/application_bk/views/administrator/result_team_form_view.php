
<h1 class="heading">Exam Results</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open('administrator/result_team/show_results', array('class' => 'form-horizontal', 'style' => 'margin-bottom:0;')); ?>

    <fieldset>

        <div class="control-group">
            <label class="control-label" for="exam_id">Select Exam</label>
            <div class="controls">
                <?php echo form_dropdown('exam_id', $this->exam_list, $this->form_data->exam_id, 'id="exam_id" class="chosen-select input-xxlarge"'); ?>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="group_id">Select Group</label>
            <div class="controls">
                <?php echo form_dropdown('group_id', $this->user_group_list, $this->form_data->group_id, 'id="group_id" class="chosen-select input-xxlarge"'); ?>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group" id="user_team_field">
            <label class="control-label" for="team_id">Select Team</label>
            <div class="controls">
                <?php echo form_dropdown('team_id', $this->user_team_list, $this->form_data->team_id, 'id="team_id" class="chosen-select input-xxlarge"'); ?>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <input type="hidden" name="date_from" id="date_from" value="<?php echo set_value('date_from', $this->form_data->date_from); ?>" />
        <input type="hidden" name="date_to" id="date_to" value="<?php echo set_value('date_to', $this->form_data->date_to); ?>" />

        <div class="control-group">
            <label class="control-label" for="date_from">Exam Assigned Date Range</label>
            <div class="controls">
                <div id="reportrange" style="background: #ffffff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display: inline-block;">
                    <i class="icon-calendar icon-large"></i>
                    <span><?php echo date("F j, Y", strtotime('-30 day')); ?> - <?php echo date("F j, Y"); ?></span>
                    <b class="caret" style="margin-top: 8px"></b>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" name="user_results_submit" id="user_results_submit" value="Show Results" class="btn btn-primary btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<?php if (isset($records_table) && $records_table != '') : ?>
<div class="row-fluid" style="margin-top: 0;"><div class="span12">


    <div class="well">
        <div class="row-fluid result_summary" style="margin-top: 0;">
            <div class="span4" style="text-align: center;">
                <div class="big_text success"><?php echo $attendee; ?></div> Attendee
            </div>
            <div class="span4" style="text-align: center;">
                <div class="big_text error"><?php echo $non_attendee; ?></div> Non-Attendee
            </div>
            <div class="span4" style="text-align: center;">
                <div class="big_text info"><?php echo $response_rate; ?>%</div> Response Rate
            </div>
        </div>
    </div>


    <div class="export-buttons">
        <?php echo form_open('administrator/result_team/export'); ?>
        <button type="submit" class="btn pull-right"><i class="icon-download-alt"></i> Export Results</button>
        <?php echo form_close(); ?>

        <?php echo form_open('administrator/result_team/export_attendee_list'); ?>
        <button type="submit" class="btn pull-right"><i class="icon-download-alt"></i> Export Attendee, Non Attendee List</button>
        <?php echo form_close(); ?>
    </div>


    <div class="row">
        <div class="span6"></div>
        <div class="span6 pagination-top" style="text-align: right">

            <?php echo $pagin_links; ?>

        </div>
    </div>

    <?php echo $records_table; ?>

    <div class="row">
        <div class="span6"></div>
        <div class="span6 pagination-bottom" style="text-align: right">

            <?php echo $pagin_links; ?>

        </div>
    </div>

</div></div>
<?php endif; ?>

<style type="text/css">
    .form-horizontal .control-label { width: 200px; }
    .form-horizontal .controls { margin-left: 220px; }
    .form-horizontal .form-actions { padding-left: 220px; }

    .result_summary { font-size: 16px; font-weight: bold; }
    .big_text { font-weight: bold; font-size: 60px; text-align: center; line-height: 1em; text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25); }
    .big_text.success { color: #70A415; }
    .big_text.error { color: #B94A48; }
    .big_text.info { color: #3A87AD; }

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

    var dateForm = jQuery('#date_from').val();
    var dateTo = jQuery('#date_to').val();

    if (dateForm == '' || dateTo == '') {
        jQuery('#reportrange span').html('Select a Date Range');
    } else {
        var dateFromArray = dateForm.split('/');
        var dateFormObj = new Date(dateFromArray[2] +"-"+ dateFromArray[1] +"-"+ dateFromArray[0]);

        var dateToArray = dateTo.split('/');
        var dateToObj = new Date(dateToArray[2] +"-"+ dateToArray[1] +"-"+ dateToArray[0]);

        jQuery('#reportrange span').html(dateFormObj.toString('MMMM d, yyyy') + ' - ' + dateToObj.toString('MMMM d, yyyy'));
    }

    jQuery('#reportrange').daterangepicker({
        ranges: {
            'Today': ['today', 'today'],
            'Yesterday': ['yesterday', 'yesterday'],
            'Last 7 Days': [Date.today().add({ days: -6 }), 'today'],
            'Last 30 Days': [Date.today().add({ days: -29 }), 'today'],
            'This Month': [Date.today().moveToFirstDayOfMonth(), Date.today().moveToLastDayOfMonth()],
            'Last Month': [Date.today().moveToFirstDayOfMonth().add({ months: -1 }), Date.today().moveToFirstDayOfMonth().add({ days: -1 })]
        } }, function(start, end) {
            jQuery('#reportrange span').html(start.toString('MMMM d, yyyy') + ' - ' + end.toString('MMMM d, yyyy'));
            jQuery('#date_from').val(start.toString('dd/MM/yyyy'));
            jQuery('#date_to').val(end.toString('dd/MM/yyyy'));
        }
    );

    jQuery('.clearBtn').click(function(){
        jQuery('#reportrange span').html('Select a Date Range');
        jQuery('#date_from').val('');
        jQuery('#date_to').val('');
    });

    // ajax call
    jQuery('#group_id').change(function(){

        var user_group_id = parseInt(jQuery('#group_id').val());
        if (user_group_id >= 0) {

            jQuery('#user_results_submit').attr('disabled', 'disabled');
            jQuery.ajax({
                type: "POST",
                url: siteUrlJs +"administrator/ajax/user_team_select_box_by_group/"+ user_group_id +"/team_id",
                success: function(msg){
                    msg = jQuery.trim(msg);
                    if (msg != '') {
                        jQuery('#user_team_field .controls').html(msg);
                        jQuery(".chosen-select").chosen();
                        jQuery("#group_id").trigger("liszt:updated");
                    } else {
                        //jQuery('#user-team-field .input-append').html('');
                        //jQuery('#user-team-field').hide();
                    }
                    jQuery('#user_results_submit').removeAttr('disabled');
                }
            });

        } else {
            jQuery('#user_team_field .input-append').html('');
        }

    });

});
</script>