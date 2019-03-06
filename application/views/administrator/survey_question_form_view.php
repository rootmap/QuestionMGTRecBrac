

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> Question</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/survey_question/add_question', array('class' => 'form-horizontal', 'id' => 'question-form')); ?>
<?php else : ?>
<?php echo form_open('administrator/survey_question/update_question', array('class' => 'form-horizontal', 'id' => 'question-form')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="ques_text">Question</label>
            <div class="controls">
                <textarea name="ques_text" id="ques_text" rows="5" cols="30" class="input-xxlarge"><?php echo $this->form_data->ques_text; ?></textarea>
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="category_id">Category</label>
            <div class="controls">
                <?php echo form_dropdown('category_id', $this->cat_list, $this->form_data->category_id, 'id="category_id" class="chosen-select input-xlarge"'); ?>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="ques_expiry_date">Expiry Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="ques_expiry_date" id="ques_expiry_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('ques_expiry_date', $this->form_data->ques_expiry_date); ?>"
                        class="date input-small" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="survey_weight">Survey Weight</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="survey_weight" id="survey_weight"
                        value="<?php echo set_value('survey_weight', $this->form_data->survey_weight); ?>"  class="input-small" />
                </div>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ques_type1">Question Type</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="ques_type" id="ques_type1" value="option_based" <?php echo set_radio('ques_type', 'option_based', $this->form_data->ques_type == 'option_based'); ?> /> Option Based Question
                </label>
                <label class="radio inline">
                    <input type="radio" name="ques_type" id="ques_type2" value="descriptive" <?php echo set_radio('ques_type', 'descriptive', $this->form_data->ques_type == 'descriptive'); ?> /> Descriptive Question
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="ques_type1">Question Format</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="qus_fixed" id="qus_fixed1" value="0" <?php echo set_radio('qus_fixed', '0', $this->form_data->qus_fixed == '0'); ?> /> Genaeral
                </label>
                <label class="radio inline">
                    <input type="radio" name="qus_fixed" id="qus_fixed2" value="1" <?php echo set_radio('qus_fixed', '1', $this->form_data->qus_fixed == '1'); ?> /> Template
                </label>
            </div>
        </div>
        
        
        <div class="control-group" id="mcqchoice-row">
            <label class="control-label" for="ques_type1">
                Options <br /><br />
                <a href="#AddChoiceModal" role="button" class="btn" data-toggle="modal">Add Options</a><br /><br />
                <a href="#" role="button" class="btn" data-toggle="modal" id="clear-choices">Clear All Options</a>
            </label>
            <div class="controls" id="mcqchoice-div">
            
            <?php if ($is_edit): ?>

                <?php if ($this->form_data->ques_choices != '' && count($this->form_data->ques_choices) > 0) : ?>

                    <?php for ($i=0; $i<count($this->form_data->ques_choices); $i++):
                        $text = $this->form_data->ques_choices[$i]['text'];
                        if(isset($this->form_data->ques_choices[$i]['marks'])){
                            $marks = $this->form_data->ques_choices[$i]['marks'];
                        }else{
                            $marks = "";
                        }
                        
                        $class_name = '';
                    ?>
                    <div class="choice<?php echo $class_name; ?>">
                        <div>
                            <a href="javascript:void(0)" class="move icon-move">move</a>
                            <a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>
                            <a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>
                        </div>
                        <textarea name="mcq_options[]" rows="3" cols="30" style="width:70%;"><?php echo $text; ?></textarea>
                        <input type="text" value="<?php echo $marks; ?>" name="mcq_marks[]" rows="3" cols="30" style="width:20%; height: 63px;font-size: 2em; font-weight: bolder;"/>
                    </div>
                    <?php endfor; ?>

                <?php else: ?>

                    <div class="choice">
                        <div>
                            <a href="javascript:void(0)" class="move icon-move">move</a>
                            <a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>
                            <a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>
                        </div>
                        <textarea name="mcq_options[]" rows="3" cols="30" style="width:70%;"></textarea>
                        <input type="" name="mcq_marks[]" rows="3" cols="30" style="width:20%; height: 63px;font-size: 2em; font-weight: bolder;"/>

                    </div>

                <?php endif; ?>

            <?php else: ?>

                <div class="choice">
                    <div style="width:92%;overflow:hidden;">
                        <a href="javascript:void(0)" class="move icon-move">move</a>
                        <a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>
                        <a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>
                    </div>
                    <textarea name="mcq_options[]" rows="3" cols="30" style="width:70%;">dd</textarea>
                    <input type="" name="mcq_marks[]" rows="3" cols="30" style="width:20%; height: 63px;font-size: 2em; font-weight: bolder;"/>
                </div>

            <?php endif; ?>

            </div>
        </div>

        <div class="form-actions">
            <input type="hidden" name="question_id" value="<?php echo set_value('question_id', $this->form_data->question_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Question" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<div class="modal hide fade" id="AddChoiceModal">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Add Multiple Options Collectively</h3>
    </div>
    <div class="modal-body">
        <p>Enter multiple options in the textarea below. Separate each option by new line.</p>
        <textarea rows="10" cols="30" class="input-block-level" id="collective-mcq"></textarea>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-primary" id="add-choice-button">Add Options</a>
        <a href="#" class="btn" data-dismiss="modal" aria-hidden="true" class="close-button">Close</a>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function(){
    /* show-hide choice options div */
    if (jQuery('#ques_type1').is(':checked')) {
        jQuery('#mcqchoice-row').show();
    } else {
        jQuery('#mcqchoice-row').hide();
    }
    
    
    jQuery('#ques_type1').click(function(){
        jQuery('#mcqchoice-row').show();
    });

    jQuery('#ques_type2').click(function(){
        jQuery('#mcqchoice-row').hide();
    });


    /* sort choice div */
    jQuery('#mcqchoice-div').sortable({
        update: function(event, ui) {
            jQuery('#mcqchoice-div .choice').each(function(index){
                jQuery(this).find('input').val(index);
                rearrangeChoiceButtons();
            });
        }
    });

    rearrangeChoiceButtons();

    /* add choice button */
    jQuery('.choice .add').live('click', function(){
        addNewChoice();
        rearrangeChoiceButtons();
    });

    /* remove choice button */
    jQuery('.choice .delete').live('click', function(){
        jQuery(this).parent().parent().find('textarea').val('');
        jQuery(this).parent().parent().hide();
        rearrangeChoiceButtons();
    });


    /* multiple choice add modal button */
    jQuery('#AddChoiceModal').on('shown', function () {
        jQuery('#collective-mcq').focus();
    });

    jQuery('#AddChoiceModal').on('hide', function () {
        jQuery('#collective-mcq').val('');
    });

    jQuery('#add-choice-button').live('click', function(){

        var mcqOptions = jQuery('#collective-mcq').val();
        var mcqOptionsArr = mcqOptions.split('\n');
        var mcqChoiceText = '';

        for (i = 0; i<mcqOptionsArr.length; i++) {
            mcqChoiceText = jQuery.trim(mcqOptionsArr[i]);
            if (mcqChoiceText != '') {
                addNewChoice(mcqChoiceText);
            }
        }

        rearrangeChoiceButtons();
        jQuery('#AddChoiceModal').modal('hide');
        return false;
    });

    /* clear choice button */
    jQuery('#clear-choices').click(function(){
        var response = confirm('This is will remove all options. Are you sure you want to continue?');
        if (response) {
            jQuery('#mcqchoice-div .choice').each(function(){
                jQuery(this).remove();
            });

            addNewChoice();
            rearrangeChoiceButtons();
        }
    });


    /* submit validation */
    jQuery('#question-form').submit(function(){

        var questionText = jQuery.trim(jQuery('#ques_text').val());
        if (questionText == '') {
            alert('Question is required');
            jQuery('#ques_text').focus();
            return false;
        }

        if (jQuery('#ques_type1').is(':checked')) {

            var totalChoice = 0;
            jQuery('.choice textarea').each(function(){
                var t = jQuery.trim(jQuery(this).val());
                if (t != '') { totalChoice = totalChoice + 1; }
            });

            if (totalChoice < 2) {
                alert('Please enter at least two options');
                return false;
            }

        }
    });

});

