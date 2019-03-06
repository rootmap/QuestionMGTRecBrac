

<h1 class="heading">Take an Action</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>

<?php

$bulk_survey_count = count($bulk_survey);
$bulk_invalid_survey_count = count($bulk_invalid_survey);

?>

<?php echo form_open_multipart('administrator/assign_survey/bulk_upload_do_action', array('class' => 'form-horizontal')); ?>

    <?php if ($bulk_survey_count > 0) : ?>
    <div class="alert alert-success"><?php echo $bulk_survey_count; ?> records found to be valid and can be imported. Press the 'Insert Bulk Users' button below to insert valid users.</div>
    <?php endif; ?>

    <?php if ($bulk_invalid_survey_count > 0) : ?>
    <div class="alert alert-error"><?php echo $bulk_invalid_survey_count; ?> records found invalid. <!--You can download the Invalid User List by pressing the 'Download Invalid User List' button below.--></div>
    <?php endif; ?>

    <fieldset>

        <div class="form-actions">

            <?php if ($bulk_survey_count > 0) : ?>
            <input type="submit" name="insert_bulk_users" value="Assign Survey to Bulk Users" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <?php endif; ?>

            <a href="<?php echo base_url('administrator/assign_survey/bulk'); ?>" class="btn btn-large">Upload File Again</a>&nbsp;&nbsp;

        </div>

    </fieldset>

<?php echo form_close(); ?>


<?php if ($bulk_invalid_survey_count > 0 && isset($bulk_invalid_survey_count)): ?>
<div class="row-fluid">
    <div class="span12">

        <?php echo $bulk_invalid_users_table; ?>

    </div>
</div>
<?php endif; ?>