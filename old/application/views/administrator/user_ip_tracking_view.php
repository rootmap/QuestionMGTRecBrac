<h1 class="heading"> Device / IP Tracking Log</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<div class="row-fluid">
    <div class="span12">

        <div class="row control-row control-row-top">
        	<div class="span6 left">
            <?php echo form_open('administrator/user/filter_user_iptracking', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_login_name" id="filter_login_name" value="<?php echo set_value('filter_login_name', $this->form_data->filter_login_name); ?>" placeholder="Login Pin" class="input-medium" />
                &nbsp;
                <span class="btn-group">
                    <input type="submit" value="Filter" class="btn" />
                    <?php if (count($filter_act_user) > 0): ?>
                    <button type="submit" name="filter_clear" value="Clear" title="Clear Filter" class="btn"><i class="icon-remove"></i></button>
                    <?php endif; ?>
                </span>

            <?php echo form_close(); ?>
            </div>
            <div class="right">
                <?php echo $pagin_links; ?>
            </div>
        </div>


        <?php echo $records_table; ?>


        <div class="row control-row control-row-bottom">
            <div class="right">
                <?php echo $pagin_links; ?>
            </div>
        </div>

    </div>
</div>