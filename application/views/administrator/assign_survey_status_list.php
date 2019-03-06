
<h1 class="heading">Manage Survey Status</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>



    <div class="row control-row control-row-top">
        <div class="span6 left">
      
            <?php echo form_open('administrator/assign_survey_status/survey_assign_list_filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>

                <input type="text" name="filter_login" id="filter_login" value="<?php echo set_value('filter_login', $this->form_data->filter_login); ?>" placeholder="User ID" class="input-medium" />
                <?php echo form_dropdown('filter_group', $this->group_list_filter, $this->form_data->filter_group, 'id="filter_group" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_status', $this->status_list_filter, $this->form_data->filter_status, 'id="filter_status" class="chosen-select"'); ?>
                <?php echo form_dropdown('filter_approval_status', $this->approval_status_list_filter, $this->form_data->filter_approval_status, 'id="filter_approval_status" class="chosen-select"'); ?>

                &nbsp;
                <input type="submit" value="Filter" class="btn" />

            <?php echo form_close(); ?>
           
        </div>
        
        <div class="span2">
            <?php if ($records && count($records) > 0): ?>
                <?php echo form_open('administrator/assign_survey_status/export_data'); ?>
                <button type="submit" class="btn"><i class="icon-download-alt"></i>Export Data</button>
                <?php echo form_close(); ?>
            <?php endif; ?> 
        </div>
        
        <div class="span4 right">

            <?php echo $pagin_links; ?>

        </div>
    </div>
    <?php if ($records && count($records) > 0): ?>
                <?php echo form_open('administrator/assign_survey_status/survey_user_approval_bulk', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
    <?php echo $records_table; ?>
    <div class="row control-row control-row-bottom">
    <div class="span10 right">
            
                <button type="submit" name="approve_all" value="Approve Selected" title="Approve All" class="btn bulkact">Approve Selected Questions</button>
                <button type="submit" name="reject_all" value="Reject Selected" title="Reject All" class="btn bulkact">Reject Selected Questions</button>
                 
        </div>
    </div>

    <?php echo form_close(); ?>
            <?php endif; ?>
    <div class="row control-row control-row-bottom">
        <div class="span6 left">&nbsp;</div>
        <div class="span6 right">

            <?php echo $pagin_links; ?>

        </div>
    </div>



<style type="text/css">
    .row-fluid { margin-top: 0!important; }
    .span10 form { margin-bottom: 0; }
    .export-buttons {
        width: 100%;
        margin-bottom: 10px;
        overflow: hidden;
    }
    .export-buttons form {
        display: block;
        float: right;
        margin-left: 10px;
        margin-bottom: 0;
    }
    .pagination { margin: 0; }
    .pagination-top { margin: 0 0 15px 0; }
    .pagination-bottom { margin: 15px 0 0 0; }
</style>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('.action-delete').click(function(){
        var response = confirm('Are you sure you want to continue?');
        if (!response) {
            return false;
        }
    });    
})
</script>
<script type="text/javascript">
    $(document).ready(function(){
        //approve_ques
        
        $("#aprall").click(function () {
          if(document.getElementById("aprall").checked==true)
          {
               //alert('checked');
              $('input[type="checkbox"]').prop('checked', true);
          }
          else
          {
                //alert('unchecked');
             $('input[type="checkbox"]').prop('checked', false);
          }
          //$(".aprall")
          
        });
    });
</script>
