

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Create New'; ?> Exam</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/exam/add_exam', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/exam/update_exam', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="exam_title">Exam Title</label>
            <div class="controls">
                <input type="text" name="exam_title" id="exam_title" value="<?php echo set_value('cat_name', $this->form_data->exam_title); ?>" class="input-xxlarge" />
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="exam_description">Short Description</label>
            <div class="controls">
                <textarea name="exam_description" id="exam_description" rows="4" cols="30" class="input-xxlarge"><?php echo set_value('exam_description', $this->form_data->exam_description); ?></textarea>
            </div>
        </div>

        
 

        <div class="control-group formSep">


     
            <div class="controls">
                <input type="text" name="search" id="search" value="" class="input-md" />
            </div>

            <script type="text/javascript">
                $('#search').keyup(function () {
                  var valthis = $(this).val().toLowerCase();
                  var num = 0;
                  $('select#source>option').each(function () {
                      var text = $(this).text().toLowerCase();
                      if(text.indexOf(valthis) !== -1)  
                          {$(this).show(); $(this).prop('selected',true);}
                      else{$(this).hide();}
                       });
                });
            </script>
         

    <label class="control-label" for="source">Question Set</label>
    <div class="controls">
        <div class="">
            <div class="span2">
                <div class="row">
                    <select name="all_question_pool[]" id="source" class="form-control target" size="15" multiple="multiple">
                        <?php foreach ($qSet as $key => $value) { ?>
                            <option value="<?php echo $value['id']; ?>"><?php echo $value['id']; ?>. <?php echo $value['name']; ?></option>
                        <?php } ?>
                </select>
                </div>
            </div>
            <div class="span2" style="margin-right: 10px;">
                <button type="button" id="src2TargetAll" class="btn btn-block">
                    <i class="icon-forward"></i>
                </button>
                <button type="button" id="src2Target" class="btn btn-block btn-success">
                    <i class="icon-step-forward"></i>
                </button>
                <button type="button" id="target2Src" class="btn btn-block btn-success">
                    <i class="icon-step-backward"></i>
                </button>
                <button type="button" id="target2SrcAll" class="btn btn-block">
                    <i class="icon-backward"></i>
                </button>
            </div>
            <div class="span2">
                <div class="row">
                <select required name="exam_category[]" id="target" class="form-control target" size="15" multiple="multiple">
                    <?php foreach ($selected_cat as $keys => $values) { ?>
                        <option value="<?php echo $values['category_id']; ?>"><?php echo $values['name']; ?></option>
                    <?php } ?>
                </select>
                </div>
            </div>
        </div>
    </div>
