
$(document).ready(function(){

    /* accordion icons */
    acc_icons.init();

    /*

    $(".exam .desc").shorten({
        "showChars" : 200
    });

    /**
     * login page
     ********************************************************************************/
    if ($('.login_box').length > 0) {

        if ($('#user_login').val() == '') {
            $('#user_login').focus();
        } else {
            $('#user_password').focus();
        }

        //* boxes animation
        form_wrapper = $('.login_box');
        $('.linkform a, .link_reg a').on('click',function(e){
            var target	= $(this).attr('href'),
                target_height = $(target).actual('height');
            $(target).find('.alert-error').hide();
            $(form_wrapper).css({
                'height'		: form_wrapper.height()
            });
            $(form_wrapper.find('form:visible')).fadeOut(400,function(){
                form_wrapper.stop().animate({
                    height	: target_height
                },500,function(){
                    $(target).fadeIn(400);
                    if (target == '#login_form') {
                        $('#user_login').focus();
                    } else if (target == '#pass_form') {
                        $('#fp_user_login').focus();
                    }
                    $('.links_btm .linkform').toggle();
                    $(form_wrapper).css({
                        'height'		: ''
                    });
                });
            });
            e.preventDefault();
        });

        //* validation
        $('#login_form').validate({
            onkeyup: false,
            errorClass: 'error',
            validClass: 'valid',
            rules: {
                user_login: { required: true },
                user_password: { required: true }
            },
            highlight: function(element) {
                $(element).closest('div').addClass("f_error");
            },
            unhighlight: function(element) {
                $(element).closest('div').removeClass("f_error");
            },
            errorPlacement: function(error, element) {
                $(element).closest('div').append(error);
            }
        });
    }

    /**
     * answer form validation
     ********************************************************************************/
    $('#next-button, #finish-button').click(function(){

        if (isAnswered()) {
            if ($(this).attr('id') == 'finish-button') {
                disableExam();
            }
        } else {
            return false;
        }

    });


    $('.latest-content-link, .ajax-content-link').on('click', function(){

        var contentId = parseInt($(this).attr('data-id'));
        if(contentId > 0){

            // call ajax to set current_content_id
            $('#current_content_id').val(contentId);
            $.ajax({
                url: baseUrlJs + 'home/set_current_content_id/' + contentId,
                success: function(data) {}
            });

            $('.content .row-fluid').fadeTo(0, 0.5);

            /* call ajax to get the content */
            $.ajax({
                url: baseUrlJs + 'content/' + contentId,
                success: function(data) {
                    $('.content .row-fluid .span6').hide();
                    $('.content .row-fluid .span12').remove();
                    $('.content .row-fluid').append(data);
                    $('.content .row-fluid').fadeTo(0.5, 1);
                }
            });

        }
        return false;
    })


    /* call AJAX method to load training */
    $('.ajax-training-link').on('click', function(){
        var trainingId = parseInt($(this).attr('data-id'));
        if(trainingId > 0){
            $('.content .row-fluid').fadeTo(0, 0.5);

            /* call ajax to get the content */
            $.ajax({
                url: baseUrlJs + 'training/' + trainingId,
                success: function(data) {
                    $('.content .row-fluid .span6').hide();
                    $('.content .row-fluid .span12').remove();
                    $('.content .row-fluid').append(data);
                    $('.content .row-fluid').fadeTo(0.5, 1);
                }
            });

        }
        return false;
    })


    /* call AJAX method to load survey */
    $('.ajax-survey-link').on('click', function(){
        var surveyId = parseInt($(this).attr('data-id'));
        var status = $(this).attr('data-status');

        if(status == 'open'){
            if(surveyId > 0){
                $('.content .row-fluid').fadeTo(0, 0.5);

                /* call ajax to get the content */
                $.ajax({
                    url: baseUrlJs + 'survey/' + surveyId,
                    success: function(data) {
                        $('.content .row-fluid .span6').hide();
                        $('.content .row-fluid .span12').remove();
                        $('.content .row-fluid').append(data);
                        $('.content .row-fluid').fadeTo(0.5, 1);
                    }
                });

            }
        }else if(status == 'completed'){
            if(surveyId > 0){
                $('.content .row-fluid').fadeTo(0, 0.5);

                /* call ajax to get the content */
                $.ajax({
                    url: baseUrlJs + 'survey/get_completed_survey/' + surveyId,
                    success: function(data) {
                        $('.content .row-fluid .span6').hide();
                        $('.content .row-fluid .span12').remove();
                        $('.content .row-fluid').append(data);
                        $('.content .row-fluid').fadeTo(0.5, 1);
                    }
                });

            }
        }

        return false;
    })

    /* call AJAX method to load survey question */
    $( "a.start_survey" ).live( "click", function() {

        console.log("amit");
        var survey_is_started = parseInt($(this).attr('data-survey_is_started'));
        if(survey_is_started == 0){
            $('.content .row-fluid').fadeTo(0, 0.5);

            /* call ajax to get the content */
            $.ajax({
                url: baseUrlJs + 'survey/start_survey/' + survey_is_started,
                success: function(data) {
                    $('.content .row-fluid .span6').hide();
                    $('.content .row-fluid .span12').remove();
                    $('.content .row-fluid').append(data);
                    $('.content .row-fluid').fadeTo(0.5, 1);
                }
            });

        }
        return false;
    });


     /* call AJAX method to load survey question */
    $( "a.start_surveys" ).live( "click", function() {
        console.log("babu");
        var survey_is_started = parseInt($(this).attr('data-survey_is_started'));
        if(survey_is_started == 0){
            $('.content .row-fluid').fadeTo(0, 0.5);
            /*call ajax to get the content */

            $.ajax({
                url: baseUrlJs + 'startSurveyNoUser/' + survey_is_started,
                success: function(data) {
                    //console.log(baseUrlJs);
                    $('.content .row-fluid .span6').hide();
                    $('.content .row-fluid .span12').remove();
                    $('.content .row-fluid').append(data);
                    $('.content .row-fluid').fadeTo(0.5, 1);
                }
            });

        }
        return false;
    });


    /* call AJAX method to load next survey question */
    $( "#next-buttons" ).live( "click", function(e) {
        e.preventDefault();
        var answerFlag = false;
        var answer = null;
        var current_question_id=0;
        $('.answers input').each(function(){
            if ($(this).is(':checked')) {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('.answers textarea').each(function(){
            if ($(this).val() != '') {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('input[type=hidden]').each(function(){
            current_question_id = $(this).val();
        });

        current_question_id = parseInt(current_question_id) + 1;


        if ( ! answerFlag) {
            alert('You haven\'t answered the question.\nYou can\'t move to the next question or finish the exam unless you answer the question.');
            return false;
        } else {

            if(current_question_id > 0){
                $('.content .row-fluid').fadeTo(0, 0.5);

                /* call ajax to get the content */
                $.ajax({
                    type: "POST",
                    url: baseUrlJs + 'startSurveyNoUser/' + current_question_id,
                    data: "answer="+answer,
                    success: function(msg) {
                        $('.content .row-fluid .span6').hide();
                        $('.content .row-fluid .span12').remove();
                        $('.content .row-fluid').append(msg);
                        $('.content .row-fluid').fadeTo(0.5, 1);
                    }
                });

            }else{
                return false;
            }
        }

        return false;
    });

    /* call AJAX method to load next survey question */
    $( "#next-button" ).live( "click", function(e) {
        e.preventDefault();
        var answerFlag = false;
        var answer = null;
        var current_question_id=0;
        $('.answers input').each(function(){
            if ($(this).is(':checked')) {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('.answers textarea').each(function(){
            if ($(this).val() != '') {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('input[type=hidden]').each(function(){
            current_question_id = $(this).val();
        });

        current_question_id = parseInt(current_question_id) + 1;


        if ( ! answerFlag) {
            alert('You haven\'t answered the question.\nYou can\'t move to the next question or finish the exam unless you answer the question.');
            return false;
        } else {

            if(current_question_id > 0){
                $('.content .row-fluid').fadeTo(0, 0.5);

                /* call ajax to get the content */
                $.ajax({
                    type: "POST",
                    url: baseUrlJs + 'survey/start_survey/' + current_question_id,
                    data: "answer="+answer,
                    success: function(msg) {
                        $('.content .row-fluid .span6').hide();
                        $('.content .row-fluid .span12').remove();
                        $('.content .row-fluid').append(msg);
                        $('.content .row-fluid').fadeTo(0.5, 1);
                    }
                });

            }else{
                return false;
            }
        }

        return false;
    });


    $( "#finish-buttons" ).live( "click", function(e) {
        e.preventDefault();
        var answerFlag = false;
        var answer = null;
        var current_question_id=0;
        $('.answers input').each(function(){
            if ($(this).is(':checked')) {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('.answers textarea').each(function(){
            if ($(this).val() != '') {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('input[type=hidden]').each(function(){
            current_question_id = $(this).val();
        });

        current_question_id = parseInt(current_question_id) + 1;


        if ( ! answerFlag) {
            alert('You haven\'t answered the question.\nYou can\'t move to the next question or finish the exam unless you answer the question.');
            return false;
        } else {

            if(current_question_id > 0){
                /* call ajax to get the content */

                $.ajax({
                    type: "POST",
                    url: baseUrlJs + 'completeSurveyNoUser/' + current_question_id,
                    data: "answer="+answer,
                    success: function(msg) {
                        //window.location.replace(baseUrlJs + 'home');
                        $('.content .row-fluid .span6').hide();
                        $('.content .row-fluid .span12').remove();
                        $('.content .row-fluid').append(msg);

                        $('.content .row-fluid').append('<a class="btn btn-success" href="'+baseUrlJs+'home">Back To Home</a>');

                        $('.content .row-fluid').fadeTo(0.5, 1);
                    }
                });

            }else{
                return false;
            }
        }

        return false;
    });


    /* call AJAX method to load next survey question */
    $( "#finish-button" ).live( "click", function(e) {
        e.preventDefault();
        var answerFlag = false;
        var answer = null;
        var current_question_id=0;
        $('.answers input').each(function(){
            if ($(this).is(':checked')) {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('.answers textarea').each(function(){
            if ($(this).val() != '') {
                answerFlag = true;
                answer = $(this).val();
            }
        });

        $('input[type=hidden]').each(function(){
            current_question_id = $(this).val();
        });

        current_question_id = parseInt(current_question_id) + 1;


        if ( ! answerFlag) {
            alert('You haven\'t answered the question.\nYou can\'t move to the next question or finish the exam unless you answer the question.');
            return false;
        } else {

            if(current_question_id > 0){
                /* call ajax to get the content */

                $.ajax({
                    type: "POST",
                    url: baseUrlJs + 'survey/complete_survey/' + current_question_id,
                    data: "answer="+answer,
                    success: function(msg) {
                        //window.location.replace(baseUrlJs + 'home');
                        $('.content .row-fluid .span6').hide();
                        $('.content .row-fluid .span12').remove();
                        $('.content .row-fluid').append(msg);

                        $('.content .row-fluid').append('<a class="btn btn-success" href="'+baseUrlJs+'home">Back To Home</a>');

                        $('.content .row-fluid').fadeTo(0.5, 1);
                    }
                });

            }else{
                return false;
            }
        }

        return false;
    });

    /* call AJAX method to load next survey question */
    $( "#previous-button" ).live( "click", function(e) {
        e.preventDefault();
        var current_question_id=0;

        $('input[type=hidden]').each(function(){
            current_question_id = $(this).val();
        });

        current_question_id = parseInt(current_question_id) - 1;

        if(current_question_id >= 0){
            $('.content .row-fluid').fadeTo(0, 0.5);

            /* call ajax to get the content */
            $.ajax({
                url: baseUrlJs + 'survey/start_survey/' + current_question_id,
                success: function(msg) {
                    $('.content .row-fluid .span6').hide();
                    $('.content .row-fluid .span12').remove();
                    $('.content .row-fluid').append(msg);
                    $('.content .row-fluid').fadeTo(0.5, 1);
                }
            });

        }else{
            return false;
        }

        return false;
    });


    // set action value as button clicked
    $('#previous-button').click(function(){
        $('#action').val('previous');
    });

    $('#next-button').click(function(){
        $('#action').val('next');
    });

    $('#finish-button').click(function(){
        $('#action').val('finish');
    });

    $('#pause-button').click(function(){
        $('#action').val('pause');
    });

    $('#quit-button').click(function(){
        $('#action').val('quit');
    });

    $('#previous-button-exam').click(function(){
        $('#action-exam').val('previous');
    });

    $('#next-button-exam').click(function(){
        var answerFlag = false;
        $('.answers2 input').each(function(){
            if ($(this).is(':checked')) {
                answerFlag = true;
            }
        });

        $('#action-exam').val('next');
    });

    $('#finish-button-exam').click(function(){
        $('#action-exam').val('finish');
    });

    $('#pause-button-exam').click(function(){
        $('#action-exam').val('pause');
    });

    $('#quit-button-exam').click(function(){
        $('#action-exam').val('quit');
    });

});

function isAnswered() {
    var answerFlag = false;
    $('.answers input').each(function(){
        if ($(this).is(':checked')) {
            answerFlag = true;
        }
    });

    $('.answers textarea').each(function(){
        if ($(this).val() != '') {
            answerFlag = true;
        }
    });

    if ( ! answerFlag) {
        alert('You haven\'t answered the question.\nYou can\'t move to the next question or finish the exam unless you answer the question.');
        return false;
    } else {
        return true;
    }
}


function isAnswered_Exam() {

    $('.answers2 textarea').each(function(){
        if ($(this).val() != '') {
            answerFlag = true;
        }
    });

    if ( ! answerFlag) {
        alert('You haven\'t answered the question.\nYou can\'t move to the next question or finish the exam unless you answer the question.');
        return false;
    } else {
        return true;
    }
}


/* accordion icons */
acc_icons = {
    init: function() {
        var accordions = $('.accordion');

        accordions.find('.accordion-group').each(function(){
            var acc_active = $(this).find('.accordion-body').filter('.in');
            acc_active.prev('.accordion-heading').find('.accordion-toggle').addClass('acc-in');
        });
        accordions.on('show', function(option) {
            $(this).find('.accordion-toggle').removeClass('acc-in');
            $(option.target).prev('.accordion-heading').find('.accordion-toggle').addClass('acc-in');
        });
        accordions.on('hide', function(option) {
            $(option.target).prev('.accordion-heading').find('.accordion-toggle').removeClass('acc-in');
        });
    }
};



