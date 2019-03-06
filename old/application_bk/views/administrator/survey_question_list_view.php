
<h1 class="heading">Manage Questions</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">
        
        <div class="row control-row control-row-top">
            <div class="span6 left">
            <?php echo form_open('administrator/survey_question/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_question" id="filter_question" value="<?php echo set_value('filter_question', $this->form_data->filter_question); ?>" placeholder="Question" class="input-medium" />
                <?php echo form_dropdown('filter_category', $this->cat_list_filter, $this->form_data->filter_category, 'id="filter_category" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_type', $this->type_list_filter, $this->form_data->filter_type, 'id="filter_type" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_expired', $this->expired_list_filter, $this->form_data->filter_expired, 'id="filter_expired" class="chosen-select"'); ?>

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

        <div class="row control-row control-row-bottom">
            <div class="span6 left">&nbsp;</div>
            <div class="span6 right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

    </div>
</div>

<style type="text/css">
    .mark { padding: 2px 5px; cursor: pointer; color: #0088CC; border-bottom: 1px solid #0088CC; }
    .mark:hover { color: #ffffff; background: #0088CC; border-bottom: none; }
</style>