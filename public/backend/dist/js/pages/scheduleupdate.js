function deleteSchedule(e, uuid) {
    var modelId = $('#myModal');
    $.get(adminUrl + '/schedule/' + uuid + '/delete', function (response) {
        modelId.html(response);
        modelId.modal({
            backdrop: 'static',
            keyboard: false
        });
    }).fail(function (error) {
        toastr.error('Ooops...Something went wrong. Please try again.');
    });
}
function getSensor(company_id){
    $.get(adminUrl+'/schedule/getsensor/'+company_id,function (data) {
        var sensors = data.data;
        var option = '<option value="">Select Sensor</option>';
        $.each(sensors, function (i, sensor) {
            option += '<option value="' + sensor.id + '">' + sensor.device_name + '</option>';
        });
        $('#sensor_id').html(option).select2();
    });
}
function changeSuType(suType){
    $('#su_week_day_box').hide();
    $('#su_month_date_box').hide();
    if(suType){
        if(suType == 2){
            $('#su_week_day_box').show();
            $('#su_week_day').select2();
        }
        if(suType == 3){
            $('#su_month_date_box').show();
            $('#su_month_date').select2();
        }
    }
}
$(function () {
    $("#frmSchedule").validate({
        errorElement: 'span',
        errorClass: 'help-block error-help-block',
        errorPlacement: function (error, element) {
            if (element.hasClass('select2')) {
                error.insertAfter(element.parent().find('span.select2'));
            } else if (element.parent('.input-group').length ||
                element.prop('type') === 'checkbox' || element.prop('type') === 'radio') {
                error.insertAfter(element.parent());
                // else just place the validation message immediatly after the input
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error'); // add the Bootstrap error class to the control group
        },

        success: function (element) {
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // remove the Boostrap error class from the control group
        },

        focusInvalid: false,
        submitHandler: function (form) {
            var $btn = $('#btnSubmit');
            $btn.button('loading');
            $('.alert').hide();

            $.ajax({
                url: $('#frmSchedule').attr('action'),
                type: "POST",
                data: new FormData(form),
                contentType: false,
                cache: false,
                processData: false,
                success: function (res) {
                    var message = res.message;
                    if (res.status == 200) {
                        localStorage.setItem('message', message);
                        window.location = res.redirect;
                    } else {
                        $('.alert-danger').show().html(message);
                    }
                    $btn.button('reset');
                },
                error: function (err) {
                    $('.alert-danger').show().html('Ooops...Something went wrong. Please try again.');
                    $btn.button('reset');
                    $btn.attr('disabled', false);
                }
            });
        }
    });
});
