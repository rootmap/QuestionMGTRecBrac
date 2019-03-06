

<h3 class="heading"><?php if($is_edit) echo 'Edit'; else echo ''; ?> Password Change Mail Layout</h3>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>

<?php echo form_open('administrator/'.$view_controller.'/update_password_mail_layout', array('class' => 'form-horizontal')); ?>

<fieldset>





    <div class="control-group formSep">
        <label class="control-label" for="layout">Layout Available Prefix</label>
        <div class="controls">
            <pre><?=layoutPrefixUser()?></pre>
        </div>
    </div>

    <div class="control-group formSep">
        <label class="control-label" for="layout">Layout</label>
        <div class="controls" id="body-control">
            <textarea name="cat_layout" id="cat_layout" rows="5" cols="30"><?php
                if(!isset($this->form_data->password_mail_layout))
                {
                    $this->form_data->password_mail_layout='';
                }
                echo set_value('cat_layout', $this->form_data->password_mail_layout); ?></textarea>
            <div id="charNum">Character Count =  <span style="color: #f00;">0</span></div>
            <!-- <div id="charNums"><span style='color: #f00;'>Max Character Allowed 160 Limit</span></div> -->
            <!--<span class="help-inline">Inline help text</span>-->
        </div>
    </div>



    <div class="form-actions">
        <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

        <input type="submit" value="Update Mail Layout" class="btn btn-primary btn-large" />&nbsp;&nbsp;
        <input type="reset" value="Reset" class="btn btn-large" />
    </div>

</fieldset>

<?php echo form_close(); ?>
<link rel="stylesheet" href="<?=base_url('assets/backend/lib/CLEditor/jquery.cleditor.css')?>" />
<script src="<?=base_url('assets/backend/lib/CLEditor/jquery.cleditor.min.js')?>"></script>
<script>
    $(document).ready(function(){







        $("#cat_layout").cleditor({ width: 800,height: 250});
        $(".cleditorMain").css("border","1px #ccc solid");




        //$("textarea").val(jQuery.parseJSON(<?php //echo json_encode($this->form_data->cat_layout); ?>));

        $.get("<?=base_url('administrator/smsnemail/$mail_layout/'.$this->form_data->cat_id) ?>",function(data){
            $("textarea").val(jQuery.parseJSON(data));
            <?php if($this->form_data->cat_layout_type!=2){ ?>
            var myString=$("textarea").val();
            var totalcountcharacter=myString.length;
            $('#charNum').html(" Character Count = <span style='color: #f00;'>"+totalcountcharacter+"</span>");
            <?php } ?>

        });






        /*$("#cat_layout").cleditor({
            width: 800, // width not including margins, borders or padding
            height: 250, // height not including margins, borders or padding
            controls: // controls to add to the toolbar
                "bold italic underline strikethrough subscript superscript | font size " +
                "style | color highlight removeformat | bullets numbering | outdent " +
                "indent | alignleft center alignright justify | undo redo | " +
                "rule image link unlink | cut copy paste pastetext | print source",
            colors: // colors in the color popup
                "FFF FCC FC9 FF9 FFC 9F9 9FF CFF CCF FCF " +
                "CCC F66 F96 FF6 FF3 6F9 3FF 6FF 99F F9F " +
                "BBB F00 F90 FC6 FF0 3F3 6CC 3CF 66C C6C " +
                "999 C00 F60 FC3 FC0 3C0 0CC 36F 63F C3C " +
                "666 900 C60 C93 990 090 399 33F 60C 939 " +
                "333 600 930 963 660 060 366 009 339 636 " +
                "000 300 630 633 330 030 033 006 309 303",
            fonts: // font names in the font popup
                "Arial,Arial Black,Comic Sans MS,Courier New,Narrow,Garamond," +
                "Georgia,Impact,Sans Serif,Serif,Tahoma,Trebuchet MS,Verdana",
            sizes: // sizes in the font size popup
                "1,2,3,4,5,6,7",
            styles: // styles in the style popup
                [["Paragraph", "<p>"], ["Header 1", "<h1>"], ["Header 2", "<h2>"],
                ["Header 3", "<h3>"],  ["Header 4","<h4>"],  ["Header 5","<h5>"],
                ["Header 6","<h6>"]],

            bodyStyle: // style to assign to document body contained within the editor
                "margin:4px; font:10pt Arial,Verdana; cursor:text"
        });$(".cleditorMain").css("border","1px #ccc solid");*/

    });
</script>

