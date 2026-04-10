(function ($) {
    'use strict';

    // Media uploader for image fields
    $(document).on('click', '.mariage-upload-btn', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        var input = $('#' + target);
        var preview = $('#' + target + '_preview');
        var removeBtn = $(this).siblings('.mariage-remove-btn');

        var frame = wp.media({
            title: 'Choisir une image',
            button: { text: 'Utiliser cette image' },
            multiple: false,
            library: { type: 'image' }
        });

        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            var url = attachment.sizes && attachment.sizes.medium
                ? attachment.sizes.medium.url
                : attachment.url;

            input.val(attachment.url);
            preview.html('<img src="' + url + '" alt="">');
            removeBtn.show();
        });

        frame.open();
    });

    // Remove image
    $(document).on('click', '.mariage-remove-btn', function (e) {
        e.preventDefault();
        var target = $(this).data('target');
        $('#' + target).val('');
        $('#' + target + '_preview').html('<img src="" alt="" style="display:none">');
        $(this).hide();
    });

    // Delete photo
    $(document).on('click', '.mariage-delete-photo-btn', function (e) {
        e.preventDefault();
        if (!confirm('Supprimer cette photo ?')) return;

        var btn = $(this);
        var id = btn.data('id');
        var card = $('#photo-card-' + id);

        $.post(mariageAdmin.ajaxurl, {
            action: 'mariage_delete_photo',
            nonce: mariageAdmin.nonce,
            photo_id: id
        }, function (response) {
            if (response.success) {
                card.addClass('mariage-fade-out');
                setTimeout(function () { card.remove(); }, 300);
            }
        });
    });

    // Delete RSVP / Questionnaire row
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
                row.addClass('mariage-fade-out');
                setTimeout(function () { row.remove(); }, 300);
            }
        });
    });

})(jQuery);
