<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>

<h3 class="page-title">
    View Profile
    <a class="btn pull-right" href="<?php echo site_url(); ?>"><i class="icon-arrow-left"></i> Back to Homepage</a>
</h3>

<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<div class="form-horizontal">

    <div class="control-group" style="margin-bottom: 0px;">
        <label class="control-label">Login ID</label>
        <div class="controls">
            <strong style="display: inline-block; padding-top: 5px;"><?php echo $user->user_login; ?></strong>
        </div>
    </div>

    <div class="control-group" style="margin-bottom: 0px;">
        <label class="control-label">Team Name</label>
        <div class="controls">
            <strong style="display: inline-block; padding-top: 5px;"><?php echo $user->user_team_name; ?></strong>
        </div>
    </div>

    <div class="control-group" style="margin-bottom: 0px;">
        <label class="control-label">First Name</label>
        <div class="controls">
            <strong style="display: inline-block; padding-top: 5px;"><?php echo $user->user_first_name; ?></strong>
        </div>
    </div>

    <div class="control-group" style="margin-bottom: 0px;">
        <label class="control-label">Last Name</label>
        <div class="controls">
            <strong style="display: inline-block; padding-top: 5px;"><?php echo $user->user_last_name; ?></strong>
        </div>
    </div>

    <div class="control-group" style="margin-bottom: 0px;">
        <label class="control-label">Email</label>
        <div class="controls">
            <strong style="display: inline-block; padding-top: 5px;"><?php echo $user->user_email; ?></strong>
        </div>
    </div>

</div>