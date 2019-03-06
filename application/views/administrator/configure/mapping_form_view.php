

<h3 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> SMS &amp; Email Mapping With Exam</h3>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/'.$view_controller.'/add_mapping', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/'.$view_controller.'/update_mapping', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>
        <?php 
        set_value('exam_id', $this->form_data->exam_id);
        set_value('exam_id', $this->form_data->layout_id); 
        ?>
        

        <div class="control-group formSep">
            <label class="control-label" for="exam_name">SMS & EMail Layout</label>
            <div class="controls">
                <select name="layout_id" id="layout_id" class="input-xlarge">
                    <option value="0">Select Layout</option>
                    <?php 
                    if(!empty($layoutData)){
                        foreach ($layoutData as $lxm) {
                        ?>
                        <option value="<?=$lxm['id']?>" <?php if($this->form_data->layout_id==$lxm['id']){ ?> selected="selected" <?php } ?>><?=$lxm['cat_name']?></option>
                        <?php 
                        }
                    }
                    ?>
                </select>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <span id="cloneData" class="cloneData">
            <div class="control-group formSep">
                <label class="control-label" for="exam_id">Exam Name</label>
                <div class="controls">
                    <select name="exam_id<?php if(!$is_edit){ ?>[]<?php } ?>" id="exam_id" class="input-xlarge">
                        <option value="">Select Exam</option>
                        <?php 
                        if(!empty($exam)){
                            foreach ($exam as $exm) {
                            ?>
                            <option value="<?=$exm['id']?>" <?php if($this->form_data->exam_id==$exm['id']){ ?> selected="selected" <?php } ?>><?=$exm['exam_title']?></option>
                            <?php 
                            }
                        }
                        ?>
                    </select>
                    <a href="#" class="remove_field btn btn-danger" ><i class="icon-remove"></i></a>
                </div>
            </div>
        </span>
        

        

        <div class="form-actions">
            <input type="hidden" name="mapping_id" value="<?php echo set_value('mapping_id', $this->form_data->mapping_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Mapping Layout" class="btn btn-primary" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn" />
            <?php if(!$is_edit){ ?>
            <button class="btn btn-warning" id="addMoreExam" type="button"><i class="icon-plus"></i> Add More Exam In Same Layout</button>
            <?php } ?>
            <a class="btn btn-info" href="<?=base_url('administrator/smsnemail/mapping')?>"><i class="icon-list"></i> Back To List </a>

        </div>

    </fieldset>

<?php echo form_close(); ?>
<script>
    $(document).ready(function() {
        var max_fields      = 10; //maximum input boxes allowed
        var wrapper         = $(".cloneData"); //Fields wrapper
        var add_button      = $("#addMoreExam"); //Add button ID
        //var add_clone_data  = $("#cloneData").html();
        var add_clone_data=$("#cloneData").first().html();
        $(".remove_field" ).first().fadeOut();
        
        var x = 1; //initlal text box count
        $(add_button).click(function(e){ //on add input button click

            
            //alert(add_clone_data);

            e.preventDefault();
            if(x < max_fields){ 
                //max input box allowed
                x++; 
                //text box increment
                $(wrapper).append(add_clone_data); 
                //add input box
            }
        });
        
        $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
            var c=confirm("Are you sure to perform this action ??");
            if(c)
            {
                e.preventDefault(); $(this).parent('div').parent('div').remove(); x--;
            }
            
        });
    });
            
</script>

