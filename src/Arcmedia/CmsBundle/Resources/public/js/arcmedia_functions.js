/**
 * Created by Jose on 22/04/16.
 */
$.fn.extend({
    activate_action: function(){
        var button = $(this);
        var action = button.hasClass('activate') ? 'activate' : 'unactivate';
        var new_class = action == 'activate' ? 'unactivate' : 'activate';
        $.ajax({
            type: 'PUT',
            url: Routing.generate(action, {'page_id': $(this).closest('tr').data('pid')}),
            beforeSend: function(){
                button.prop('disabled', true);
                button.removeClass(action);
                button.find('span.glyphicon-refresh-animate').show();
            }
        }).done(function (html) {
            var td = button.closest('td');
            button.addClass(new_class);
            button.prop('disabled', false);
            td.html(html.html);
            toastr.success($('input#success').val());
        }).fail(function (data) {
            toastr.error($('input#failed').val());
            button.prop('disabled', false);
            button.find('span.glyphicon-refresh-animate').hide();
        });
        return false;
    },

    delete_action: function(){
        var button = $(this);
        var dialog = $('div.delete-page');

        $.ajax({
            type: 'DELETE',
            url: Routing.generate('delete', {'page_id': $('div.delete-page-confirm').find('input[name=pchosen]').val()}),
            beforeSend: function(){
                button.prop('disabled', true);
                button.find('span.glyphicon-refresh-animate').show();
            }
        }).done(function (html) {
            button.addClass('');
            toastr.success($('input#success').val());
            dialog.modal();
            setTimeout(function(){
                window.location.reload()
            },  300);
        });
    }
});

$(function () {
    var table = $('table');
    var body = $('body');
    var dialog = $('div.delete-page');

    table.on('click', 'button.unactivate', $.fn.activate_action);
    table.on('click', 'button.activate', $.fn.activate_action);

    $('button.btn-close').click(function(){
        $(this).closest('div.alert').hide('slow');
    });


    $('td').on('click', 'a.delete',function(){
        dialog.modal();
        dialog.find('input[name=pchosen]').val($(this).closest('tr').data('pid'));
    });

    dialog.on('hidden.bs.modal', function () {
        $(this).find('input[name=pchosen]').val('');
        $(this).find('button.confirm-delete').prop('disabled', false);
    });

    body.on('click', 'button.confirm-delete', $.fn.delete_action);

});