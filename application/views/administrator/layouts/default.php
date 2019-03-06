<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<?php $this->load->view('administrator/partials/header'); ?>


<!-- main content -->
<div id="contentwrapper">
    <div class="main_content">

        <?php if (isset($view_page)) { $this->load->view($view_page); } ?>

    </div>
</div><!--contentwrapper ends-->


<?php $this->load->view('administrator/partials/sidebar'); ?>
<?php $this->load->view('administrator/partials/footer'); ?>