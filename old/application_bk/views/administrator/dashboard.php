<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?>


<div class="row-fluid">
    <h1 class="heading">Dashboard</h1>
</div>

<div class="row-fluid">

    <!-- application environment -->
    <div class="span6">

        <div class="w-box">
            <div class="w-box-header">Application Environment Status Check</div>
            <div class="w-box-content cnt_a">

                <?php if ($event_status == 'OFF') : ?>
                <div class="alert alert-error" style="margin-bottom: 0;">
                    <?php echo form_open('administrator/dashboard/set_event', array('style' => 'margin:0;')); ?>
                    Database EVENT SCHEDULER is currently set OFF. Please <input type="submit" name="switch_event_on" value="Set ON" class="btn btn-success" />  the EVENT SCHEDULER.
                    <?php echo form_close(); ?>
                </div>
                <?php elseif ($event_status == 'ON') : ?>
                <div class="alert alert-success" style="margin-bottom: 0;">
                    Database EVENT SCHEDULER is currently set ON.
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>

</div>