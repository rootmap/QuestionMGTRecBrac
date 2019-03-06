
<h1 class="heading">Manage Users</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">

        <div class="row control-row control-row-top">
            <div class="span6 left">
                <?php echo form_open('administrator/user/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_loginoremail" id="filter_loginoremail" value="<?php echo set_value('filter_loginoremail', $this->form_data->filter_loginoremail); ?>" placeholder="Login ID or Email" class="input-medium" />
                <?php echo form_dropdown('filter_type', $this->type_list_filter, $this->form_data->filter_type, 'id="filter_type" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_team', $this->team_list_filter, $this->form_data->filter_team, 'id="filter_team" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_active', $this->active_list_filter, $this->form_data->filter_active, 'id="filter_active" class="chosen-select"'); ?>

                &nbsp;
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

            <div class="span5 left" >
                <a class="btn btn-success" href="user/download_user"> Export Users </a>
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

