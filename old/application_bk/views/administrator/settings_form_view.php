
<h1 class="heading">General Settings</h1>

<?php if ($message_success != '') { echo '<div class="alert alert-success"><a data-dismiss="alert" class="close">&times;</a>'. $message_success .'</div>'; } ?>
<?php if ($message_error != '') { echo '<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>'. $message_error .'</div>'; } ?>


<?php echo validation_errors('<div class="alert alert-error"><a data-dismiss="alert" class="close">&times;</a>', '</div>'); ?>


<?php echo form_open('administrator/settings/update', array('class' => 'form-horizontal')); ?>

    <fieldset>

        <div class="control-group formSep">
            <label class="control-label" for="site_name">Site Name</label>
            <div class="controls">
                <input type="text" name="site_name" id="site_name" value="<?php echo set_value('site_name', $this->form_data->site_name); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="default_category">Default Category</label>
            <div class="controls">
                <?php echo form_dropdown('default_category', $this->cat_list, $this->form_data->default_category, 'id="default_category" class="input-xlarge chosen-select"'); ?>
                <!--<span class="help-inline"></span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="failed_login_message">Failed Login Message</label>
            <div class="controls">
                <input type="text" name="failed_login_message" id="failed_login_message" value="<?php echo set_value('failed_login_message', $this->form_data->failed_login_message); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label" for="locked_login_message">Locked Login Message</label>
            <div class="controls">
                <input type="text" name="locked_login_message" id="locked_login_message" value="<?php echo set_value('locked_login_message', $this->form_data->locked_login_message); ?>" class="input-xlarge" />
                <!--<span class="help-inline">Inline help text</span>-->
            </div>
        </div>

        <div class="control-group input-append formSep">
            <label class="control-label" for="failed_login_count">No. of Allowed Failed Login</label>
            <div class="controls">
                <input type="text" name="failed_login_count" id="failed_login_count" value="<?php echo set_value('failed_login_count', $this->form_data->failed_login_count); ?>"
                       class="input-mini right" /><span class="add-on">times</span>
                <span class="help-block">Set 0 (zero) to disable the feature.</span>
            </div>
        </div>

        <div class="control-group input-append formSep">
            <label class="control-label" for="user_inactivity_period">User Inactive Period</label>
            <div class="controls">
                <input type="text" name="user_inactivity_period" id="user_inactivity_period" value="<?php echo set_value('user_inactivity_period', $this->form_data->user_inactivity_period); ?>"
                       class="input-mini right" /><span class="add-on">days</span>
                <span class="help-block">Set 0 (zero) to disable the feature.</span>
            </div>
        </div>

        <div class="control-group formSep">
            <label class="control-label">Competency Level for Front Office Users</label>
            <div class="controls">
                <div class="alert alert-info">You can enter 'below' in Lower point field and 'above' in Higher point
                    field. Other than than that, enter integer values in Lower point and Higher point field.</div>
                <table class="table" style="width: auto" id="clfu">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Label</th>
                        <th>Lower point</th>
                        <th>Higher point</th>
                        <th width="50">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                        $foc = $this->form_data->front_office_competency;

                        if ($foc): for($i=0; $i<count($foc); $i++):
                            $label = $foc[$i]['label'];
                            $lower = $foc[$i]['lower'];
                            if ($lower == -99999) { $lower = 'below'; }
                            $higher = $foc[$i]['higher'];
                            if ($higher == 99999) { $higher = 'above'; }
                    ?>
                    <tr>
                        <td width="20" class="center" valign="middle">
                            <a href="javascript:void(0);" class="move_clfu_label"><i class="icon-move"></i></a>
                        </td>
                        <td><input type="text" name="clfu_label[]" value="<?php echo $label; ?>" /></td>
                        <td><div class="input-append">
                            <input type="text" name="clfu_lower[]" value="<?php echo $lower; ?>"
                                   class="clfu-lower input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td><div class="input-append">
                            <input type="text" name="clfu_higher[]" value="<?php echo $higher; ?>"
                                   class="clfu-higher input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td width="30" class="center">
                            <a href="javascript:void(0);" title="Add Label" class="add_clfu_label"><i class="icon-plus"></i></a>
                            <a href="javascript:void(0);" title="Remove Label" class="remove_clfu_label"><i class="icon-remove"></i></a>
                        </td>
                    </tr>
                    <?php endfor; endif; ?>

                    <tr>
                        <td width="20" class="center" valign="middle">
                            <a href="javascript:void(0);" class="move_clfu_label"><i class="icon-move"></i></a>
                        </td>
                        <td><input type="text" name="clfu_label[]" /></td>
                        <td><div class="input-append">
                            <input type="text" name="clfu_lower[]" value=""
                                   class="clfu-lower input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td><div class="input-append">
                            <input type="text" name="clfu_higher[]" value=""
                                   class="clfu-higher input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td width="30" class="center">
                            <a href="javascript:void(0);" title="Add Label" class="add_clfu_label"><i class="icon-plus"></i></a>
                            <a href="javascript:void(0);" title="Remove Label" class="remove_clfu_label"><i class="icon-remove"></i></a>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">Competency Level for Back Office Users</label>
            <div class="controls">
                <div class="alert alert-info">You can enter 'below' in Lower point field and 'above' in Higher point
                    field. Other than than that, enter integer values in Lower point and Higher point field.</div>
                <table class="table" style="width: auto" id="clbu">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Label</th>
                        <th>Lower point</th>
                        <th>Higher point</th>
                        <th width="50">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                        $boc = $this->form_data->back_office_competency;

                        if ($boc): for($i=0; $i<count($boc); $i++):
                            $label = $boc[$i]['label'];
                            $lower = $boc[$i]['lower'];
                            if ($lower == -99999) { $lower = 'below'; }
                            $higher = $boc[$i]['higher'];
                            if ($higher == 99999) { $higher = 'above'; }
                    ?>
                    <tr>
                        <td width="20" class="center" valign="middle">
                            <a href="javascript:void(0);" class="move_clbu_label"><i class="icon-move"></i></a>
                        </td>
                        <td><input type="text" name="clbu_label[]" value="<?php echo $label; ?>" /></td>
                        <td><div class="input-append">
                            <input type="text" name="clbu_lower[]" value="<?php echo $lower; ?>"
                                   class="clbu-lower input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td><div class="input-append">
                            <input type="text" name="clbu_higher[]" value="<?php echo $higher; ?>"
                                   class="clbu-higher input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td width="30" class="center">
                            <a href="javascript:void(0);" title="Add Label" class="add_clbu_label"><i class="icon-plus"></i></a>
                            <a href="javascript:void(0);" title="Remove Label" class="remove_clbu_label"><i class="icon-remove"></i></a>
                        </td>
                    </tr>
                    <?php endfor; endif; ?>

                    <tr>
                        <td width="20" class="center" valign="middle">
                            <a href="javascript:void(0);" class="move_clbu_label"><i class="icon-move"></i></a>
                        </td>
                        <td><input type="text" name="clbu_label[]" /></td>
                        <td><div class="input-append">
                            <input type="text" name="clbu_lower[]" value=""
                                   class="clbu-lower input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td><div class="input-append">
                            <input type="text" name="clbu_higher[]" value=""
                                   class="clbu-higher input-mini center" /><span class="add-on">%</span>
                        </div></td>
                        <td width="30" class="center">
                            <a href="javascript:void(0);" title="Add Label" class="add_clbu_label"><i class="icon-plus"></i></a>
                            <a href="javascript:void(0);" title="Remove Label" class="remove_clbu_label"><i class="icon-remove"></i></a>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="form-actions">
            <input type="submit" value="Update Settings" class="btn btn-primary btn-large" />
        </div>

    </fieldset>

