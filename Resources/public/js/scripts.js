$(function () {
    $(document).on('refresh', '[data-refresh]', function (e, params) {
        var action = $(this).data('refresh');
        if (!action) {
            bootbox.alert('An action was completed, but the elements on the page were not refreshed.');
            return;
        }
        params = params || {};
        if (params.element) {
            $(params.element).tooltip('hide');
            $(params.element).popover('hide');
        }
        $(this).block({
            message: '<i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>',
            element: $(this)
        });
        var that = this;
        $.ajax({
            url: action,
            type: 'GET'
        }).done(function (html) {
            $(that).unblock();
            $(that).html(html);
            $(that).trigger('refreshed');
        });
    });
    $(document).on('click', '[data-trigger="refresh"]', function (e) {
        $(this).parents('[data-refresh]').trigger('refresh', {
            element: $(this)
        });
    });
    $.fn.select2.defaults.set('theme', 'bootstrap');
    $('select.select2').select2();
});