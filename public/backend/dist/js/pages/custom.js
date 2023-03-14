function getDevice(company_id) {
    var option = '<option value="">Select Sensor</option>';
    $('#device_id').html('<option value="" selected>loading...</option>').trigger('changed');
    if (company_id) {
        $.get(adminUrl + '/sensor/' + company_id + '/list', function (response) {
            $.each(response.data, function (i, device) {
                option += '<option value="' + device.id + '">' + device.device_name + '</option>';
            });
            $('#device_id').html(option).trigger('changed');
        }).fail(function (error) {
            $('#device_id').html(option).trigger('changed');
        });
    } else {
        $('#device_id').html(option).trigger('changed');
    }
}

function getGroup(company_id) {
    var option = '<option value="">Select Group</option>';
    $('#group_id').html('<option value="" selected>loading...</option>').trigger('changed');
    if (company_id) {
        $.get(adminUrl + '/group/' + company_id + '/list', function (response) {
            $.each(response.data, function (i, group) {
                option += '<option value="' + group.id + '">' + group.name + '</option>';
            });
            $('#group_id').html(option).trigger('changed');
        }).fail(function (error) {
            $('#group_id').html(option).trigger('changed');
        });
    } else {
        $('#group_id').html(option).trigger('changed');
    }
}