function addNewChoice(text) {

    var totalChoice = parseInt(jQuery('#mcqchoice-div .choice').length);
    var choiceHtml = '';
    var text = jQuery.trim(text);

    choiceHtml += '<div class="choice">';
        choiceHtml += '<div>';
            choiceHtml += '<a href="javascript:void(0)" class="move icon-move" title="Sort Option">move</a>';
            choiceHtml += '<a href="javascript:void(0)" class="add icon-plus" title="Add New Option">add</a>';
            choiceHtml += '<a href="javascript:void(0)" class="delete icon-minus" title="Delete Option">delete</a>';
        choiceHtml += '</div>';
        choiceHtml += '<textarea name="mcq_options[]" rows="3" cols="30" style="width:70%;">'+ text +'</textarea>';
        choiceHtml += '<input type="" name="mcq_marks[]" rows="3" cols="30" style="width:20%; height: 63px;font-size: 2em; font-weight: bolder;"/>';
    choiceHtml += '</div>';

    jQuery('#mcqchoice-div').append(choiceHtml);
    return false;

}

function rearrangeChoiceButtons() {
    jQuery('.choice .add').css('display', 'none');
    jQuery('.choice .delete').css('display', 'inline-block');
    jQuery('.choice:last-child .add').css('display', 'inline-block');
    jQuery('.choice:last-child .delete').css('display', 'none');
}

</script>


