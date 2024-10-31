jQuery(document).ready(function($)
{
    function wp_plugin_framework_read_form_inputs(my_object, form_selector){
        var form;
        if(form_selector === ''){
            form = $(my_object).closest('form');
        }else {
            form = $(form_selector);
        }

        var data = {};

        var input_selector = wp_plugin_framework_script_vars.form_input_selector;
        var input_list = $(form).find(input_selector);

        for(var i=0; i<input_list.length; i++) {
            var input_item = input_list[i];
            var element_name = input_item.name;
            var element_value = '';
            var value_valid = true;
            switch(input_item.type) {
                case 'textarea':
                    element_value = input_item.value;
                    break;
                case 'text':
                    element_value = input_item.value;
                    break;
                case 'number':
                    element_value = input_item.value;
                    break;
                case 'checkbox':
                    element_value = input_item.checked?1:0;
                    break;
                case 'radio':
                    if(input_item.checked){
                        element_value = input_item.value;
                    } else {
                        value_valid = false;
                    }
                    break;
                case 'password':
                    element_value = input_item.value;
                    break;
                case 'select-one':
                    element_value = input_item.value;
                    break;
                case 'hidden':
                    element_value = input_item.value;
                    break;
                default:
                    element_value = null;
                    break;
            }
            if(value_valid) {
                data[element_name] = element_value;
            }
        }

        return data;
    }

    function wp_plugin_framework_call_ajax(my_object, action, controller, view, wpnonce, event_type, event, arguments, form_selector, context_data) {
        var data = wp_plugin_framework_read_form_inputs(my_object, form_selector);
        data['action'] = action;
        data['_event_type'] = event_type;
        data['_event'] = event;
        data['_arguments'] = arguments;
        data['_context_data'] = context_data;

        if(controller){
            data['_controller'] = controller;
        }
        if(view){
            data['_view'] = view;
        }
        if(wpnonce){
            data['_wpnonce'] = wpnonce;
        }

        var jqXHR = $.post(wp_plugin_framework_script_vars.url_to_my_site, data, function (resp, status) {
            if(status === "success") {
                if(resp != null) {
                    if (resp.result === "ok") {
                        var work_item;
                        for (var i = 0; i < resp.work.length; i++) {
                            work_item = resp.work[i];
                            switch (work_item.type) {
                                case 'html':
                                    var arg_count = 0;
                                    if (work_item.arguments != null) {
                                        arg_count = work_item.arguments.length;
                                    }
                                    switch (arg_count) {
                                        case 0:
                                            $(work_item.selector)[work_item.method]();
                                            break;

                                        case 1:
                                            $(work_item.selector)[work_item.method](work_item.arguments[0]);
                                            break;

                                        case 2:
                                            $(work_item.selector)[work_item.method](work_item.arguments[0], work_item.arguments[1]);
                                            break;

                                        case 3:
                                            $(work_item.selector)[work_item.method](work_item.arguments[0], work_item.arguments[1], work_item.arguments[2]);
                                            break;

                                        case 4:
                                            $(work_item.selector)[work_item.method](work_item.arguments[0], work_item.arguments[1], work_item.arguments[2], work_item.arguments[3]);
                                            break;

                                        case 5:
                                            $(work_item.selector)[work_item.method](work_item.arguments[0], work_item.arguments[1], work_item.arguments[2], work_item.arguments[3], work_item.arguments[4]);
                                            break;
                                    }
                                    break;

                                case 'wp_framework_ajax_callback':
                                    my_object = null;
                                    wp_plugin_framework_call_ajax(
                                        my_object,
                                        work_item.action,
                                        work_item.controller,
                                        work_item.view,
                                        work_item.wpnonce,
                                        work_item.event_type,
                                        work_item.event,
                                        work_item.arguments,
                                        work_item.selector,
                                        resp.context_data
                                    );
                                    break;

                                case 'js_call_function':
                                    window[work_item.function](work_item.arguments);
                                    break;

                                case 'do_window_action':
                                    window[work_item.object][work_item.function](work_item.arguments);
                                    break;
                            }
                        }
                    } else {
                        alert(resp.message);
                    }
                }
                else {
                    alert('ERROR: No response from server.');
                }
            }else {
                alert('ERROR: Server response: ' + status);
            }
            $(my_object).attr("disabled", false);
        }, 'json');

        jqXHR.fail(function(jqXHR, textStatus) {
            $(my_object).attr("disabled", false);
            alert('ERROR: Server error. Status: ' + textStatus );
        });
    }

    function wp_plugin_framework_get_data(my_object, event){
        var form_selector = '';
        var action = wp_plugin_framework_script_vars.wp_ajax_function;
        var controller = null;
        var view = null;
        var wpnonce = null;
        var context_data = wp_plugin_framework_script_vars.context_data;
        var event_type = 'click';
        var arguments = null;

        wp_plugin_framework_call_ajax(
            my_object,
            action,
            controller,
            view,
            wpnonce,
            event_type,
            event,
            arguments,
            form_selector,
            context_data
        );
    }

    $(document).on('click', 'input.wp_plugin_framework_ajax_button', function() {
        var my_object = this;
        var event = this.name;
        $(my_object).attr("disabled", true);
        wp_plugin_framework_get_data(my_object, event);
    });

    $(document).on('click', 'button.wp_plugin_framework_ajax_button', function() {
        var my_object = this;
        var event = this.value;
        $(my_object).attr("disabled", true);
        wp_plugin_framework_get_data(my_object, event);
    });

    $(document).on('click', 'button.notice-dismiss', function() {
        $(this).parent('div').remove();
    });
});
