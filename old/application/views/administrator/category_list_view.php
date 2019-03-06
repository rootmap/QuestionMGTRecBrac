
<h1 class="heading">Manage Categories</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">
    <div class="span12">
        <!--

        <div class="row control-row control-row-top">
            <div class="span6 left">
            <?php echo form_open('administrator/category/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_cat_name" id="filter_cat_name" value="<?php echo set_value('filter_cat_name', $this->form_data->filter_cat_name); ?>" placeholder="Category name" class="input-medium" />
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
    -->

        <?php echo $records_table; ?>

        <div class="row">
            <div class="span6"></div>
            <div class="span6" style="text-align: right">

                <?php echo $pagin_links; ?>

            </div>
        </div>

    </div>
</div>

<style>
.table-bordered {
   
    border-left-width:1px !important;
    border-left-style: initial;
    border-left-color: initial;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
}
table.table-bordered.dataTable th, table.table-bordered.dataTable td {
    border-left-width: 1px !important;
}
</style>

<script>
    $(document).ready(function() {
        $('#category_table').DataTable(
        );
    } );
</script>