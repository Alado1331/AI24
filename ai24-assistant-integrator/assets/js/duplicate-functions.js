jQuery(document).ready(function($) {
    $('#AI24AI_functions_duplicate_button').on('click', function(e) {
        e.preventDefault();

        var fileName = $('#duplicate-file-name').val().trim();
        if (!fileName) {
            alert('Please enter a file name.');
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'AI24AI_duplicate_function_file',
                file_name: fileName,
            },
            success: function(response) {
                if (response.success) {
                    alert('File duplicated successfully.');
                } else {
                    alert('Failed to duplicate file: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while duplicating the file.');
            }
        });
    });
});
