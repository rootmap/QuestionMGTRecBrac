
<h1 class="heading">Manage Candidates</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">
        <div class="row control-row control-row-top">
            <div class="span6 left">
                <?php echo form_open('administrator/candidate/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
                <input type="text" name="filter_cand_name" id="filter_cand_name" value="<?php echo set_value('filter_cand_name', $this->form_data->filter_cand_name); ?>" placeholder="Name" class="input-medium" />
                <input type="text" name="filter_cand_email" id="filter_cand_email" value="<?php echo set_value('filter_cand_email', $this->form_data->filter_cand_email); ?>" placeholder="Email" class="input-medium" />
                <input type="text" name="filter_cand_address" id="filter_cand_address" value="<?php echo set_value('filter_cand_address', $this->form_data->filter_cand_address); ?>" placeholder="Address" class="input-medium" />
                <input type="text" name="filter_phone" id="filter_phone" value="<?php echo set_value('filter_phone', $this->form_data->filter_phone); ?>" placeholder="Phone" class="input-medium" />
                <?php echo form_dropdown('filter_cand_is_active', $this->active_list_filter, $this->form_data->filter_cand_is_active, 'id="filter_cand_is_active" class="chosen-select"'); ?>
                <span class="btn-group">
                    <input type="submit" value="Filter" class="btn" />
                    <?php if (count($filter) > 0): ?>
                        <button type="submit" name="filter_clear" value="Clear" title="Clear Filter" class="btn"><i class="icon-remove"></i></button>
                    <?php endif; ?>
                </span>

                <?php echo form_close(); ?>
            </div>

            <div class="span1 left">

            </div>

            <div class="span2 left" >
                <a class="btn btn-success" href="candidate/download_candidate"> Export Candidates </a>
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

