jQuery(document).ready(function($)
{
    var args_list;

    window.rml_fade_in_out = function (arguments) {
        args_list = arguments;

        $(args_list.selector).fadeOut(500, "swing", function(){
            $(args_list.selector).html(args_list.html);
            $(args_list.selector).fadeIn(500);
        });
    };

    window.rml_roll_down_up = function (arguments) {
        args_list = arguments;

        $(args_list.selector).slideUp(500, function(){
            $(args_list.selector).html(args_list.html);
            $(args_list.selector).slideDown(500);
        });
    };

    window.rml_load_remaining_text = function (arguments) {
        args_list = arguments;

        $(args_list.selector_text_holder).hide();

        if(args_list.form_loading === 'rml_fade_in_out') {
            $(args_list.selector_controller).fadeOut(500, "swing", function () {
                $(args_list.selector_controller).remove();
                $(args_list.selector_fade_div).remove();
                $(args_list.selector_text_holder).html(args_list.remaining_text);

                if(args_list.text_loading === 'rml_fade_in_out') {
                    $(args_list.selector_text_holder).fadeIn(500, "swing");
                } else if(args_list.text_loading === 'rml_roll_down_up') {
                    $(args_list.selector_text_holder).slideDown(500);
                } else {
                    $(args_list.selector_text_holder).show();
                }
            });
        } else if(args_list.form_loading === 'rml_roll_down_up') {
            $(args_list.selector_controller).slideUp(500, function () {
                $(args_list.selector_controller).remove();
                $(args_list.selector_fade_div).remove();
                $(args_list.selector_text_holder).html(args_list.remaining_text);

                if(args_list.text_loading === 'rml_fade_in_out') {
                    $(args_list.selector_text_holder).fadeIn(500, "swing");
                } else if(args_list.text_loading === 'rml_roll_down_up') {
                    $(args_list.selector_text_holder).slideDown(500);
                } else {
                    $(args_list.selector_text_holder).show();
                }
            });
        } else {
            $(args_list.selector_controller).remove();
            $(args_list.selector_fade_div).remove();
            $(args_list.selector_text_holder).html(args_list.remaining_text);

            if(args_list.text_loading === 'rml_fade_in_out') {
                $(args_list.selector_text_holder).fadeIn(500, "swing");
            } else if(args_list.text_loading === 'rml_roll_down_up') {
                $(args_list.selector_text_holder).slideDown(500);
            } else {
                $(args_list.selector_text_holder).show();
            }
        }
    };

});
