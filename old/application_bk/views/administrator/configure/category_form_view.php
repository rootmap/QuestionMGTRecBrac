

<h3 class="heading"><?php if($is_edit) echo 'Edit'; else echo 'Add New'; ?> SMS &amp; Email Category</h3>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php if ( ! $is_edit) : ?>
<?php echo form_open('administrator/'.$view_controller.'/add_category', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/'.$view_controller.'/update_category', array('class' => 'form-horizontal')); ?>
<?php endif; ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="cat_name">Layout Name</label>
            <div class="controls">
                <input type="text" name="cat_name" id="cat_name"  value="<?php echo set_value('cat_name', $this->form_data->cat_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="cat_layout_type">Layout Type</label>
            <?php 
            if(!isset($this->form_data->cat_layout_type))
            {
                $this->form_data->cat_layout_type=1;
            }
            set_value('cat_layout_type', $this->form_data->cat_layout_type);
             ?>
            <div class="controls">
                <lavel><input type="radio"
                 <?php 
                 if($this->form_data->cat_layout_type==1)
                 {
                    ?>
                    checked="checked" 
                    <?php
                 }
                 ?>
                 name="cat_layout_type"  id="layout_for_0" value="1" class="input-xlarge" /> <span style="line-height: 25px !important; font-size: 15px !important;">SMS</span> </lavel>
                <lavel><input type="radio" 
                    <?php 
                     if($this->form_data->cat_layout_type==2)
                     {
                        ?>
                        checked="checked" 
                        <?php
                     }
                     ?>
                 name="cat_layout_type" id="layout_for_1" value="2" class="input-xlarge" /> <span style="line-height: 25px !important; font-size: 15px !important;">Email</span> </lavel>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="layout">Layout Available Prefix</label>
            <div class="controls">
                <pre><?=layoutPrefix()?></pre>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="layout">Layout</label>
            <div class="controls" id="body-control">
                <textarea name="cat_layout" id="cat_layout" rows="5" cols="30"><?php 
                if(!isset($this->form_data->cat_layout))
                {
                    $this->form_data->cat_layout='';
                }
                echo set_value('cat_layout', $this->form_data->cat_layout); ?></textarea>
                <div id="charNum">Character Count =  <span style="color: #f00;">0</span></div>
                <!-- <div id="charNums"><span style='color: #f00;'>Max Character Allowed 480 Limit</span></div> -->
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        

        <div class="form-actions">
            <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

            <input type="submit" value="<?php if($is_edit) echo 'Update'; else echo 'Add'; ?> Category Layout" class="btn btn-primary btn-large" />&nbsp;&nbsp;
            <input type="reset" value="Reset" class="btn btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>
<link rel="stylesheet" href="<?=base_url('assets/backend/lib/CLEditor/jquery.cleditor.css')?>" />
    <script src="<?=base_url('assets/backend/lib/CLEditor/jquery.cleditor.min.js')?>"></script>
<script>
    $(document).ready(function(){

        <?php 
        if($this->form_data->cat_layout_type==2)
        { 
            ?>
            $("#cat_layout").cleditor({ width: 800,height: 250});
            $(".cleditorMain").css("border","1px #ccc solid");
            <?php
        }

        if(empty($this->form_data->cat_layout_type))
        { ?>
            $("#layout_for_0").click();
            if(document.getElementById('layout_for_0').checked==true){

                $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea><div id="charNum">Character Count =  <span style="color: #f00;">0/480</span></div>');

                $('textarea').keyup(function(e){
                    var max = 480;
                    if (e.which < 0x20) {
                        return;     // Do nothing
                    }
                    if (this.value.length == max) {
                        e.preventDefault();
                    } else if (this.value.length > max) {
                        // Maximum exceeded
                        this.value = this.value.substring(0, max);

                    }
                    var totalData=$(this).val();
                    var totalcountcharacter=totalData.length;
                    $('#charNum').html(" Character Count = <span style='color: #f00;'>"+totalcountcharacter+"</span>");
                });
            }
            else if(document.getElementById('layout_for_1').checked==true){
                $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea>');
                $("#cat_layout").cleditor({ width: 800,height: 250});
                $(".cleditorMain").css("border","1px #ccc solid");
            }
            <?php
        }
        elseif($this->form_data->cat_layout_type==1)
        { ?>
            $("#layout_for_0").click();

                if(document.getElementById('layout_for_0').checked==true){

                    $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea><div id="charNum">Character Count =  <span style="color: #f00;">0/480</span></div>');

                    $('textarea').keyup(function(e){
                        var max = 480;
                        if (e.which < 0x20) {
                            return;     // Do nothing
                        }
                        if (this.value.length == max) {
                            e.preventDefault();
                        } else if (this.value.length > max) {
                            // Maximum exceeded
                            this.value = this.value.substring(0, max);

                        }
                        var totalData=$(this).val();
                        var totalcountcharacter=totalData.length;
                        $('#charNum').html(" Character Count = <span style='color: #f00;'>"+totalcountcharacter+"</span>");
                    });
                }
                else if(document.getElementById('layout_for_1').checked==true){
                    $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea>');
                    $("#cat_layout").cleditor({ width: 800,height: 250});
                    $(".cleditorMain").css("border","1px #ccc solid");
                }
            
            <?php
        }
        ?>

        $("#layout_for_0").click(function(){

            if(document.getElementById('layout_for_0').checked==true){

                $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea><div id="charNum">Character Count =  <span style="color: #f00;">0/480</span></div>');

                $('textarea').keyup(function(e){
                    var max = 480;
                    if (e.which < 0x20) {
                        return;     // Do nothing
                    }
                    if (this.value.length == max) {
                        e.preventDefault();
                    } else if (this.value.length > max) {
                        // Maximum exceeded
                        this.value = this.value.substring(0, max);

                    }
                    var totalData=$(this).val();
                    var totalcountcharacter=totalData.length;
                    $('#charNum').html(" Character Count = <span style='color: #f00;'>"+totalcountcharacter+"</span>");
                });
            }
            else if(document.getElementById('layout_for_1').checked==true){
                $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea>');
                $("#cat_layout").cleditor({ width: 800,height: 250});
                $(".cleditorMain").css("border","1px #ccc solid");
            }
            
        });

        $("#layout_for_1").click(function(){

            if(document.getElementById('layout_for_0').checked==true){
                $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea><div id="charNum">Character Count =  <span style="color: #f00;">0/480</span></div>');
                $('textarea').keyup(function(e){
                    var max = 480;
                    if (e.which < 0x20) {
                        return;     // Do nothing
                    }
                    if (this.value.length == max) {
                        e.preventDefault();
                    } else if (this.value.length > max) {
                        // Maximum exceeded
                        this.value = this.value.substring(0, max);

                    }
                    var totalData=$(this).val();
                    var totalcountcharacter=totalData.length;
                    $('#charNum').html(" Character Count = <span style='color: #f00;'>"+totalcountcharacter+"</span>");
                });
            }
            else if(document.getElementById('layout_for_1').checked==true){
                $("#body-control").html('<textarea name="cat_layout" id="cat_layout" rows="5" cols="30"></textarea>');
                $("#cat_layout").cleditor({ width: 800,height: 250});
                $(".cleditorMain").css("border","1px #ccc solid");
            }
            
        });

        //$("textarea").val(jQuery.parseJSON(<?php //echo json_encode($this->form_data->cat_layout); ?>));

        $.get("<?=base_url('administrator/smsnemail/getencapsulatedData/'.$this->form_data->cat_id) ?>",function(data){
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

