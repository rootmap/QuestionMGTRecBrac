<h1 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Preview'; ?> Exam</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>



<div class="control-group ">


    <div class="controls">
        <div class="">

            <?php foreach ($question_set as $key => $value) { ?>
                <a href="<?=base_url('administrator/exam/questionsetpreview/'.$exam_id.'/'.$value->id)?>" class="span3 btn-primary" style="height:100px;line-height:100px;text-align: center; border-radius: 10px;">
                    
                    <?php echo $value->name; ?>

                </a>

            <?php } ?>



        </div>
    </div>
</div>




<script type="text/javascript">
    function SelectAllSelectOptions() {
        $('#target option').prop('selected',true);
    }
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





