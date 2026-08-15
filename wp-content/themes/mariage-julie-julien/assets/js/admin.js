(function ($) {
    'use strict';

    // Delete RSVP row
    $(document).on('click', '.mariage-delete-btn', function (e) {
        e.preventDefault();
        if (!confirm('Supprimer cette reponse ?')) return;

        var btn = $(this);
        var type = btn.data('type');
        var id = btn.data('id');
        var row = $('#' + type + '-row-' + id);

        $.post(mariageAdmin.ajaxurl, {
            action: 'mariage_delete_' + type,
            nonce: mariageAdmin.nonce,
            item_id: id
        }, function (response) {
            if (response.success) {
                row.fadeOut(300, function () { row.remove(); });
            }
        });
    });

})(jQuery);
