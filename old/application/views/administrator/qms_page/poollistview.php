
<h1 class="heading">Questions Pool List</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>
<?php echo form_open('questionpoollist', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
    <input type="text" name="filter_question" id="filter_question" value="<?php if(isset($_POST['filter_question'])){ echo $_POST['filter_question']; }?>" placeholder="Question" class="input-medium" />
    &nbsp;
    <span class="btn-group">
        <input type="submit" value="Filter" name="filter" class="btn" />
    </span>
<?php echo form_close(); ?>
<div class="row-fluid">
    <div class="span12">
        
        <div class="row control-row control-row-top">
            <div class="span6 left">
            
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