</div>
    


    <div class="control-group formSep">
        <label class="control-label" for="exam_type1">Question Set Random </label>
        <div class="controls">
            <label class="radio inline">
                <input type="radio" name="random" id="random" value="random" <?php if($this->form_data->random == 'random' || $this->form_data->random == '') { echo "checked"; }?> /> Yes
            </label>
            <label class="radio inline">
                <input type="radio" name="random" id="random" value="fixed" <?php if($this->form_data->random == 'fixed') { echo "checked"; }?>/> No
            </label>
        </div>
    </div>

        

 
     

        <div class="control-group formSep">
            <label class="control-label" for="exam_status1">Exam Status</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="exam_status" id="exam_status1" value="open" <?php echo set_radio('exam_status', 'open', $this->form_data->exam_status == 'open'); ?> /> Open
                </label>
                <label class="radio inline">
                    <input type="radio" name="exam_status" id="exam_status2" value="closed" <?php echo set_radio('exam_status', 'closed', $this->form_data->exam_status == 'closed'); ?> /> Closed
                </label>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="exam_allow_result_mail1">Allow Sending Result Mail?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="exam_allow_result_mail" id="exam_allow_result_mail1" value="1" <?php echo set_radio('exam_allow_result_mail', '1', $this->form_data->exam_allow_result_mail == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="exam_allow_result_mail" id="exam_allow_result_mail2" value="0" <?php echo set_radio('exam_allow_result_mail', '0', $this->form_data->exam_allow_result_mail == '0'); ?> /> No
                </label>
                <span class="help-block">If allowed, then an email will be sent containing detail result
                    with answers, after finishing the exam.</span>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="exam_expiry_date">Expiry Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="exam_expiry_date" id="exam_expiry_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('exam_expiry_date', $this->form_data->exam_expiry_date); ?>"
                        class="date input-small" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
                <!--<span class="help-block">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="exam_time">Duration</label>
            <div class="controls">
                <input type="text" name="exam_time" id="exam_time"
                       value="<?php echo set_value('exam_time', $this->form_data->exam_time); ?>"
                       class="input-mini right" />
                <span class="help-inline">Duration time is in minute. Set 0 for unlimited duration.</span>
            </div>
        </div>

        

        <div class="control-group" id="">
            <label class="control-label" for="exam_allow_previous1">Allow Back Button?</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="exam_allow_previous" id="exam_allow_previous1" value="1" <?php echo set_radio('exam_allow_previous', '1', $this->form_data->exam_allow_previous == '1'); ?> /> Yes
                </label>
                <label class="radio inline">
                    <input type="radio" name="exam_allow_previous" id="exam_allow_previous2" value="0" <?php echo set_radio('exam_allow_previous', '0', $this->form_data->exam_allow_previous == '0'); ?> /> No
                </label>
            </div>
        </div>
        <input type="hidden" name="exam_allow_dontknow" id="exam_allow_dontknow1" value="0"  checked /> 
        <div class="control-group input-append" id="allow-negative-mark-weight-field" style="display: none;">
            <label class="control-label" for="exam_negative_mark_weight">Negative Marking Weight</label>
            <div class="controls">
                <input type="text" name="exam_negative_mark_weight" id="exam_negative_mark_weight"
                       value="<?php echo set_value('exam_negative_mark_weight', $this->form_data->exam_negative_mark_weight); ?>"
                       class="input-mini right" /><span class="add-on">%</span>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="exam_nop">Exam for Name of the position</label>
            <div class="controls">
                <input type="text" name="exam_nop" id="exam_nop" class="input-xxlarge" value="<?php echo set_value('exam_nop', $this->form_data->exam_nop); ?>" />
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="exam_instructions">Instructions to Candidates</label>
            <div class="controls">
                <code>Please Use -> Sign to separate each row.</code><br>
                <textarea name="exam_instructions" id="exam_instructions" rows="8" cols="15" class="input-xxlarge"><?php echo @set_value('exam_instructions', $this->form_data->exam_instructions); ?></textarea>
                <div style="width: 100%; padding-bottom: 10px; padding-top:10px; text-decoration: underline; font-weight: bold; display: block;">
                Instructions to Candidates : Preview</div>
                <span id="ins_preview"><br>
                    ◙ Write your name, contact no. and signature on the space given and check all your details carefully <br>
                    ◙ Use of Calculator is strictly prohibited <br>
                    ◙ for incorrect MCQ <br>
                    ◙ Use of cell phones are strictly prohibited during the time of exam <br>
                    ◙ The mark allocation is indicated at the beginning of each segment <br>
                    ◙ You are not permitted to leave the examination room early without the prior consent of the invigilator<br>
                </span>
            </div>
        </div>


        <div class="form-actions">
            <input type="hidden" name="exam_id" value="<?php echo set_value('exam_id', $this->form_data->exam_id); ?>" />

            <input type="submit" onclick="SelectAllSelectOptions()" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Exam" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<script type="text/javascript">
function SelectAllSelectOptions() {
    $('#target option').prop('selected',true);
}
jQuery(document).ready(function(){

    jQuery('#question-set-div strong').text('Total number of questions: ' + getNumberOfQuestions());
    jQuery('#question-set-div .set input, #question-set-div .set select').blur(function(){
        jQuery('#question-set-div strong').text('Total number of questions: ' + getNumberOfQuestions());
    });
    jQuery('#exam_instructions').keyup();
    jQuery('#exam_instructions').keyup(function(){
        var stringContent=$(this).val();
        //var newDataString=stringContent.split("->").join(", ");
        var showWord=stringContent.split('->').slice(0,2000).join('<br>&#x25D9; ');
            showWord=showWord?showWord:'';
        //alert(showWord);
        $("#ins_preview").html(showWord);
    });

    /* #allow-dontknow-field show/hide */
    if (jQuery('#exam_type1').is(':checked')) {
        jQuery('#allow-dontknow-field').show();
        jQuery('#allow-negative-marking-field').show();
        jQuery('#allow-negative-mark-weight-field').show();
        jQuery('#allow-back-button-field').addClass('formSep');
    } else {
        jQuery('#allow-dontknow-field').hide();
        jQuery('#allow-negative-marking-field').hide();
        jQuery('#allow-negative-mark-weight-field').hide();
        jQuery('#allow-back-button-field').removeClass('formSep');
    }

    jQuery('#exam_type1').click(function(){
        jQuery('#allow-dontknow-field').show();
        jQuery('#allow-negative-marking-field').show();
        jQuery('#allow-negative-mark-weight-field').show();
        jQuery('#allow-back-button-field').addClass('formSep');
    });

    jQuery('#exam_type2').click(function(){
        jQuery('#allow-dontknow-field').hide();
        jQuery('#allow-negative-marking-field').hide();
        jQuery('#allow-negative-mark-weight-field').hide();
        jQuery('#allow-back-button-field').removeClass('formSep');
    });

});

function getNumberOfQuestions() {
    var qno = 0;
    jQuery('#question-set-div .set').each(function(){

        var selectField = jQuery(this).find('select');
        var inputField = jQuery(this).find('input:last');

        var selectValue = parseInt(jQuery(selectField).val());
        var inputValue = parseInt(jQuery(inputField).val());

        if (!isNaN(inputValue) && selectValue > 0) {
            qno = qno + inputValue;
        }

    });
    return qno;
}
function sureTransfer(from, to, all) {
        if ( from.getElementsByTagName && to.appendChild ) {
            while ( getCount(from, !all) > 0 ) {
                transfer(from, to, all);
            }
        }
    }
    function getCount(target, isSelected) {
        var options = target.getElementsByTagName("option");
        if ( !isSelected ) {
            return options.length;
        }
        var count = 0;
        for ( i = 0; i < options.length; i++ ) {
            if ( isSelected && options[i].selected ) {
                count++;
            }
        }
        return count;
    }
    function transfer(from, to, all) {
        if ( from.getElementsByTagName && to.appendChild ) {
            var options = from.getElementsByTagName("option");
            for ( i = 0; i < options.length; i++ ) {
                if ( all ) {
                    to.appendChild(options[i]);
                } else {
                    if ( options[i].selected ) {
                        to.appendChild(options[i]);
                    }
                }
            }
        }
    }
    window.onload = function(){
        document.getElementById("src2TargetAll").onclick = function()
        {
            sureTransfer(document.getElementById("source"), document.getElementById("target"), true);
        };
        document.getElementById("src2Target").onclick = function()
        {
            sureTransfer(document.getElementById("source"), document.getElementById("target"), false);
        };
        document.getElementById("target2SrcAll").onclick = function()
        {
            sureTransfer(document.getElementById("target"), document.getElementById("source"), true);
        };
        document.getElementById("target2Src").onclick = function()
        {
            sureTransfer(document.getElementById("target"), document.getElementById("source"), false);
            $('#target option').prop('selected',true);
        };
    }
</script>