<?php echo form_close(); ?>


<style type="text/css">
    .table thead th { background: none!important; }
    .table th, .table td { vertical-align: middle; }
    .move_clfu_label, .move_clbu_label { cursor: move; }
    .form-horizontal .control-label { width: 180px; }
    .form-horizontal .controls { margin-left: 200px; }
    .form-horizontal .form-actions { padding-left: 200px; }
    .control-group.input-append { display: block; }
    .input-append .add-on {
        border-radius: 0 3px 3px 0;
        -moz-border-radius: 0 3px 3px 0;
        -webkit-border-radius: 0 3px 3px 0;
    }
</style>

<script type="text/javascript">
jQuery(document).ready(function(){

    update_control_buttons_clfu();
    update_control_buttons_clbu();

    jQuery('#clfu tbody').sortable({
        handle: '.move_clfu_label',
        update: function(event, ui) {
            update_control_buttons_clfu();
        }
    });

    jQuery('#clbu tbody').sortable({
        handle: '.move_clbu_label',
        update: function(event, ui) {
            update_control_buttons_clbu();
        }
    });


    jQuery('#clfu .add_clfu_label').live('click', function() {
        add_clfu();
    });

    jQuery('#clbu .add_clbu_label').live('click', function() {
        add_clbu();
    });


    jQuery('#clfu .remove_clfu_label').live('click', function() {
        jQuery(this).parent().parent().remove();
        update_control_buttons_clfu();
    });

    jQuery('#clbu .remove_clbu_label').live('click', function() {
        jQuery(this).parent().parent().remove();
        update_control_buttons_clbu();
    });


    jQuery('#clfu .clfu-lower').live('blur', function() {
        var lowerValue = jQuery(this).val();
        if (lowerValue.toLowerCase() != 'below') {
            lowerValue = parseInt(lowerValue);
            if (isNaN(lowerValue)) {
                lowerValue = '';
            }
            jQuery(this).val(lowerValue);
        } else {
            jQuery(this).val(lowerValue.toLowerCase());
        }
    });

    jQuery('#clbu .clbu-lower').live('blur', function() {
        var lowerValue = jQuery(this).val();
        if (lowerValue.toLowerCase() != 'below') {
            lowerValue = parseInt(lowerValue);
            if (isNaN(lowerValue)) {
                lowerValue = '';
            }
            jQuery(this).val(lowerValue);
        } else {
            jQuery(this).val(lowerValue.toLowerCase());
        }
    });


    jQuery('#clfu .clfu-higher').live('blur', function() {
        var higherValue = jQuery(this).val();
        if (higherValue.toLowerCase() != 'above') {
            higherValue = parseInt(higherValue);
            if (isNaN(higherValue)) {
                higherValue = '';
            }
            if (higherValue > 100) {
                higherValue = 100;
            }
            jQuery(this).val(higherValue);
        } else {
            jQuery(this).val(higherValue.toLowerCase());
        }
    });

    jQuery('#clbu .clbu-higher').live('blur', function() {
        var higherValue = jQuery(this).val();
        if (higherValue.toLowerCase() != 'above') {
            higherValue = parseInt(higherValue);
            if (isNaN(higherValue)) {
                higherValue = '';
            }
            if (higherValue > 100) {
                higherValue = 100;
            }
            jQuery(this).val(higherValue);
        } else {
            jQuery(this).val(higherValue.toLowerCase());
        }
    });

});

