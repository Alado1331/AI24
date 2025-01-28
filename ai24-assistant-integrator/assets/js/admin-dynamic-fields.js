jQuery(document).ready(function($) {
    $('#add_more_keys').click(function(e) {
        e.preventDefault();
        var newIndex = $('#api_keys_container .api_key_field').length;
        var newField = '<div class="api_key_field" style="margin-bottom: 20px;">' +
                       '<div style="margin-bottom: 5px;">' +
                       '<label for="api_key_name_' + newIndex + '">Identifier:</label>' +
                       '<input type="text" id="api_key_name_' + newIndex + '" name="AI24AI_api_keys[' + newIndex + '][name]" class="regular-text" placeholder="Unique Identifier" />' +
                       '</div>' +
                       '<div style="margin-bottom: 5px;">' +
                       '<label for="api_key_value_' + newIndex + '">Secret:</label>' +
                       '<input type="password" id="api_key_value_' + newIndex + '" name="AI24AI_api_keys[' + newIndex + '][key]" class="regular-text" placeholder="Secret Key" />' +
                       '</div>' +
                       '<button type="button" class="remove_field button">Remove</button>' +
                       '</div>';
        $('#api_keys_container').append(newField);
    });

    $(document).on('click', '.remove_field', function() {
        $(this).parent('.api_key_field').remove();
    });
    
    $(document).on('click', '.toggle_visibility', function() {
        var $this = $(this);
        var $passwordField = $this.prev('input[type="password"], input[type="text"]');
        if ($passwordField.attr('type') === 'password') {
            $passwordField.attr('type', 'text');
            $this.text('Hide');
        } else {
            $passwordField.attr('type', 'password');
            $this.text('Show');
        }
    });
});