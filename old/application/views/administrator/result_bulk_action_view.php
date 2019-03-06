

<h1 class="heading">Take an Action</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>

<?php

$bulk_results_count = count($bulk_results);
$bulk_invalid_results_count = count($bulk_invalid_results);

?>


<?php echo form_open_multipart('administrator/result/bulk_upload_do_action', array('class' => 'form-horizontal')); ?>

<?php if ($bulk_results_count > 0) : ?>
    <div class="alert alert-success"><?php echo $bulk_results_count; ?> records found to be valid and can be imported. Press the 'Insert Bulk results' button below to insert valid results.</div>
<?php endif; ?>

<?php if ($bulk_invalid_results_count > 0) : ?>
    <div class="alert alert-error"><?php echo $bulk_invalid_results_count; ?> records found invalid. <!--You can download the Invalid candidate List by pressing the 'Download Invalid candidate List' button below.--></div>
<?php endif; ?>

<fieldset>

    <div class="form-actions">

        <?php if ($bulk_results_count > 0) : ?>
            <input type="submit" name="insert_bulk_results" value="Insert Bulk results" class="btn btn-primary btn-large" />&nbsp;&nbsp;
        <?php endif; ?>

        <?php /*if ($bulk_invalid_results_count > 0) : */?><!--
            <input type="submit" name="download_invalid_candidate_list" value="Download Invalid candidate List" class="btn" />&nbsp;&nbsp;
            --><?php /*endif; */?>

        <a href="<?php echo base_url('administrator/result/upload_result'); ?>" class="btn btn-large">Upload File Again</a>&nbsp;&nbsp;

    </div>

</fieldset>

<?php echo form_close(); ?>


<?php if ($bulk_invalid_results_count > 0 && isset($bulk_invalid_results_table)): ?>

    <?php echo $bulk_invalid_results_table; ?>

<?php endif; ?>