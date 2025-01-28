jQuery(document).ready(function($) {
    // alert('Generate script loaded'); // Debugging alert
    $("#AI24AI_functions_generate_button").click(function() {
        // alert('Generate button clicked'); // Debugging alert
        // console.log('Generate button clicked'); // Console log for further debugging

        var data = { action: "AI24AI_generate_function" };

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
