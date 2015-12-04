$(function() {

    var $controls = $('[data-control]');
    $controls.on('change', function() {
        $(this).parent().submit();
    });

});
