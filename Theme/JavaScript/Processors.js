function FormProcessor(parametres) {
    var object = this;
    object.JsMainError;

    object.parameters = {
        form_id: '',
        api_link: '/Api/TestApi',
        submit_button_id: 'SubmitData',
        submit_button_loading: 'SubmitDataLoadingStatus',
        success_location: '',
        run_succsess_function: false,
        hard_debug: false,
        inputs_id: [],
        inputs_id_mask: {},
        inputs_data: {},
    };

    if(parametres !== undefined) {
        $.each(parametres, function(key, value) {
            object.parameters[key] = value;
        });
    }

    object.run = function (parameters) {
        if(parameters !== undefined)
            object.build_parameters(parameters);

        object.baze_set_keys();
        object.baze_first_load();

        object.set_keys();
        object.first_load();

        object.build_form();
    };

    object.create_form = function() {

    };

    object.set_keys = function () {

    };

    object.first_load = function () {

    };

    object.succsess_function = function () {

    };

    object.error_function = function () {

    };

    object.baze_first_load = function() {

    };

    object.baze_set_keys = function() {
        $(document).on('click', '#' + object.parameters.submit_button_id, object.submit_action);

        $(document).on('change', 'input', function() {
            object.erase_error (this);  });

        $(document).on('change', 'textarea', function() {
            object.erase_error (this);  });
    };

    object.pack_mask_in_array = function(mask) {
        var first_letter = mask.charAt(0);
        var last_letter = mask[mask.length - 1];

        if(first_letter === '*') {
            var postfix = mask.substring(mask.length - (mask.length-1));
            var all_postfix_inputs = $('input[id$="' + postfix +  '"]');

            var out_postfix_params = {};
            $.each(all_postfix_inputs, function(id_key, id_value) {
                if(id_value.type === 'checkbox' || id_value.type === 'radio')
                    out_postfix_params[id_value.id.substring(0, (id_value.id.length-postfix.length))] = id_value.checked ? id_value.value : '';
                else
                    out_postfix_params[id_value.id.substring(0, (id_value.id.length-postfix.length))] = id_value.value;
            });

            return out_postfix_params;
        }
        else if(last_letter === '*') {
            var prefix = mask.substring(0, (mask.length-1));
            var all_inputs = $('input[id^="' + prefix +  '"]');

            var out_params = {};

            $.each(all_inputs, function(id_key, id_value) {
                if(id_value.type === 'checkbox' || id_value.type === 'radio')
                    out_params[id_value.id.substring(prefix.length, id_value.id.length)] = id_value.checked ?  id_value.value : '';
                else
                    out_params[id_value.id.substring(prefix.length, id_value.id.length)] = id_value.value;
            });

            return out_params;
        }
        else return {};
    };

    object.get_submit_params = function () {

        //готовим описание инпутов формы
        var submit_params = {};
        object.parameters.inputs_id.forEach (function(param, i) {
            submit_params[param] = $('#' + param).val();
        });

        //Защита от межсайтового скриптинга
        submit_params['formid'] = object.parameters.form_id;

        //Добавляем к запросы то, что должно придти по дефолту
        $.each(object.parameters.inputs_data, function(param_name, param_value) {
            submit_params[param_name] = param_value;
        });

        //поиск по маске и упаковка в массив
        $.each(object.parameters.inputs_id_mask, function(param_name, param_value) {
            submit_params[param_name] = object.pack_mask_in_array(param_value);
        });

        return submit_params;
    };

    object.build_parameters = function (parameters) {
        $.each(parameters, function(key, value) {
            object.parameters[key] = value;
        });
    };

    object.build_form = function() {
        var parameters = object.create_form();

        object.build_parameters(parameters);
    };

    object.display_errors = function(errors) {
        $.each(errors, function(key, value) {
            if(value) {
                $('#Error' + key).html(value);
                $('#Error' + key).show(value);
                $('#' + key).addClass('is-invalid');
            } else {
                $('#' + key).removeClass('is-invalid');
                $('#Error' + key).hide(value);
            }
        });
    };

    object.erase_error  = function(object) {
        var edited_input = $(object).attr('id');
        $('#Error' + edited_input).html('');
        $('#' + edited_input).removeClass('is-invalid');
    };

    object.show_error= function(message) {
        $('#JsMainErrorBody').html(message);

        var JsMainError = new bootstrap.Modal(document.getElementById('JsMainError'), {
            keyboard: false
        });
        JsMainError.show();
    };

    object.show_loading = function(message) {
        $('#' + object.parameters.submit_button_id).addClass('disabled');
        $('#' + object.parameters.submit_button_loading).show();
    };

    object.hide_loading = function(message) {
        $('#' + object.parameters.submit_button_id).removeClass('disabled');
        $('#' + object.parameters.submit_button_loading).fadeOut(1000);
    };

    object.scroll_top = function(message) {
        $('html, body').animate({scrollTop: 0}, 200);
    };

    object.show_head_message = function(container_message_id) {
        $(document).on('click', '#' + container_message_id + '_close', function() {
            $('#' + container_message_id).fadeOut(400); });

        object.scroll_top();
        $('#' + container_message_id).fadeIn(800);
    };

    object.query = function() {
        object.show_loading();

        $.post(object.parameters.api_link, object.get_submit_params(),
            function(data) {
                object.hide_loading();

                if(object.parameters.hard_debug)
                    alert(data);

                try {
                    var response = JSON.parse(data);

                    if(response.api_error)
                        object.show_error(response.api_error);
                    else if(response.formerror) {
                        object.display_errors(response.formerror);
                        object.error_function();
                    }
                    else {
                        if(object.parameters.success_location)
                            window.location = object.parameters.success_location;
                        else if(response.location)
                            window.location = response.location;
                        else if(object.parameters.run_succsess_function)
                            object.succsess_function(response);
                        else if(object.parameters.show_head_message)
                            object.show_head_message(object.parameters.show_head_message);
                    }
                } catch (e) {
                    object.show_error(data);
                }
            }
        );
    };

    object.submit_action = function() {
        object.query();
    };

    return object;
}