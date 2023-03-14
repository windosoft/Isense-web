function viewNotification(e, uuid) {
    var modelId = $('#myModal');
    $.get(adminUrl + '/notification/' + uuid, function (response) {
        modelId.html(response);
        modelId.modal({
            backdrop: 'static',
            keyboard: false
        });
        $('.row_' + uuid).removeClass('info');
    }).fail(function (error) {
        toastr.error('Ooops...Something went wrong. Please try again.');
    });
}

function deleteNotification(e, uuid) {
    var modelId = $('#myModal');
    $.get(adminUrl + '/notification/' + uuid + '/delete', function (response) {
        modelId.html(response);
        modelId.modal({
            backdrop: 'static',
            keyboard: false
        });
    }).fail(function (error) {
        toastr.error('Ooops...Something went wrong. Please try again.');
    });
}

function destroyNotification(e, uuid) {
    var $btn = $(e);
    $btn.button('loading');
    $('.alert').hide();

    var obj = {
        '_token': $('meta[name="csrf-token"]').attr('content'),
        '_method': 'DELETE',
    };
    $.post(adminUrl + '/notification/' + uuid, obj, function (res) {
        var message = res.message;
        if (res.status == 200) {
            localStorage.setItem('message', message);
            window.location = res.redirect;
        } else {
            $('.alert-danger').show().html(message);
            $btn.button('reset');
        }
    }).fail(function (error) {
        $('.alert-danger').show().html('Ooops...Something went wrong. Please try again.');
        $btn.button('reset');
    });
}

function deleteMultipleNotification() {
    var ids = [];
    $('.notification_input').each(function (k, input) {
        if ($(input).prop('checked') == true) {
            ids.push($(input).val());
        }
    });

    if (ids.length > 0) {
        var modelId = $('#myModal');
        var obj = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ids: ids
        };
        $.post(adminUrl + '/notification/multi-delete', obj, function (response) {
            modelId.html(response);
            modelId.modal({
                backdrop: 'static',
                keyboard: false
            });
        });
    } else {
        toastr.error('Please select the notification');
    }
}

function destroyMultiNotification(e) {
    var $btn = $(e);
    $btn.button('loading');
    $('.alert').hide();

    var ids = [];
    $('.notification-uuid').each(function (k, notify) {
        ids.push($(notify).data('uuid'));
    });

    var obj = {
        '_token': $('meta[name="csrf-token"]').attr('content'),
        '_method': 'DELETE',
        'ids': ids
    };
    $.post(adminUrl + '/notification/multi-delete', obj, function (res) {
        var message = res.message;
        if (res.status == 200) {
            localStorage.setItem('message', message);
            window.location = res.redirect;
        } else {
            $('.alert-danger').show().html(message);
            $btn.button('reset');
        }
    }).fail(function (error) {
        $('.alert-danger').show().html('Ooops...Something went wrong. Please try again.');
        $btn.button('reset');
    });
}
