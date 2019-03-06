
<h1 class="heading">Survey Summary Detail Reports</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">

        <div class="row control-row control-row-top">            
            <div class="span2">
                <?php if ($records && count($records) > 0): ?>
                    <?php 
                    $attributes = array('action' => 'method');
                    echo form_open('administrator/survey_report/export_summary_data',$attributes); ?>
                    <input type="hidden" name="survey_id" value="<?php echo $survey_id; ?>">
                    <button type="submit" class="btn"><i class="icon-download-alt"></i>Export Data</button>
                    <?php echo form_close(); ?>
                <?php endif; ?> 
            </div>
            
            
            <div class="span4 right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

        <?php echo $records_table; ?>

        <div class="row">
            <div class="span6"></div>
            <div class="span6" style="text-align: right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

    </div>
</div>

