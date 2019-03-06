
<h1 class="heading">User Results</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open('administrator/result_user/show_results', array('class' => 'form-horizontal', 'style' => 'margin-bottom:0;')); ?>

    <fieldset>

        <div class="control-group">
            <label class="control-label" for="user_login">User ID</label>
            <div class="controls">
                <input type="text" name="user_login" id="user_login" value="<?php echo set_value('user_login', $this->form_data->user_login); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <input type="hidden" name="date_from" id="date_from" value="<?php echo set_value('date_from', $this->form_data->date_from); ?>" />
        <input type="hidden" name="date_to" id="date_to" value="<?php echo set_value('date_to', $this->form_data->date_to); ?>" />

        <div class="control-group">
            <label class="control-label" for="date_from">Exam Assigned <br />Date Range</label>
            <div class="controls">
                <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; display: inline-block;">
                    <i class="icon-calendar icon-large"></i>
                    <span><?php echo date("F j, Y", strtotime('-30 day')); ?> - <?php echo date("F j, Y"); ?></span>
                    <b class="caret" style="margin-top: 8px"></b>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" name="user_results_submit" value="Show Results" class="btn btn-primary btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<?php if (isset($records_table) && $records_table != '') : ?>
<div class="row-fluid" style="margin-top: 0;"><div class="span12">

    <?php echo form_open('administrator/result_user/export'); ?>
    <div style="margin-bottom: 10px;width: 100%; overflow: hidden;">
        <button type="submit" class="btn pull-right"><i class="icon-download-alt"></i> Export to Excel</button>
    </div>
    <?php echo form_close(); ?>

    <?php echo $records_table; ?>

</div></div>
<?php endif; ?>


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

});
</script>