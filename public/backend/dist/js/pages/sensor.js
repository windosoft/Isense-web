function deleteSensor(e, uuid) {
    var modelId = $('#myModal');
    $.get(adminUrl + '/sensor/' + uuid + '/delete', function (response) {
        modelId.html(response);
        modelId.modal({
            backdrop: 'static',
            keyboard: false
        });
    }).fail(function (error) {
        toastr.error('Ooops...Something went wrong. Please try again.');
    });
}

function getBranch(company_id) {
    var option = '<option value="">Select Branch</option>';
    $('#branch_id').html('<option value="">loading...</option>').trigger('changed');
    if (company_id) {
        $.get(adminUrl + '/branches/' + company_id + '/list', function (response) {

            $.each(response.data, function (i, branch) {
                option += '<option value="' + branch.id + '">' + branch.name + '</option>';
            });
            $('#branch_id').html(option).trigger('changed');
        }).fail(function (error) {
            $('#branch_id').html(option).trigger('changed');
        });
    } else {
        $('#branch_id').html(option).trigger('changed');
    }
}

function getGateway(branch_id) {
    var option = '<option value="">Select Gateway</option>';
    $('#terminal_id').html('<option value="">loading...</option>').trigger('changed');
    if (branch_id) {
        $.get(adminUrl + '/gateway/' + branch_id + '/list', function (response) {

            $.each(response.data, function (i, terminal) {
                option += '<option value="' + terminal.id + '">' + terminal.name + '</option>';
            });
            $('#terminal_id').html(option).trigger('changed');
        }).fail(function (error) {
            $('#terminal_id').html(option).trigger('changed');
        });
    } else {
        $('#terminal_id').html(option).trigger('changed');
    }
}

$(function () {
    $("#frmSensor").validate({
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
                url: $('#frmSensor').attr('action'),
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
