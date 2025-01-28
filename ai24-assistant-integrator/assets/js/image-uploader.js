jQuery(document).ready(function($) {
    $('#AI24AI_assistant_image_button').click(function(e) {
        e.preventDefault();
        var imageUploader = wp.media({
            'title': uploadImageText,
            'button': {
                'text': useThisImageText
            },
            'multiple': false
        }).on('select', function() {
            var selection = imageUploader.state().get('selection').first();
            if (selection) {
                var attachment = selection.toJSON();
                if (attachment.url) {
                    $('#AI24AI_assistant_image').val(attachment.url);
                }
            }
        }).open();
    });
});
