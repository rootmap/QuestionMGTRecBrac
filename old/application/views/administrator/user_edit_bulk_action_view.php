

<h1 class="heading">Take an Action</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>

<?php

$bulk_users_count = count($bulk_users);
$bulk_invalid_users_count = count($bulk_invalid_users);

?>


<?php echo form_open_multipart('administrator/user/edit_bulk_upload_do_action', array('class' => 'form-horizontal')); ?>

    <?php if ($bulk_users_count > 0) : ?>
    <div class="alert alert-success"><?php echo $bulk_users_count; ?> records found to be valid and can be imported. Press the 'Update Bulk Users' button below to update valid users.</div>
    <?php endif; ?>

    <?php if ($bulk_invalid_users_count > 0) : ?>
    <div class="alert alert-error"><?php echo $bulk_invalid_users_count; ?> records found invalid. <!--You can download the Invalid User List by pressing the 'Download Invalid User List' button below.--></div>
    <?php endif; ?>

    <fieldset>

        <div class="form-actions">

            <?php if ($bulk_users_count > 0) : ?>
            <input type="submit" name="update_bulk_users" value="Update Bulk Users" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <?php endif; ?>

            <?php /*if ($bulk_invalid_users_count > 0) : */?><!--
            <input type="submit" name="download_invalid_user_list" value="Download Invalid User List" class="btn" />&nbsp;&nbsp;
            --><?php /*endif; */?>

            <a href="<?php echo base_url('administrator/user/edit_bulk'); ?>" class="btn btn-large">Upload File Again</a>&nbsp;&nbsp;

        </div>

    </fieldset>

<?php echo form_close(); ?>


<?php if ($bulk_invalid_users_count > 0 && isset($bulk_invalid_users_table)): ?>

    <?php echo $bulk_invalid_users_table; ?>

<?php endif; ?>