function update_control_buttons_clfu() {
    jQuery('#clfu tbody tr').each(function(index){
        jQuery(this).find('.add_clfu_label').css('display', 'none');
        jQuery(this).find('.remove_clfu_label').css('display', 'inline-block');
    });
    jQuery('#clfu tbody tr:last-child .add_clfu_label').css('display', 'inline-block');
    jQuery('#clfu tbody tr:last-child .remove_clfu_label').css('display', 'none');
}

function update_control_buttons_clbu() {
    jQuery('#clbu tbody tr').each(function(index){
        jQuery(this).find('.add_clbu_label').css('display', 'none');
        jQuery(this).find('.remove_clbu_label').css('display', 'inline-block');
    });
    jQuery('#clbu tbody tr:last-child .add_clbu_label').css('display', 'inline-block');
    jQuery('#clbu tbody tr:last-child .remove_clbu_label').css('display', 'none');
}

function add_clfu() {
    var html = '';
    html += '<tr>';
        html += '<td width="20" class="center" valign="middle">';
            html += '<a href="javascript:void(0);" class="move_clfu_label"><i class="icon-move"></i></a>';
        html += '</td>';
        html += '<td><input type="text" name="clfu_label[]" /></td>';
        html += '<td><div class="input-append">';
            html += '<input type="text" name="clfu_lower[]" value=""';
                   html += 'class="clfu-lower input-mini center" /><span class="add-on">%</span>';
        html += '</div></td>';
        html += '<td><div class="input-append">';
            html += '<input type="text" name="clfu_higher[]" value=""';
                   html += 'class="clfu-higher input-mini center" /><span class="add-on">%</span>';
        html += '</div></td>';
        html += '<td width="30" class="center">';
            html += '<a href="javascript:void(0);" title="Add Label" class="add_clfu_label"><i class="icon-plus"></i></a>';
            html += '<a href="javascript:void(0);" title="Remove Label" class="remove_clfu_label"><i class="icon-remove"></i></a>';
        html += '</td>';
    html += '</tr>';
    
    jQuery('#clfu tbody').append(html);
    update_control_buttons_clfu();
}

function add_clbu() {
    var html = '';
    html += '<tr>';
        html += '<td width="20" class="center" valign="middle">';
            html += '<a href="javascript:void(0);" class="move_clbu_label"><i class="icon-move"></i></a>';
        html += '</td>';
        html += '<td><input type="text" name="clbu_label[]" /></td>';
        html += '<td><div class="input-append">';
            html += '<input type="text" name="clbu_lower[]" value=""';
                   html += 'class="clbu-lower input-mini center" /><span class="add-on">%</span>';
        html += '</div></td>';
        html += '<td><div class="input-append">';
            html += '<input type="text" name="clbu_higher[]" value=""';
                   html += 'class="clbu-higher input-mini center" /><span class="add-on">%</span>';
        html += '</div></td>';
        html += '<td width="30" class="center">';
            html += '<a href="javascript:void(0);" title="Add Label" class="add_clbu_label"><i class="icon-plus"></i></a>';
            html += '<a href="javascript:void(0);" title="Remove Label" class="remove_clbu_label"><i class="icon-remove"></i></a>';
        html += '</td>';
    html += '</tr>';

    jQuery('#clbu tbody').append(html);
    update_control_buttons_clbu();
}

</script>