<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    
    <title><?php if (isset($title)) { echo $title; } else { echo $this->site_name; } ?></title>

    <!--style framework-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/styles/jquery-ui-1.8.22.custom.css" />

    <!--custom styles-->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/fonts/fontface.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/styles/common.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/frontend/styles/style.css" />

    <!--scripts-->
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/jquery-1.7.2.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/jquery-ui-1.8.22.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/bootstrap/js/bootstrap.min.js"></script>

    <script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/jquery.cookie.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/validation/jquery.validate.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/jquery.actual.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/jquery.shorten.1.0.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/jwerty.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/bxslider/jquery.bxslider.js"></script>

    <script type="text/javascript" src="<?php echo base_url(); ?>assets/frontend/scripts/frontend.js"></script>
    <script type="text/javascript">
        var baseUrlJs = '<?php echo base_url(); ?>';
    </script>

    <!--favicon-->
    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/backend/brack_favicon.ico" />


    <script type="text/javascript">
    
        var timer = 0;
function set_interval() {
  // the interval 'timer' is set as soon as the page loads
  timer = setInterval("auto_logout()", 10000);
  // the figure '10000' above indicates how many milliseconds the timer be set to.
  // Eg: to set it to 5 mins, calculate 5min = 5x60 = 300 sec = 300,000 millisec.
  // So set it to 300000
 
}

function reset_interval() {
  //resets the timer. The timer is reset on each of the below events:
  // 1. mousemove   2. mouseclick   3. key press 4. scroliing
  //first step: clear the existing timer

  
  if (timer != 0) {
    clearInterval(timer);
    timer = 0;
    // second step: implement the timer again
    timer = setInterval("auto_logout()", 1800000);
    // completed the reset of the timer
    //1800000
  }
}

function auto_logout() {
  // this function will redirect the user to the logout script

  //var abc =  "<?php if($this->session->userdata('logged_in_user')) echo 1 ; else echo 2 ; ?>";
  //console.log('hello '+abc);

  <?php if($this->session->userdata('logged_in_user')) { ?>
    //console.log('calling log out');
  alert('You are inactive for 30 minutes. Hence you are logged out from the system.');
  window.location = "<?php echo base_url(); ?>/logout"; 
   <?php } ?>
  
}
    </script>
    
    
<!--ALL NEW-->
    <!-- main styles -->
    <!--<link rel="stylesheet" href="<?php //echo base_url(); ?>assets/backend/styles/style.css" />-->
    <!-- scrollbar -->
    <!--<script type="text/javascript" src="<?php //echo base_url(); ?>assets/backend/lib/antiscroll/antiscroll.js"></script>-->
    <!--<script type="text/javascript" src="<?php //echo base_url(); ?>assets/backend/lib/antiscroll/jquery-mousewheel.js"></script>-->
    <!-- common functions -->
    <!--<script type="text/javascript" src="<?php //echo base_url(); ?>assets/backend/scripts/gebo_common.js"></script>-->
<!--END OF ALL NEW-->
    
    


</head>
<body>

<div id="wrap">

<div class="header-wrap">
    <div class="inner">
     <?php if ($this->current_page != 'login') : ?>
    <h1 class="logo">
        <a href="<?php echo base_url(); ?>">
            <img src="<?php echo base_url(); ?>assets/images/bracbank.jpg" alt="BRAC Bank" />
        </a>
        <span class="logotext">Brac Bank QMS</span>
    </h1>
    <?PHP endif; ?>
    <?php if ($this->current_page != 'login') : ?>
    <div class="controls">


        <div class="btn-group pull-right">
            <button class="btn">Hello, <?php echo $this->user_model->get_user_name(); ?></button>
            <button class="btn dropdown-toggle" data-toggle="dropdown">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="<?php echo base_url('profile'); ?>">View Profile</a></li>
                 <!-- <li><a href="<?php //echo base_url('profile/profile_picture'); ?>">Change Profile Picture</a></li> -->
                <li><a href="<?php echo base_url('profile/password'); ?>">Change Password</a></li>
                <li class="divider"></li>
                <li><a href="<?php echo base_url('logout'); ?>">Logout</a></li>
            </ul>
        </div>

        <div class=" pull-right" style="margin-right:20px;">
            <?php 
            $filePath=base_url().'/uploads/user/'.$this->user_model->get_user_profile_path();
            $fileExists=file_exists('uploads/user/'.$this->user_model->get_user_profile_path());
            if(!$fileExists)
            {
                $filePath=base_url().'/assets/images/avatar.png';
            }
            ?>
            <img src ="<?=$filePath?>" width="30" height="30">
        </div>

    </div>
    <?php endif; ?>

</div></div><!--header-wrap ends-->

