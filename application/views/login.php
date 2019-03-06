<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<?php $this->load->view('user/partials/header'); ?>

<?php if ($show_box == '2'): ?>
<style type="text/css">
    #login_form { display: none; }
    #pass_form { display: block; }
</style>
<?php else: ?>
<style type="text/css">
    #login_form { display: block; }
    #pass_form { display: none; }

</style>
<?php endif; ?>
<style type="text/css">
    .header-wrap {
    position: relative;
    height: 54px;
    min-width: 972px;
    color: #ffffff;
    box-shadow:none;
    background:none;
	background-color:none;
}
.btn-srv{
    color: #ffffff;
    text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
    background: rgb(0, 84, 166);
    border-color: #222 #222 #000;
}

.btn-srv:hover{
    color: #ffffff;
    text-shadow: 0 -1px 0 rgba(0,0,0,0.25);
    background: #1A237E;
    border-color: #222 #222 #000;

}

</style>
<p align="center">
<img align="center" src="<?php echo base_url(); ?>assets/images/brac_bank.png" alt="BRAC Bank" />
</p>
<div class="login_box" style="background: rgb(0, 84, 166);">

    <?php echo form_open('login/do_login', array('id' => 'login_form')); ?>

        <div class="top_b" style="color: #fff; background: none;">Sign in to <?php echo $this->site_name; ?></div>

        <?php if (isset($message_error) && $message_error != '') : ?>
            <div class="alert alert-danger text-center" ><?php echo $message_error; ?></div>
        <?php endif; ?>
        <?php if (isset($message_success) && $message_success != '') : ?>
        <div class="alert alert-success"><?php echo $message_success; ?></div>
        <?php endif; ?>

        <div class="cnt_b">
            <div class="formRow">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-user"></i></span><input type="text" name="user_login" id="user_login" placeholder="Login ID" />
                </div>
            </div>
            <div class="formRow">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-lock"></i></span><input type="password" name="user_password" id="user_password" placeholder="Password" />
                </div>
            </div>
        </div>

        <div class="btm_b clearfix" style="background: none;">
            <input type="hidden" name="redirect_url" value="<?php echo $redirect_url; ?>" >
            <button type="submit" class="btn btn-inverse pull-right">Sign In</button>
        </div>

    <?php echo form_close(); ?>


    <?php echo form_open('login/send_new_password', array('id' => 'pass_form')); ?>
        <div class="top_b" style="color: #fff; background: none;">Can't sign in?</div>

        <?php if (isset($message_error) && $message_error != '') : ?>
            <div class="alert alert-error" style="margin-bottom: 10px;"><?php echo $message_error; ?></div>
        <?php endif; ?>
        <div class="alert alert-info">Please enter your Login ID. We will send an email to your email address with a new password.</div>

        <div class="cnt_b">
            <div class="formRow clearfix">
                <div class="input-prepend">
                    <span class="add-on"><i class="icon-user"></i></span></span><input type="text" name="fp_user_login" id="fp_user_login" placeholder="Login ID" />
                </div>
            </div>
        </div>
        <div class="btm_b tac clearfix" style="background: none;">
            <button type="submit" class="btn btn-inverse pull-right">Send New Password</button>
        </div>
    <?php echo form_close(); ?>

<?php if ($show_box == '2'): ?>
    <div class="links_b links_btm">
        <span class="linkform" style="display:none"><a href="#pass_form">Forgot password?</a></span>
        <span class="linkform">Never mind, <a href="#login_form">send me back to the sign-in screen</a></span>
    </div>
<?php else: ?>
    <div class="links_b links_btm">

        <span class="linkform"><a href="#pass_form">Forgot password?</a></span>
        <span class="linkform" style="display:none">Never mind, <a href="#login_form">send me back to the sign-in screen</a></span>
    </div>
<?php endif; ?>

</div><!--login_box ends-->
<?php if($srv) { ?>
<span ><a class="btn btn-srv" href="<?php echo base_url('openSrv'); ?>">
    <img src="<?php echo base_url('assets/images/blink_image/Green_16x16.gif'); ?>" alt="New" width="20" height="20">
Go to survey!</a></span>
<?php } ?>
<?php $this->load->view('user/partials/footer'); ?>