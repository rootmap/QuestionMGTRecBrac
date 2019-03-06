
<h1 class="heading">Admin  IPs</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">

        <div class="row control-row control-row-top">
            <div class="span6 left">
            <?php echo form_open('administrator/settings/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
			
			
                <input type="text" name="admin_ip" id="admin_ip" value="<?php echo set_value('admin_ip', $this->form_data->admin_ip); ?>" placeholder="IP" class="input-medium" />
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
<a class="pull-right btn btn-primary" href="<?php echo site_url('administrator/settings/add_ip'); ?>" >Add New IP</a>
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

