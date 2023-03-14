$(function () {
    $('.select2').select2();
    $('.datepicker').datepicker({
        autoclose: true,
        format: 'yyyy-mm-dd',
        orientation: "bottom auto"
    });
});

function changeTimePeriod(time) {
    $('#start_date, #end_date').val('');
    if (time == 'custom') {
        $('.custom-period').show();
        $('#start_date, #end_date').attr('required', true);
    } else {
        $('.custom-period').hide();
        $('#start_date, #end_date').attr('required', false);
    }
}

function downloadReport(e, type) {
    var $btn = $(e);
    $btn.button('loading');

    var deviceId = $('#device_id').val();
    var reportType = $('#report_type').val();
    var timePeriod = $('#time_period').val();
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();

    var obj = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        device_id: deviceId,
        report_type: reportType,
        time_period: timePeriod,
        start_date: startDate,
        end_date: endDate,
    };
    $.post(adminUrl + '/report/download/' + type, obj, function (res) {
        if (res.status == 200) {
            var link = document.createElement("a");
            link.download = res.file_name;
            link.href = res.path;
            link.click();
        } else {
            toastr.error(res.message);
        }
        $btn.button('reset');
    }).fail(function (error) {
        toastr.error('Ooops...Something went wrong. Please try again.');
        $btn.button('reset');
    });
}

$(function () {
    $.validator.addMethod('checkdate', function (value, element) {
        var timePeriod = $('#time_period').val();
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();

        if (timePeriod == 'custom') {
            if (Date.parse(endDate) > Date.parse(startDate)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }, 'End date should be greater than Start date');

    $("#frmReport").validate({
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
            end_date: {
                checkdate: true
            }
        },
        messages: {
            end_date: {
                checkdate: 'End date should be greater than Start date!'
            }
        },

        focusInvalid: false,
        submitHandler: function (form) {
            var $btn = $('#btnSubmit');
            $btn.button('loading');
            $('.alert').hide();
            $('#device_report').hide();

            $.ajax({
                url: $('#frmReport').attr('action'),
                type: "POST",
                data: new FormData(form),
                contentType: false,
                cache: false,
                processData: false,
                success: function (res) {
                    var message = res.message;
                    if (res.status == 200) {
                        var data = res.data;

                        $('.hidden_device_id').val($('#device_id').val());
                        $('.hidden_report_type').val($('#report_type').val());
                        $('.hidden_time_period').val($('#time_period').val());
                        $('.hidden_start_date').val($('#start_date').val());
                        $('.hidden_end_date').val($('#end_date').val());

                        var deviceData = data.device_detail;
                        $('#current_datetime').html(data.current_datetime);
                        $('#device_name, #report_device_name').html(deviceData.device_name);
                        $('#report_device_imei').html(deviceData.device_sn);
                        $('#report_device_facility').html(deviceData.type_of_facility);

                        $('#report_device_interval').html(deviceData.data_interval);
                        $('#report_device_alarm_count').html(data.alarm_list.length);
                        $('#report_device_log_count').html(data.device_log_list.length);

                        var alarmTbody = '';
                        if (data.alarm_list.length > 0) {
                            $.each(data.alarm_list, function (index, alarm) {
                                var num = parseInt(index) + 1;
                                alarmTbody += '<tr>';
                                alarmTbody += '<td>' + num + '</td>';
                                alarmTbody += '<td>' + alarm.name + '</td>';
                                alarmTbody += '<td>' + alarm.type + '</td>';
                                alarmTbody += '<td>' + alarm.time + '</td>';
                                alarmTbody += '<td>' + alarm.date + '</td>';
                                alarmTbody += '</tr>';
                            });
                        } else {
                            alarmTbody = '<tr>';
                            alarmTbody += '<td colspan="5" class="text-center">No alarm available!</td>';
                            alarmTbody += '</tr>';
                        }
                        $('#device-alarm-list').find('tbody').html(alarmTbody);

                        var logTbody = '';
                        if (data.device_log_list.length > 0) {
                            $.each(data.device_log_list, function (index, log) {
                                var num = parseInt(index) + 1;
                                logTbody += '<tr>';
                                logTbody += '<td>' + num + '</td>';
                                logTbody += '<td style="color:' + log.device_color + ';"><strong>' + log.temperature + '℃</strong></td>';
                                logTbody += '<td style="color:' + log.device_color + ';"><strong>' + log.humidity + '%</strong></td>';
                                logTbody += '<td>' + log.created_at + '</td>';
                                logTbody += '</tr>';
                            });
                        } else {
                            logTbody = '<tr>';
                            logTbody += '<td colspan="4" class="text-center">No device log found</td>';
                            logTbody += '</tr>';
                        }

                        $('#report-device-log').find('tbody').html(logTbody);

                        $('#report_start_date').html(data.start_date);
                        $('#report_end_date').html(data.end_date);
                        $('#report_data_count').html(data.data_count);

                        $('#device_report').show();

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
