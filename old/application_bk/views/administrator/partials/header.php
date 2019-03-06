<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?><!DOCTYPE html>
<html lang="en">
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?php echo base_url(); ?>assets/backend/brack_favicon.ico" />
    
    <title><?php if (isset($title)) { echo $title; } else { echo $this->site_name. ' Admin Panel'; } ?></title>

    <!-- Bootstrap framework -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bootstrap/css/bootstrap.min.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/bootstrap/css/bootstrap-responsive.min.css" />
    <!-- jQuery UI theme-->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/backend/lib/jquery-ui/css/Aristo/Aristo.css" />
    <!-- gebo blue theme-->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/backend/styles/red.css" id="link_theme" />
    <!-- breadcrumbs-->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/backend/lib/jBreadcrumbs/css/BreadCrumb.css" />
    <!-- colorbox -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/backend/lib/colorbox/colorbox.css" />
    <!-- date picker -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/libraries/datepicker/datepicker.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/libraries/datepicker/timepicker.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/libraries/daterangepicker/daterangepicker.css" />

    <!-- chosen -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/libraries/chosen/chosen.css" />

        <!--<link rel="stylesheet" href="<?php /*echo base_url(); */?>assets/libraries/bxslider/jquery.bxslider.css" />-->

    <!--[if lte IE 8]>
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/backend/styles/ie.css" />
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/ie/html5.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/ie/respond.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/lib/excanvas.min.js"></script>
    <![endif]-->

    <!-- main styles -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/styles/common.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/backend/styles/style.css" />
        <!--<link rel="stylesheet" href="<?php /*echo base_url(); */?>assets/fonts/fontface.css" />-->






<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/date.js"></script>
<!-- smart resize event -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/jquery.debouncedresize.min.js"></script>
<!-- hidden elements width/height -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/jquery.actual.min.js"></script>
<!-- js cookie plugin -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.cookie.min.js"></script>
<!-- main bootstrap js -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/bootstrap/js/bootstrap.min.js"></script>
<!-- jBreadcrumbs -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/lib/jBreadcrumbs/js/jquery.jBreadCrumb.1.1.min.js"></script>
<!-- lightbox -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/lib/colorbox/jquery.colorbox.min.js"></script>
<!-- fix for ios orientation change -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/ios-orientationchange-fix.js"></script>
<!-- scrollbar -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/lib/antiscroll/antiscroll.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/lib/antiscroll/jquery-mousewheel.js"></script>
<!-- common functions -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/gebo_common.js"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/lib/jquery-ui/jquery-ui-1.8.20.custom.min.js"></script>
<!-- touch events for jquery ui-->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/forms/jquery.ui.touch-punch.min.js"></script>
<!-- multi-column layout -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/jquery.imagesloaded.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/jquery.wookmark.js"></script>
<!-- responsive table -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/jquery.mediaTable.min.js"></script>
<!-- small charts -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/jquery.peity.min.js"></script>
<!-- dashboard functions -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/gebo_tables.js"></script>
<!-- date picker -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/datepicker/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/datepicker/bootstrap-timepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/daterangepicker/daterangepicker.js"></script>

<!-- date picker -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/chosen/chosen.jquery.min.js"></script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/scripts/jquery.cycle.all.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/libraries/bxslider/jquery.bxslider.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/backend/scripts/backend.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/frontend/scripts/frontend.js"></script>


<script type="text/javascript">
    document.documentElement.className += 'js';     //* hide all elements & show preloader
    var siteUrlJs = '<?php echo site_url(); ?>';
    jQuery(document).ready(function() {
        setTimeout('$("html").removeClass("js")', 300);    //* show all elements & remove preloader
    });
    var link = '<?php echo base_url(); ?>';
</script>

<style type="text/css">
    .upload_img{
        max-width:180px;
    }
    .upload_input[type=file]{
    padding:10px;
    background:#b3b3b3;
    }
</style>

</head>


<body>

    
    <div id="loading_layer" style="display:none">
        <img src="<?php echo base_url(); ?>assets/backend/images/ajax_loader.gif" alt="loading..." />
    </div>


    <div id="maincontainer" class="clearfix">

        
        <!-- header -->
        <header>
            <div class="navbar navbar-fixed-top">
                <div class="navbar-inner">
                    <div class="container-fluid">

                        <a class="brand" href="<?php echo base_url(); ?>"">
                            <!-- <i class="icon-home icon-white"></i>  -->
                            <img src="<?=base_url()?>/assets/images/favicon.ico">
                            <?php echo $this->site_name_raw; ?>
                        </a>
						
						<?php if($this->session->userdata('logged_in_user')->user_type == 'User'): ?>
							<a href="<?php echo base_url('home'); ?>" class="site-link" target="_blank">Visit Site</a>
						<?php endif; ?>	
						<style type="text/css">
                        .navbar .nav li.dropdown>.dropdown-toggle .caret
                        {
                            border-top-color: #fff;
                            border-bottom-color: #fff;
                        }                  
                        </style>
                        <ul class="nav user_menu pull-right">
                            <li class="dropdown">
                                <a href="#" style="border-left: 1px #000 inset; border-right: 1px #000 inset; color: #fff;" class="dropdown-toggle" data-toggle="dropdown">User Links <b style="color: #fff;" class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo base_url('administrator/profile'); ?>">My Profile</a></li>
                                    <li><a href="<?php echo base_url('administrator/profile/password'); ?>">Change Password</a></li>
                                    <li class="divider"></li>
                                    <li><a href="<?php echo site_url('logout'); ?>">Log Out</a></li>
                                </ul>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>
        </header><!--header ends-->

