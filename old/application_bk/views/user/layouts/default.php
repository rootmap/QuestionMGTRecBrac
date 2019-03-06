<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<?php $this->load->view('user/partials/header'); ?>


<div class="content-wrap">
<div class="main-content">


    <?php if (isset($view_page)) { $this->load->view($view_page); } ?>


</div><!-- main-content ends -->
</div>

<?php //$this->load->view('user/partials/sidebar'); ?>
<?php $this->load->view('user/partials/footer'); ?>