

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> Category</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/survey_category/add_category', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/survey_category/update_category', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="cat_name">Category Name</label>
            <div class="controls">
                <input type="text" name="cat_name" id="cat_name" value="<?php echo set_value('cat_name', $this->form_data->cat_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="cat_parent">Parent Category</label>
            <div class="controls">
                <?php echo form_dropdown('cat_parent', $this->cat_list, $this->form_data->cat_parent, 'id="cat_parent" class="chosen-select input-xlarge"'); ?>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

        <div class="form-actions">
            <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Category" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>