

<h1 class="heading">Edit Bulk Questions</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open_multipart('administrator/survey_question/edit_bulk_upload', array('class' => 'form-horizontal')); ?>

    <div class="alert">
        Upload an Excel file to edit survey questions into the system. <br />
        Uploaded excel file's columns should match with defined format.
        <a href="<?php echo base_url('download'); ?>/edit_bulk-survey-question-import-example.xls" target="_blank">Click here</a>
        to download the predefined formatted excel file.
    </div>

    <fieldset>

        <div class="control-group">
            <label class="control-label" for="question_file">Upload File</label>
            <div class="controls">
                <input type="file" name="question_file" id="question_file" />
                <!--<span class="help-inline">Login Id is unique for each user.</span>-->
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="question_file_has_column_header">&nbsp;</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox" value="1" name="question_file_has_column_header" id="question_file_has_column_header" /> First row has column name
                </label>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" value="Upload and Bulk Edit" class="btn btn-primary btn-large" />&nbsp;&nbsp;
        </div>

    </fieldset>

<?php echo form_close(); ?>