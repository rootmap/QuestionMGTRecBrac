

<h1 class="heading"> Exam venue Mapping</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>

<?php echo form_open('exam_venues/exam_venue_mapping/do_mapping/', array( 'onsubmit'=>'return form_priority_validation();', 'class' => 'form-horizontal')); ?>
    <fieldset>

        <div class="control-group formSep" id="exam-field">
            <label class="control-label" for="exam_id">Exam</label>
            <div class="controls">
                <select class="chosen-select input-xxlarge" name="exam_all">
                    <option value=""></option>
                    <?php foreach ($exam_all as $key): ?>
                    <option value="<?php echo $key->id; ?>"><?php echo $key->exam_title; ?></option>
                    <?php endforeach ?>
                </select>
            </div>
                <!--<span class="help-block">Inline help text</span>-->
        </div>
        

        

        <div class="control-group" id="venue-field">
            <label class="control-label" for="venie_id">Venues</label>
            <div class="controls input_fields_wrap">
              <select class="chosen-select input-xxlarge check_field" name="venue_all[]">
                <option value=""></option>
                <?php foreach ($venue_all as $key1): ?>
                <option class="" value="<?php echo $key1->id; ?>"><?php echo $key1->name; ?></option>
                <?php endforeach ?>
              </select>
            </div>
        </div>

<!--<?php echo form_dropdown('user_ids[]', $this->user_list, '', 'id="user_ids" multiple="multiple" class="chosen-select input-xxlarge"'); ?>-->

        <div class="form-actions">
            <input type="submit" value="Exam Venues Mapping" id="" class="btn btn-primary" />
            <input type="reset" name="reset" class="btn btn-default">
            <button class="add_field_button btn-info btn">Add More Fields</button>
        </div>

    </fieldset>

<?php echo form_close(); ?>

<div class="row-fluid">
    <div class="span12">

        <div class="row control-row control-row-top">
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



<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo form_open('exam_venues/exam_venue_mapping/update_mapping', array('class' => 'form-horizontal' , 'onsubmit' => 'return form_validation();')); ?>

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="myModalLabel">Exam Venue Update</h4>
          </div>
          <div class="modal-body">
          <input type="hidden" id="evid" name="did">
                <div class="control-group">
                    <label class="col-sm-4 control-label" for="mo_exam_id">Exam</label>
                    <div class="controls">
                        <select class="input-xlarge" id="mo_exam_all" name="mo_exam_all">
                            
                            <?php foreach ($exam_all as $key): ?>
                            <option value="<?php echo $key->id; ?>"><?php echo $key->exam_title; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="col-sm-4 control-label" for="mo_venue_id">Venues</label>
                    <div class="controls">
                        <select class="input-xlarge" id="mo_venue_all" name="mo_venue_all">
                            
                            <?php foreach ($venue_all as $key2): ?>
                            <option value="<?php echo $key2->id; ?>"><?php echo $key2->name; ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>

      <?php echo form_close(); ?>
    </div>
  </div>
</div>



<script type="text/javascript">

$(document).ready(function() {
    var max_fields      = 10; //maximum input boxes allowed
    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $(".add_field_button"); //Add button ID
    
    var x = 1; //initlal text box count
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
            x++; //text box increment
            $(wrapper).append('<div class="" style="margin-bottom: 5px;"><select class=" input-xxlarge check_field" name="venue_all[]"><option value="">select an option</option><?php foreach ($venue_all as $key1): ?><option value="<?php echo $key1->id; ?>"><?php echo $key1->name; ?></option><?php endforeach ?></select><a style="margin-left: 5px;" href="#" class="remove_field">Remove</a></div>'); //add input box
        }
    });
    
    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    })


    $('a[data-toggle=modal], a[data-toggle=modal]').click(function () {
        var evid              = $(this).data('evid');
        var mo_exam_all       = $(this).data('mo_exam_all');
        var mo_venue_all    = $(this).data('mo_venue_all');
        //console.log("arin"+mo_venue_all);
        //console.log("arin"+mo_exam_all);
        //console.log(evid);

        $('#evid').val(evid);

        //$('#mo_exam_all ').val(mo_exam_all);
        $('#mo_exam_all option[value='+mo_exam_all+']').attr('selected','selected');
        $('#mo_venue_all option[value='+mo_venue_all+']').attr('selected','selected');
  
        //$('#mo_venue_all').val(mo_venue_all);
        //$('#mo_exam_all').trigger("chosen:updated");
        //$('#mo_venue_all').trigger("chosen:updated");
    });

});


function form_priority_validation(){
    var valid = true;
        
        

    return valid;
}

</script>