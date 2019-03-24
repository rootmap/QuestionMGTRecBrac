

<!-- <h1 class="heading"><?php //if($is_edit) echo 'Edit'; else echo 'Add New'; ?> Category</h1> -->


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>

<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php /*if ( ! $is_edit) : ?>
<?php echo form_open('administrator/category/add_category', array('class' => 'form-horizontal')); ?>
<?php else : ?>
<?php echo form_open('administrator/category/update_category', array('class' => 'form-horizontal')); ?>
<?php endif;*/
 ?>

      <div class="container">
        <ul class="nav nav-tabs">
            <li<?php if(!empty($this->session->userdata('tab_cat'))){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#home">Category </a></li>
            <li<?php if(!empty($this->session->userdata('tab_subcat'))){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#menu1">Sub Category</a></li>
            <li<?php if(!empty($this->session->userdata('tab_subcat_two'))){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#menu2">Sub 2 Category</a></li>
            <li<?php if(!empty($this->session->userdata('tab_subcat_three'))){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#menu3">Sub 3 Category</a></li>
            <li<?php if(!empty($this->session->userdata('tab_subcat_four'))){ ?> class="active"<?php } ?>><a data-toggle="tab" href="#menu4">Sub 4 Category</a></li>
          </ul>

          <div class="tab-content">
            <div id="home" class="tab-pane fade <?php if(!empty($this->session->userdata('tab_cat'))){ ?> in active<?php } ?>">
                <form method="post" action="<?php if(isset($is_edit)){ echo site_url('administrator/category/update_category'); }else{ echo site_url('administrator/category/add_category'); } ?>">
                    <fieldset>

                        <div class="control-group formSep">
                            <label class="control-label" for="cat_name">Category Name</label>
                            <div class="controls">
                                <input type="text" name="cat_name" id="cat_name" value="<?php echo set_value('cat_name', $this->form_data->cat_name); ?>" class="input-xlarge" />
                                <!--<span class="help-inline">Inline help text</span>-->
                            </div>
                        </div>

                        <!-- <div class="control-group">
                            <label class="control-label" for="cat_parent">Category</label>
                            <div class="controls">
                                <select id="cat_parent" name="cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>-->

                        <div class="form-actions">
                            <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

                            <input type="submit" value="<?php if(isset($is_edit)) echo 'Update'; else echo 'Save'; ?> Category" class="btn btn-primary" />&nbsp;&nbsp;
                            <input type="reset" value="Reset" class="btn" />
                        </div>

                    </fieldset>
                </form>
            </div>
            <div id="menu1" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat'))){ ?> in active<?php } ?>">
              <form method="post" action="<?=site_url('administrator/category/add_sub_category')?>">
                    <fieldset>

                        <div class="control-group formSep">
                            <label class="control-label" for="cat_name">Name</label>
                            <div class="controls">
                                <input type="text" name="cat_name" id="cat_name"  class="input-xlarge" />
                            </div>
                        </div>

                         <div class="control-group">
                            <label class="control-label" for="cat_parent">Select Category</label>
                            <div class="controls">
                                <select name="cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

                            <input type="submit" value="<?php if(isset($is_category_edit)) echo 'Update'; else echo 'Save'; ?> Category" class="btn btn-primary" />&nbsp;&nbsp;
                            <input type="reset" value="Reset" class="btn" />
                        </div>

                    </fieldset>
                </form>
            </div>
            <div id="menu2" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat_two'))){ ?> in active<?php } ?>">
              <form method="post" action="<?=site_url('administrator/category/add_sub_two_category')?>">
                    <fieldset>

                        <div class="control-group formSep">
                            <label class="control-label" for="cat_name">Name</label>
                            <div class="controls">
                                <input type="text" name="cat_name" id="cat_name"  class="input-xlarge" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="cat_parent">Select Category</label>
                            <div class="controls">
                                <select name="cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                         <div class="control-group">
                            <label class="control-label" for="sub_cat_parent">Select Sub Category</label>
                            <div class="controls">
                                <select name="sub_cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                        

                        <div class="form-actions">
                            <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

                            <input type="submit" value="<?php if(isset($is_category_edit)) echo 'Update'; else echo 'Save'; ?> Category" class="btn btn-primary" />&nbsp;&nbsp;
                            <input type="reset" value="Reset" class="btn" />
                        </div>

                    </fieldset>
                </form>
            </div>
            <div id="menu3" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat_three'))){ ?> in active<?php } ?>">
              <form method="post" action="<?=site_url('administrator/category/add_sub_three_category')?>">
                    <fieldset>

                        <div class="control-group formSep">
                            <label class="control-label" for="cat_name">Name</label>
                            <div class="controls">
                                <input type="text" name="cat_name" id="cat_name"  class="input-xlarge" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="cat_parent">Select Category</label>
                            <div class="controls">
                                <select name="cat_parent"  id="lt_cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                         <div class="control-group">
                            <label class="control-label" for="sub_cat_parent">Select Sub Category</label>
                            <div class="controls">
                                <select name="sub_cat_parent"  id="lt_sub_cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="sub_two_cat_parent">Select Sub 2 Category</label>
                            <div class="controls">
                                <select name="sub_two_cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                        

                        <div class="form-actions">
                            <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

                            <input type="submit" value="<?php if(isset($is_category_edit)) echo 'Update'; else echo 'Save'; ?> Category" class="btn btn-primary" />&nbsp;&nbsp;
                            <input type="reset" value="Reset" class="btn" />
                        </div>

                    </fieldset>
                </form>
            </div>
            <div id="menu4" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat_four'))){ ?> in active<?php } ?>">
              <form method="post" action="<?=site_url('administrator/category/add_sub_four_category')?>">
                    <fieldset>

                        <div class="control-group formSep">
                            <label class="control-label" for="cat_name">Name</label>
                            <div class="controls">
                                <input type="text" name="cat_name" id="cat_name"  class="input-xlarge" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="cat_parent">Select Category</label>
                            <div class="controls">
                                <select name="cat_parent"  id="lf_cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                         <div class="control-group">
                            <label class="control-label" for="sub_cat_parent">Select Sub Category</label>
                            <div class="controls">
                                <select name="sub_cat_parent"  id="lf_sub_cat_parent"  class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label"  for="sub_two_cat_parent">Select Sub 2 Category</label>
                            <div class="controls">
                                <select name="sub_two_cat_parent" id="lf_sub_two_cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label"  for="sub_three_cat_parent">Select Sub 3 Category</label>
                            <div class="controls">
                                <select name="sub_three_cat_parent" id="lf_sub_three_cat_parent" class="chosen-select input-xlarge">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                        </div>

                        

                        <div class="form-actions">
                            <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', $this->form_data->cat_id); ?>" />

                            <input type="submit" value="<?php if(isset($is_category_edit)) echo 'Update'; else echo 'Save'; ?> Category" class="btn btn-primary" />&nbsp;&nbsp;
                            <input type="reset" value="Reset" class="btn" />
                        </div>

                    </fieldset>
                </form>
            </div>
          </div>
        </div>

        

<?php //echo form_close(); ?>

<script type="text/javascript">

    function loadCategory()
    {
        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("Counter Display Status : "+data);

                var htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Category</option>';
                    $.each(data,function(key,row){
                        htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                    });
                }

                $("select[name=cat_parent]").html(htmlString).chosen();
                $('select[name=cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubCategory(cid)
    {
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                        }
                        
                    });
                }

                $("select[name=sub_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubTwoCategory(cid,SubCat)
    {
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_two_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 2 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                            }
                        }
                        
                    });
                }

                $("select[name=sub_two_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_two_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    function loadSubThreeCategory(cid,SubCat,SubTwoCat)
    {
        var htmlString='<option value="">Loading.. Please wait...</option>';
        $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
        $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

        //------------------------Ajax Customer Start-------------------------//
         var AddHowMowKhaoUrl="<?=site_url('administrator/category/load_sub_three_category')?>";
         $.ajax({
            'async': true,
            'type': "GET",
            'global': false,
            'cache' : false,
            'dataType': 'json',
            'url': AddHowMowKhaoUrl,
            'success': function (data) {
                console.log("subcat : "+data);

                htmlString='';
                if(data)
                {
                    htmlString +='<option value="">Please Select Sub 3 Category</option>';
                    $.each(data,function(key,row){
                        if(row.cat_parent==cid)
                        {
                            if(row.sub_cat_parent==SubCat)
                            {
                                if(row.sub_two_cat_parent==SubTwoCat)
                                {
                                    htmlString +='<option value="'+row.id+'">'+row.cat_name+'</option>';
                                }
                            }
                        }
                        
                    });
                }

                $("select[name=sub_three_cat_parent]").html(htmlString).chosen();
                $('select[name=sub_three_cat_parent]').trigger("liszt:updated");

            }
        });
        //------------------------Ajax Customer End---------------------------//
    }

    $(document).ready(function(){
        
        loadCategory();
        

        $("select[name=cat_parent]").change(function(){
            var cat_parent=$(this).val();
            if(cat_parent.length>0)
            {
                loadSubCategory(cat_parent);
            }
        });

        $("#lt_sub_cat_parent").change(function(){
            var cat_parent=$("#lt_cat_parent").val();

            //console.log(cat_parent);
            //return false;
            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$(this).val();
            if(sub_cat_parent.length>0)
            {
                loadSubTwoCategory(cat_parent,sub_cat_parent);
            }
            
        });

        $("#lf_sub_cat_parent").change(function(){
            var cat_parent=$("#lf_cat_parent").val();

            //console.log(cat_parent);
            //return false;
            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$(this).val();
            if(sub_cat_parent.length>0)
            {
                loadSubTwoCategory(cat_parent,sub_cat_parent);
            }
            
        });
        
        $("#lf_sub_two_cat_parent").change(function(){
            var cat_parent=$("#lf_cat_parent").val();

            if(cat_parent.length==0)
            {
                alert("Please select a category.");
                return false;
            }

            var sub_cat_parent=$("#lf_sub_cat_parent").val();

            if(sub_cat_parent.length==0)
            {
                alert("Please select a Sub Category.");
                return false;
            }

            var sub_two_cat_parent=$(this).val();
            if(sub_two_cat_parent.length>0)
            {
                loadSubThreeCategory(cat_parent,sub_cat_parent,sub_two_cat_parent);
            }
            
        });
       
        
    });

   

</script>
