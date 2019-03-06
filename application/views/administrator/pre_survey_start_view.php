<h1 class="heading">Survey Start</h1>


<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>




<div class="content">
    <div class="row-fluid" style="opacity: 1;">
        <div class="span6" style="display: none;">
            <div class="w-box">
            </div>
        </div>

        <div class="span6" style="display: none;">
            <div class="w-box">
            </div>
        </div>
        <?php echo $survey_start ?>
</div>

