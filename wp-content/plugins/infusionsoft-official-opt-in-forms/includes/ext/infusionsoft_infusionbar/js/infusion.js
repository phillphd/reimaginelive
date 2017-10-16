(function($) {
    //setup some variables to use throughout
    var isSticky = ($('.inf_infusionsoft_infusionbar').hasClass('stickytop')) ? true : false;
    var isStickyStuck = ($('.inf_infusionsoft_infusionbar').hasClass('stickytop_stick')) ? true : false;
    var infusion_displayed = jQuery('.inf_infusionsoft_infusionbar').length;
    var infusion_timedelay = jQuery('.inf_infusionsoft_infusionbar.inf_infusionsoft_infusionbar_trigger_auto').data('delay');
    var delay = '' !== infusion_timedelay ? infusion_timedelay * 1000 : 500;
    var submit_remove = $('.inf_infusionsoft_redirect_page').data('submit_remove');
    var $body = $('body');

    if(isSticky == true){
        $(window).on('scroll', function(){
            if($(window).scrollTop() > 0){
                $('.inf_infusionsoft_infusionbar').addClass('stickytop_stick');
                $('.inf_infusionsoft_infusionbar').removeClass('stickytop');
                if(infusion.admin_bar) {
                    $('.inf_infusionsoft_infusionbar').css('margin-top', '32px');
                }
            }else{
                $('.inf_infusionsoft_infusionbar').removeClass('stickytop_stick');
                $('.inf_infusionsoft_infusionbar').addClass('stickytop');
                if(infusion.admin_bar) {
                    $('.inf_infusionsoft_infusionbar').css('margin-top', '0px');
                }
            }
        });
    }

    /*---------------------------------------
    ------------Adding heights for bar-------
    -----------------------------------------*/
    $(window).on('load', function () {
        //set inital heights
        load_delay = delay + 500;
	//need to delay as if we don't and the bar has a delay we end up with a negative margin
        setTimeout(function(){
            new_height = $('.inf_infusionsoft_infusionbar').height();
            infusion_add_padding(new_height);
        }, load_delay);
        replicate_text_color(delay);
        var text_height = $('.inf_infusionsoft_infusionbar_form_header').height();
        infusion_responsive_css(text_height);
    });
    
    $(window).resize(function() {
        new_height = $('.inf_infusionsoft_infusionbar').height();
        infusion_add_padding(new_height);
        var text_height = $('.inf_infusionsoft_infusionbar_form_header').height();
        infusion_responsive_css(text_height);
    });

    function infusion_add_padding(height){
        $('body').attr('data-inf_height', height);
        /*---fixed header heights----*/
        var header = $('header');
        if($(header).css('position') == 'fixed' || $(header).css('position') == 'absolute'){
            $(header).css('margin-top', height);
            $(header).attr('data-infusion_height', height);
        }

        jQuery('body').children().each(function(){
            var this_el = jQuery(this);
            if (jQuery(this_el).css('position') == 'fixed' || jQuery(this_el).css('position') == 'absolute' ) {
                if(!jQuery(this_el).hasClass('inf_infusionsoft_infusionbar') && jQuery(this_el).attr('id') != 'wpadminbar') {
                    var current_padding_top_el = jQuery(this_el).css('padding-top');
                    var new_padding_el = parseInt(current_padding_top_el.replace('px', '')) + height;
                    jQuery(this_el).css('padding-top', height);
                    jQuery(this_el).attr('data-infusion_height', height);
                }
            }
        });
        $('.sticky_adminbar_push').css('height', '32');
    }

    /*------------------------------------------
     ------------removing heights for bar-------
     ------------------------------------------*/

    // triggers for closing infusion
    jQuery('.inf_infusionsoft_redirect_page').on('click', function () {
        var submit_remove = $('.inf_infusionsoft_redirect_page').data('submit_remove');//will be true of false to pass into remove padding to keep bar from being removed
        if(submit_remove == true) {
            setTimeout(
                function () {
                    console.log('here');
                    infusion_remove_padding(true);
                }, 400); //use set timeout as it is used the other closing functions
        }
    });

    jQuery('.inf_infusionsoft_infusionbar .inf_infusionsoft_close_button').on('click', function () {
        setTimeout(
            function(){
                infusion_remove_padding(true, true);
            }, 400); //use set timeout as it is used the other closing functions
    });

    //scroll trigger to remove padding
    if(isSticky == false) {
        jQuery(window).scroll(function () {
            inf_scroll_height = $('.inf_infusionsoft_infusionbar').height();
            var scroll = $(window).scrollTop();
            if (scroll >= inf_scroll_height) {
                infusion_remove_padding(false);
            } else {
                infusion_add_padding(inf_scroll_height);
            }
        });
    }

    function infusion_remove_padding(remove_bar, closebtn){
        height = $('.inf_infusionsoft_infusionbar').height(); //get height of bar
        if( $('.infusion_consent_form') && $('.consent_error').is(":visible") ){
            consent_height = $('.infusion_consent_form').height();
            height = height - consent_height;
        }
        if( $('.consent_error') && $('.consent_error').is(":visible") ){
            consent_error_height = $('.consent_error').height();
            console.log(consent_error_height);
            height = height - consent_error_height;
        }
        var removebar = (remove_bar == false ? false : true);
        var header = $('header');
        if($(header).data('infusion_height')){
            var current_height = $(header).data('infusion_height');
            $(header).css('margin-top', current_height - height);
        }
        $('.sticky_adminbar_push').css('height', '0');

        $("[data-infusion_height]").each(function(){
            var padding_to_remove = jQuery(this).data('infusion_height');
            var current_padding = jQuery(this).css('padding-top');
            var new_padding_el = parseInt(current_padding.replace('px', '')) - padding_to_remove;
            $(this).css('padding-top', new_padding_el);
        });

        if(removebar == true) {
            var redirectUrl = $('.inf_infusionsoft_submit_subscription').data('redirect_url');
            if (!redirectUrl) { //dont want to remove if they have a redirect setup with a timer as we want the form to stick around
                $('.inf_infusionsoft_infusionbar').remove();
            }
        }
        if(closebtn ==  true){
            $('.inf_infusionsoft_infusionbar').remove();
        }
    }

    function replicate_text_color(delay){
        //loop through any infusion on the page and set the color appropriately if text color has been changed in the admin editor
        //only happens on btns as links
        setTimeout(function(delay){
            $('.inf_infusionsoft_infusionbar').each(function(){
                var this_el = jQuery(this);
                var button = jQuery(this_el).find('button'); //find our button on this form
                var btnAsLink = jQuery(button).attr('class').match(/btnaslink/); //make sure button has link class
                if(btnAsLink && btnAsLink.index > 0) { //if the result index from match is > 0 then we can change it, if not we won't.
                    var barTextEl = jQuery(this_el.find('.inf_infusionsoft_form_text p span'));
                    var textColor = barTextEl.css('color');
                    if (textColor) {
                        var buttonText = jQuery(this_el.find('.inf_infusionsoft_button_text'));
                        jQuery(buttonText).attr('style', 'color: ' + textColor + ' !important; text-decoration: underline !important');
                    }else{
                        $('.inf_infusionsoft_button_text').attr('style', 'text-decoration: underline !important');
                    }
                }
            });
        }, delay);
    }



    /*------------------------------------------
     -----fix css for bar on window resize------
     ------------------------------------------*/

    function infusion_responsive_css(height){
        if(height > 50){
            $('.inf_infusionsoft_infusionbar .inf_infusionsoft_form_text p').addClass('infusion_form_responsive');
        }else if(height <= 50){
            $('.inf_infusionsoft_infusionbar .inf_infusionsoft_form_text p').removeClass('infusion_form_responsive');
        }
    }


    /*------------------------------------------
     -----consent for infusion------
     ------------------------------------------*/
    if('.infusion_consent_form') {
        $(".inf_infusionsoft_infusionbar_input input").keyup(function () {
            if($(this).val().length > 0) {
                if ($('.infusion_consent_form').hasClass('infusion_consent_closed')) {
                    $('.infusion_consent_form').removeClass('infusion_consent_closed');
                    $('.infusion_consent_form').addClass('infusion_consent');
                }
            }else{
                $('.infusion_consent_form').removeClass('infusion_consent');
                $('.infusion_consent_form').addClass('infusion_consent_closed');
            }
        });
    }

    $body.on('click', '.infusion_consent_form .accept_consent', function(){
        if($('.infusion_consent_form .accept_consent').prop('checked')){
            $('.consent_error').hide();
        }
    });
})( jQuery );





