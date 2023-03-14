function deleteTemperatures(e, uuid) {
    var modelId = $('#myModal');
    $.get(adminUrl + '/temperatures/' + uuid + '/delete', function (response) {
        modelId.html(response);
        modelId.modal({
            backdrop: 'static',
            keyboard: false
        });
    }).fail(function (error) {
        toastr.error('Ooops...Something went wrong. Please try again.');
    });
}

$(function () {
    $.validator.addMethod('checkhighwarn', function (value, element) {
        var lw = $('#low_temp_warning').val();
        var hw = $('#high_temp_warning').val();

        if (parseFloat(hw) > parseFloat(lw)) {
            return true;
        } else {
            return false;
        }
    }, 'The high warning should be larger than low warning');

    $.validator.addMethod('checklowthres', function (value, element) {
        var lw = $('#low_temp_warning').val();
        var lt = $('#low_temp_threshold').val();

        if (parseFloat(lt) < parseFloat(lw)) {
            return true;
        } else {
            return false;
        }
    }, 'The low threshold should be smaller than low warning');

    $.validator.addMethod('checkhighthres', function (value, element) {
        var hw = $('#high_temp_warning').val();
        var ht = $('#high_temp_threshold').val();

        if (parseFloat(ht) > parseFloat(hw)) {
            return true;
        } else {
            return false;
        }
    }, 'The high threshold should be larger than high warning');

    $.validator.addMethod('checkdate', function (value, element) {
        var startDate = $('#effective_start_date').val();
        var endDate = $('#effective_end_date').val();

        if (Date.parse(endDate) > Date.parse(startDate)) {
            return true;
        } else {
            return false;
        }
    }, 'End date should be greater than Start date');

    $.validator.addMethod('checktime', function (value, element) {
        var startTime = $('#effective_start_time').val().split(":");
        var timefrom = new Date();
        timefrom.setHours((parseInt(startTime[0]) + 24) % 24);
        timefrom.setMinutes(parseInt(startTime[1]));

        var endTime = $('#effective_end_time').val().split(":");
        var timeto = new Date();
        timeto.setHours((parseInt(endTime[0]) + 24) % 24);
        timeto.setMinutes(parseInt(endTime[1]));

        if (timeto > timefrom) {
            return true;
        } else {
            return false;
        }
    }, 'End time should be larger than start time!');

    $("#frmTemperatures").validate({
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

        rules: {
            high_temp_warning: {
                checkhighwarn: true
            },
            low_temp_threshold: {
                checklowthres: true
            },
            high_temp_threshold: {
                checkhighthres: true
            },
            effective_end_date: {
                checkdate: true
            },
            effective_end_time: {
                checktime: true
            }
        },
        messages: {
            high_temp_warning: {
                checkhighwarn: "The high warning should be larger than low warning"
            },
            low_temp_threshold: {
                checklowthres: 'The low threshold should be smaller than low warning'
            },
            high_temp_threshold: {
                checkhighthres: 'The high threshold should be larger than high warning'
            },
            effective_end_date: {
                checkdate: 'End date should be greater than Start date!'
            },
            effective_end_time: {
                checktime: 'End time should be larger than start time!'
            }
        },

        focusInvalid: false,
        submitHandler: function (form) {
            var $btn = $('#btnSubmit');
            $btn.button('loading');
            $('.alert').hide();

            $.ajax({
                url: $('#frmTemperatures').attr('action'),
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
