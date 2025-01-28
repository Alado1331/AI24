// image-uploader-toggle.js
jQuery(document).ready(function($) {
    $(document).on('click', '#AI24AI_toggle_image_button', function(e) {
        e.preventDefault();
        
        // If the media frame already exists, reopen it.
        if ( typeof imageUploader !== 'undefined' ) {
            imageUploader.open();
            return;
        }

        // Create the media frame.
        imageUploader = wp.media.frames.file_frame = wp.media({
            title: ai24aiToggleImage.uploadIconText,
            button: {
                text: ai24aiToggleImage.useIconText,
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        imageUploader.on('select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = imageUploader.state().get('selection').first().toJSON();
            
            // Do something with attachment.id and/or attachment.url here
            $('#AI24AI_toggle_image').val(attachment.url);
            $('#AI24AI_toggle_image_preview').attr('src', attachment.url).css('display', 'block');
        });

        // Finally, open the modal
        imageUploader.open();
    });
});
