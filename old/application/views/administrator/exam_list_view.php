
<h1 class="heading">Manage Exams</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">
        
        <div class="row control-row control-row-top">
            <div class="span6 left">
            <?php echo form_open('administrator/exam/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_exam_title" id="filter_exam_title" value="<?php echo set_value('filter_exam_title', $this->form_data->filter_exam_title); ?>" placeholder="Exam title" class="input-medium" />
                <?php echo form_dropdown('filter_exam_type', $this->exam_type_list_filter, $this->form_data->filter_exam_type, 'id="filter_exam_type" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_status', $this->exam_status_list_filter, $this->form_data->filter_status, 'id="filter_status" class="chosen-select"'); ?>
                &nbsp;
                <span class="btn-group">
                    <input type="submit" value="Filter" class="btn" />
                    <?php if (count($filter) > 0): ?>
                    <button type="submit" name="filter_clear" value="Clear" title="Clear Filter" class="btn"><i class="icon-remove"></i></button>
                    <?php endif; ?>
                </span>

            <?php echo form_close(); ?>
            </div>
            <div class="span6 right">

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

