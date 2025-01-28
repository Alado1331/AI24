jQuery(document).ready(function($) {
    // alert('Regenerate script loaded'); // Debugging alert
    $("#AI24AI_functions_regenerate_button").click(function() {
        // alert('Regenerate button clicked'); // Debugging alert
        // console.log('Regenerate button clicked'); // Console log for further debugging

        var data = { action: "AI24AI_regenerate_function" };

        $.post(ajaxurl, data, function(response) {
            // console.log(response); // Debugging line
            if (response.success) {
                alert(response.data);
            } else {
                alert("Error: " + response.data);
            }
        }).fail(function(xhr, status, error) {
            // console.log("AJAX Error: ", status, error);
            alert("AJAX Error: " + status + ", " + error); // Debugging alert
        });
    });
});
