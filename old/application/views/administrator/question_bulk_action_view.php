

<h1 class="heading">Take an Action</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>

<?php

$bulk_questions_count = count($bulk_questions);
$bulk_invalid_questions_count = count($bulk_invalid_questions);

?>

<?php echo form_open_multipart('administrator/question/bulk_upload_do_action', array('class' => 'form-horizontal')); ?>

    <?php if ($bulk_questions_count > 0) : ?>
    <div class="alert alert-success"><?php echo $bulk_questions_count; ?> records found to be valid and can be imported. Press the 'Insert Bulk Questions' button below to insert valid questions.</div>
    <?php endif; ?>

    <?php if ($bulk_invalid_questions_count > 0) : ?>
    <div class="alert alert-error"><?php echo $bulk_invalid_questions_count; ?> records found invalid. <!--You can download the Invalid User List by pressing the 'Download Invalid User List' button below.--></div>
    <?php endif; ?>

    <fieldset>

        <div class="form-actions">

            <?php if ($bulk_questions_count > 0) : ?>
            <input type="submit" name="insert_bulk_questions" value="Insert Bulk Questions" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <?php endif; ?>

            <?php /*if ($bulk_invalid_questions_count > 0) : */?><!--
            <input type="submit" name="download_invalid_user_list" value="Download Invalid User List" class="btn" />&nbsp;&nbsp;
            --><?php /*endif; */?>

            <a href="<?php echo base_url('administrator/question/bulk'); ?>" class="btn btn-large">Upload File Again</a>&nbsp;&nbsp;

        </div>

    </fieldset>

<?php echo form_close(); ?>


<?php if ($bulk_invalid_questions_count > 0 && isset($bulk_invalid_questions_table)): ?>

    <?php echo $bulk_invalid_questions_table; ?>

<?php endif; ?>