

<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Create New'; ?> Survey</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/survey/add_survey', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/survey/update_survey', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="survey_title">Survey Title</label>
            <div class="controls">
                <input type="text" name="survey_title" id="survey_title" value="<?php echo set_value('survey_title', $this->form_data->survey_title); ?>" class="input-xxlarge" />
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="survey_description">Short Description</label>
            <div class="controls">
                <textarea name="survey_description" id="survey_description" rows="4" cols="30" class="input-xxlarge"><?php echo set_value('survey_description', $this->form_data->survey_description); ?></textarea>
            </div>
        </div>
        
        <div class="control-group">
            <label class="control-label" for="cat_parent">Parent Category</label>
            <div class="controls">
                <select name="cat_parent" id="cat_parent" class="chosen-select input-xlarge chzn-done">
                    <option value="0" selected="selected">Select a Category</option>
                    <?php foreach ($catList as $key => $value) { ?>
                    <option value="<?php echo $value['id']; ?>"><?php echo $value['cat_name']; ?></option> 
                    <?php } ?>
                </select>                
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="question_ids">Questions</label>
            <div class="controls">
                <div class="input-append">
                    <select name="question_ids[]" id="question_ids" multiple="multiple" class="chosen-select input-xxlarge"> 
                    </select> 
                </div>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="survey_status1">Survey Status</label>
            <div class="controls">
                <label class="radio inline">
                    <input type="radio" name="survey_status" id="survey_status1" value="open" <?php echo set_radio('survey_status', 'open', $this->form_data->survey_status == 'open'); ?> /> Open
                </label>
                <label class="radio inline">
                    <input type="radio" name="survey_status" id="survey_status2" value="closed" <?php echo set_radio('survey_status', 'closed', $this->form_data->survey_status == 'closed'); ?> /> Closed
                </label>
            </div>
        </div>

        <input value="yes"  type="hidden" name="survey_anms" id="survey_anms1" />


        <div class="control-group formSep">
            <label class="control-label" for="survey_expiry_date">Expiry Date</label>
            <div class="controls">
                <div class="input-append">
                    <input type="text" name="survey_expiry_date" id="exam_expiry_date" data-date-format="dd/mm/yyyy"
                        value="<?php echo set_value('survey_expiry_date', $this->form_data->survey_expiry_date); ?>"
                        class="date input-small" /><span class="add-on"><i class="icon-calendar"></i></span>
                </div>
            </div>
        </div>

        
        <div class="form-actions">
            <input type="hidden" name="survey_id" value="<?php echo set_value('survey_id', $this->form_data->survey_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Survey" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<script type="text/javascript">

jQuery(document).ready(function(){

    jQuery('#question-set-div strong').text('Total number of questions: ' + getNumberOfQuestions());
    jQuery('#question-set-div .set input, #question-set-div .set select').blur(function(){
        jQuery('#question-set-div strong').text('Total number of questions: ' + getNumberOfQuestions());
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
$( "#cat_parent" ).change(function() {
    var value = $( this ).val();
    $("#question_ids").html("");
    $("#question_ids").trigger("liszt:updated");
    destination = link + "administrator/survey/getQes/"+value;
    $.ajax({
        method: "POST",
        dataType: 'json',
        url: destination
    }).done(function (obj) {
        for (var i = 0; i < obj.length; i++){
            console.log(obj);
            $("#question_ids").append("<option value='" + obj[i].id + "'>" + obj[i].ques_text + "</option>");
            $("#question_ids").trigger("liszt:updated");
        }
    });
    
});

 

</script>



