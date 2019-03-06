
jQuery(document).ready(function(){

    jQuery('.date').datepicker().live('changeDate', function (ev) {
        jQuery(this).datepicker('hide');
    });

    jQuery('.time').timepicker({
        minuteStep: 5,
        defaultTime: 'value',
        showInputs: true
    });

    jQuery(".chosen-select").chosen();

    jQuery('a[title], button[title]').tooltip();
    jQuery('.mark').tooltip();

});