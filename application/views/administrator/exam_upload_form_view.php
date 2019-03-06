

<h1 class="heading">Upload Result</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open_multipart('administrator/result/bulk_upload', array('class' => 'form-horizontal')); ?>
<?php /*
<div class="alert">
    Upload an Excel file to import exam results into the system. <br />
    Uploaded excel file's columns should match with defined format.
    <!--  -->
    <a href="<?php echo base_url('download'); ?>/bulk-result_upload_example.xlsx" target="_blank" class="genarateFormat">Click here</a>
    to download the predefined formatted excel file.
</div> */ ?>

<fieldset>

    <div class="control-group">
        <label class="col-sm-4 control-label" for="mo_exam_id">Exam</label>
        <div class="controls">
            <select class="input-xlarge" id="mo_exam_all" name="mo_exam_all">
                <option value="">Select an exam</option>
                <?php foreach ($exam_all as $key): ?>
                    <option value="<?php echo $key->id; ?>"><?php echo $key->exam_title; ?></option>
                <?php endforeach ?>
            </select>
        </div>
    </div>

    <div class="control-group formSep">
        <label class="control-label" for="source">Question Set</label>
        <div class="controls">
            <div class="">
                <div class="span2">
                    <div class="row">
                        <select name="exam_question_Set" id="source" class="form-control">

                        </select>
                    </div>
                </div>
                <!-- <div class="span2" style="margin-right: 10px;">
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
                        <select required name="exam_question_Set[]" id="target" class="form-control target" size="15" multiple="multiple">
                            <?php //foreach ($selected_cat as $keys => $values) { ?>
                                <option value="<?php //echo $values['set_id']; ?>"><?php //echo $values['name']; ?></option>
                            <?php //} ?>
                        </select>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="result_file">Sample Format</label>
        <div class="controls">
            <a href="#" target="_blank" id="downloadSample" class="btn btn-info">Download Format</a>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="result_file">Upload File</label>
        <div class="controls">
            <input type="file" name="result_file" id="result_file" />
            <!--<span class="help-inline">Login Id is unique for each candidate.</span>-->
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="result_file_has_column_header">&nbsp;</label>
        <div class="controls">
            <label class="checkbox">
                <input type="checkbox" value="1" name="result_file_has_column_header" id="result_file_has_column_header" /> First row has column name
            </label>
        </div>
    </div>

    <div class="form-actions">
        <input type="submit" value="Upload and Bulk Add" class="btn btn-primary btn-large" />&nbsp;&nbsp;
    </div>

</fieldset>

<?php echo form_close(); ?>



<script type="text/javascript">
    function SelectAllSelectOptions() {
        $('#target option').prop('selected',true);
    }
    jQuery(document).ready(function(){ 

        $("#mo_exam_all").change(function(){ 
            var setID=$("#source").val();
            var examID=$(this).val();
            var compositeURL="#";
            if(setID>0 && examID>0)
            {
                compositeURL="<?php echo base_url('administrator/result/downloadformat'); ?>/"+examID+"/"+setID;
            }

            $("#downloadSample").attr("href",compositeURL);
        });

        $("#source").change(function(){
            var examID=$("#mo_exam_all").val();
            var setID=$(this).val();
            var compositeURL="#";
            if(setID>0 && examID>0)
            {
                compositeURL="<?php echo base_url('administrator/result/downloadformat'); ?>/"+examID+"/"+setID;
            }
            $("#downloadSample").attr("href",compositeURL);
        });

        /*$('.genarateFormat').click(function(){
            var examID=$('#mo_exam_all').val();
            if(!examID.length)
            {
                alert("Please Select a Exam.!!");
                return false;
            }

            var dSet=$('select#target').val();
            alert(dSet);
            if(!dSet.length)
            {
                alert("Please Select Question Set.!!");
                return false;
            }

            alert(dSet);

            alert('success');
        });
*/
        $('#mo_exam_all').on('change', function() {

            $("#source").empty().append("");






            $.ajax({
                method: "POST",
                dataType: 'json',
                url: link + "administrator/question_set/getQuestionSetByExam/"+this.value
            }).done(function (obj) {

                console.log(obj);

                var strHtml="<option value=''>Please select set</option>";
                for (var i = 0; i < obj.length; i++)
                {
                    strHtml +="<option value='"+obj[i].id+"'>"+obj[i].name+"</option>";
                }


                $("#source").append(strHtml);

            });

        })








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

