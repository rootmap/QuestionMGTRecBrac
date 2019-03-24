
<h1 class="heading">Manage Categories</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="row-fluid">




    <div class="container">
        <ul class="nav nav-tabs">
            <li  data-id="1"  class="activateTab<?php if(!empty($this->session->userdata('tab_cat'))){ ?> active<?php } ?>"><a data-toggle="tab" class="activateTab" data-id="1" href="#home">Category </a></li>
            <li  data-id="1"  class="activateTab<?php if(!empty($this->session->userdata('tab_subcat'))){ ?> active<?php } ?>"><a data-toggle="tab" class="activateTab" data-id="2"  href="#menu1">Sub Category</a></li>
            <li  data-id="3"  class="activateTab<?php if(!empty($this->session->userdata('tab_subcat_two'))){ ?> active<?php } ?>"><a data-toggle="tab" class="activateTab" data-id="3"  href="#menu2">Sub 2 Category</a></li>
            <li  data-id="4"  class="activateTab<?php if(!empty($this->session->userdata('tab_subcat_three'))){ ?> active<?php } ?>"><a data-toggle="tab" class="activateTab" data-id="4"  href="#menu3">Sub 3 Category</a></li>
            <li  data-id="5"  class="activateTab<?php if(!empty($this->session->userdata('tab_subcat_four'))){ ?> active<?php } ?>"><a data-toggle="tab" class="activateTab" data-id="5"  href="#menu4">Sub 4 Category</a></li>
          </ul>

          <div class="tab-content">
            <div id="home" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_cat'))){ ?> in active<?php } ?>">

                            <div class="span12">
                                <div class="row control-row control-row-top">
                                    <div class="span6 left">
                                        <?php echo form_open('administrator/category/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
                                        <div class="input-append">
                                          <input class="span6" name="search" value="<?php echo set_value('filter_search', $this->form_data->filter_search); ?>" placeholder="Enter Your Search" id="appendedInputButtons" type="text">
                                          <button class="btn btn-primary"  value="Filter"  type="submit">Search</button>
                                          <button class="btn" name="clear" value="Clear" type="submit">Clear</button>
                                        </div>

                                        <?php echo form_close(); ?>
                                    </div>

                                    <div class="span1 left">

                                    </div>

                                    <div class="span2 left" >
                                        <a class="btn btn-success" href="candidate/download_candidate"> Export Candidates </a>
                                    </div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                                <?php echo $records_table; ?>

                                <div class="row control-row control-row-bottom">
                                    <div class="span6 left">&nbsp;</div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                            </div>
            </div>
            <div id="menu1" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat'))){ ?> in active<?php } ?>">
              
                    <fieldset>

                        <div class="span12">
                                <div class="row control-row control-row-top">
                                    <div class="span6 left">
                                        <?php echo form_open('administrator/category/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
                                        <div class="input-append">
                                          <input class="span6" name="search" value="<?php echo set_value('filter_search', $this->form_data->filter_search); ?>" placeholder="Enter Your Search" id="appendedInputButtons" type="text">
                                          <button class="btn btn-primary"  value="Filter"  type="submit">Search</button>
                                          <button class="btn" name="clear" value="Clear" type="submit">Clear</button>
                                        </div>

                                        <?php echo form_close(); ?>
                                    </div>

                                    <div class="span1 left">

                                    </div>

                                    <div class="span2 left" >
                                        <a class="btn btn-success" href="candidate/download_candidate"> Export Candidates </a>
                                    </div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                                <?php echo $records_table; ?>

                                <div class="row control-row control-row-bottom">
                                    <div class="span6 left">&nbsp;</div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                            </div>

                    </fieldset>
            </div>
            <div id="menu2" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat_two'))){ ?> in active<?php } ?>">
              <fieldset>

                        <div class="span12">
                                <div class="row control-row control-row-top">
                                    <div class="span6 left">
                                        <?php echo form_open('administrator/category/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
                                        <div class="input-append">
                                          <input class="span6" name="search" value="<?php echo set_value('filter_search', $this->form_data->filter_search); ?>" placeholder="Enter Your Search" id="appendedInputButtons" type="text">
                                          <button class="btn btn-primary"  value="Filter"  type="submit">Search</button>
                                          <button class="btn" name="clear" value="Clear" type="submit">Clear</button>
                                        </div>

                                        <?php echo form_close(); ?>
                                    </div>

                                    <div class="span1 left">

                                    </div>

                                    <div class="span2 left" >
                                        <a class="btn btn-success" href="candidate/download_candidate"> Export Candidates </a>
                                    </div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                                <?php echo $records_table; ?>

                                <div class="row control-row control-row-bottom">
                                    <div class="span6 left">&nbsp;</div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                            </div>

                    </fieldset>
            </div>
            <div id="menu3" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat_three'))){ ?> in active<?php } ?>">
                 <fieldset>

                        <div class="span12">
                                <div class="row control-row control-row-top">
                                    <div class="span6 left">
                                        <?php echo form_open('administrator/category/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
                                        <div class="input-append">
                                          <input class="span6" name="search" value="<?php echo set_value('filter_search', $this->form_data->filter_search); ?>" placeholder="Enter Your Search" id="appendedInputButtons" type="text">
                                          <button class="btn btn-primary"  value="Filter"  type="submit">Search</button>
                                          <button class="btn" name="clear" value="Clear" type="submit">Clear</button>
                                        </div>

                                        <?php echo form_close(); ?>
                                    </div>

                                    <div class="span1 left">

                                    </div>

                                    <div class="span2 left" >
                                        <a class="btn btn-success" href="candidate/download_candidate"> Export Candidates </a>
                                    </div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                                <?php echo $records_table; ?>

                                <div class="row control-row control-row-bottom">
                                    <div class="span6 left">&nbsp;</div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                            </div>

                    </fieldset>
            </div>
            <div id="menu4" class="tab-pane fade<?php if(!empty($this->session->userdata('tab_subcat_four'))){ ?> in active<?php } ?>">
              <fieldset>

                        <div class="span12">
                                <div class="row control-row control-row-top">
                                    <div class="span6 left">
                                        <?php echo form_open('administrator/category/filter', array('class' => 'form-inline', 'id' => 'filter-form')); ?>
                                        <div class="input-append">
                                          <input class="span6" name="search" value="<?php echo set_value('filter_search', $this->form_data->filter_search); ?>" placeholder="Enter Your Search" id="appendedInputButtons" type="text">
                                          <button class="btn btn-primary"  value="Filter"  type="submit">Search</button>
                                          <button class="btn" name="clear" value="Clear" type="submit">Clear</button>
                                        </div>

                                        <?php echo form_close(); ?>
                                    </div>

                                    <div class="span1 left">

                                    </div>

                                    <div class="span2 left" >
                                        <a class="btn btn-success" href="candidate/download_candidate"> Export Candidates </a>
                                    </div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                                <?php echo $records_table; ?>

                                <div class="row control-row control-row-bottom">
                                    <div class="span6 left">&nbsp;</div>
                                    <div class="span6 right">

                                        <?php echo $pagin_links; ?>

                                    </div>
                                </div>

                            </div>

                    </fieldset>
            </div>
          </div>
        </div>







</div>

<script type="text/javascript">
    $(document).ready(function(){
        $(".activateTab").click(function(){
            var interfaceID=$(this).attr("data-id");

            if(interfaceID.length>0)
            {

                //------------------------Ajax Customer Start-------------------------//
                 var AddHowMowKhaoUrl="<?=site_url('administrator/category/tabSessionSet')?>";
                 $.ajax({
                    'async': true,
                    'type': "POST",
                    'global': false,
                    'cache' : false,
                    'dataType': 'json',
                    'data':{'interfaceID':interfaceID},
                    'url': AddHowMowKhaoUrl,
                    'success': function (data) {
                        //console.log("Counter Display Status : "+data);

                        window.location.reload(true);

                    }
                });
                //------------------------Ajax Customer End---------------------------//

            }
        });
    });
</script>

