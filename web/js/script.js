$(function() {

    var $controls = $('[data-control]'),
        translateUrl = $('#parameters').data('url')
    ;
    $controls.on('change', function() {
        $(this).parent().submit();
    });

    $('textarea[data-trans-id]')
        .blur(function() {
            var self = this;
            $.ajax(translateUrl + '?id=' + encodeURIComponent($(this).data('transId')), {
                type: 'POST',
                headers: {'X-HTTP-METHOD-OVERRIDE': 'PUT'},
                data: {'_method': 'PUT', 'message': $(this).val()},
                error: function() {
                    $(self).parent().append('<div class="alert alert-danger">Translation could not be saved</div>');
                },
                success: function() {
                    $(self).parent().append('<div class="alert alert-success">Translation was saved.</div>');
                },
                complete: function() {
                    var parent = $(self).parent();
                    $(self).data('timeoutId', setTimeout(function() {
                        $(self).data('timeoutId', undefined);
                        parent.children('.alert').fadeOut(500, function() { $(this).remove(); });
                    }, 3000));
                }
            });
        })
    ;
});
