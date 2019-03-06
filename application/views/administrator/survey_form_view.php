

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
        <label class="control-label" for="cat_parent">Survey Category</label>
        <div class="controls">
            <select onchange="getQuestions(this)" name="cat_parent" id="cat_parent" class="chosen-select input-xxlarge">
                <option value="">Select Category</option>
                 <?php foreach ($catList as $key => $value) { ?>
                    <option value="<?php echo $value['id']; ?>"><?php echo $value['cat_name']; ?></option> 
                    <?php } ?>
            </select>
        </div>
    </div>




    <div class="span10">
        <table class="table " id="ques_tbl" style="margin-bottom: 20px;">
            <thead>
            <tr>
                <th width="500">Questions</th>
                <th>Select Question</th>
            </tr>
            </thead>
            <tbody id="ques_tbl_body">
            </tbody>
        </table>

        <div class="" style="margin-top:2%;margin-bottom: 5%">
            <input onclick="AddAllSelectedQuestion()" type="button" value="Add All Question" class="btn btn-success btn-sm" />&nbsp;&nbsp;
        </div>

    </div>



    <div class="control-group formSep">

    </div>

    <div style="margin-top:2%;">
        <h3>Added Question List:</h3>
    </div>


    <div class="span10" style="overflow-x: hidden;">
        <table class="table  table-striped" id="added_ques_tbl" style="margin-bottom: 0;">
            <thead>
            <tr>
                <th width="500">Questions</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody id="added_ques_tbl_body">
                  </tbody>
        </table>

        <div class="control-group formSep">

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
$( document ).ready(function() {
        $('input[name=exam_type]').on('change', function(e) {
            if($(this).val()==='mcq')
                $('.nevetive-mark-div').show();
            else{
                $('.nevetive-mark-div').hide();
            }
        });


        $('#added_ques_tbl_body').sortable();
    });

    function SelectAllSelectOptions() {
        $('#target option').prop('selected',true);
    }

    function getQuestions(elem) {
       
            $("#source").empty().append("");
            $('#ques_tbl_body').empty().append("");

            var type = $('input[name=exam_type]:checked').val();
            var cat = $(elem+':selected').text();
            $.ajax({
                method: "GET",
                dataType: 'json',
                url: link + "getcategorysurveyquestion/"+$(elem).val(),
                data:{type:type}
            }).done(function (obj) {
                var dataobjQues=obj.question;
                var dataobjQuesRand=obj.questionrand;
                console.log(dataobjQues);
                for (var i = 0; i < dataobjQues.length; i++)
                {
                    var mandatory_check='';
                    if(dataobjQues[i].is_mandatory!==0 || dataobjQues[i].is_mandatory!==null ){mandatory_check='check=""'};

                    $("#ques_tbl_body").append("<tr id='data_ini_"+dataobjQues[i].id+"'><td>"+(i+1)+". "+dataobjQues[i].ques_text+"</td><td><button type='button' name='question_list[]' data-ques='"+dataobjQues[i].ques_text+"' data-mand='"+dataobjQues[i].is_mandatory+"' data-cat='"+cat+"' data-mark='"+dataobjQues[i].mark+"' data-ques='"+dataobjQues[i].ques_text+"'   data-id='"+dataobjQues[i].id+"' id='add_ques' onclick='addQuestionAsSelected(this)'  class='form-control btn btn-success input-sm question_list' >Add</button></td></tr>");

                }

                for (var i = 0; i < dataobjQuesRand.length; i++)
                {
                    $('#data_ini_'+dataobjQuesRand[i].id).children('td:eq(1)').children('button').click();
                    //addQuestionAsSelected(elmn);
                    //console.log(i,elmn);
                }


            });
       

    }


    function form_priority_validation(){
        var valid = true;
        var limit = $('#set_limit').val();
        var items = document.getElementsByClassName('tr-number');
        var ques_num = items.length;



        return valid;
    }

    var sl=1;

    function addQuestionAsSelected(elem) {

        var totalmark=0;
        var is_mandatory="";
        if($(elem).data('mand')!==0)
            is_mandatory='checked=""';
        else
            is_mandatory='';

        var content = $(elem).data('ques');
        if ($('#added_ques_tbl_body:contains('+content+')').length == 0) {
            $("#added_ques_tbl_body").append("<tr class='tr-number'><td id='ques_text'>"+sl+". "+$(elem).data('ques')+"</td><td><input type='hidden' name='question_ids[]' value='" + $(elem).data('id') + "' /><button type='button' name='question_list[]'  id='remove_ques' onclick='removeQuestionAsSelected(this)'  class='form-control btn btn-success input-sm' >Remove</button></td></tr>");

            sl++;
        }
        else{
            alert('Adding same question is not allowed.');
        }
        
    }



    function AddAllSelectedQuestion() {
        var items = document.getElementsByClassName('question_list');
        var j = 0;
        for (var i = 0; i < items.length; i++) {
            var content = items[i].dataset.ques;
            if ($('#added_ques_tbl_body:contains('+content+')').length == 0) {
                var is_mandatory = "";
                if (items[i].dataset.mand !== '0')
                    is_mandatory='checked=""';
                else
                    is_mandatory = '';
                if(items[i].dataset.mark===null || items[i].dataset.mark==='null')
                    items[i].dataset.mark=0;
                else
                    items[i].dataset.mark=parseFloat(items[i].dataset.mark).toFixed(2);
                $("#added_ques_tbl_body").append("<tr class='tr-number'><td id='ques_text'>" + items[i].dataset.ques + "</td><td><input type=\"checkbox\" value=\"1\" name=\"is_mandatory_" + items[i].dataset.id + "\" id=\"is_mandatory\"  class='form-control input-sm'" +is_mandatory+"></td><td><input type='hidden' name='id[]' value='" + items[i].dataset.id + "' /><input name='mark_" + items[i].dataset.id + "' onkeyup='totalmarkcount()' class='mark-class' style='text-align:center;' value='" + items[i].dataset.mark + "' /></td><td><button type='button' name='question_list[]'  id='remove_ques' onclick='removeQuestionAsSelected(this)'  class='form-control btn btn-success input-sm' >Remove</button></td></tr>");
            }
            else {
                if(j<=0) {
                    alert('Adding same question is not allowed.');
                }
                j++;
            }
        }
       
    }




    $(document).ready(function(){
        //$("button[name=mappingButton]").text();

        $("#mappingButton").css("pointer-events","none");

        $("input[name=set_limit]").keyup(function(){
            
        });

        $("input[name=set_mark]").keyup(function(){
            
        });
    });


    function removeQuestionAsSelected(elem) {
        $(elem).closest("tr").remove();
        
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

    }

 

</script>



